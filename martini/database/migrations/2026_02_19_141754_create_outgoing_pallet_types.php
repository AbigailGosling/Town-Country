<?php

use App\Models\OutgoingPallet;
use App\Models\OutgoingPalletPickWeight;
use App\Models\OutgoingPalletType;
use App\Models\PickerSheet;
use App\Models\PickWeightOut;
use Carbon\Carbon;
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
        Schema::connection('tandc_live')->create('outgoing_pallet_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('max_weight', 8, 4);
            $table->timestamps();
        });
        Schema::connection('tandc_live')->table('outgoing_pallet', function (Blueprint $table) {
            $table->unsignedBigInteger('outgoing_pallet_type_id')->default(1)->after('id');
            $table->date('estimated_delivery_date')->after('outgoing_pallet_type_id');
            $table->boolean('dispatched')->default(false)->after('estimated_delivery_date');
        });
        OutgoingPalletType::create([
            'name' => 'Standard',
            'max_weight' => 1200.000,
        ]);
        OutgoingPalletType::create([
            'name' => 'Euro',
            'max_weight' => 1000.0000,
        ]);
        $ps = PickerSheet::whereRaw(
                "STR_TO_DATE(estimated_delivery_date, '%d/%m/%Y') >= ?",
                [Carbon::now()->format('Y-m-d')]
            )->get();
        PickWeightOut::processPickerSheetsForPalletization($ps);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->dropIfExists('outgoing_pallet_types');
        Schema::connection('tandc_live')->table('outgoing_pallet', function (Blueprint $table) {
            $table->dropColumn('outgoing_pallet_type_id');
            $table->dropColumn('dispatched');
            $table->dropColumn('estimated_delivery_date');
        });
        OutgoingPallet::truncate();
        OutgoingPalletPickWeight::truncate();

    }
};
