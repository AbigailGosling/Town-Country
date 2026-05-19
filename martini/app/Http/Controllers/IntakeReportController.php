<?php

namespace App\Http\Controllers;

use App\Helpers\FuncHelper;
use App\Helpers\ReportHelper;
use App\Models\Brand;
use App\Models\CreditNoteItem;
use App\Models\Customer;
use App\Models\Cut;
use App\Models\Intake;
use App\Models\InvoicePayment;
use App\Models\Nationality;
use App\Models\Pallet;
use App\Models\PickWeightOut;
use App\Models\PickerItem;
use App\Models\PickerSheet;
use App\Models\Product;
use App\Models\Species;
use App\Models\Supplier;
use App\Models\Temperature;
use App\Models\User;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;

class IntakeReportController extends Controller
{
    private const OVERVIEW_TOTAL_FIELDS = ["qty", "kg", "cost", "actCost", "subTotal", "actSubTotal"];
    private const FINANCIAL_TOTAL_FIELDS = ["qty", "kg", "cost", "actCost", "sell", "profit", "actProfit"];
    private const STOCK_TOTAL_FIELDS = ["qty", "kg", "subTotal", "actSubTotal"];

    public function show(Request $request)
    {
        $viewData = $this->getViewData($request);

        if (!$this->hasFilters($viewData)) {
            return view("reports.intake", $this->buildViewPayload($viewData));
        }

        $validationError = $this->validateFilters($viewData);
        if ($validationError !== null) {
            return Redirect::back()->withErrors($validationError)->withInput();
        }

        $selectedIntakes = $this->findSelectedIntakes($viewData);
        if ($selectedIntakes->isEmpty()) {
            return Redirect::back()->withErrors("No intakes were found for the selected range")->withInput();
        }

        $rootIntakes = $this->resolveRootIntakes($selectedIntakes);
        $summary = new Collection();
        $saleInfo = new Collection();
        $creditInfo = new Collection();
        $resaleInfo = new Collection();
        $stockInfo = new Collection();
        $resolvedIntakeIds = [];

        foreach ($rootIntakes as $intake) {
            $reportData = $this->buildReportDataForIntake($intake);
            if ($reportData === null) {
                continue;
            }

            $resolvedIntakeIds[] = $intake->id;
            $summary = $summary->merge($reportData["summary"]);
            $saleInfo = $saleInfo->merge($reportData["saleInfo"]);
            $creditInfo = $creditInfo->merge($reportData["creditInfo"]);
            $resaleInfo = $resaleInfo->merge($reportData["resaleInfo"]);
            $stockInfo = $stockInfo->merge($reportData["stockInfo"]);
        }

        if (count($resolvedIntakeIds) < 1) {
            return Redirect::back()->withErrors("No reportable intakes were found for the selected range")->withInput();
        }

        return view("reports.intake", $this->buildViewPayload(
            $viewData,
            $resolvedIntakeIds,
            [
                "summary" => $summary,
                "saleInfo" => $saleInfo,
                "creditInfo" => $creditInfo,
                "resaleInfo" => $resaleInfo,
                "stockInfo" => $stockInfo,
            ]
        ));
    }

    private function buildViewPayload(array $viewData, array $resolvedIntakeIds = [], array $reportData = []): array
    {
        $reportData = array_merge($this->emptyReportCollections(), $reportData);

        return array_merge($viewData, $reportData, [
            "resolved_intake_ids" => $resolvedIntakeIds,
            "reportedIntakeRange" => $this->buildReportedIntakeRange($resolvedIntakeIds),
            "report_scope" => empty($resolvedIntakeIds) ? null : $this->buildReportScope($viewData),
        ], $this->buildPresentationData($reportData));
    }

    private function emptyReportCollections(): array
    {
        return [
            "summary" => collect(),
            "saleInfo" => collect(),
            "creditInfo" => collect(),
            "resaleInfo" => collect(),
            "stockInfo" => collect(),
        ];
    }

    private function buildPresentationData(array $reportData): array
    {
        $overviewTotals = $this->sumRows($reportData["summary"], self::OVERVIEW_TOTAL_FIELDS, true);
        $salesTotals = $this->sumRows($reportData["saleInfo"], self::FINANCIAL_TOTAL_FIELDS);
        $returnTotals = $this->sumRows($reportData["creditInfo"], self::FINANCIAL_TOTAL_FIELDS);
        $resaleTotals = $this->sumRows($reportData["resaleInfo"], self::FINANCIAL_TOTAL_FIELDS);
        $finalSummaryTotals = $this->combineTotals(self::FINANCIAL_TOTAL_FIELDS, $salesTotals, $returnTotals, $resaleTotals);
        $stockTotals = $this->sumRows($reportData["stockInfo"], self::STOCK_TOTAL_FIELDS);

        return [
            "overviewTotals" => $overviewTotals,
            "salesTotals" => $salesTotals,
            "returnTotals" => $returnTotals,
            "resaleTotals" => $resaleTotals,
            "finalSummaryTotals" => $finalSummaryTotals,
            "stockTotals" => $stockTotals,
            "exportSheets" => $this->buildExportSheets(
                $reportData,
                $overviewTotals,
                $salesTotals,
                $returnTotals,
                $resaleTotals,
                $finalSummaryTotals,
                $stockTotals
            ),
        ];
    }

    private function getViewData(Request $request): array
    {
        return [
            "intake_id_from" => $request->input("intake_id_from", $request->input("intake_id")),
            "intake_id_to" => $request->input("intake_id_to", $request->input("intake_id")),
            "date_from" => $request->input("date_from"),
            "date_to" => $request->input("date_to"),
        ];
    }

    private function hasFilters(array $viewData): bool
    {
        return filled($viewData["intake_id_from"])
            || filled($viewData["intake_id_to"])
            || filled($viewData["date_from"])
            || filled($viewData["date_to"]);
    }

    private function validateFilters(array $viewData): ?string
    {
        $hasIdRange = filled($viewData["intake_id_from"]) || filled($viewData["intake_id_to"]);
        $hasDateRange = filled($viewData["date_from"]) || filled($viewData["date_to"]);

        if ($hasIdRange && $hasDateRange) {
            return "Use either an intake ID range or a date range, not both";
        }

        if (!$hasIdRange && !$hasDateRange) {
            return "Enter an intake ID range or a date range to run the report";
        }

        if ($hasIdRange && (!filled($viewData["intake_id_from"]) || !filled($viewData["intake_id_to"]))) {
            return "Enter both a start and end intake ID";
        }

        if ($hasDateRange && (!filled($viewData["date_from"]) || !filled($viewData["date_to"]))) {
            return "Enter both a start and end date";
        }

        if ($hasDateRange) {
            try {
                Carbon::createFromFormat("Y-m-d", $viewData["date_from"]);
                Carbon::createFromFormat("Y-m-d", $viewData["date_to"]);
            } catch (\Throwable $exception) {
                return "Enter valid report dates";
            }
        }

        return null;
    }

    private function findSelectedIntakes(array &$viewData): Collection
    {
        $hasIdRange = filled($viewData["intake_id_from"]) || filled($viewData["intake_id_to"]);

        if ($hasIdRange) {
            $from = min((int) $viewData["intake_id_from"], (int) $viewData["intake_id_to"]);
            $to = max((int) $viewData["intake_id_from"], (int) $viewData["intake_id_to"]);
            $viewData["intake_id_from"] = $from;
            $viewData["intake_id_to"] = $to;

            return Intake::whereBetween("id", [$from, $to])
                ->orderBy("id")
                ->get();
        }

        $from = Carbon::createFromFormat("Y-m-d", $viewData["date_from"]);
        $to = Carbon::createFromFormat("Y-m-d", $viewData["date_to"]);

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $viewData["date_from"] = $from->format("Y-m-d");
        $viewData["date_to"] = $to->format("Y-m-d");

        return Intake::whereBetween("date_received", [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy("date_received")
            ->orderBy("id")
            ->get();
    }

    private function resolveRootIntakes(Collection $selectedIntakes): Collection
    {
        $rootIntakes = new Collection();

        foreach ($selectedIntakes as $intake) {
            $rootIntake = $this->resolveRootIntake($intake);
            if ($rootIntake !== null) {
                $rootIntakes->add($rootIntake);
            }
        }

        return $rootIntakes->unique("id")->values();
    }

    private function resolveRootIntake(Intake $intake): ?Intake
    {
        $visited = [];
        $currentIntake = $intake;

        while ($currentIntake !== null && !isset($visited[$currentIntake->id])) {
            $visited[$currentIntake->id] = true;

            $originalIntakeId = Product::query()
                ->join("pallet", "product.pallet_id", "=", "pallet.id")
                ->where("pallet.intake_id", $currentIntake->id)
                ->value("product.original_intake_id");

            if ($originalIntakeId === null || $originalIntakeId === "") {
                return $currentIntake;
            }

            $currentIntake = Intake::find($originalIntakeId);
        }

        return $currentIntake;
    }

    private function buildReportDataForIntake(Intake $intake): ?array
    {
        $pallets = Pallet::where("intake_id", $intake->id)->get();
        if ($pallets->isEmpty()) {
            return null;
        }

        $products = Product::whereIn("pallet_id", $pallets->pluck("id")->all())->get();
        if ($products->isEmpty()) {
            return null;
        }

        $productIds = $products->pluck("id")->all();
        $weights = Weight::whereIn("product_id", $productIds)->get();
        $pickItems = PickerItem::whereIn("product_id", $productIds)
            ->groupBy(["pickersheet_id", "product_id"])
            ->get();
        $sales = PickerSheet::whereIn("id", $pickItems->pluck("pickersheet_id")->all())->get();
        $credits = InvoicePayment::where("payment_method", "=", "CREDIT_NOTE")
            ->whereIn("invoice_id", $pickItems->pluck("pickersheet_id")->all())
            ->get();
        $creditNotes = CreditNoteItem::whereIn("payment_id", $credits->pluck("id")->all())->get();

        $returnProducts = Product::where("original_intake_id", $intake->id)
            ->orWhereIn("original_pallet_id", $pallets->pluck("id")->all())
            ->orWhereIn("id", $creditNotes->pluck("product_id")->all())
            ->get();
        $returnProductIds = $returnProducts->pluck("id")->all();
        $returnWeights = Weight::whereIn("product_id", $returnProductIds)->get();
        $resalePickItems = PickerItem::whereIn("product_id", $returnProductIds)
            ->groupBy(["pickersheet_id", "product_id"])
            ->get();
        $resales = PickerSheet::whereIn("id", $resalePickItems->pluck("pickersheet_id")->all())->get();

        $cuts = Cut::whereIn("id", $products->pluck("cut_id")->filter()->unique()->all())->get();
        $returnCuts = Cut::whereIn("id", $returnProducts->pluck("cut_id")->filter()->unique()->all())->get();
        $allProducts = $products->merge($returnProducts);
        $brandsById = Brand::whereIn("id", $allProducts->pluck("brand_id")->filter()->unique()->all())->get()->keyBy("id");
        $nationalitiesById = Nationality::whereIn("id", $allProducts->pluck("nationality_id")->filter()->unique()->all())->get()->keyBy("id");
        $temperaturesById = Temperature::whereIn("id", $allProducts->pluck("cooling_id")->filter()->unique()->all())->get()->keyBy("id");
        $speciesById = Species::whereIn("id", $cuts->pluck("species_id")->merge($returnCuts->pluck("species_id"))->filter()->unique()->all())->get()->keyBy("id");

        $productsById = $products->keyBy("id");
        $returnProductsById = $returnProducts->keyBy("id");
        $weightsById = $weights->keyBy("id");
        $returnWeightsById = $returnWeights->keyBy("id");
        $weightsByProductId = $weights->groupBy("product_id");
        $returnWeightsByProductId = $returnWeights->groupBy("product_id");
        $returnWeightSumsByProductId = $returnWeightsByProductId->map(fn (Collection $productWeights) => $productWeights->sum("weight_tear"));
        $cutsById = $cuts->keyBy("id");
        $returnCutsById = $returnCuts->keyBy("id");
        $pickItemsBySheetId = $pickItems->groupBy("pickersheet_id");
        $resalePickItemsBySheetId = $resalePickItems->groupBy("pickersheet_id");
        $pickItemByProductId = $pickItems->groupBy("product_id")->map(fn (Collection $items) => $items->first());
        $pickWeightOutsBySheetId = PickWeightOut::whereIn(
            "pickersheet_id",
            $sales->pluck("id")->merge($resales->pluck("id"))->filter()->unique()->all()
        )->get()->groupBy("pickersheet_id");
        $creditsByInvoiceId = $credits->groupBy("invoice_id");
        $creditNotesByPaymentId = $creditNotes->groupBy("payment_id");
        $newPalletsById = Pallet::whereIn("id", $returnProducts->pluck("pallet_id")->filter()->unique()->all())->get()->keyBy("id");
        $customersById = Customer::whereIn(
            "id",
            $sales->where("is_return_to_supplier", false)->pluck("customer_id")
                ->merge($resales->pluck("customer_id"))
                ->merge([$intake->supplier_id])
                ->filter()
                ->unique()
                ->all()
        )->get()->keyBy("id");
        $suppliersById = Supplier::whereIn(
            "id",
            $sales->where("is_return_to_supplier", true)->pluck("customer_id")
                ->merge([$intake->supplier_id])
                ->filter()
                ->unique()
                ->all()
        )->get()->keyBy("id");
        $userIds = $sales->map(function ($sale) use ($customersById) {
            if ($sale->is_return_to_supplier == false) {
                return $customersById->get($sale->customer_id)?->default_salesman_id;
            }

            return $sale->user_from_id;
        })->merge(
            $resales->map(fn ($resale) => $customersById->get($resale->customer_id)?->default_salesman_id)
        )->filter()->unique()->all();
        $usersById = User::whereIn("id", $userIds)->get()->keyBy("id");
        $originalProductsById = Product::whereIn("id", $returnProducts->pluck("original_product_id")->filter()->unique()->all())
            ->get()
            ->keyBy("id");
        $potentialOriginalProductsByPalletId = Product::whereIn("pallet_id", $returnProducts->pluck("original_pallet_id")->filter()->unique()->all())
            ->get()
            ->groupBy("pallet_id");
        $supplier = $suppliersById->get($intake->supplier_id);
        $supCust = $customersById->get($intake->supplier_id);
        $supplierName = $supplier?->name ?? $supCust?->businessname;
        $displayLookups = [
            "speciesById" => $speciesById,
            "nationalitiesById" => $nationalitiesById,
            "temperaturesById" => $temperaturesById,
            "brandsById" => $brandsById,
            "supplierName" => $supplierName,
        ];

        $summary = new Collection();
        $saleInfo = new Collection();
        $creditInfo = new Collection();
        $resaleInfo = new Collection();
        $stockInfo = new Collection();

        $productIdsByCutId = $products->groupBy("cut_id")->map(fn (Collection $cutProducts) => $cutProducts->pluck("id"));
        $combinedProducts = $products->merge($returnProducts);
        $combinedProductsById = $combinedProducts->keyBy("id");
        $combinedProductsByCutId = $combinedProducts->groupBy("cut_id")->map(fn (Collection $cutProducts) => $cutProducts->pluck("id"));
        $combinedAvailableWeightsByProductId = $weights->merge($returnWeights)
            ->where("status_id", 0)
            ->groupBy("product_id");

        foreach ($cuts as $cut) {
            $productIdsForCut = $productIdsByCutId->get($cut->id, collect());
            if ($productIdsForCut->isEmpty()) {
                continue;
            }

            $productsForCut = $productsById->only($productIdsForCut->all());
            $weightAggregate = $this->aggregateWeightsForProducts($productIdsForCut, $weightsByProductId);
            $summary->add($this->makeInventoryRow(
                $intake->id,
                $cut,
                $speciesById,
                $weightAggregate["qty"],
                $weightAggregate["kg"],
                $productsForCut->first()
            ));
        }

        foreach ($sales as $sale) {
            [$weightIDsForSale, $internalCount] = $this->buildWeightIdsForSale(
                $pickWeightOutsBySheetId->get($sale->id, collect()),
                $weightsById,
                $productsById
            );

            if ($internalCount === 0) {
                continue;
            }

            $customer = ($sale->is_return_to_supplier == false)
                ? $customersById->get($sale->customer_id)
                : $suppliersById->get($sale->customer_id);
            if ($customer === null) {
                continue;
            }

            $user = $usersById->get(($sale->is_return_to_supplier == false) ? $customer->default_salesman_id : $sale->user_from_id);
            $salePickItems = $pickItemsBySheetId->get($sale->id, collect());

            foreach ($salePickItems as $pickItem) {
                if (!array_key_exists($pickItem->product_id, $weightIDsForSale)) {
                    continue;
                }

                $product = $productsById->get($pickItem->product_id);
                $cut = $product ? $cutsById->get($product->cut_id) : null;
                if ($product === null || $cut === null) {
                    continue;
                }

                $saleInfo->add($this->makeFinancialRow(
                    [
                        "intake_id" => $intake->id,
                        "salesperson" => $user?->name ?? "Unknown",
                        "date" => $sale->date_completed,
                        "invoice_id" => $sale->id,
                        "customer" => ($sale->is_return_to_supplier == false) ? $customer->businessname : $customer->name,
                    ],
                    $product,
                    $product,
                    $cut,
                    $displayLookups,
                    count($weightIDsForSale[$product->id]),
                    $this->sumWeightTearByIds($weightsById, $weightIDsForSale[$product->id]),
                    $product->cost,
                    $product->price,
                    $pickItem->price
                ));
            }

            foreach ($creditsByInvoiceId->get($sale->id, collect()) as $payment) {
                foreach ($creditNotesByPaymentId->get($payment->id, collect()) as $creditNote) {
                    if ($creditNote->product_id == null || $creditNote->product_id == 0) {
                        continue;
                    }

                    $newProduct = $returnProductsById->get($creditNote->product_id);
                    if ($newProduct === null) {
                        continue;
                    }

                    $originalProduct = $this->guessTheOriginal($newProduct, $originalProductsById, $potentialOriginalProductsByPalletId);
                    $cut = $returnCutsById->get($newProduct->cut_id);
                    $newPallet = $newPalletsById->get($newProduct->pallet_id);
                    if ($cut === null || $newPallet === null) {
                        continue;
                    }

                    $pickItem = $pickItemByProductId->get($originalProduct->id);
                    $pickItemPrice = $pickItem ? $pickItem->price : $newProduct->cost;
                    $creditInfo->add($this->makeFinancialRow(
                        [
                            "intake_id" => $intake->id,
                            "new_intake_id" => $newPallet->intake_id,
                            "salesperson" => $user?->name ?? "Unknown",
                            "date" => $payment->created_at,
                            "invoice_id" => $sale->id,
                            "customer" => $customer->businessname,
                        ],
                        $newProduct,
                        $originalProduct,
                        $cut,
                        $displayLookups,
                        $creditNote->quantity,
                        (float) ($returnWeightSumsByProductId->get($newProduct->id) ?? 0),
                        $creditNote->price,
                        $creditNote->price,
                        $pickItemPrice
                    ));
                }
            }
        }

        foreach ($resales as $resale) {
            [$weightIDsForSale, $internalCount] = $this->buildWeightIdsForSale(
                $pickWeightOutsBySheetId->get($resale->id, collect()),
                $returnWeightsById,
                $returnProductsById
            );

            if ($internalCount === 0) {
                continue;
            }

            $customer = $customersById->get($resale->customer_id);
            if ($customer === null) {
                continue;
            }

            $user = $usersById->get($customer->default_salesman_id);
            $salePickItems = $resalePickItemsBySheetId->get($resale->id, collect());

            foreach ($salePickItems as $pickItem) {
                if (!array_key_exists($pickItem->product_id, $weightIDsForSale)) {
                    continue;
                }

                $product = $returnProductsById->get($pickItem->product_id);
                $cut = $product ? $returnCutsById->get($product->cut_id) : null;
                if ($product === null || $cut === null) {
                    continue;
                }

                $resaleInfo->add($this->makeFinancialRow(
                    [
                        "intake_id" => $intake->id,
                        "salesperson" => $user?->name ?? "Unknown",
                        "date" => $resale->date_completed,
                        "invoice_id" => $resale->id,
                        "customer" => $customer->businessname,
                    ],
                    $product,
                    $product,
                    $cut,
                    $displayLookups,
                    count($weightIDsForSale[$product->id]),
                    $this->sumWeightTearByIds($returnWeightsById, $weightIDsForSale[$product->id]),
                    $product->cost,
                    $product->price,
                    $pickItem->price
                ));
            }
        }

        foreach ($cuts->merge($returnCuts)->unique("id")->values() as $cut) {
            $productIdsForCut = $combinedProductsByCutId->get($cut->id, collect());
            if ($productIdsForCut->isEmpty()) {
                continue;
            }

            $productsForCut = $combinedProductsById->only($productIdsForCut->all());
            $weightAggregate = $this->aggregateWeightsForProducts($productIdsForCut, $combinedAvailableWeightsByProductId);
            $stockInfo->add($this->makeInventoryRow(
                $intake->id,
                $cut,
                $speciesById,
                $weightAggregate["qty"],
                $weightAggregate["kg"],
                $productsForCut->first()
            ));
        }

        return [
            "summary" => $summary,
            "saleInfo" => $saleInfo,
            "creditInfo" => $creditInfo,
            "resaleInfo" => $resaleInfo,
            "stockInfo" => $stockInfo,
        ];
    }

    private function makeInventoryRow(
        int $intakeId,
        Cut $cut,
        Collection $speciesById,
        float|int $qty,
        float $kg,
        Product $pricingProduct
    ): object {
        $row = new \stdClass();
        $row->intake_id = $intakeId;
        $row->name = $this->formatCutName($cut, $speciesById);
        $row->qty = $qty;
        $row->kg = $kg;
        $row->cost = $this->normalizeRate($pricingProduct->cost);
        $row->actCost = $this->normalizeRate($pricingProduct->price, $row->cost);
        $row->subTotal = $this->calculateAmountForUnit($pricingProduct->unit, $row->cost, $qty, $kg);
        $row->actSubTotal = $this->calculateAmountForUnit($pricingProduct->unit, $row->actCost, $qty, $kg);

        return $row;
    }

    private function makeFinancialRow(
        array $baseData,
        Product $displayProduct,
        Product $pricingProduct,
        Cut $cut,
        array $displayLookups,
        float|int $qty,
        float $kg,
        float|int|string|null $costRate,
        float|int|string|null $actCostRate,
        float|int|string|null $sellRate
    ): object {
        $row = (object) $baseData;
        $normalizedCostRate = $this->normalizeRate($costRate);
        $normalizedActCostRate = $this->normalizeRate($actCostRate, $normalizedCostRate);
        $normalizedSellRate = $this->normalizeRate($sellRate);
        $row->pallet_id = $displayProduct->pallet_id;
        $row->product_name = $this->formatCutName($cut, $displayLookups["speciesById"]);
        $row->nationality_name = $displayLookups["nationalitiesById"]->get($displayProduct->nationality_id)?->name;
        $row->cooling_name = $this->temperatureName($displayProduct->cooling_id, $displayLookups["temperaturesById"]);
        $row->brand_name = $displayLookups["brandsById"]->get($displayProduct->brand_id)?->name;
        $row->supplier_name = $displayLookups["supplierName"];
        $row->qty = $qty;
        $row->unit = $this->formatUnit($pricingProduct->unit);
        $row->kg = $kg;
        $row->cost = $this->calculateAmountForUnit($pricingProduct->unit, $normalizedCostRate, $qty, $kg);
        $row->actCost = $this->calculateAmountForUnit($pricingProduct->unit, $normalizedActCostRate, $qty, $kg);
        $row->sell = $this->calculateAmountForUnit($pricingProduct->unit, $normalizedSellRate, $qty, $kg);
        $row->profit = $row->sell - $row->cost;
        $row->actProfit = $row->sell - $row->actCost;

        return $row;
    }

    private function buildWeightIdsForSale(Collection $pickWeightOuts, Collection $weightsById, Collection $productsById): array
    {
        $weightIDsForSale = [];
        $internalCount = 0;

        foreach ($pickWeightOuts as $pickWeightOut) {
            foreach ($pickWeightOut->getWeights() as $weightId) {
                $weight = $weightsById->get($weightId);
                if ($weight === null) {
                    continue;
                }

                $product = $productsById->get($weight->product_id);
                if ($product === null) {
                    continue;
                }

                if (!isset($weightIDsForSale[$product->id])) {
                    $weightIDsForSale[$product->id] = [];
                }

                $weightIDsForSale[$product->id][] = $weightId;
                $internalCount++;
            }
        }

        return [$weightIDsForSale, $internalCount];
    }

    private function aggregateWeightsForProducts(Collection $productIds, Collection $weightsByProductId): array
    {
        $qty = 0;
        $kg = 0.0;

        foreach ($productIds as $productId) {
            $productWeights = $weightsByProductId->get($productId, collect());
            $qty += $productWeights->count();
            $kg += $productWeights->sum("weight_tear");
        }

        return [
            "qty" => $qty,
            "kg" => $kg,
        ];
    }

    private function sumWeightTearByIds(Collection $weightsById, array $weightIds): float
    {
        $kg = 0.0;

        foreach ($weightIds as $weightId) {
            $kg += (float) ($weightsById->get($weightId)?->weight_tear ?? 0);
        }

        return $kg;
    }

    private function buildReportScope(array $viewData): string
    {
        if (filled($viewData["intake_id_from"]) || filled($viewData["intake_id_to"])) {
            return "Intake IDs {$viewData["intake_id_from"]} to {$viewData["intake_id_to"]}";
        }

        return "Dates {$viewData["date_from"]} to {$viewData["date_to"]}";
    }

    private function buildReportedIntakeRange(array $resolvedIntakeIds): string
    {
        if (count($resolvedIntakeIds) === 0) {
            return "";
        }

        $min = min($resolvedIntakeIds);
        $max = max($resolvedIntakeIds);

        return $min === $max ? (string) $min : "{$min} to {$max}";
    }

    private function defaultTotals(array $fields): array
    {
        return array_fill_keys($fields, 0.0);
    }

    private function sumRows(Collection $rows, array $fields, bool $floorDecimals = false): array
    {
        $totals = $this->defaultTotals($fields);

        foreach ($rows as $row) {
            foreach ($fields as $field) {
                $value = (float) ($row->{$field} ?? 0);
                $totals[$field] += $floorDecimals ? FuncHelper::floorDec($value, 3) : $value;
            }
        }

        return $totals;
    }

    private function combineTotals(array $fields, array ...$totalsSets): array
    {
        $combined = $this->defaultTotals($fields);

        foreach ($totalsSets as $totals) {
            foreach ($fields as $field) {
                $combined[$field] += (float) ($totals[$field] ?? 0);
            }
        }

        return $combined;
    }

    private function buildExportSheets(
        array $reportData,
        array $overviewTotals,
        array $salesTotals,
        array $returnTotals,
        array $resaleTotals,
        array $finalSummaryTotals,
        array $stockTotals
    ): array {
        return [
            "Overview" => $this->buildOverviewSheet($reportData["summary"], $overviewTotals),
            "Sales" => $this->buildFinancialSheet($reportData["saleInfo"], $salesTotals, "Inv ID"),
            "Returns" => $this->buildFinancialSheet($reportData["creditInfo"], $returnTotals, "Invoice ID", true, true),
            "Resales" => $this->buildFinancialSheet($reportData["resaleInfo"], $resaleTotals, "Inv ID"),
            "Summary" => [$this->mapSummaryExportRow($finalSummaryTotals)],
            "Remaining Stock" => $this->buildStockSheet($reportData["stockInfo"], $stockTotals),
        ];
    }

    private function buildOverviewSheet(Collection $rows, array $totals): array
    {
        return array_merge(
            $rows->map(fn ($row) => $this->mapOverviewExportRow($row))->all(),
            [$this->mapOverviewTotalExportRow($totals)]
        );
    }

    private function buildFinancialSheet(
        Collection $rows,
        array $totals,
        string $invoiceColumn,
        bool $negateAmounts = false,
        bool $includeNewIntakeId = false
    ): array {
        return array_merge(
            $rows->map(fn ($row) => $this->mapFinancialExportRow($row, $invoiceColumn, $negateAmounts, $includeNewIntakeId))->all(),
            [$this->mapFinancialTotalExportRow($totals, $invoiceColumn, $negateAmounts, $includeNewIntakeId)]
        );
    }

    private function buildStockSheet(Collection $rows, array $totals): array
    {
        return array_merge(
            $rows->map(fn ($row) => $this->mapStockExportRow($row))->all(),
            [$this->mapStockTotalExportRow($totals)]
        );
    }

    private function calculateAmountForUnit(?string $unit, float|int|string|null $rate, float|int $qty, float $kg): float
    {
        return $this->quickFloatMulti($this->normalizeRate($rate), $unit != "PPC" ? $kg : (float) $qty);
    }

    private function normalizeRate(float|int|string|null $rate, float $default = 0.0): float
    {
        if ($rate === null || $rate === "") {
            return $default;
        }

        return (float) $rate;
    }

    private function mapOverviewExportRow(object $row): array
    {
        return [
            "Intake ID" => $row->intake_id,
            "Prod" => $row->name,
            "Qty" => number_format((float) $row->qty, 0, '.', ''),
            "kg" => number_format((float) $row->kg, 3, '.', ''),
            "Cost" => $this->formatCurrencyForExport($row->cost),
            "Act Cost" => $this->formatCurrencyForExport($row->actCost),
            "Sub Total" => $this->formatCurrencyForExport($row->subTotal),
            "Act Sub Total" => $this->formatCurrencyForExport($row->actSubTotal),
        ];
    }

    private function mapOverviewTotalExportRow(array $totals): array
    {
        return [
            "Intake ID" => "Total",
            "Prod" => "",
            "Qty" => number_format($totals["qty"], 0, '.', ''),
            "kg" => number_format($totals["kg"], 3, '.', ''),
            "Cost" => $this->formatCurrencyForExport($totals["cost"]),
            "Act Cost" => $this->formatCurrencyForExport($totals["actCost"]),
            "Sub Total" => $this->formatCurrencyForExport($totals["subTotal"]),
            "Act Sub Total" => $this->formatCurrencyForExport($totals["actSubTotal"]),
        ];
    }

    private function mapFinancialExportRow(
        object $row,
        string $invoiceColumn,
        bool $negateAmounts = false,
        bool $includeNewIntakeId = false
    ): array {
        $exportRow = [
            "Salesperson" => $row->salesperson,
            "Date" => (string) $row->date,
            $invoiceColumn => $row->invoice_id,
            "Customer" => $row->customer,
            "Intake ID" => $row->intake_id,
        ];

        if ($includeNewIntakeId) {
            $exportRow["New Intake ID"] = $row->new_intake_id;
        }

        return $exportRow + [
            "Pallet ID" => $row->pallet_id,
            "Nationality" => $row->nationality_name,
            "Temperature" => $row->cooling_name,
            "Prod Name" => $row->product_name,
            "Brand" => $row->brand_name,
            "Supplier" => $row->supplier_name,
            "Qty" => $row->qty,
            "Unit" => $row->unit,
            "kg" => number_format((float) $row->kg, 3, '.', ''),
            "Cost" => $this->formatCurrencyForExport($row->cost, $negateAmounts),
            "Act Cost" => $this->formatCurrencyForExport($row->actCost, $negateAmounts),
            "Sell" => $this->formatCurrencyForExport($row->sell, $negateAmounts),
            "Profit" => $this->formatCurrencyForExport($row->profit),
            "Profit %" => $this->formatPercentForExport($row->profit, $row->cost),
            "Act Profit" => $this->formatCurrencyForExport($row->actProfit),
            "Act Profit %" => $this->formatPercentForExport($row->actProfit, $row->actCost),
        ];
    }

    private function mapFinancialTotalExportRow(
        array $totals,
        string $invoiceColumn,
        bool $negateAmounts = false,
        bool $includeNewIntakeId = false
    ): array {
        $exportRow = [
            "Salesperson" => "Total",
            "Date" => "",
            $invoiceColumn => "",
            "Customer" => "",
            "Intake ID" => "",
        ];

        if ($includeNewIntakeId) {
            $exportRow["New Intake ID"] = "";
        }

        return $exportRow + [
            "Pallet ID" => "",
            "Nationality" => "",
            "Temperature" => "",
            "Prod Name" => "",
            "Brand" => "",
            "Supplier" => "",
            "Qty" => number_format($totals["qty"], 0, '.', ''),
            "Unit" => "",
            "kg" => number_format($totals["kg"], 3, '.', ''),
            "Cost" => $this->formatCurrencyForExport($totals["cost"], $negateAmounts),
            "Act Cost" => $this->formatCurrencyForExport($totals["actCost"], $negateAmounts),
            "Sell" => $this->formatCurrencyForExport($totals["sell"], $negateAmounts),
            "Profit" => $this->formatCurrencyForExport($totals["profit"]),
            "Profit %" => $this->formatPercentForExport($totals["profit"], $totals["cost"]),
            "Act Profit" => $this->formatCurrencyForExport($totals["actProfit"]),
            "Act Profit %" => $this->formatPercentForExport($totals["actProfit"], $totals["actCost"]),
        ];
    }

    private function mapSummaryExportRow(array $totals): array
    {
        return [
            "Qty" => number_format((float) $totals["qty"], 0, '.', ''),
            "kg" => number_format((float) $totals["kg"], 3, '.', ''),
            "Cost" => $this->formatCurrencyForExport($totals["cost"]),
            "Act Cost" => $this->formatCurrencyForExport($totals["actCost"]),
            "Sell" => $this->formatCurrencyForExport($totals["sell"]),
            "Profit" => $this->formatCurrencyForExport($totals["profit"]),
            "Profit %" => $this->formatPercentForExport($totals["profit"], $totals["cost"]),
            "Act Profit" => $this->formatCurrencyForExport($totals["actProfit"]),
            "Act Profit %" => $this->formatPercentForExport($totals["actProfit"], $totals["actCost"]),
        ];
    }

    private function mapStockExportRow(object $row): array
    {
        return [
            "Intake ID" => $row->intake_id,
            "Prod" => $row->name,
            "Qty" => $row->qty,
            "kg" => number_format((float) $row->kg, 3, '.', ''),
            "Cost" => $this->formatCurrencyForExport($row->cost),
            "Act Cost" => $this->formatCurrencyForExport($row->actCost),
            "Sub Total" => $this->formatCurrencyForExport($row->subTotal),
            "Act Sub Total" => $this->formatCurrencyForExport($row->actSubTotal),
        ];
    }

    private function mapStockTotalExportRow(array $totals): array
    {
        return [
            "Intake ID" => "Total",
            "Prod" => "",
            "Qty" => number_format($totals["qty"], 0, '.', ''),
            "kg" => number_format($totals["kg"], 3, '.', ''),
            "Cost" => "",
            "Act Cost" => "",
            "Sub Total" => $this->formatCurrencyForExport($totals["subTotal"]),
            "Act Sub Total" => $this->formatCurrencyForExport($totals["actSubTotal"]),
        ];
    }

    private function formatCurrencyForExport(float|int|null $value, bool $negate = false): string
    {
        if ($value === null) {
            $value = 0.0;
        }
        $formatted = "GBP " . number_format((float) $value, 3, '.', '');

        return $negate ? "-" . $formatted : $formatted;
    }

    private function formatPercentForExport(float|int|null $numerator, float|int|null $denominator): string
    {
        if ((float) $denominator === 0.0 || $denominator === null || (float) $numerator === 0.0 || $numerator === null) {
            return "0.000%";
        }

        return number_format(((float) $numerator / (float) $denominator) * 100, 3, '.', '') . "%";
    }

    private function formatCutName(Cut $cut, ?Collection $speciesById = null): string
    {
        $speciesName = $speciesById?->get($cut->species_id)?->name ?? Species::find($cut->species_id)?->name ?? "Unknown";

        return $speciesName . " " . $cut->name;
    }

    private function formatUnit(?string $unit): string
    {
        return match ($unit) {
            "PPC" => "PPC",
            "P" => "G/T",
            "C" => "Cases",
            default => $unit ?? "",
        };
    }

    private function temperatureName($coolingId, ?Collection $temperaturesById = null): string
    {
        $temperature = $temperaturesById?->get($coolingId) ?? Temperature::find($coolingId);

        return $temperature?->name ?? $temperature?->temperature ?? "";
    }

    private function guessTheOriginal(
        Product $newproduct,
        ?Collection $originalProductsById = null,
        ?Collection $potentialOriginalProductsByPalletId = null
    ): Product {
        $originalProductId = $newproduct->getAttribute("original_product_id");
        if (!empty($originalProductId)) {
            $originalProduct = $originalProductsById?->get($originalProductId) ?? Product::find($originalProductId);
            if ($originalProduct !== null) {
                return $originalProduct;
            }
        }

        $potentialProds = $potentialOriginalProductsByPalletId?->get($newproduct->original_pallet_id)
            ?? Product::where("pallet_id", $newproduct->original_pallet_id)->get();
        if (count($potentialProds) == 1) {
            return $potentialProds->first();
        }

        if (count(array_unique($potentialProds->pluck("cut_id")->toArray())) == 1) {
            return $potentialProds->first();
        }

        return $newproduct;
    }

    private function quickFloatMulti($val1, $val2, $percision = 2)
    {
        $i = 1000 * 1000;
        $val1 *= 1000;
        $val2 *= 1000;
        return FuncHelper::floorDec(($val1 * $val2) / $i, $percision);
    }
}
