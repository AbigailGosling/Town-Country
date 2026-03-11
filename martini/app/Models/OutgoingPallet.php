<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OutgoingPallet extends Model
{
    use HasFactory;

    protected $connection = 'tandc_live';

    protected $table = 'outgoing_pallet';

    protected $fillable = [
        'outgoing_pallet_type_id',
        'customer_id',
        'address_id',
        'estimated_delivery_date',
        'dispatched',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'address_id' => 'integer',
        'outgoing_pallet_type_id' => 'integer',
        'estimated_delivery_date' => 'date',
        'dispatched' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function outgoingPalletType()
    {
        return $this->belongsTo(OutgoingPalletType::class, 'outgoing_pallet_type_id');
    }

    public function pickWeightOuts()
    {
        return $this->hasMany(OutgoingPalletPickWeight::class, 'outgoing_pallet_id');
    }
    public function vehicleAllocations()
    {
        return $this->belongsTo(VehicleOutgoingPalletAllocation::class, 'outgoing_pallet_id');
    }
    public function getTotalWeight()
    {
        $total = 0;
        foreach (OutgoingPalletPickWeight::where('outgoing_pallet_id', $this->id)->get() as $link) {
            foreach (PickWeightOut::where('id', $link->pickWeightOut_id)->get() as $pickWeightOut) {
                $total += $pickWeightOut->getTotalWeight();
            }
        }
        Log::info("Calculated total weight for OutgoingPallet ID {$this->id}: {$total} kg");
        return round($total, 3);
    }
    private string $_temperatureCategoryCache = '';
    public function getTemperatureCategory()
    {
        if ($this->_temperatureCategoryCache == '') {
            foreach (OutgoingPalletPickWeight::where('outgoing_pallet_id', $this->id)->get() as $link) {
                $pickWeightOut = PickWeightOut::where('id', $link->pickWeightOut_id)->first();
                $weight = Weight::find($pickWeightOut->getWeights()[0]);
                $product = Product::find($weight->product_id);
                if ($product->cooling_id == 2){
                    $this->_temperatureCategoryCache = 'Frozen';
                }
                else {
                    $this->_temperatureCategoryCache = 'Fresh';
                }
            }

        }
        return $this->_temperatureCategoryCache;
    }
    public function isOverWeight()
    {
        if (!$this->outgoingPalletType) {
            return false;
        }
        return $this->getTotalWeight() > $this->outgoingPalletType->max_weight;
    }

    /* public function address()
    {
        return $this->belongsTo(Address::class);
    } */
    /**
     * Sync this pallet's estimated_delivery_date to the common date of all attached PickerSheets, or null if mixed.
     */

    public function checkUpdateEstimatedDeliveryDate()
    {
        $pickWeightOuts = $this->pickWeightOuts()->with('pickWeightOut')->get()->pluck('pickWeightOut');
        $dates = [];
        foreach ($pickWeightOuts as $pickWeightOut) {
            if (!$pickWeightOut) continue;
            $pickerSheet = \App\Models\PickerSheet::find($pickWeightOut->pickersheet_id);
            if ($pickerSheet && $pickerSheet->estimated_delivery_date) {
                $dates[] = $pickerSheet->estimated_delivery_date;
            }
        }
        $dates = array_unique($dates);
        if (count($dates) === 1) {
            $this->estimated_delivery_date = Carbon::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d');
        } else if (count($dates) > 1) {
            $this->estimated_delivery_date = Carbon::createFromFormat('d/m/Y', min($dates))->format('Y-m-d');
        }
        else {
            $this->estimated_delivery_date = Carbon::today()->format('Y-m-d');
        }
        $this->save();
    }
    public static function CHECK_UPDATE_ESTIMATED_DELIVERY_DATE($outgoingPalletId)
    {
        $pallet = self::find($outgoingPalletId);
        if ($pallet) {
            $pallet->checkUpdateEstimatedDeliveryDate();
        }
    }
}
