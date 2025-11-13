<?php

namespace App\Http\Controllers;

use App\Models\CutGroup;
use Illuminate\Http\Request;

class CutGroupController extends Controller
{
    public function getCutGroups($speciesId)
    {
        $cuts = CutGroup::where('species_id', $speciesId)
                ->select('id', 'name')
                ->get();

        return response()->json($cuts);
    }
}
