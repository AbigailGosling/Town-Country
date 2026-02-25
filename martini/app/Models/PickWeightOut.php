<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PickWeightOut
 *
 * @property int $id
 * @property int|null $pickersheet_id
 * @property string|null $weight_ids
 * @property string|null $stringName
 * @property string|null $picker_ids
 *
 * @package App\Models
 */
class PickWeightOut extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'pickWeightOut';
	public $timestamps = false;

	protected $casts = [
		'pickersheet_id' => 'int'
	];

	protected $fillable = [
		'pickersheet_id',
		'weight_ids',
		'stringName',
		'picker_ids'
	];

	public function outgoingPalletLinks()
	{
		return $this->hasMany(OutgoingPalletPickWeight::class, 'pickWeightOut_id');
	}

	public function pickerSheet()
	{
		return $this->belongsTo(PickerSheet::class, 'pickersheet_id');
	}
    public function getWeights():array
    {
        return array_map('intval', array_filter(explode(',', $this->weight_ids)));
    }
	public function getTotalWeight()
	{
		if (!$this->weight_ids) {
			return 0;
		}

		$weightIds = $this->getWeights();
		if (empty($weightIds)) {
			return 0;
		}

		$weights = Weight::whereIn('id', $weightIds)->get();
		$total = 0;

		foreach ($weights as $weight) {
            if($weight->weight_tear == $weight->weight_gross){
                (double)$netWeight = (double)$weight->weight_gross;
            }else{
                (double)$netWeight = (double)$weight->weight_gross - (double)$weight->weight_tear;
            }
			$total += $netWeight;
		}
		return round($total, 3);
	}
    	/**
	 * Returns the minimum number of weights (from the start of the list) whose sum is <= targetWeight.
	 * If no single weight fits, returns 0.
	 * @param float $targetWeight
	 * @return int
	 */
	public function getNumWeightsForTarget($targetWeight)
	{
		$weightIds = $this->getWeights();
		if (empty($weightIds)) {
			return 0;
		}
		$weights = Weight::whereIn('id', $weightIds)->get()->keyBy('id');
		$sum = 0;
		$count = 0;
		foreach ($weightIds as $wid) {
			if (!isset($weights[$wid])) continue;
			$weight = $weights[$wid];
			$netWeight = ($weight->weight_tear == $weight->weight_gross)
				? (double)$weight->weight_gross
				: (double)$weight->weight_gross - (double)$weight->weight_tear;
			if ($sum + $netWeight > $targetWeight) {
				break;
			}
			$sum += $netWeight;
			$count++;
		}
		return $count;
	}
    public function formatPickWeightOutSummary(): array
    {
        $pickerSheet = $this->pickerSheet;

        return [
            'id' => $this->id,
            'pickersheet_id' => $this->pickersheet_id,
            'estimated_delivery_date' => $pickerSheet?->estimated_delivery_date,
            'order_reference_number' => $pickerSheet?->orderReferenceNumber,
            'total_weight' => $this->getTotalWeight(),
            'weight_count' => count($this->getWeights()),
        ];
    }
    public static function processPickerSheetsForPalletization(Collection $pickerSheets)
    {
        self::whereIn('pickersheet_id', $pickerSheets->pluck('id'))->chunkById(10000, function ($pickWeightOuts) {
            /** @var PickWeightOut $pickWeightOut */
            foreach ($pickWeightOuts as $pickWeightOut) {
                $pickerSheet = PickerSheet::find($pickWeightOut->pickersheet_id);
                if (!$pickerSheet || $pickerSheet->completed != 1 || $pickerSheet->deleted != 0) {
                    continue;
                }
                $edd = Carbon::createFromFormat('d/m/Y', $pickerSheet->estimated_delivery_date);
                $customerId = $pickerSheet->customer_id;
                $addressId = $pickerSheet->addressid ?? 1;
                $date = $edd->format('Y-m-d');


                do {
                    $palletSearch = OutgoingPallet::where('customer_id', $customerId)
                        ->where('address_id', $addressId)
                        ->where('estimated_delivery_date', $date);
                    $palletSearch = $palletSearch->get();
                    $pallet = null;
                    $lowestWeight = null;
                    $lowestPallet = null;
                    $stdMax = OutgoingPalletType::find(1)->max_weight;
                    foreach ($palletSearch as $p) {
                        $thisWeight = $p->getTotalWeight();
                        if ($lowestWeight === null || $thisWeight < $lowestWeight) {
                            $lowestWeight = $thisWeight;
                            $lowestPallet = $p;
                            $pMaxWeight = OutgoingPalletType::find($p->outgoing_pallet_type_id)->max_weight;
                            if ($pMaxWeight - $thisWeight >= $pickWeightOut->getTotalWeight()) {
                                $pallet = $p;
                                $stdMax = $pMaxWeight;
                            }
                        }
                    }
                    if (count($palletSearch)==0) {
                        $pallet = OutgoingPallet::create([
                            'customer_id' => $customerId,
                            'address_id' => $addressId,
                            'outgoing_pallet_type_id' => 1,
                            'dispatched' => $pickerSheet->deliverynote_printed==1,
                            'estimated_delivery_date' => $date,
                        ]);
                    }
                    if ($pallet) {
                        OutgoingPalletPickWeight::create([
                            'outgoing_pallet_id' => $pallet->id,
                            'pickWeightOut_id' => $pickWeightOut->id,
                        ]);
                    }
                    else
                    {
                        $spaceLeft = $stdMax - $lowestWeight;
                        $numToMove = $pickWeightOut->getNumWeightsForTarget($spaceLeft);
                        if ($numToMove > 0 && $numToMove < count($pickWeightOut->getWeights())) {
                            self::SPLIT_PICK($pickWeightOut->id, $numToMove, $lowestPallet->id, null);
                        }
                        else
                        {
                            break;
                        }
                    }
                    $pickWeightOut->refresh();
                }   while ($pickWeightOut->getTotalWeight() > $stdMax);
            }
        });
    }
    public static function SPLIT_PICK(int $pickWeightOutId, int $moveWeightCount, ?int $targetOutgoingPalletId, ?int $fromOutgoingPalletId): array
    {
       $sourcePickWeightOut = self::query()->findOrFail($pickWeightOutId);
        $pickerSheetId = (int) $sourcePickWeightOut->pickersheet_id;
        $sourceWeightIds = $sourcePickWeightOut->getWeights();
        $sourceWeightIds = array_values(array_filter($sourceWeightIds, fn ($id) => is_numeric($id)));

        $moveWeightCount = (int) $moveWeightCount;
        $sourceCount = count($sourceWeightIds);

        if ($sourceCount <= 1 || $moveWeightCount >= $sourceCount) {
            abort(422, 'Split quantity must be less than total weights on the pick.');
        }

        $movedWeightIds = self::pickWeightsForSplit($sourceWeightIds, $moveWeightCount);
        if (count($movedWeightIds) !== $moveWeightCount) {
            abort(422, 'Unable to split requested weight count.');
        }

        $remainingWeightIds = array_values(array_diff($sourceWeightIds, $movedWeightIds));

        $sourcePickWeightOut->update([
            'weight_ids' => implode(',', $remainingWeightIds),
        ]);

        $newPickWeightOut = self::query()->create([
            'pickersheet_id' => $sourcePickWeightOut->pickersheet_id,
            'weight_ids' => implode(',', $movedWeightIds),
            'stringName' => $sourcePickWeightOut->stringName,
            'picker_ids' => $sourcePickWeightOut->picker_ids,
        ]);

        if (!empty($targetOutgoingPalletId)) {
            OutgoingPalletPickWeight::query()->firstOrCreate([
                'outgoing_pallet_id' => (int) $targetOutgoingPalletId,
                'pickWeightOut_id' => $newPickWeightOut->id,
            ]);
        }

        $sourceSummaryPick = null;
        if (!empty($fromOutgoingPalletId)) {
            $sourceSummaryPick = self::recombineWithinPalletByPickerSheet((int) $fromOutgoingPalletId, $pickerSheetId);
        } else {
            $sourceSummaryPick = self::recombineWithinUnloadedByPickerSheet($pickerSheetId);
        }

        $movedSummaryPick = null;
        if (!empty($targetOutgoingPalletId)) {
            $movedSummaryPick = self::recombineWithinPalletByPickerSheet((int) $targetOutgoingPalletId, $pickerSheetId);
        } else {
            $movedSummaryPick = self::recombineWithinUnloadedByPickerSheet($pickerSheetId);
        }

        return [
            'source' => $sourceSummaryPick ? $sourceSummaryPick->formatPickWeightOutSummary() : $sourcePickWeightOut->fresh('pickerSheet')->formatPickWeightOutSummary(),
            'moved' => $movedSummaryPick ? $movedSummaryPick->formatPickWeightOutSummary() : $newPickWeightOut->fresh('pickerSheet')->formatPickWeightOutSummary(),
        ];
    }
    public static function recombineWithinPalletByPickerSheet(int $outgoingPalletId, int $pickerSheetId): ?PickWeightOut
    {
        $pickWeightOutIds = OutgoingPalletPickWeight::query()
            ->where('outgoing_pallet_id', $outgoingPalletId)
            ->whereHas('pickWeightOut', function ($query) use ($pickerSheetId) {
                $query->where('pickersheet_id', $pickerSheetId);
            })
            ->pluck('pickWeightOut_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($pickWeightOutIds === []) {
            return null;
        }

        $keeperId = self::mergePickWeightOutIds($pickWeightOutIds, $outgoingPalletId);
        OutgoingPallet::CHECK_UPDATE_ESTIMATED_DELIVERY_DATE($outgoingPalletId);
        return PickWeightOut::query()->with('pickerSheet')->find($keeperId);
    }
    public static function recombineWithinUnloadedByPickerSheet(int $pickerSheetId): ?PickWeightOut
    {
        $pickWeightOutIds = PickWeightOut::query()
            ->where('pickersheet_id', $pickerSheetId)
            ->doesntHave('outgoingPalletLinks')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($pickWeightOutIds === []) {
            return null;
        }

        $keeperId = self::mergePickWeightOutIds($pickWeightOutIds, null);

        return PickWeightOut::query()->with('pickerSheet')->find($keeperId);
    }
    private static function pickWeightsForSplit(array $sourceWeightIds, int $moveWeightCount): array
    {
        $weights = Weight::query()
            ->select(['id', 'product_id'])
            ->whereIn('id', $sourceWeightIds)
            ->get()
            ->keyBy('id');

        $positions = array_flip($sourceWeightIds);
        $groupedByProduct = [];

        foreach ($sourceWeightIds as $weightId) {
            $weight = $weights->get((int) $weightId);
            if (!$weight) {
                continue;
            }
            $groupedByProduct[$weight->product_id] ??= [];
            $groupedByProduct[$weight->product_id][] = (int) $weightId;
        }

        uasort($groupedByProduct, function (array $a, array $b) use ($positions) {
            $countCompare = count($b) <=> count($a);
            if ($countCompare !== 0) {
                return $countCompare;
            }

            $firstA = min(array_map(fn ($id) => $positions[(string) $id] ?? PHP_INT_MAX, $a));
            $firstB = min(array_map(fn ($id) => $positions[(string) $id] ?? PHP_INT_MAX, $b));

            return $firstA <=> $firstB;
        });

        $selected = [];
        foreach ($groupedByProduct as $weightIds) {
            foreach ($weightIds as $weightId) {
                if (count($selected) >= $moveWeightCount) {
                    break 2;
                }
                $selected[] = $weightId;
            }
        }

        if (count($selected) < $moveWeightCount) {
            foreach ($sourceWeightIds as $weightId) {
                $numericWeightId = (int) $weightId;
                if (in_array($numericWeightId, $selected, true)) {
                    continue;
                }
                $selected[] = $numericWeightId;
                if (count($selected) >= $moveWeightCount) {
                    break;
                }
            }
        }

        return array_values($selected);
    }
    private static function mergePickWeightOutIds(array $pickWeightOutIds, ?int $contextOutgoingPalletId): int
    {
        $pickWeightOutIds = array_values(array_unique(array_map('intval', $pickWeightOutIds)));
        sort($pickWeightOutIds);

        if (count($pickWeightOutIds) === 1) {
            return $pickWeightOutIds[0];
        }

        $pickWeightOuts = PickWeightOut::query()->whereIn('id', $pickWeightOutIds)->orderBy('id')->get();
        $keeper = $pickWeightOuts->first();
        if (!$keeper) {
            return $pickWeightOutIds[0];
        }

        $combinedWeightIds = [];
        foreach ($pickWeightOuts as $pickWeightOut) {
            foreach ($pickWeightOut->getWeights() as $weightId) {
                if (!is_numeric($weightId)) {
                    continue;
                }
                $numericWeightId = (int) $weightId;
                if (!in_array($numericWeightId, $combinedWeightIds, true)) {
                    $combinedWeightIds[] = $numericWeightId;
                }
            }
        }

        $keeper->update([
            'weight_ids' => implode(',', $combinedWeightIds),
        ]);

        foreach ($pickWeightOuts->slice(1) as $redundant) {
            if ($contextOutgoingPalletId !== null) {
                OutgoingPalletPickWeight::query()
                    ->where('outgoing_pallet_id', $contextOutgoingPalletId)
                    ->where('pickWeightOut_id', $redundant->id)
                    ->delete();
            }

            $stillLinked = OutgoingPalletPickWeight::query()
                ->where('pickWeightOut_id', $redundant->id)
                ->exists();

            if (!$stillLinked) {
                $redundant->delete();
            }
        }

        return (int) $keeper->id;
    }
}
