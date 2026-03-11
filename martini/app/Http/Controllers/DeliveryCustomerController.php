<?php

namespace App\Http\Controllers;

use App\Exports\DeliveryCustomerExport;
use Illuminate\Http\Request;

class DeliveryCustomerController extends Controller
{
    public function index()
    {
        return view('reports.deliverycustomer', ['data' => (new DeliveryCustomerExport)->collection()]);
    }
    public function download()
    {
        return (new DeliveryCustomerExport)->download();
    }
}
