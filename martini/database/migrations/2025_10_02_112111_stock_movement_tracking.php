<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tandc_live')->rename('stock_movements', 'stock_movement_rules');
        Schema::connection('tandc_live')->create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->integer('pallet_id');
            $table->integer('from_location_id')->nullable();
            $table->integer('to_location_id')->nullable();
            $table->integer('user_id')->nullable();

            $table->boolean('is_approved')->default(false);
            $table->integer('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->string('reference')->nullable();
            $table->string('note')->nullable();

            $table->timestamps();

            $table->index('pallet_id');
            $table->index('from_location_id');
            $table->index('to_location_id');
            $table->index('user_id');
            $table->index('approved_by');
        });/*
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`stock_movements` ADD INDEX `pallet_id` (`pallet_id`);");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`stock_movements` ADD INDEX `from_location_id` (`from_location_id`);");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`stock_movements` ADD INDEX `to_location_id` (`to_location_id`);");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`stock_movements` ADD INDEX `user_id` (`user_id`);");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`stock_movements` ADD INDEX `approved_by` (`approved_by`);");
        */
    }

    public function down(): void
    {
        Schema::connection('tandc_live')->dropIfExists('stock_movements');
        Schema::connection('tandc_live')->rename('stock_movement_rules', 'stock_movements');
    }
};
