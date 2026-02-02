<?php

use App\Models\Permission;
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
        Permission::where('name', 'supplierreturnstatements')->first()->update(['label' => 'Supplier Return/Credit Statement','description'=>'Supplier Return/Credit Statement']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Permission::where('name', 'supplierreturnstatements')->first()->update(['label' => 'Supplier Return Statements','description'=>'Supplier Return Statements']);
    }
};
