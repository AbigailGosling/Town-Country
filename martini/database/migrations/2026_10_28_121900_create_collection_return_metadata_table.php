<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('tandc_live')->create('collection_return_metadata', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_pallet_id');
            $table->string('transaction_id')->nullable();
            $table->string('type_selector', 32)->nullable();
            $table->boolean('is_supplier')->default(false);
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->text('picksheet_note')->nullable();
            $table->string('order_reference_number')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_date')->nullable();
            $table->timestamps();

            $table->index('transport_pallet_id');
            $table->index('client_id');
            $table->index('invoice_id');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tandc_live')->dropIfExists('collection_return_metadata');
    }
};

