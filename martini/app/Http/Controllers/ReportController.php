<?php

namespace App\Http\Controllers;

use App\Helpers\ReportHelper;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\CutGroup;
use App\Models\HealthMark;
use App\Models\Nationality;
use App\Models\Report;
use App\Models\ReportColumn;
use App\Models\ReportTable;
use App\Models\ReportTableColumn;
use App\Models\ReportTableLink;
use App\Models\Species;
use App\Models\Supplier;
use App\Models\Temperature;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use stdClass;

class ReportController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function show(Report $report)
    {
        if (request()->has("selector") && request()->input("selector")!=$report->id)
        {
            Session::put("start",request()->input("start",null));
            Session::put("end",request()->input("end",null));
            Session::put("dateType",request()->input("dateType",null));
            return redirect()->to(route("report.show",["report"=>request()->input("selector")]));
        }
        $start = request()->input("start", Session::get("start",null));
        $end = request()->input("end", Session::get("end",null));
        $dateType = request()->input("dateType","assembled");
        Session::forget(["start","end","dateType"]);

        if ($start != null) {
            $startCarbon = Carbon::parse($start);
        }
        else {
            $startCarbon = new Carbon();
        }
        $startCarbon->startOfDay();
        if ($end != null) {
            $endCarbon = Carbon::parse($end);
        }
        else {
            $endCarbon = new Carbon();
        }
        $endCarbon->endOfDay();
        $INTERESTED_PICKS = [];
        $INVOICE_ID = request()->input("pickersheet_id");
        $INTAKE_ID = request()->input('intake_id');
        $PALLET_ID = request()->input('pallet_id');
        $USER_ID = request()->input('user_id');
        $CUSTOMER_ID = request()->input('customer_id');
        $SPECIES_ID = request()->input('species_id');
        $CUTGROUP_ID = request()->input('cutgroup_id');
        $COOLING_ID = request()->input('cooling_id');
        $BRAND_ID = request()->input('brand_id');
        $NATIONALITY_ID = request()->input('nationality_id');
        $SUPPLIER_ID = request()->input('supplier_id');
        $HEALTH_ID = request()->input('health_id');
        $INTERNAL_NUM = request()->input('internal_num');
        $IMPORT_NUM = request()->input('import_num');

        $filters = ReportHelper::filterBuilder($INTERESTED_PICKS,$INVOICE_ID,$INTAKE_ID,$PALLET_ID,$USER_ID,$CUSTOMER_ID,$SPECIES_ID,$CUTGROUP_ID,$COOLING_ID,$BRAND_ID,$NATIONALITY_ID,$SUPPLIER_ID,$HEALTH_ID,$INTERNAL_NUM,$IMPORT_NUM);
        if (count(array_keys($filters))==0)$filters = null;
        $dataRanges = ReportHelper::getCollectionsForReportRange($report,$dateType,$startCarbon,$endCarbon,$INTERESTED_PICKS,request()->input("customer_id",null),request()->input("user_id",null),$filters);
        $dataRanges2= [];
        foreach ($dataRanges as $key=>$range)
        {
            $range2=ReportHelper::resolveTableBody($report->getTables()[$key],$range);
             $dataRanges2[] = $range2;
        }
        while (count($dataRanges2)<4){
            $dataRanges2[] = [];
        }
        $args = [
            "reports"=>Report::all(),
            "species"=>Species::all(),
            "cut_groups"=>CutGroup::all(),
            "brands"=>Brand::all(),
            "suppliers"=>Supplier::all(),
            "nationalities"=>Nationality::all(),
            "tempuratures"=>Temperature::all(),
            "users"=>User::all(),
            "customers"=>Customer::all(),
            'health_marks'=>HealthMark::all(),
            "report"=>$report,
            "start"=>$startCarbon,
            "end"=>$endCarbon,
            "dateType"=>$dateType,
            "dataRanges"=>$dataRanges2,
            "pickersheet_id"=>$INVOICE_ID,
            'intake_id'=>$INTAKE_ID,
            'pallet_id'=>$PALLET_ID,
            'user_id'=>$USER_ID,
            'customer_id'=>$CUSTOMER_ID,
            'species_id'=>$SPECIES_ID,
            'cutgroup_id'=>$CUTGROUP_ID,
            'cooling_id'=>$COOLING_ID,
            'brand_id'=>$BRAND_ID,
            'nationality_id'=>$NATIONALITY_ID,
            'supplier_id'=>$SUPPLIER_ID,
            'health_id'=>$HEALTH_ID,
            'internal_num'=>$INTERNAL_NUM,
            'import_num'=>$IMPORT_NUM,

        ];
        return view("reports.show", $args);
    }
    public function userCustomer()
    {
        $result = new Collection();

        foreach (User::where("disabled",false)->orderBy("name")->get() as $user)
        {
            foreach (Customer::where("disabled",false)->where("default_salesman_id",$user->id)->orderBy("businessname")->get() as $customer)
            {
                $out = new stdClass();
                $out->user_id = $user->id;
                $out->user_name = $user->name;
                $out->customer_id= $customer->id;
                $out->customer_name=$customer->businessname;
                $result->add($out);
            }
        }
        return view("reports.usercustomer", ["list"=>$result]);
    }
}
