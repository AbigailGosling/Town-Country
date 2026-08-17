<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\TransportPallet;
use App\Models\TransportPalletPickWeight;
use App\Models\TransportPalletType;
use App\Models\PickerSheet;
use App\Models\PickWeightOut;
use App\Models\VehicleTransportPalletAllocation;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OutgoingPalletController extends Controller
{
    /**
     * Display a listing of upcoming deliveries grouped by customer address.
     */
    public function index(): View
    {

        $startDate = Carbon::today()->addDays(0);
        $endDate = Carbon::today()->addDays(3);

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
            ->join('customers', 'pickerSheets.customer_id', '=', 'customers.id')
            ->where('pickerSheets.deleted', 0)
            ->where('pickerSheets.completed', 1)
            ->whereRaw(
                "STR_TO_DATE(pickerSheets.estimated_delivery_date, '%d/%m/%Y') BETWEEN ? AND ?",
                [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]
            )
            ->orderByRaw("STR_TO_DATE(pickerSheets.estimated_delivery_date, '%d/%m/%Y') asc");

        $pickSheets = $pickSheets->get();

        $deliveryGroups = $pickSheets->groupBy(function ($sheet) {
            return $sheet->customer_id.'|'.$sheet->addressid;
        });

        $mapped = collect();
        foreach ($deliveryGroups as $key => $sheets) {

            $first = $sheets->first();
            $customerName = $first->businessname ?: ($first->tradingas ?: '');

            // Group deliveries by date and count
            $deliveriesByDate = $sheets->groupBy(function ($sheet) {
                return $sheet->estimated_delivery_date;
            })->map(function ($group) {
                return count($group);
            });

            $mapped->push([
                'customer_id' => $first->customer_id,
                'customer_name' => $customerName,
                'address_id' => $first->addressid,
                'address_lines' => $this->formatCustomerAddress($first->customer_id, (string) $first->addressid),
                'deliveries_by_date' => $deliveriesByDate,
            ]);
        }

        $sorted = $mapped->sortBy(function ($group) {
            return mb_strtolower($group['customer_name'] ?? '');
        })
        ->values();

        return view('outgoing-pallets.index', [
            'deliveryGroups' => $sorted,
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
        $startDate = Carbon::today()->addDays(0);
        $endDate = Carbon::today()->addDays(3);

        $pickSheets = PickerSheet::query()
            ->with('pickWeightOuts')
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

        // Get all pickersheet IDs that are already loaded in transport_pallets via the pivot table
        $outgoingPallets = TransportPallet::where('customer_id', $customerId)
            ->where('address_id', $addressId)
            ->with(['pickWeightOuts.pickWeightOut.pickerSheet'])
            ->get();

        $loadedPickWeightOutIds = [];

        $outgoingPallets2 = collect();
        foreach ($outgoingPallets as $pallet) {
            //if (!$pallet->dispatched) {
                $outgoingPallets2->push($pallet);
            //}
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

        $palletTypes = TransportPalletType::all();
        $customer = Customer::find($customerId);
        $customerAddress = ClientAddress::where('client_id', $customerId)
            ->where('address_id', $addressId)
            ->where('client_type', ClientType::CUSTOMER->value)
            ->first();
        return view('outgoing-pallets.details', [
            'customer' => $customer,
            'customer_id' => $customerId,
            'address_id' => $addressId,
            'deliveries' => $unloadedPickSheets,
            'outgoingPallets' => $outgoingPallets2,
            'palletTypes' => $palletTypes,
            'customerAddress' => $customerAddress,
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
        $pallet = TransportPallet::create($validated);
        $pallet->load(['customer', 'address']);

        return response()->json($pallet, 201);
    }
    public function pickPallets(Request $request): JsonResponse
    {
        $pickSheetId = $request->input('pickersheet_id');
         if (!$pickSheetId) {
             return response()->json(['error' => 'picksheet_id query parameter is required'], 400);
        }
        $pickWeightOutIds = PickWeightOut::where('pickersheet_id', $pickSheetId)->pluck('id')->toArray();

        $pallets = TransportPallet::query()
            ->select('transport_pallets.*')
            ->join('transport_pallet_pick_weights', 'transport_pallets.id', '=', 'transport_pallet_pick_weights.transport_pallet_id')
            ->whereIn('transport_pallet_pick_weights.pickWeightOut_id', $pickWeightOutIds)
            ->get();

        return response()->json($pallets);
    }
    /**
     * Display the specified outgoing pallet.
     */
    public function show(int $id): JsonResponse
    {
        $pallet = TransportPallet::with(['customer', 'address'])->findOrFail($id);

        return response()->json($pallet);
    }
    /**
     * Update the specified outgoing pallet.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $pallet = TransportPallet::findOrFail($id);

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
        $pallet = TransportPallet::findOrFail($id);
        if ($pallet->dispatched == 1) return response()->json(['error' => 'Cannot Delete Pallet after dispatch']);
        foreach (VehicleTransportPalletAllocation::where("transport_pallet_id",$pallet)->get() as $vopa)
        {
            $vopa->delete();
        }
        $pallet->delete();
        return response()->json(['message' => 'Outgoing pallet deleted successfully']);
    }
    public function createPallet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:tandc_live.customers,id',
            'address_id' => 'required|integer',
            'transport_pallet_type_id' => 'nullable|integer|exists:tandc_live.transport_pallet_types,id',
        ]);
        $validated['estimated_delivery_date'] = Carbon::today()->format('Y-m-d');
        if (!isset($validated['transport_pallet_type_id']) || !$validated['transport_pallet_type_id']) {
            $validated['transport_pallet_type_id'] = 1;
        }

        $pallet = TransportPallet::create($validated);

        return response()->json([
            'id' => $pallet->id,
            'customer_id' => $pallet->customer_id,
            'address_id' => $pallet->address_id,
        ], 201);
    }
    public function deletePallet(int $id): JsonResponse
    {
        $pallet = TransportPallet::findOrFail($id);
        if ($pallet->dispatched != 0 && $pallet->pod_sent != 0) return response()->json(['message' => 'Could not delete']);
        TransportPalletPickWeight::where('transport_pallet_id', $pallet->id)->delete();
        VehicleTransportPalletAllocation::where('transport_pallet_id', $pallet->id)->delete();
        $pallet->delete();

        return response()->json(['message' => 'Outgoing pallet deleted successfully']);
    }
    public function updatePalletType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transport_pallet_id' => 'required|integer|exists:tandc_live.transport_pallets,id',
            'transport_pallet_type_id' => 'required|integer|exists:tandc_live.transport_pallet_types,id',
        ]);

        $pallet = TransportPallet::findOrFail($validated['transport_pallet_id']);
        $pallet->update(['transport_pallet_type_id' => $validated['transport_pallet_type_id']]);

        return response()->json([
            'id' => $pallet->id,
            'transport_pallet_type_id' => $pallet->transport_pallet_type_id,
            'message' => 'Pallet type updated successfully',
        ]);
    }
    public function attachPick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transport_pallet_id' => 'required|integer|exists:tandc_live.transport_pallets,id',
            'pick_weight_out_id' => 'required|integer|exists:tandc_live.pickWeightOut,id',
        ]);

        $result = DB::connection('tandc_live')->transaction(function () use ($validated) {
            $pickWeightOut = PickWeightOut::query()->findOrFail($validated['pick_weight_out_id']);

            $link = TransportPalletPickWeight::query()->firstOrCreate([
                'transport_pallet_id' => (int) $validated['transport_pallet_id'],
                'pickWeightOut_id' => (int) $validated['pick_weight_out_id'],
            ]);

            $targetPick = PickWeightOut::recombineWithinPalletByPickerSheet(
                (int) $validated['transport_pallet_id'],
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
            'transport_pallet_id' => 'required|integer|exists:tandc_live.transport_pallets,id',
            'pick_weight_out_id' => 'required|integer|exists:tandc_live.pickWeightOut,id',
            'recombine_unloaded' => 'nullable|boolean',
        ]);

        $result = DB::connection('tandc_live')->transaction(function () use ($validated) {
            $pickWeightOut = PickWeightOut::query()->findOrFail($validated['pick_weight_out_id']);
            $pickerSheetId = (int) $pickWeightOut->pickersheet_id;
            $recombineUnloaded = array_key_exists('recombine_unloaded', $validated)
                ? (bool) $validated['recombine_unloaded']
                : true;

            TransportPalletPickWeight::query()
                ->where('transport_pallet_id', (int) $validated['transport_pallet_id'])
                ->where('pickWeightOut_id', (int) $validated['pick_weight_out_id'])
                ->delete();

            $sourcePalletPick = PickWeightOut::recombineWithinPalletByPickerSheet((int) $validated['transport_pallet_id'], $pickerSheetId);
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
            'target_transport_pallet_id' => 'nullable|integer|exists:tandc_live.transport_pallets,id',
            'from_transport_pallet_id' => 'nullable|integer|exists:tandc_live.transport_pallets,id',
        ]);

        $result = DB::connection('tandc_live')->transaction(function () use ($validated) {
            return PickWeightOut::SPLIT_PICK(
                (int) $validated['pick_weight_out_id'],
                (int) $validated['move_weight_count'],
                $validated['target_transport_pallet_id'] ?? null,
                $validated['from_transport_pallet_id'] ?? null,
                $validated['move_cut_id'] ?? null,
            );
        });

        return response()->json($result);
    }
    public function renderPickHtml(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pick_weight_out_id' => 'required|integer|exists:tandc_live.pickWeightOut,id',
            'from_transport_pallet_id' => 'nullable|integer|exists:tandc_live.transport_pallets,id',
            'selected_cut_id' => 'nullable|integer|exists:tandc_live.cuts,id',
            'move_weight_count' => 'nullable|integer|min:1',
        ]);

        $pickWeightOut = PickWeightOut::query()->with('pickerSheet')->findOrFail((int) $validated['pick_weight_out_id']);

        $html = view('components.draggable-pick', [
            'pickWeightOut' => $pickWeightOut,
            'pickerSheet' => $pickWeightOut->pickerSheet,
            'fromPalletId' => $validated['from_transport_pallet_id'] ?? null,
            'selectedCutId' => $validated['selected_cut_id'] ?? null,
            'moveWeightCount' => $validated['move_weight_count'] ?? null,
        ])->render();

        return response()->json([
            'html' => $html,
        ]);
    }
    private function formatCustomerAddress(int $customerId, string $addressId): array
    {
        $addressId = trim($addressId);
        if ($addressId === '') {
            return [];
        }
       $ca = ClientAddress::where('client_id', $customerId)
            ->where('address_id', $addressId)
            ->where('client_type', ClientType::CUSTOMER->value)
            ->first();
        if(!$ca) {
            $customer = Customer::find($customerId);
            $ca = new ClientAddress([
                'client_id' => $customerId,
                'address_id' => $addressId,
                'client_type' => ClientType::CUSTOMER->value,
                'address_number' => $customer->{'contact_number'} ?? '',
                'address_1' => $customer->{'account_address_1'} ?? '',
                'address_2' => $customer->{'account_address_2'} ?? '',
                'address_3' => $customer->{'account_address_3'} ?? '',
                'address_4' => $customer->{'account_address_4'} ?? '',
                'postcode' => $customer->{'account_address_4'} ?? '',
             ]);
        }
        $lines = [];
        $numberKey = "address_number";
        if (!empty($ca->{$numberKey} ?? null)) {
            $lines[] = $ca->{$numberKey};
        }
        else {
            $lines[] = "";
        }

        $key = "address_1";
        if (!empty($ca->{$key} ?? null)) {
            $lines[] = $ca->{$key};
        }
        else {
            $lines[] = "";
        }

        $postcodeKey = "postcode";
        if (!empty($ca->{$postcodeKey} ?? null)) {
            $lines[] = $ca->{$postcodeKey};
        }
        else {
            $lines[] = "";
        }

        return $lines;
    }
}
