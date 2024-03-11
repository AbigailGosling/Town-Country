<?php

namespace App\Http\Controllers;

use App\Helpers\ReportHelper;
use App\Models\Report;
use App\Models\ReportColumn;
use App\Models\ReportVersion;
use App\Models\ReportVersionColumn;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
    public function show(Report $report,ReportVersion $report_version)
    {
        $reportColIDs = ReportVersionColumn::where(["report_version_id"=>$report_version->id])->orderBy("order")->get()->pluck("report_column_id")->toArray();
        $reportCol = ReportColumn::whereIn('id',$reportColIDs)->get();
        $dataRanges = ReportHelper::getDataRange(new Carbon(1701388800),new Carbon(1701993600));
        return view("reports.show",[
            "report"=>$report,
            "report_version"=>$report_version,
            "columns"=>$reportCol,
            "debits"=>ReportHelper::resolveBody($reportCol,$dataRanges[0]),
            "credits"=>ReportHelper::resolveBody($reportCol,$dataRanges[1]),
        ]);
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
