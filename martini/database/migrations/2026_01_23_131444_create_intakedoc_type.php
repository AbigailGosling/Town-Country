<?php

use App\Models\DocType;
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
        Schema::connection('tandc_live')->create('doc_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('abbreviation', 20)->nullable();
        });
        DocType::create([
            'name' => 'Proof of Delivery',
            'abbreviation' => 'POD', ]);
        DocType::create([
            'name' => 'Invoice',
            'abbreviation' => 'INV', ]);
        DocType::create([
            'name' => 'Other',
            'abbreviation' => 'OTH', ]);
        Schema::connection('tandc_live')->table('intakedocs', function (Blueprint $table) {
            $table->integer('type_id')->default(-1);
            $table->boolean('new_file_system')->default(false);
            $table->integer('file_id')->default(-1);
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->dropIfExists('doc_type');
        Schema::connection('tandc_live')->table('intakedocs', function (Blueprint $table) {
            $table->dropColumn('type_id');
            $table->dropColumn('new_file_system');
            $table->dropColumn('file_id');
        });
    }
};
