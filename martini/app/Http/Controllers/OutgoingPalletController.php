<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OutgoingPallet;
use App\Models\OutgoingPalletPickWeight;
use App\Models\OutgoingPalletType;
use App\Models\PickerSheet;
use App\Models\PickWeightOut;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OutgoingPalletController extends Controller
{
    /**
     * Display a listing of upcoming deliveries grouped by customer address.
     */
    public function index(): View
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(3);

        $addressFields = [];
        for ($i = 1; $i <= 9; $i++) {
            $addressFields[] = "customers.address{$i}_1";
            $addressFields[] = "customers.address{$i}_2";
            $addressFields[] = "customers.address{$i}_3";
            $addressFields[] = "customers.address{$i}_4";
            $addressFields[] = "customers.postcode_{$i}";
            $addressFields[] = "customers.address{$i}_number";
        }

        $pickSheets = PickerSheet::query()
            ->select([
                'pickerSheets.id as pickersheet_id',
                'pickerSheets.customer_id',
                'pickerSheets.addressid',
                'pickerSheets.estimated_delivery_date',
                'pickerSheets.orderReferenceNumber',
                'customers.businessname',
                'customers.tradingas',
            ])
            ->addSelect($addressFields)
            ->join('customers', 'pickerSheets.customer_id', '=', 'customers.id')
            ->where('pickerSheets.deleted', 0)
            ->whereRaw(
                "STR_TO_DATE(pickerSheets.estimated_delivery_date, '%d/%m/%Y') BETWEEN ? AND ?",
                [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]
            )
            ->orderByRaw("STR_TO_DATE(pickerSheets.estimated_delivery_date, '%d/%m/%Y') asc");
            $pickSheets = $pickSheets->get();

        $expandedSheets = $pickSheets->flatMap(function ($sheet) {
            $addressIds = $this->parseAddressIds((string) $sheet->addressid);

            return collect($addressIds)->map(function ($addressId) use ($sheet) {
                $copy = clone $sheet;
                $copy->addressid = $addressId;

                return $copy;
            });
        });

        $deliveryGroups = $expandedSheets
            ->groupBy(function ($sheet) {
                return $sheet->customer_id.'|'.$sheet->addressid;
            })
            ->map(function ($sheets) {
                $first = $sheets->first();
                $customerName = $first->businessname ?: ($first->tradingas ?: '');

                // Group deliveries by date and count
                $deliveriesByDate = $sheets->groupBy(function ($sheet) {
                    return $sheet->estimated_delivery_date;
                })->map(function ($group) {
                    return count($group);
                });

                return [
                    'customer_id' => $first->customer_id,
                    'customer_name' => $customerName,
                    'address_id' => $first->addressid,
                    'address_lines' => $this->formatCustomerAddress($first, (string) $first->addressid),
                    'deliveries_by_date' => $deliveriesByDate,
                ];
            })
            ->sortBy(function ($group) {
                return mb_strtolower($group['customer_name'] ?? '');
            })
            ->values();

        return view('outgoing-pallets.index', [
            'deliveryGroups' => $deliveryGroups,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
    /**
     * Show details for a specific customer/address group.
     */
    public function details(Request $request): View
    {
        $customerId = $request->query('customer_id');
        $addressId = $request->query('address_id');

        // Find all pickersheets for this customer/address in the next 3 days
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(3);

        $pickSheets = PickerSheet::query()
            ->with('pickWeightOut')
            ->where('customer_id', $customerId)
            ->where('addressid', $addressId)
            ->where('deleted', 0)
            ->where('completed', 1)
            ->whereRaw(
                "STR_TO_DATE(estimated_delivery_date, '%d/%m/%Y') BETWEEN ? AND ?",
                [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]
            )
            ->orderByRaw("STR_TO_DATE(estimated_delivery_date, '%d/%m/%Y') asc")
            ->get();

        // Get all pickersheet IDs that are already loaded in outgoing_pallet via the pivot table
        $outgoingPallets = OutgoingPallet::where('customer_id', $customerId)
            ->where('address_id', $addressId)
            ->with(['pickWeightOuts.pickWeightOut.pickerSheet'])
            ->get();

        $loadedPickWeightOutIds = [];
        foreach ($outgoingPallets as $pallet) {
            foreach ($pallet->pickWeightOuts as $pwLink) {
                $loadedPickWeightOutIds[] = $pwLink->pickWeightOut_id;
            }
        }

        // Get all pickersheet IDs linked to these pickWeightOut IDs
        $loadedIds = PickWeightOut::whereIn('id', $loadedPickWeightOutIds)
            ->pluck('pickersheet_id')
            ->toArray();

        // Only show those not already loaded
        $unloadedPickSheets = $pickSheets->filter(function ($sheet) use ($loadedIds) {
            return !in_array($sheet->id, $loadedIds);
        });

        $palletTypes = OutgoingPalletType::all();
        $customer = Customer::find($customerId);
        return view('outgoing-pallets.details', [
            'customer' => $customer,
            'customer_id' => $customerId,
            'address_id' => $addressId,
            'deliveries' => $unloadedPickSheets,
            'outgoingPallets' => $outgoingPallets,
            'palletTypes' => $palletTypes,
        ]);
    }
    /**
     * Store a newly created outgoing pallet.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:tandc_live.customers,id',
            'address_id' => 'required|integer',//|exists:address,id
        ]);
        $validated['estimated_delivery_date'] = Carbon::today()->format('Y-m-d');
        $pallet = OutgoingPallet::create($validated);
        $pallet->load(['customer', 'address']);

        return response()->json($pallet, 201);
    }
    /**
     * Display the specified outgoing pallet.
     */
    public function show(int $id): JsonResponse
    {
        $pallet = OutgoingPallet::with(['customer', 'address'])->findOrFail($id);

        return response()->json($pallet);
    }
    /**
     * Update the specified outgoing pallet.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $pallet = OutgoingPallet::findOrFail($id);

        $validated = $request->validate([
            'customer_id' => 'sometimes|required|integer|exists:tandc_live.customers,id',
            'address_id' => 'sometimes|required|integer',//|exists:address,id',
        ]);

        $pallet->update($validated);
        $pallet->load(['customer', 'address']);

        return response()->json($pallet);
    }
    /**
     * Remove the specified outgoing pallet.
     */
    public function destroy(int $id): JsonResponse
    {
        $pallet = OutgoingPallet::findOrFail($id);
        $pallet->delete();

        return response()->json(['message' => 'Outgoing pallet deleted successfully']);
    }
    public function createPallet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:tandc_live.customers,id',
            'address_id' => 'required|integer',
            'outgoing_pallet_type_id' => 'nullable|integer|exists:tandc_live.outgoing_pallet_types,id',
        ]);
        $validated['estimated_delivery_date'] = Carbon::today()->format('Y-m-d');
        if (!isset($validated['outgoing_pallet_type_id']) || !$validated['outgoing_pallet_type_id']) {
            $validated['outgoing_pallet_type_id'] = 1;
        }

        $pallet = OutgoingPallet::create($validated);

        return response()->json([
            'id' => $pallet->id,
            'customer_id' => $pallet->customer_id,
            'address_id' => $pallet->address_id,
        ], 201);
    }
    public function deletePallet(int $id): JsonResponse
    {
        $pallet = OutgoingPallet::findOrFail($id);
        OutgoingPalletPickWeight::where('outgoing_pallet_id', $pallet->id)->delete();
        $pallet->delete();

        return response()->json(['message' => 'Outgoing pallet deleted successfully']);
    }
    public function updatePalletType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'outgoing_pallet_id' => 'required|integer|exists:tandc_live.outgoing_pallet,id',
            'outgoing_pallet_type_id' => 'required|integer|exists:tandc_live.outgoing_pallet_types,id',
        ]);

        $pallet = OutgoingPallet::findOrFail($validated['outgoing_pallet_id']);
        $pallet->update(['outgoing_pallet_type_id' => $validated['outgoing_pallet_type_id']]);

        return response()->json([
            'id' => $pallet->id,
            'outgoing_pallet_type_id' => $pallet->outgoing_pallet_type_id,
            'message' => 'Pallet type updated successfully',
        ]);
    }
    public function attachPick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'outgoing_pallet_id' => 'required|integer|exists:tandc_live.outgoing_pallet,id',
            'pick_weight_out_id' => 'required|integer|exists:tandc_live.pickWeightOut,id',
        ]);

        $result = DB::connection('tandc_live')->transaction(function () use ($validated) {
            $pickWeightOut = PickWeightOut::query()->findOrFail($validated['pick_weight_out_id']);

            $link = OutgoingPalletPickWeight::query()->firstOrCreate([
                'outgoing_pallet_id' => (int) $validated['outgoing_pallet_id'],
                'pickWeightOut_id' => (int) $validated['pick_weight_out_id'],
            ]);

            $targetPick = PickWeightOut::recombineWithinPalletByPickerSheet(
                (int) $validated['outgoing_pallet_id'],
                (int) $pickWeightOut->pickersheet_id
            );

            return [
                'id' => $link->id,
                'target_pick' => $targetPick ? $targetPick->formatPickWeightOutSummary() : null,
            ];
        });

        return response()->json($result);
    }
    public function detachPick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'outgoing_pallet_id' => 'required|integer|exists:tandc_live.outgoing_pallet,id',
            'pick_weight_out_id' => 'required|integer|exists:tandc_live.pickWeightOut,id',
            'recombine_unloaded' => 'nullable|boolean',
        ]);

        $result = DB::connection('tandc_live')->transaction(function () use ($validated) {
            $pickWeightOut = PickWeightOut::query()->findOrFail($validated['pick_weight_out_id']);
            $pickerSheetId = (int) $pickWeightOut->pickersheet_id;
            $recombineUnloaded = array_key_exists('recombine_unloaded', $validated)
                ? (bool) $validated['recombine_unloaded']
                : true;

            OutgoingPalletPickWeight::query()
                ->where('outgoing_pallet_id', (int) $validated['outgoing_pallet_id'])
                ->where('pickWeightOut_id', (int) $validated['pick_weight_out_id'])
                ->delete();

            $sourcePalletPick = PickWeightOut::recombineWithinPalletByPickerSheet((int) $validated['outgoing_pallet_id'], $pickerSheetId);
            $unloadedPick = $recombineUnloaded ? PickWeightOut::recombineWithinUnloadedByPickerSheet($pickerSheetId) : null;

            return [
                'message' => 'Pick unlinked',
                'source_pallet_pick' => $sourcePalletPick ? $sourcePalletPick->formatPickWeightOutSummary() : null,
                'moved_pick' => $unloadedPick ? $unloadedPick->formatPickWeightOutSummary() : null,
            ];
        });

        return response()->json($result);
    }
    public function splitPick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pick_weight_out_id' => 'required|integer|exists:tandc_live.pickWeightOut,id',
            'move_weight_count' => 'required|integer|min:1',
            'move_cut_id' => 'nullable|integer|exists:tandc_live.cuts,id',
            'target_outgoing_pallet_id' => 'nullable|integer|exists:tandc_live.outgoing_pallet,id',
            'from_outgoing_pallet_id' => 'nullable|integer|exists:tandc_live.outgoing_pallet,id',
        ]);

        $result = DB::connection('tandc_live')->transaction(function () use ($validated) {
            return PickWeightOut::SPLIT_PICK(
                (int) $validated['pick_weight_out_id'],
                (int) $validated['move_weight_count'],
                $validated['target_outgoing_pallet_id'] ?? null,
                $validated['from_outgoing_pallet_id'] ?? null,
                $validated['move_cut_id'] ?? null,
            );
        });

        return response()->json($result);
    }
    public function renderPickHtml(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pick_weight_out_id' => 'required|integer|exists:tandc_live.pickWeightOut,id',
            'from_outgoing_pallet_id' => 'nullable|integer|exists:tandc_live.outgoing_pallet,id',
            'selected_cut_id' => 'nullable|integer|exists:tandc_live.cuts,id',
            'move_weight_count' => 'nullable|integer|min:1',
        ]);

        $pickWeightOut = PickWeightOut::query()->with('pickerSheet')->findOrFail((int) $validated['pick_weight_out_id']);

        $html = view('components.draggable-pick', [
            'pickWeightOut' => $pickWeightOut,
            'pickerSheet' => $pickWeightOut->pickerSheet,
            'fromPalletId' => $validated['from_outgoing_pallet_id'] ?? null,
            'selectedCutId' => $validated['selected_cut_id'] ?? null,
            'moveWeightCount' => $validated['move_weight_count'] ?? null,
        ])->render();

        return response()->json([
            'html' => $html,
        ]);
    }
    private function formatCustomerAddress($row, string $addressId): array
    {
        $addressId = trim($addressId);
        if ($addressId === '') {
            return [];
        }

        $lines = [];
        $numberKey = "address{$addressId}_number";
        if (!empty($row->{$numberKey} ?? null)) {
            $lines[] = $row->{$numberKey};
        }
        else {
            $lines[] = "";
        }

        $key = "address{$addressId}_1";
        if (!empty($row->{$key} ?? null)) {
            $lines[] = $row->{$key};
        }
        else {
            $lines[] = "";
        }

        $postcodeKey = "postcode_{$addressId}";
        if (!empty($row->{$postcodeKey} ?? null)) {
            $lines[] = $row->{$postcodeKey};
        }
        else {
            $lines[] = "";
        }

        return $lines;
    }
    private function parseAddressIds(string $addressId): array
    {
        $addressId = trim($addressId);
        if ($addressId === '') {
            return [];
        }

        $parts = preg_split('/[^0-9]+/', $addressId);
        $parts = array_values(array_filter($parts, fn ($part) => $part !== ''));

        return $parts !== [] ? $parts : [$addressId];
    }
}
