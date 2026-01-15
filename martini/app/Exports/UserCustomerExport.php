<?php

namespace App\Exports;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Cut;
use App\Models\CutGroup;
use App\Models\Intake;
use App\Models\Nationality;
use App\Models\OldUser;
use App\Models\Pallet;
use App\Models\PickerItem;
use App\Models\Product;
use App\Models\Species;
use App\Models\Weight;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class UserCustomerExport implements FromCollection
{
    function __construct()
    {
        define('DEL_SUNDAY',     1);
        define('DEL_SATURDAY',   2);
        define('DEL_FRIDAY',     4);
        define('DEL_THURSDAY',   8);
        define('DEL_WEDNESDAY', 16);
        define('DEL_TUESDAY',   32);
        define('DEL_MONDAY',    64);
    }
    public function builder():Builder
    {
        return Customer::with("user")->with("site")->where("disabled",0);
    }
    private Collection $_collection;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if (!isset($this->_collection))
        {
            $tmparr=  $this->builder()->get();
            $this->_collection = new Collection();
            foreach($tmparr as $cust)
            {
                $newRow = new stdClass();
                foreach ($cust->getAttributes() as $key=>$value)
                {
                    if ($key == "delivery_days")
                    {
                        $name = "monday";
                        $newRow->$name =  ($value & DEL_MONDAY)?1:0;
                        $name = "tuesday";
                        $newRow->$name =  ($value & DEL_TUESDAY)?1:0;
                        $name = "wednesday";
                        $newRow->$name =  ($value & DEL_WEDNESDAY)?1:0;
                        $name = "thursday";
                        $newRow->$name =  ($value & DEL_THURSDAY)?1:0;
                        $name = "friday";
                        $newRow->$name =  ($value & DEL_FRIDAY)?1:0;
                        $name = "saturday";
                        $newRow->$name =  ($value & DEL_SATURDAY)?1:0;
                        $name = "sunday";
                        $newRow->$name =  ($value & DEL_SUNDAY)?1:0;
                    }
                    else $newRow->$key = $value;
                    if ($key == "tradingas")
                    {
                        $name = "salesman_username";
                        $newRow->$name = $cust->user->name;
                        $name = "site_name";
                        $newRow->$name = $cust->site->name;
                    }
                }
                $this->_collection->add($newRow);
            }
        }
        return $this->_collection;
    }
    public function download() {
        return Excel::download($this,Carbon::now()->format('d-M-Y').".xlsx");
    }
    public function file() {
        $r = Carbon::now()->format('d-M-Y').".xlsx";
        Excel::store($this,$r,"public");
        return $r;
    }
}
