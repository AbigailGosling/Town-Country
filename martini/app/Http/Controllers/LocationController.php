<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
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
        if (!$showDisabled)$locations = Location::where('disabled', false)->paginate($this::$defaultPaginate);
        else $locations = Location::paginate($this::$defaultPaginate);
        return view('locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Site $site)
    {
        return view('locations.edit', ['site' => $site,
                                        'otherSites' => Site::generateHTMLList($site->name),
                                        'location' => new Location,
                                        'otherLocations' => $site->locations()->where('disabled',false)->orderBy("name")->get(),
                                        'rules' => [],
                                        'isNew' => true]);
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
            'site_id' => ['required', 'int'],
        ]);
        $input = $request->all();
        $location = new Location;
        $location->name = $input['name'];
        $location->site_id = Site::find($input['site_id'])->id;
        $location->save();
        $location->bulkUpdateSaleRule((array_key_exists('rules',$input))?$input['rules']:[]);
        return redirect(route('sites.edit',$location->site_id))->with(['message' => "Successfully created $location->name"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show(Site $site, Location $location)
    {
        return view('locations.edit', ['site' => $site,
                                    'otherSites' => Site::generateHTMLList($site->name),
                                    'location' => $location,
                                    'otherLocations' => $site->locations()->
                                                where([['disabled','=',false],['id','<>',$location->id]])->orderBy("name")->get(),
                                    'rules' => $location->sale_rules,
                                    'isNew' => false]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function edit(Site $site, Location $location)
    {
        return $this->show($site,$location);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Site $site, Location $location)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'site_id' => ['required', 'int'],
        ]);
        $input = $request->all();
        $location->name = $input['name'];
        $location->site_id = Site::find($input['site_id'])->id;
        $location->disabled = array_key_exists("disabled", $input);
        $location->save();
        $location->bulkUpdateSaleRule((array_key_exists('rules',$input))?$input['rules']:[]);
        return redirect(route('sites.edit',$site->id))->with(['message' => "Successfully updated $location->name"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
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
            'locations.index', [
                'locations' => $showDisabled ? Location::where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->withCount('permissions')
                    ->paginate($this::$defaultPaginate)
                    ->appends(request()->query())
                    : Location::where('name', 'like', '%' . $searchTerm . '%')
                        ->paginate($this::$defaultPaginate)
                        ->appends(request()->query()),
                'show_disabled' => true,
                'search_term' => $searchTerm
            ]
        );
    }
    public function generateSiteList():array{
        $siteList = [];
        foreach(Site::where('disabled',false)->get() as $site){
            $siteList[] = ['value'=>$site->id,'test'=>$site->name];
        }
        return $siteList;
    }
}
