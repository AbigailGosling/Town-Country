<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\StockMovementRule;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    private static int $defaultPaginate = 25;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $showDisabled = $request->input('showDisabled', false);
        return view(
            'sites.index', [
                'sites' => $showDisabled ? Site::paginate($this::$defaultPaginate)
                    : Site::where('disabled', false)->paginate($this::$defaultPaginate),
                        'search_term' => '',
                'show_disabled' => $showDisabled
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
        return view('sites.edit', ['site' => new Site,'locations' => [],'movements' => [], 'isNew' => true]);
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
            'name' => ['required', 'string', 'max:255'],
            'abbr' => ['nullable', 'string', 'max:255'],
            'cutoff' => ['required', 'date_format:H:i'],

        ]);
        $input = $request->all();
        $site = new Site;
        $site->name = $input['name'];
        $site->abbreviation = $input['abbr'];
        $site->cutoff = $input['cutoff'];
        $site->sale_blocked = array_key_exists("sale_blocked", $input);
        $site->save();
        return redirect(route('sites.index'))->with(['message' => "Successfully created $site->name"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,Site $site)
    {
        $showDisabled = $request->input('showDisabled', false);
        return view('sites.edit', ['site' => $site,'locations' =>
            ($showDisabled) ?
            $site->locations()->orderBy("name")->get() :
            $site->locations()->orderBy("name")->where("disabled",false)->get(),
            'movements' => StockMovementRule::where("origin",$site->id)->get(),
            'isNew' => false]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,Site $site)
    {
        return $this->show($request,$site);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Site $site)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abbr' => ['nullable', 'string', 'max:255'],
            'cutoff' => ['required', 'date_format:H:i'],
        ]);
        $input = $request->all();
        $site->name = $input['name'];
        $site->abbreviation = $input['abbr'];
        $site->cutoff = $input['cutoff'];
        $site->sale_blocked = array_key_exists("sale_blocked", $input);
        $site->disabled = array_key_exists("disabled", $input);
        $site->save();
        return redirect(route('sites.index'))->with(['message' => "Successfully updated $site->name"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function destroy(Site $site)
    {
        //
    }
        /**
     * GET method to search users in the system from the Users Index page
     * @param Request $request
     * @return View
     */
    public function search(Request $request)
    {
        $showDisabled = $request->input('showDisabled', false);
        $searchTerm = $request->get('search');
        return view(
            'sites.index', [
                'sites' => $showDisabled ? Site::where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->withCount('permissions')
                    ->paginate($this::$defaultPaginate)
                    ->appends(request()->query())
                    : Site::where('name', 'like', '%' . $searchTerm . '%')
                        ->paginate($this::$defaultPaginate)
                        ->appends(request()->query()),
                'show_disabled' => true,
                'search_term' => $searchTerm
            ]
        );
    }
}
