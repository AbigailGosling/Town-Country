<?php

use App\Helpers\InternalCache;
use App\Helpers\FuncHelper;
use App\Models\ClientType;
use App\Models\CollectionReturnMetadata;
use App\Models\Cut;
use App\Models\PickWeightOut;
use App\Models\PickerSheet;
use App\Models\Product;
use App\Models\TransportPallet;
use App\Models\TransportPalletPickWeight;
use App\Models\TransportPalletType;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

require(__DIR__.'/../functions.php');

try {
    $items = request()->input('items');
    $transactionId = trim((string) request()->input('transaction_id', ''));
    $estimatedDeliveryDate = trim((string) request()->input('date', ''));
    $typeSelector = trim((string) request()->input('type_selector', ''));
    $customerId = (int) request()->input('customer_id', 0);
    $addressId = (int) request()->input('addressid', 1);
    $userId = (int) request()->input('user', $_SESSION['USER'] ?? 0);
    $isSupplier = request()->input('is_supplier', 0) == 1 || request()->input('is_supplier', 0) === '1';
    $picksheetNote = request()->input('picksheet_note', null);
    $orderReferenceNumber = request()->input('orderReferenceNumber', null);
    $invoiceInput = request()->input('invoice_id', request()->input('invoice', null));
    $invoiceId = is_numeric($invoiceInput) ? (int) $invoiceInput : 0;

    if (!is_array($items) || count($items) === 0) {
        throw new Exception('No items supplied.');
    }
    if ($transactionId === '') {
        throw new Exception('Missing transaction id.');
    }
    if ($estimatedDeliveryDate === '') {
        throw new Exception('Missing delivery date.');
    }
    if ($customerId <= 0) {
        throw new Exception('Missing customer/supplier id.');
    }
    if ($userId <= 0) {
        throw new Exception('Missing user id.');
    }
    if ($typeSelector !== 'return' && $typeSelector !== 'collection') {
        throw new Exception('Invalid type selector.');
    }

    $transactionCacheKey = 'build_collection_transaction:' . $transactionId;
    if (InternalCache::has($transactionCacheKey)) {
        throw new Exception('Duplicate transaction.');
    }
    InternalCache::put($transactionCacheKey, true, now()->addHours(12));

    $parsedDeliveryDate = Carbon::createFromFormat('d/m/Y', $estimatedDeliveryDate)->format('Y-m-d');

    startTransaction();

    $transportPallet = TransportPallet::create([
        'transport_pallet_type_id' => TransportPalletType::where('name', 'Unknown')->first()->id,
        'customer_id' => $customerId,
        'address_id' => $addressId > 0 ? $addressId : 1,
        'estimated_delivery_date' => $parsedDeliveryDate,
        'dispatched' => false,
        'pod_sent' => false,
        'inbound' => true,
        'client_type' => ($invoiceId > 0) ? "customer": "supplier",
    ]);

    $createdWeightCount = 0;
    if ($invoiceId > 0) {
        $sourcePick = PickerSheet::query()->find($invoiceId);
        if (!$sourcePick || (int) $sourcePick->deleted === 1) {
            throw new Exception('Invoice picksheet not found.');
        }
        if ((int) $sourcePick->customer_id !== $customerId) {
            throw new Exception('Invoice does not belong to selected customer.');
        }

        $sourceWeightIds = [];
        foreach ($sourcePick->pickWeightOuts as $sourcePwo) {
            $sourceWeightIds = array_merge($sourceWeightIds, $sourcePwo->getWeights());
        }
        $sourceWeightIds = array_values(array_unique(array_map('intval', $sourceWeightIds)));
        if (count($sourceWeightIds) === 0) {
            throw new Exception('Invoice has no linked weights.');
        }

        $weightsByProduct = [];
        $invoiceWeights = Weight::query()
            ->select(['id', 'product_id'])
            ->whereIn('id', $sourceWeightIds)
            ->orderBy('id')
            ->get();
        foreach ($invoiceWeights as $invoiceWeight) {
            $productKey = (int) $invoiceWeight->product_id;
            if (!isset($weightsByProduct[$productKey])) {
                $weightsByProduct[$productKey] = [];
            }
            $weightsByProduct[$productKey][] = (int) $invoiceWeight->id;
        }

        $consumedWeightIds = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $amount = (int) ($item['amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $productId = isset($item['product_id']) && is_numeric($item['product_id']) ? (int) $item['product_id'] : 0;
            if ($productId <= 0) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name !== '') {
                    $match = Product::query()
                        ->select(['product.id'])
                        ->join('cuts', 'cuts.id', '=', 'product.cut_id')
                        ->whereIn('product.id', array_keys($weightsByProduct))
                        ->whereRaw("TRIM(CONCAT(IFNULL((SELECT name FROM species WHERE id = cuts.species_id LIMIT 1), ''), ' ', IFNULL(cuts.name, ''))) = ?", [$name])
                        ->orderBy('product.id')
                        ->first();
                    if ($match) {
                        $productId = (int) $match->id;
                    }
                }
            }
            if ($productId <= 0 || !isset($weightsByProduct[$productId])) {
                continue;
            }

            $availableIds = array_values(array_diff($weightsByProduct[$productId], $consumedWeightIds));
            if (count($availableIds) === 0) {
                continue;
            }
            $selectedIds = array_slice($availableIds, 0, $amount);
            if (count($selectedIds) === 0) {
                continue;
            }

            $pickWeightOut = PickWeightOut::create([
                'pickersheet_id' => -1,
                'weight_ids' => implode(',', $selectedIds),
                'stringName' => 'collection:' . $transactionId . ':' . $productId,
                'picker_ids' => (string) $userId,
            ]);

            TransportPalletPickWeight::create([
                'transport_pallet_id' => $transportPallet->id,
                'pickWeightOut_id' => $pickWeightOut->id,
            ]);

            $consumedWeightIds = array_merge($consumedWeightIds, $selectedIds);
            $createdWeightCount += count($selectedIds);
        }
    } else {
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = trim((string) ($item['name'] ?? ''));
            $amount = (int) ($item['amount'] ?? 0);
            $weight = FuncHelper::floorDec((float) ($item['weight'] ?? 0), 3);
            $cost = FuncHelper::floorDec((float) ($item['cost'] ?? 0), 3);
            $unit = trim((string) ($item['unit'] ?? ''));

            if ($name === '' || $amount <= 0) {
                continue;
            }
            if ($unit === '') {
                $unit = 'C';
            }

            $cut = Cut::where('species_id', 13)->where('name', $name)->first();
            if (!$cut) {
                $cut = Cut::create([
                    'species_id' => 13,
                    'name' => $name,
                    'cutgroup_id' => -1,
                ]);
            }

            $product = Product::create([
                'pallet_id' => -1,
                'cut_id' => $cut->id,
                'brand_id' => -1,
                'nationality_id' => -1,
                'cooling_id' => -1,
                'status' => 0,
                'range_from' => null,
                'range_to' => null,
                'ubbb' => null,
                'unit' => $unit,
                'pricetype' => null,
                'cost' => $cost,
                'price' => $cost,
                'product_temp' => 1,
                'quantity' => $amount,
            ]);

            $weightIds = [];
            for ($i = 0; $i < $amount; $i++) {
                $newWeight = Weight::create([
                    'product_id' => $product->id,
                    'status_id' => 0,
                    'weight_gross' => $weight,
                    'weight_tear' => $weight,
                    'pallet_tare' => 1,
                    'tare_per_carton' => 1,
                    'number_of_cartons' => 1,
                    'original_gross' => 1,
                    'tampered' => 0,
                    'grosstare' => 0,
                ]);
                $weightIds[] = (int) $newWeight->id;
            }

            if (count($weightIds) === 0) {
                continue;
            }

            $pickWeightOut = PickWeightOut::create([
                'pickersheet_id' => -1,
                'weight_ids' => implode(',', $weightIds),
                'stringName' => 'collection:' . $transactionId . ':' . $product->id,
                'picker_ids' => (string) $userId,
            ]);

            TransportPalletPickWeight::create([
                'transport_pallet_id' => $transportPallet->id,
                'pickWeightOut_id' => $pickWeightOut->id,
            ]);

            $createdWeightCount += count($weightIds);
        }
    }

    if ($createdWeightCount === 0) {
        throw new Exception('No valid items to create.');
    }

    $approvedByInput = request()->input('approved_by', null);
    $approvedBy = is_numeric($approvedByInput) ? (int) $approvedByInput : null;
    $approvedDateInput = request()->input('approved_date', null);
    $approvedDate = null;
    if ($approvedDateInput !== null && trim((string) $approvedDateInput) !== '') {
        $approvedDate = Carbon::parse((string) $approvedDateInput);
    }

    CollectionReturnMetadata::create([
        'transport_pallet_id' => $transportPallet->id,
        'transaction_id' => $transactionId,
        'type_selector' => $typeSelector,
        'is_supplier' => $isSupplier,
        'client_id' => $customerId,
        'invoice_id' => $invoiceId > 0 ? $invoiceId : null,
        'address_id' => $addressId > 0 ? $addressId : 1,
        'estimated_delivery_date' => $parsedDeliveryDate,
        'submitted_by' => $userId,
        'picksheet_note' => $picksheetNote,
        'order_reference_number' => $orderReferenceNumber,
        'approved_by' => $approvedBy,
        'approved_date' => $approvedDate,
    ]);

    commitTransaction();
    echo (string) $transportPallet->id;
} catch (Exception $e) {
    rollbackTransaction();
    Log::error($e, [
        'script' => 'buildCollection.php',
        'request' => request()->all(),
    ]);
    http_response_code(500);
    echo 'N/A';
}
