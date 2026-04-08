<?php

namespace App\Http\Controllers;

use App\Exports\ReturnedStockExport;
use Illuminate\Http\Request;

class ReturnedStockReportController extends Controller
{
    public function index()
    {
        return view('reports.shortstock', ['data' => (new ReturnedStockExport())->collection()]);
    }
    public function download()
    {
        return (new ReturnedStockExport())->download();
    }
}
