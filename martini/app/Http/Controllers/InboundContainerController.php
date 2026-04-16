<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ContainerProduct;
use App\Models\Cut;
use App\Models\InboundContainer;
use App\Models\Nationality;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationProduct;
use App\Models\Site;
use App\Models\Species;
use App\Models\Temperature;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
        $containers = InboundContainer::with('site')->where('deleted',false)->orderByDesc("id")->paginate($this::$defaultPaginate);
        return view("container.index",["containers"=>$containers,"brandLookup"=>$this->containerBrandLookup($containers)]);
    }

    /**
     * GET method to search users in the system from the Users Index page
     * @param Request $request
     * @return View
     */
    public function search(Request $request)
    {
        $searchTerm = $request->get('search');
        $containers = InboundContainer::where('internal_number','LIKE','%'.$searchTerm.'%')->orWhere('origin_port','LIKE','%'.$searchTerm.'%')->orderByDesc("id")->paginate($this::$defaultPaginate);
        return view("container.index",["containers"=>$containers,"brandLookup"=>$this->containerBrandLookup($containers)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("container.edit",['container'=>new InboundContainer,'temperatures'=>Temperature::whereIn('id',[1,2])->get(),'isNew'=>true,'brands'=>Brand::where('deleted',false)->get()->keyBy('id'),'sites'=>Site::all()->keyBy('id')]);
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
            'vessel'          => 'sometimes|string',
            'temperature_id'  => 'required|int|in:1,2',
            'site_id'         => 'sometimes|int|exists:tandc_live.site,id',
        ]);

        $container = new InboundContainer();
        $container->internal_number = $request->input("internal_number");
        $container->origin_port = $request->input("origin_port");
        $container->arrived = $request->input("arrived",false);
        $container->vessel = $request->input("vessel","");
        $container->eta = $request->input("eta");
        $container->temperature_id = $request->input("temperature_id");
        $container->site_id = $request->input("site_id");
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
        return view("container.edit",['container'=>$container,'containerProducts'=>ContainerProduct::where([['container_id',$container->id],['deleted',false]])->get(),'temperatures'=>Temperature::whereIn('id',[1,2])->get(),'isNew'=>false,'brands'=>Brand::where('deleted',false)->get()->keyBy('id'),'sites'=>Site::all()->keyBy('id')]);
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
        if ($container->arrived == true) return redirect()->route('containers.edit',[$container])->with("error","Cannot Update a Container that has arrived");
        $validated = $request->validate([
            'internal_number' => 'required|string',
            'origin_port'     => 'required|string',
            'eta'             => 'required|date',
            'vessel'          => 'sometimes|string',
            'temperature_id'  => 'sometimes|int|in:1,2',
            'site_id'         => 'sometimes|int|exists:tandc_live.site,id',
        ]);
        $container->internal_number = $request->input("internal_number",$container->internal_number)??"";
        $container->origin_port = $request->input("origin_port",$container->origin_port)??"";
        $container->arrived = $request->input("arrived",$container->arrived);
        $container->eta = $request->input("eta",$container->eta);
        $container->vessel = $request->input("vessel",$container->vessel);
        $container->temperature_id = $request->input("temperature_id",$container->temperature_id);
        $container->site_id = $request->input("site_id",$container->site_id);
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
        if (!$container->admin_approved) return redirect()->route('containers.edit',[$container])->with("error","Container Must be Admin Aprroved First!");
        else if (!$container->arrived) return redirect()->route("legacy",["path"=>'legacy/newDelivery.php',"container"=>$container->id]);
    }

    /**
     * Prepare to remove the specified resource from storage.
     *
     * @param  \App\Models\InboundContainer  $container
     * @return \Illuminate\Http\Response
     */
    public function preDelete(InboundContainer $container)
    {
        return view("container.edit",['container'=>$container,'temperatures'=>Temperature::whereIn('id',[1,2])->get(),'isDelete'=>true,'sites'=>Site::all()->keyBy('id')]);
    }

    /**
     * Confirm to remove the specified resource from storage.
     *
     * @param  \App\Models\InboundContainer  $container
     * @return \Illuminate\Http\Response
     */
    public function confirmDelete(InboundContainer $container)
    {
       $container->deleted = true;
       foreach (ContainerProduct::where("container_id",$container->id)->get() as $containerProduct)
       {
            $this->processContainerProductDelete($containerProduct);
       }
       $container->save();
       return redirect()->route('containers.index',[$container])->with("message", $container->internal_number." Deleted!");
    }

    /**
     * Clone the specified resource from storage.
     *
     * @param  \App\Models\InboundContainer  $existingContainer
     * @return \Illuminate\Http\Response
     */
    public function cloneContainer(InboundContainer $existingContainer)
    {
        $newContainer = $existingContainer->replicate();
        $newContainer->arrived = $newContainer->admin_approved = false;
        $newContainer->save();

        foreach (ContainerProduct::where([["container_id",$existingContainer->id],["deleted",false]])->get() as $existingContainerProduct)
        {
            $existingProduct = Product::find($existingContainerProduct->product_id);
            $newProduct =  $existingProduct->replicate();
            $newProduct->pallet_id      = -2;
            $newProduct->akg ??= $newProduct->old_akg;
            $newProduct->save();
            $newContainerProduct = $existingContainerProduct->replicate();
            $newContainerProduct->product_id = $newProduct->id;
            $newContainerProduct->container_id = $newContainer->id;
            $newContainerProduct->save();
        }
        return redirect()->route('containers.edit',[$newContainer])->with("message","Container Duplicated");
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
            'brands'=>Brand::where("deleted",false)->get(),
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
            'brands'=>Brand::where("deleted",false)->get(),
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
        if ($container->arrived == true) return redirect()->route('containers.edit',[$container])->with("error","Cannot Update a Container that has arrived");
        $validated = $request->validate([
            'nationality' => 'required|integer',
            'brand'       => 'required|integer',
            'cut'         => 'required|integer',
            'unit'        => 'required|string|max:3',
            'qty'         => 'required|integer',
            'akg'         => 'required|numeric',
            'rrp'         => 'required|numeric',
            'cost'        => 'required|numeric',
            'actual_cost' => 'required|numeric',
        ]);
        $product = (!$containerProduct->exists)?new Product():Product::find($containerProduct->product_id);
        $product->pallet_id      = -2;
        $product->nationality_id = $validated['nationality'];
        $product->brand_id       = $validated['brand'];
        $product->cut_id         = $validated['cut'];
        $product->unit           = $validated['unit'];
        $product->quantity       = $validated['qty'];
        $product->akg            = $validated['akg'];
        $product->cost           = $validated['actual_cost'];
        $product->save();

        $containerProduct->container_id = $container->id;
        $containerProduct->product_id = $product->id;
        $containerProduct->rrp = $validated['rrp'];
        $containerProduct->cost = $validated['cost'];
        $containerProduct->save();
        return redirect()->route('containers.show',[$containerProduct->container_id]);
    }
    /**
     * Prepare to remove the specified resource from storage.
     *
     * @param  \App\Models\ContainerProduct  $containerProduct
     * @return \Illuminate\Http\Response
     */
    public function preDeleteProduct(InboundContainer $container, ContainerProduct $containerProduct)
    {
        return view("container.product",
        [
            'container'=>InboundContainer::find($containerProduct->container_id),
            'containerProduct'=>$containerProduct,
            'brands'=>Brand::where('deleted',false)->get(),
            'species'=>Species::all(),
            'nationalities'=>Nationality::all(),
            'cuts'=>Cut::where('disabled',false)->get(),
            'isDelete'=>true
        ]);
    }

    /**
     * Confirm to remove the specified resource from storage.
     *
     * @param  \App\Models\ContainerProduct  $containerProduct
     * @return \Illuminate\Http\Response
     */
    public function confirmDeleteProduct(InboundContainer $container, ContainerProduct $containerProduct)
    {
        $this->processContainerProductDelete($containerProduct);
        return redirect()->route('containers.edit',[$container])->with("message", "Product Deleted!");
    }
    private function processContainerProductDelete(ContainerProduct $containerProduct)
    {
        $containerProduct->deleted = true;
        $reservationsToCheck = [];
        foreach (ReservationProduct::where("product_id",$containerProduct->product_id)->get() as $reservationProduct)
        {
            $reservationProduct->deleted = true;
            if (!in_array($reservationProduct->reservation_id, $reservationsToCheck)) $reservationsToCheck[] = $reservationProduct->reservation_id;
            $reservationProduct->save();
        }
        $reservationsToCheck = array_unique($reservationsToCheck);
        foreach ($reservationsToCheck as $id)
        {
            if (ReservationProduct::where([["reservation_id",$id],["deleted",false]])->get()->count()==0)
            {
                $r = Reservation::find($id);
                if ($r)
                {
                    $r->deleted = true;
                    $r->save();
                }
            }
        }
        $containerProduct->save();
    }
    private function containerBrandLookup(LengthAwarePaginator $containers):array
    {
        $brandLookup = [];
        /** @var InboundContainer $container */
        foreach ($containers as $container)
        {
            $containerProd = ContainerProduct::where([['container_id',$container->id],['deleted',false]])->get()->pluck("product_id")->toArray();
            if (count($containerProd)==0) $brandLookup[$container->id] = "Unknown";
            else
            {
                $brands = array_unique(Product::whereIn("id",$containerProd)->get()->pluck("brand_id")->toArray());
                switch (count($brands))
                {
                    case 1:
                        $brandLookup[$container->id] = Brand::find($brands[0])->name;
                        break;
                    case 0:
                        $brandLookup[$container->id] = "Unknown";
                        break;
                    default:
                        $brandLookup[$container->id] = "Mixed";
                        break;

                }
            }
        }
        return $brandLookup;
    }
}
