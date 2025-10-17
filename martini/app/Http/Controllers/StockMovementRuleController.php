<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\StockMovementRule;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StockMovementRuleController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request,Site $site)
    {
        return $this->show($request,new StockMovementRule(["origin"=>$site->id]),true);
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
        $stockmovementrule = new StockMovementRule;
        $stockmovementrule->origin = $input['origin'];
        $stockmovementrule->destination = $input['destination'];
        $stockmovementrule->days = $input['days'];
        $stockmovementrule->save();
        if (isset($input['mirror']) && $input['mirror'] == "on")
        {
            $stockmovement2 = new StockMovementRule;
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
     * @param  \App\Models\StockMovementRule  $stockmovementrule
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,StockMovementRule $stockmovementrule, bool $isNew = false)
    {
        $existingMovements = StockMovementRule::where("origin",$stockmovementrule->origin)->get()->pluck("destination")->toArray();
        $sites = Site::where("id","<>",$stockmovementrule->origin)->get();
        $cleanedSites = new Collection();
        foreach ($sites as $site)
        {
            if ($site->id == $stockmovementrule->destination || !in_array($site->id,$existingMovements))
            {
                $cleanedSites[] = $site;
            }
        }
        if (!$isNew) $sites[] = Site::find($stockmovementrule->destination);
        return view('stock-movements-rules.edit',
        [
            'stockmovementrule' => $stockmovementrule,
            'isMirrored' => $stockmovementrule->isMirrored(),
            'origin'=>Site::find($stockmovementrule->origin),
            'destination'=>Site::findOrNew($stockmovementrule->destination),
            'sites'=>$cleanedSites,
            'isNew' => $isNew,
            'existingDestinations' => StockMovementRule::where("origin",$stockmovementrule->origin)->get()->pluck("destination"),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StockMovementRule  $stockmovementrule
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,StockMovementRule $stockmovementrule)
    {
        return $this->show($request,$stockmovementrule);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockMovementRule  $stockmovementrule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockMovementRule $stockmovementrule)
    {
        $request->validate([
            'origin' => ['required','exists:tandc_live.site,id'],
            'destination' => ['required','exists:tandc_live.site,id'],
            'days' => ['required', 'integer','min:0'],
        ]);
        $input = $request->all();
        $stockmovementrule->origin = $input['origin'];
        $stockmovementrule->destination = $input['destination'];
        $stockmovementrule->days = $input['days'];
        $stockmovementrule->save();
        if (isset($input['mirror']) && $input['mirror'] == "on")
        {
            $stockmovement2 = StockMovementRule::where([["origin",$input['destination']],["destination",$input['origin']]])->firstOrNew();
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
     * @param  \App\Models\StockMovementRule  $stockmovementrule
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockMovementRule $stockmovementrule)
    {
        //
    }
}
