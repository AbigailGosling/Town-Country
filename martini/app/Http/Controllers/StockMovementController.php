<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StockMovementController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request,Site $site)
    {
        return $this->show($request,new StockMovement(["origin"=>$site->id]),true);
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
            'origin' => ['required','exists:tandc_live.site,id'],
            'destination' => ['required','exists:tandc_live.site,id'],
            'days' => ['required', 'integer','min:0'],
        ]);
        $input = $request->all();
        $stockmovement = new StockMovement;
        $stockmovement->origin = $input['origin'];
        $stockmovement->destination = $input['destination'];
        $stockmovement->days = $input['days'];
        $stockmovement->save();
        if (isset($input['mirror']) && $input['mirror'] == "on")
        {
            $stockmovement2 = new StockMovement;
            $stockmovement2->origin = $input['destination'];
            $stockmovement2->destination = $input['origin'];
            $stockmovement2->days = $input['days'];
            $stockmovement2->save();
        }
        return redirect(route('sites.show',[Site::find($input['origin'])]))->with(['message' => "Successfully created movement"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockMovement  $stockmovement
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,StockMovement $stockmovement, bool $isNew = false)
    {
        $existingMovements = StockMovement::where("origin",$stockmovement->origin)->get()->pluck("destination")->toArray();
        $sites = Site::where("id","<>",$stockmovement->origin)->get();
        $cleanedSites = new Collection();
        foreach ($sites as $site)
        {
            if ($site->id == $stockmovement->destination || !in_array($site->id,$existingMovements))
            {
                $cleanedSites[] = $site;
            }
        }
        if (!$isNew) $sites[] = Site::find($stockmovement->destination);
        return view('stockmovements.edit',
        [
            'stockmovement' => $stockmovement,
            'isMirrored' => $stockmovement->isMirrored(),
            'origin'=>Site::find($stockmovement->origin),
            'destination'=>Site::findOrNew($stockmovement->destination),
            'sites'=>$cleanedSites,
            'isNew' => $isNew,
            'existingDestinations' => StockMovement::where("origin",$stockmovement->origin)->get()->pluck("destination"),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StockMovement  $stockmovement
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,StockMovement $stockmovement)
    {
        return $this->show($request,$stockmovement);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockMovement  $stockmovement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockMovement $stockmovement)
    {
        $request->validate([
            'origin' => ['required','exists:tandc_live.site,id'],
            'destination' => ['required','exists:tandc_live.site,id'],
            'days' => ['required', 'integer','min:0'],
        ]);
        $input = $request->all();
        $stockmovement->origin = $input['origin'];
        $stockmovement->destination = $input['destination'];
        $stockmovement->days = $input['days'];
        $stockmovement->save();
        if (isset($input['mirror']) && $input['mirror'] == "on")
        {
            $stockmovement2 = StockMovement::where([["origin",$input['destination']],["destination",$input['origin']]])->firstOrNew();
            $stockmovement2->origin = $input['destination'];
            $stockmovement2->destination = $input['origin'];
            $stockmovement2->days = $input['days'];
            $stockmovement2->save();
        }
        return redirect(route('sites.show',[Site::find($input['origin'])]))->with(['message' => "Successfully updated movement"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockMovement  $stockmovement
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockMovement $stockmovement)
    {
        //
    }
}
