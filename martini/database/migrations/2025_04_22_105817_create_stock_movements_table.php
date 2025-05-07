<?php

use App\Models\Site;
use App\Models\StockMovement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::connection('tandc_live')->create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->integer("origin")->index();
            $table->integer("destination")->index();
            $table->integer("days");
            $table->timestamps();
            $table->unique(["origin","destination"]);
        });
        $lookup = [[1,10,2],[2,10,3],[1,3,1],[10,1,2],[11,2,3],[2,11,3]];
        foreach ($lookup as $item)
        {
            if (Site::find($item[0])!=null && Site::find($item[1]))
            {
                $sm =new StockMovement(["origin"=>$item[0],
                    "destination"=>$item[1],
                    "days"=>$item[2]]
                );
                $sm->save();
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
        Schema::connection('tandc_live')->dropIfExists('stock_movements');
    }
};
