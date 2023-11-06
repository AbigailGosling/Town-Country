<?php

use App\Models\Location;
use App\Models\PagePermission;
use App\Models\Pallet;
use App\Models\Permission;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $oldPerm = new PagePermission();
        $newPerm = new Permission();
        $oldPerm->name = '<span class="small">Manage</span> Locations';
        $newPerm->label = $newPerm->description = "Manage Locations";
        $oldPerm->column = $newPerm->group = 3;
        $oldPerm->file = $newPerm->file = "../sites";
        $newPerm->name = "locations";
        $newPerm->save();
        $oldPerm->id = $newPerm->id;
        $oldPerm->save();
        Schema::connection("tandc_live")->create("site",function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string("name");
            $table->string("abbreviation");
            $table->boolean("disabled")->default(false);
            $table->timestamps();
        });
        Schema::connection("tandc_live")->create("location",function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer("site_id");
            $table->string("name");
            $table->string("sale_rules")->default("{}");
            $table->boolean("disabled")->default(false);
            $table->timestamps();
        });
        $wolverhampton = new Site();
        $wolverhampton->name = "Wolverhampton";
        $wolverhampton->abbreviation = "WLV";
        $wolverhampton->save();

        $gatwick = new Site();
        $gatwick->name = "Gatwick";
        $gatwick->abbreviation = "GAT";
        $gatwick->save();

        $dryStore = new Site();
        $dryStore->name = "Dry Store";
        $dryStore->abbreviation = "DRY";
        $dryStore->save();

        $directDrop = new Site();
        $directDrop->name = "Direct Drop";
        $directDrop->abbreviation = "DRD";
        $directDrop->save();

        $coldStore = new Site();
        $coldStore->name = "Coldstore";
        $coldStore->abbreviation = "CLD";
        $coldStore->save();

        $other = new Site();
        $other->name = "Other";
        $other->abbreviation = "OTH";
        $other->save();

        foreach (Pallet::distinct()->get(["storage_location"]) as $storage_location){
            $storage_location = $storage_location['storage_location'];
            if (!$storage_location || $storage_location == "") continue;
            $site = Site::where("name",$storage_location)->first();
            $location = new Location();
            $location->name = ucfirst($storage_location);
            if (!$site)
            {            
              $site = $wolverhampton;                          
            }
            $location->site_id = $site->id;
            $location->save();     
            Pallet::where("storage_location","=",$storage_location)->update(["storage_location"=>$location->id]);
            if (Pallet::where("storage_location","=",$location->id)->count() < 100){
                $location->disabled = true;
                $location->save(); 
            }
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
       /* foreach (Location::all()->toArray() as $location){
            Pallet::where("storage_location","=",$location['id'])->update(["storage_location"=>$location['name']]);
        }
        Schema::connection("tandc_live")->drop("site");
        Schema::connection("tandc_live")->drop("location");*/
        $newPerm = Permission::where("name","../sites")->first();
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $oldPerm->forceDelete();
    }
};
