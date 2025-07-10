<?php

namespace App\Http\Controllers;

use App\Exports\ShortStockExport;
use Illuminate\Http\Request;

class ShortStockController extends Controller
{
    public function index()
    {
        return view('reports.shortstock', ['data' => (new ShortStockExport)->collection()]);
    }
    public function download()
    {
        return (new ShortStockExport)->download();
    }
}
