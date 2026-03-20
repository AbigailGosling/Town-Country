<?php

namespace App\Http\Controllers;

use App\Models\CutGroup;
use App\Models\CutGroupNationalityDate;
use App\Models\Nationality;
use App\Models\Species;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CutGroupNationalityDateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CutGroupNationalityDate::class, 'cutdate');
    }

    private function baseQuery():Builder
    {
        return CutGroupNationalityDate::with('cutgroup','species')->join('cutgroups','cutgroup_nationality_dates.cutgroup_id','=','cutgroups.id')->join('species','cutgroups.species_id','=','species.id')->select('cutgroup_nationality_dates.*','species.id as species_id2','cutgroups.id as cutgroups_id2');
    }
    private function dataSetBuilder(Builder $cutgroup_nationality_datess = null, Request $request = null):array
    {
        if ($cutgroup_nationality_datess == null) $cutgroup_nationality_datess = $this->baseQuery();
        if ($request != null)
        {
            $nationalities = Nationality::generateHTMLList($request->input('nationality_id', null));
            $species = Species::generateHTMLList($request->input('species_id',  null));
            $cutgroups = CutGroup::generateHTMLList($request->input('cutgroup_id', null),$request->input('species_id',  null));
        }
        else
        {
            $nationalities = Nationality::generateHTMLList();
            $species = Species::generateHTMLList();
            $cutgroups = CutGroup::generateHTMLList();
        }
        $dataSet= [
            'cutgroup_nationality_datess' => $cutgroup_nationality_datess->paginate(25),
            'nationalities' =>  $nationalities,
            'species' => $species,
            'cutgroups' => $cutgroups,
            'showcutgroups' => ($request && ($request->has('species_id') || $request->has('cutgroup_id'))),
        ];
        return $dataSet;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view(
            'cutdates.index', $this->dataSetBuilder()
        );
    }
    /**
     * GET method to search users in the system from the Users Index page
     * @param Request $request
     * @return View
     */
    public function search(Request $request)
    {
        $nationality = $request->input('nationality_id', null);
        $species = $request->input('species_id', null);
        $cutgroup = $request->input('cutgroup_id', null);
        $query = $this->baseQuery();
        if ($nationality != null) $query = $query->where("cutgroup_nationality_dates.nationality_id",$nationality);
        if ($cutgroup != null) $query = $query->where("cutgroup_nationality_dates.cutgroup_id",$cutgroup);
        else if ($species != null) $query = $query->with("species")->where("species.id",$species);
        return view(
            'cutdates.index', $this->dataSetBuilder($query,$request)
        );
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->show();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nationality_id' => ['required', 'int'],
            'cutgroup_id' => ['required','int'],
            'warning' => ['required','int'],
            'danger' => ['required', 'int']
        ]);
        $input = $request->all();
        if (Nationality::find($input['nationality_id'])==null) return redirect()->back()->withErrors(__('nationality_id'));
        if (CutGroup::find($input['cutgroup_id'])==null) return redirect()->back()->withErrors(__('cutgroup_id'));
        if (CutGroupNationalityDate::where([["nationality_id",$input['nationality_id']],["cutgroup_id",$input['cutgroup_id']]])->first()!=null)
            return redirect()->back()->withErrors(__('A rule for this Cutgroup and Nationality already exists'));
        $cutdate = new CutGroupNationalityDate;
        $cutdate->nationality_id = $input['nationality_id'];
        $cutdate->cutgroup_id = $input['cutgroup_id'];
        $cutdate->warning = $input['warning'];
        $cutdate->danger = $input['danger'];
        $cutdate->save();
        return redirect(route('cutdates.index'))->with(['message' => "Successfully updated rule"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CutGroupNationalityDate  $cutdate
     * @return \Illuminate\Http\Response
     */
    public function show(CutGroupNationalityDate $cutdate = null)
    {
        $isNew = ($cutdate==null);
        if ($isNew)$cutdate = new CutGroupNationalityDate;
        return view('cutdates.edit',
            ['cutgroup_nationality_dates' => $cutdate,
            'nationalities' => Nationality::generateHTMLList((!$isNew)?$cutdate->nationality_id:null),
            'species' => Species::generateHTMLList((!$isNew)?$cutdate->getSpeciesID():null),
            'cutgroups' => CutGroup::generateHTMLList((!$isNew)?$cutdate->cutgroup_id:null,(!$isNew)?$cutdate->getSpeciesID():null),
            'isNew'=>$isNew]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CutGroupNationalityDate  $cutdate
     * @return \Illuminate\Http\Response
     */
    public function edit(CutGroupNationalityDate $cutdate)
    {
        return $this->show($cutdate);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CutGroupNationalityDate  $cutdate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CutGroupNationalityDate $cutdate)
    {
        $request->validate([
            'nationality_id' => ['required', 'int'],
            'cutgroup_id' => ['required','int'],
            'warning' => ['required','int'],
            'danger' => ['required', 'int']
        ]);
        $input = $request->all();
        if (Nationality::find($input['nationality_id'])==null) return redirect()->back()->withErrors(__('nationality_id'));
        if (CutGroup::find($input['cutgroup_id'])==null) return redirect()->back()->withErrors(__('cutgroup_id'));
        $cutdate->nationality_id = $input['nationality_id'];
        $cutdate->cutgroup_id = $input['cutgroup_id'];
        $cutdate->warning = $input['warning'];
        $cutdate->danger = $input['danger'];
        $cutdate->save();
        return redirect(route('cutdates.index'))->with(['message' => "Successfully updated rule"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CutGroupNationalityDate  $cutdate
     * @return \Illuminate\Http\Response
     */
    public function destroy(CutGroupNationalityDate $cutdate)
    {
        //
    }
}
