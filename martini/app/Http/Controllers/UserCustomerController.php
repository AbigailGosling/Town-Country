<?php

namespace App\Http\Controllers;

use App\Exports\UserCustomerExport;
use Illuminate\Http\Request;

class UserCustomerController extends Controller
{
    public function index()
    {
        return view('reports.usercustomer', ['data' => (new UserCustomerExport)->builder()->paginate(10000)]);
    }
    public function download()
    {
        return (new UserCustomerExport)->download();
    }
}
