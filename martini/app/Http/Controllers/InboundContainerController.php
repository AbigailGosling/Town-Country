<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ContainerProduct;
use App\Models\Cut;
use App\Models\CutGroup;
use App\Models\InboundContainer;
use App\Models\Intake;
use App\Models\Nationality;
use App\Models\Product;
use App\Models\Species;
use Illuminate\Http\Request;

class InboundContainerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private static $defaultPaginate = 200;
    public function index()
    {
        $containers = InboundContainer::query()->paginate($this::$defaultPaginate);
        return view("container.index",["containers"=>$containers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("container.edit",['container'=>new InboundContainer(),'isNew'=>true]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'internal_number' => 'required|string',
            'origin_port'     => 'required|string',
            'eta'             => 'required|date|after:today',
        ]);

        $container = new InboundContainer();
        $container->internal_number = $request->input("internal_number");
        $container->origin_port = $request->input("origin_port");
        $container->arrived = $request->input("arrived",false);
        $container->eta = $request->input("eta");
        $container->save();
        return $this->show($container);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InboundContainer  $container
     * @return \Illuminate\Http\Response
     */
    public function show(InboundContainer $container)
    {
        return view("container.edit",['container'=>$container,'isNew'=>false]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InboundContainer  $container
     * @return \Illuminate\Http\Response
     */
    public function edit(InboundContainer $container)
    {
        return $this->show($container);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InboundContainer  $container
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InboundContainer $container)
    {
        $validated = $request->validate([
            'internal_number' => 'required|string',
            'origin_port'     => 'required|string',
            'eta'             => 'required|date',
        ]);
        $container->internal_number = $request->input("internal_number",$container->internal_number)??"";
        $container->origin_port = $request->input("origin_port",$container->origin_port)??"";
        $container->arrived = $request->input("arrived",$container->arrived);
        $container->eta = $request->input("eta",$container->eta);
        $container->save();
        return $this->show($container);
    }

    /**
     * Arrive container
     *
     * @param  \App\Models\InboundContainer  $container
     * @return \Illuminate\Http\Response
     */
    public function arrive(InboundContainer $container)
    {
        if (!$container->arrived) return redirect()->route("legacy",["path"=>'legacy/newDelivery.php',"container"=>$container->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InboundContainer  $container
     * @return \Illuminate\Http\Response
     */
    public function destroy(InboundContainer $container)
    {
        //
    }

     /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createProduct(InboundContainer $container)
    {
        return view("container.product",[
            'container'=>$container,
            'containerProduct'=>new ContainerProduct(),
            'brands'=>Brand::all(),
            'species'=>Species::all(),
            'nationalities'=>Nationality::all(),
            'cuts'=>Cut::where('disabled',false)->get(),
            'isNew'=>true]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeProduct(Request $request,InboundContainer $container)
    {

        return $this->updateProduct($request,$container,new ContainerProduct());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ContainerProduct  $container
     * @return \Illuminate\Http\Response
     */
    public function showProduct(ContainerProduct $containerProduct)
    {
        return view("container.product",
        [
            'container'=>InboundContainer::find($containerProduct->container_id),
            'containerProduct'=>$containerProduct,
            'brands'=>Brand::all(),
            'species'=>Species::all(),
            'nationalities'=>Nationality::all(),
            'cuts'=>Cut::where('disabled',false)->get(),
            'isNew'=>false
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ContainerProduct  $containerProduct
     * @return \Illuminate\Http\Response
     */
    public function editProduct(InboundContainer $container, ContainerProduct $containerProduct)
    {
        return $this->showProduct($containerProduct);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InboundContainer  $containerProduct
     * @return \Illuminate\Http\Response
     */
    public function updateProduct(Request $request, InboundContainer $container, ContainerProduct $containerProduct)
    {
        $validated = $request->validate([
            'nationality' => 'required|integer',
            'brand'       => 'required|integer',
            'cut'         => 'required|integer',
            'unit'         => 'required|string|max:50',
            'qty'         => 'required|integer',
            'akg'         => 'required|numeric',
            'rrp'         => 'required|numeric',
        ]);
        $product = (!$containerProduct->exists)?new Product():Product::find($containerProduct->product_id);
        $product->pallet_id      = -2;
        $product->nationality_id = $validated['nationality'];
        $product->brand_id       = $validated['brand'];
        $product->cut_id         = $validated['cut'];
        $product->unit           = $validated['unit'];
        $product->quantity       = $validated['qty'];
        $product->akg            = $validated['akg'];
        $product->price          = $validated['rrp'];
        $product->save();

        $containerProduct->container_id = $container->id;
        $containerProduct->product_id = $product->id;
        $containerProduct->save();
        return redirect()->route('containers.show',[$containerProduct->container_id]);
    }
}
