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
        $start = request()->input("start",null);
        $end = request()->input("end",null);
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
        $reportColIDs = ReportVersionColumn::where(["report_version_id"=>$report_version->id])->orderBy("order")->get()->pluck("report_column_id")->toArray();
        $reportCol = ReportColumn::whereIn('id',$reportColIDs)->get();
        switch($report->mode)
        {
            case "product":
            {
                $dataRanges = ReportHelper::getProductRange($startCarbon,$endCarbon);
                break;
            }
            case "invoice":
            {
                $dataRanges = ReportHelper::getInvoiceRange($startCarbon,$endCarbon);
                break;
            }
        }
        return view("reports.show",[
            "report"=>$report,
            "report_version"=>$report_version,
            "columns"=>$reportCol,
            "start"=>$startCarbon,
            "end"=>$endCarbon,
            "debits"=>ReportHelper::resolveBody($reportCol,$dataRanges[0],"debits"),
            "credits"=>ReportHelper::resolveBody($reportCol,$dataRanges[1],"credits"),
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
