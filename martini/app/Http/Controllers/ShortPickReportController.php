<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Cut;
use App\Models\Nationality;
use App\Models\PickerSheet;
use App\Models\Product;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ShortPickReportController extends Controller
{
    public function index(Request $request)
    {
        ini_set('memory_limit', '1G');
        $request->validate([
            'picksheet_id' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $data = collect();

        $hasPicksheetId = $request->filled('picksheet_id');
        $hasDateRange = $request->filled('start_date') && $request->filled('end_date');

        if ($hasPicksheetId) {
            $pick = PickerSheet::with(['pickWeightOuts', 'pickerItems'])->find((int) $request->input('picksheet_id'));
            if ($pick !== null) {
                $row = $this->buildShortPickRow($pick);
                if ($row !== null) {
                    $data->push($row);
                }
            }
        } elseif ($hasDateRange) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

            $picks = PickerSheet::with(['pickWeightOuts', 'pickerItems'])
                ->where('completed', true)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            foreach ($picks as $pick) {
                $row = $this->buildShortPickRow($pick);
                if ($row !== null) {
                    $data->push($row);
                }
            }
        }

        return view('reports.shortpick', [
            'data' => $data,
            'filters' => [
                'picksheet_id' => $request->input('picksheet_id', ''),
                'start_date' => $request->input('start_date', ''),
                'end_date' => $request->input('end_date', ''),
            ],
        ]);
    }

    private function buildShortPickRow(PickerSheet $pick): ?array
    {
        $weightIds = $pick->pickWeightOuts
            ->flatMap(function ($palletOut) {
                return explode(',', (string) $palletOut->weight_ids);
            })
            ->map(function ($id) {
                return trim($id);
            })
            ->filter(function ($id) {
                return $id !== '';
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values();

        $pickItems = $pick->pickerItems->where('deleted', 0);

        if ($pickItems->count() <= $weightIds->count()) {
            return null;
        }

        $productCounts = $pickItems
            ->groupBy('product_id')
            ->map(function (Collection $items) {
                return $items->count();
            });

        $actualByProduct = collect();
        if ($weightIds->isNotEmpty()) {
            $actualByProduct = Weight::whereIn('id', $weightIds->toArray())
                ->get()
                ->groupBy('product_id')
                ->map(function (Collection $weights) {
                    return $weights->count();
                });
        }

        $products = Product::whereIn('id', $productCounts->keys()->toArray())->get()->keyBy('id');

        $targetByAlias = [];
        $actualByAlias = [];
        $prodByAlias = [];

        foreach ($productCounts as $productId => $count) {
            $actualCount = (int) ($actualByProduct->get($productId) ?? 0);
            if ($count <= $actualCount) {
                continue;
            }

            $prod = $products->get($productId);
            if ($prod === null) {
                continue;
            }

            $alias = $prod->cut_id . '-' . $prod->brand_id . '-' . $prod->nationality_id;

            if (!array_key_exists($alias, $targetByAlias)) {
                $targetByAlias[$alias] = 0;
                $actualByAlias[$alias] = 0;
                $prodByAlias[$alias] = $prod;
            }

            $targetByAlias[$alias] += $count;
            $actualByAlias[$alias] += $actualCount;
        }

        if (empty($prodByAlias)) {
            return null;
        }

        $cutMap = Cut::whereIn('id', collect($prodByAlias)->pluck('cut_id')->unique()->toArray())->get()->keyBy('id');
        $brandMap = Brand::whereIn('id', collect($prodByAlias)->pluck('brand_id')->unique()->toArray())->get()->keyBy('id');
        $nationalityMap = Nationality::whereIn('id', collect($prodByAlias)->pluck('nationality_id')->unique()->toArray())->get()->keyBy('id');

        $details = [];
        $targetTotal = 0;
        $actualTotal = 0;

        foreach ($prodByAlias as $alias => $prod) {
            $target = $targetByAlias[$alias];
            $actual = $actualByAlias[$alias];
            $targetTotal += $target;
            $actualTotal += $actual;

            $nationality = $nationalityMap->get($prod->nationality_id)?->name ?? 'Unknown Nationality';
            $brand = $brandMap->get($prod->brand_id)?->name ?? 'Unknown Brand';
            $cut = $cutMap->get($prod->cut_id)?->name ?? 'Unknown Cut';

            $details[] = $nationality . ' : ' . $brand . ' : ' . $cut . ' (' . $actual . '/' . $target . ')';
        }

        $customerName = Customer::find($pick->customer_id)?->businessname ?? '';

        return [
            'Picksheet ID' => $pick->id,
            'Pick Date' => $pick->date?->format('Y-m-d') ?? '',
            'Customer' => $customerName,
            'Missing Items' => max($targetTotal - $actualTotal, 0),
            'Details' => implode(' | ', $details),
        ];
    }
}
