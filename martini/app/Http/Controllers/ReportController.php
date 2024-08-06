<?php

namespace App\Http\Controllers;

use App\Helpers\ReportHelper;
use App\Models\Report;
use App\Models\ReportColumn;
use App\Models\ReportTable;
use App\Models\ReportTableColumn;
use App\Models\ReportTableLink;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

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
        $dataRanges = ReportHelper::getCollectionsForReportRange($report,$dateType,$startCarbon,$endCarbon);
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
            "report"=>$report,
            "start"=>$startCarbon,
            "end"=>$endCarbon,
            "dateType"=>$dateType,
            "dataRanges"=>$dataRanges2,
        ];
        return view("reports.show", $args);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\Response
     */
    public function destroy(Report $report)
    {
        //
    }
}
