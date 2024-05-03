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
        $args = [
            "reports"=>Report::all(),
            "report"=>$report,
            "start"=>$startCarbon,
            "end"=>$endCarbon,
            "dateType"=>$dateType,
            "debits"=>ReportHelper::resolveTableBody(($report->getTables())[0],$dataRanges[0]),
            "credits"=>ReportHelper::resolveTableBody($report->getTables()[1],$dataRanges[1]),
        ];
        if (count($dataRanges)>2){
            $args["supdebits"] = ReportHelper::resolveTableBody($report->getTables()[2],$dataRanges[2]);
            $args["supcredits"] = ReportHelper::resolveTableBody($report->getTables()[3],$dataRanges[3]);
        }
        else {
            $args["supdebits"] = [];
            $args["supcredits"] = [];
        }
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
