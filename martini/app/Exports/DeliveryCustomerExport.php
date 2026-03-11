<?php

namespace App\Exports;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class DeliveryCustomerExport implements FromCollection
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
        return Customer::with("site")->where("disabled",0);
    }
    private Collection $_collection;
    /**
    * @return \Illuminate\Support\Collection
    */
    private array $colLookup = [
        "id"=>"id",
        "businessname"=>"Business Name",
        "tradingas"=>"Trading As",
        "postcode"=>"Postcode",
        "site_id"=>"Served By",
        "monday"=>"Monday",
        "tuesday"=>"Tuesday",
        "wednesday"=>"Wednesday",
        "thursday"=>"Thursday",
        "friday"=>"Friday",
        "saturday"=>"Saturday",
        "sunday"=>"Sunday",
        "address_number"=>"Restrictions",
    ];
    public function collection()
    {
        if (!isset($this->_collection))
        {
            $tmparr= $this->builder()->get();
            $this->_collection = new Collection();
            $this->_collection->add($this->colLookup);
            foreach($tmparr as $cust)
            {
                for($i=1;$i<=10;$i++)
                {
                    if ($cust->{"address{$i}_1"} != null && $cust->{"address{$i}_1"} != "" && $cust->{"postcode_{$i}"} != null && $cust->{"postcode_{$i}"} != "")
                    {
                        $newRow = new stdClass();
                        foreach ($this->colLookup as $key=>$value)
                        {
                            if (in_array($key, ["monday","tuesday","wednesday","thursday","friday","saturday","sunday"]))
                            {
                                $name = "monday";
                                $newRow->$name =  ($cust->delivery_days & DEL_MONDAY)?1:0;
                                $name = "tuesday";
                                $newRow->$name =  ($cust->delivery_days & DEL_TUESDAY)?1:0;
                                $name = "wednesday";
                                $newRow->$name =  ($cust->delivery_days & DEL_WEDNESDAY)?1:0;
                                $name = "thursday";
                                $newRow->$name =  ($cust->delivery_days & DEL_THURSDAY)?1:0;
                                $name = "friday";
                                $newRow->$name =  ($cust->delivery_days & DEL_FRIDAY)?1:0;
                                $name = "saturday";
                                $newRow->$name =  ($cust->delivery_days & DEL_SATURDAY)?1:0;
                                $name = "sunday";
                                $newRow->$name =  ($cust->delivery_days & DEL_SUNDAY)?1:0;
                            }
                            else if ($key == "postcode")
                            {
                                $newRow->$key =  $cust->{"postcode_{$i}"};
                            }
                            else if ($key == "address_number")
                            {
                                $newRow->$key = ""; $cust->{"address{$i}_number"};
                            }
                            else if ($key == "site_id")
                            {
                                $newRow->$key = $cust->site->name;
                            }
                            else $newRow->$key = $cust->$key;
                        }
                        $this->_collection->add($newRow);
                    }
                }
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
