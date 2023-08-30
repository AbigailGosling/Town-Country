<?php

use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        $temp = new Customer();
        $tableName = $temp->getTable();
        $columns = Schema::connection('tandc_live')->getColumnListing($tableName);
        $this->process(Customer::all(),$columns);
        $temp = new Supplier();
        $tableName = $temp->getTable();
        $columns = Schema::connection('tandc_live')->getColumnListing($tableName);
        $this->process(Supplier::all(),$columns);
    }
    public function process($array,$columns)
    {
        foreach($array as $customer){
            foreach($columns as $column){
                $value = $customer[$column];
                while (strpos($value,"\\\\") > -1){
                    $value = str_replace("\\\\","\\",$value);
                }
                $value = str_replace("\\r","\r",$value);
                $value = str_replace("\\n","\n",$value);
                if ($value == "\\") $value = "";
                $customer[$column] = trim($value);
            }
            $customer->save();
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
