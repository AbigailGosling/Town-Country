<?php

namespace App\Http\Controllers;

use App\Models\ActiveHolidayCover;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ActiveHolidayCoverController extends Controller
{
    private static int $defaultPaginate = 25;

    /**
     * The default query to populate the list of users on the User Index/Search page
     *
     * @return Builder
     */
    protected function baseQuery():Builder
    {
        return ActiveHolidayCover::query();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view(
            'holiday-cover.index', [
                'hcs' => $this->baseQuery()->paginate($this::$defaultPaginate),
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view(
            'holiday-cover.edit', [
                'users' => User::where([['disabled',false],['is_hidden',false]])->get(['id','name']),
                'isNew' => true
            ]
        );
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
            'absentee' => ['required', 'exists:users,id','different:cover'],
            'cover' => ['required', 'exists:users,id','different:absentee'],
        ]);
        ActiveHolidayCover::create([
            'absent_id' => $request->absentee,
            'cover_id' => $request->cover,
        ]);
        return redirect()->route('holidays.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActiveHolidayCover  $holiday
     * @return \Illuminate\Http\Response
     */
    public function show(ActiveHolidayCover $holiday)
    {
        return $this->edit($holiday);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ActiveHolidayCover  $holiday
     * @return \Illuminate\Http\Response
     */
    public function edit(ActiveHolidayCover $holiday)
    {
        return view(
            'holiday-cover.edit', [
                'users' => User::where([['disabled',false],['hidden',false]])->get(['id','name']),
                'isNew' => false,
                'hc' => $holiday,
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ActiveHolidayCover  $holiday
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ActiveHolidayCover $holiday)
    {
        $request->validate([
            'absentee' => ['required', 'exists:users,id','different:cover'],
            'cover' => ['required', 'exists:users,id','different:absentee'],
        ]);
        $holiday->absent_id = $request->absentee;
        $holiday->cover_id =$request->cover;
        $holiday->save();
        return redirect()->route('holidays.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ActiveHolidayCover  $holiday
     * @return \Illuminate\Http\Response
     */
    public function destroy(ActiveHolidayCover $holiday)
    {
        $holiday->forceDelete();
        return redirect()->route('holidays.index')->withErrors("Cover Deleted");
    }
}
