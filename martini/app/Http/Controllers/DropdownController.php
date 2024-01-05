<?php
  
namespace App\Http\Controllers;

use App\Models\CutGroup;
use Illuminate\Http\Request;
  
class DropdownController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function fetchCutGroups(Request $request)
    {
        return CutGroup::generateHTMLList(null,$request->input("species_id",null));
    }
}