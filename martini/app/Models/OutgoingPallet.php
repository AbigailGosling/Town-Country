<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'pod_sent',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'address_id' => 'integer',
        'outgoing_pallet_type_id' => 'integer',
        'estimated_delivery_date' => 'date',
        'dispatched' => 'boolean',
        'pod_sent' => 'boolean',
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
        foreach ($this->pickWeightOuts as $link) {
           $pickWeightOut = $link->pickWeightOut;
            if (count($pickWeightOut->getWeights()) == 0)
            {
                $link->delete();
                $pickWeightOut->delete();
                continue;
            }
            $total += $pickWeightOut->getTotalWeight();
        }
        return round($total, 3);
    }
    private string $_temperatureCategoryCache = '';
    public function getTemperatureCategory()
    {
        if ($this->_temperatureCategoryCache == '') {
            foreach ($this->pickWeightOuts as $link) {
                $pickWeightOut = $link->pickWeightOut;
                if (count($pickWeightOut->getWeights()) == 0)
                {
                    $link->delete();
                    $pickWeightOut->delete();
                    continue;
                }
                if ($this->_temperatureCategoryCache == '')
                {
                    $weight = Weight::find($pickWeightOut->getWeights()[0]);
                if (!$weight) return '';
                    $product = Product::find($weight->product_id);
                    if ($product->cooling_id == 2){
                        $this->_temperatureCategoryCache = 'Frozen';
                    }
                    else {
                        $this->_temperatureCategoryCache = 'Fresh';
                    }
                }
            }

        }
        return $this->_temperatureCategoryCache;
    }
    public function normalizePlanningTemperatureCategory(): string
    {
        $category = strtolower(trim((string) ($this->getTemperatureCategory() ?? '')));

        if (str_contains($category, 'frozen')) {
            return 'frozen';
        }

        if (str_contains($category, 'fresh')) {
            return 'fresh';
        }

        return '';
    }

    public function isOverWeight()
    {
        if (!$this->outgoingPalletType) {
            return false;
        }
        return $this->getTotalWeight() > $this->outgoingPalletType->max_weight;
    }

    public function address()
    {
        return $this->belongsTo(ClientAddress::class, 'address_id', 'address_id')->where([['client_type', ClientType::CUSTOMER->value],['client_id', $this->customer_id]]);
    }

    public function checkUpdateEstimatedDeliveryDate()
    {
        $pickWeightOuts = $this->pickWeightOuts()->with('pickWeightOut')->get()->pluck('pickWeightOut');
        $dates = [];
        foreach ($pickWeightOuts as $pickWeightOut) {
            if (!$pickWeightOut) continue;
            $pickerSheet = PickerSheet::find($pickWeightOut->pickersheet_id);
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
