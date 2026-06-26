<?php

use App\Models\Pallet;
use App\Models\PalletMovementTracking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        try {
            Schema::connection('tandc_live')->table('site', function (Blueprint $table) {
                $table->boolean('pallet_movement_tracking_enabled')->default(false);
            });
            Schema::connection('tandc_live')->create('pallet_movement_tracking', function (Blueprint $table) {
                $table->id();
                $table->integer('pallet_id')->index();
                $table->integer('from_location');
                $table->integer('to_location');
                $table->integer('created_by');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
            DB::connection('tandc_live')->transaction(function () {
                foreach (DB::connection('tandc_live')->table('pallet')->whereNotNull(['storage_location','created_at'])->where('storage_location', '!=', '')->select(['id', 'storage_location'])->get() as $pallet) {
                    DB::connection('tandc_live')->table('pallet_movement_tracking')->insert([
                        'pallet_id' => $pallet->id,
                        'from_location' => -2,
                        'to_location' => (filter_var($pallet->storage_location, FILTER_VALIDATE_INT) !== false) ? (int)$pallet->storage_location : -1,
                        'created_by' => -1,
                    ]);
                }
            });
        }catch (\Exception $e) {
            $this->down();
            throw $e;
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('site', function (Blueprint $table) {
            $table->dropColumn('pallet_movement_tracking_enabled');
        });
        Schema::connection('tandc_live')->dropIfExists('pallet_movement_tracking');
    }
};
