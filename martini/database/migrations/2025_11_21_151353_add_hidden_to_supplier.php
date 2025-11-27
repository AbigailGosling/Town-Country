<?php

use App\Models\Supplier;
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
        Schema::connection('tandc_live')->table('supplier', function (Blueprint $table) {
            $table->boolean("is_hidden")->default(false);
        });
        Schema::connection('tandc_live')->table('supplier_returns', function (Blueprint $table) {
            $table->date("agreed_payment_date")->nullable(true);
        });
        foreach (Supplier::whereIn("id",[597,548,57,77,32,598])->get() as $supplier)
        {
            $supplier->is_hidden = true;
            $supplier->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('supplier', function (Blueprint $table) {
            $table->dropColumn("is_hidden");
        });
    }
};
