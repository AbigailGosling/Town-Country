<?php

namespace App\Http\Controllers;

use App\Models\Cut;
use Illuminate\Http\Request;

class CutController extends Controller
{
    public function getCuts($cutGroupId)
    {
        $cuts = Cut::where('cutgroup_id', $cutGroupId)
                ->select('id', 'name')
                ->get();

        return response()->json($cuts);
    }
}
