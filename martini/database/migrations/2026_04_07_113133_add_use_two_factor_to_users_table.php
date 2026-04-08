<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean("use_two_factor")->after("password")->default(false);
            $table->text('two_factor_secret')
                ->after('use_two_factor')
                ->nullable();

            $table->text('two_factor_recovery_codes')
                ->after('two_factor_secret')
                ->nullable();

            $table->timestamp('two_factor_confirmed_at')
                ->after('two_factor_recovery_codes')
                ->nullable();

            $table->timestamp('two_factor_expires_at')
                ->after('two_factor_confirmed_at')
                ->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("use_two_factor");
            $table->dropColumn("two_factor_secret");
            $table->dropColumn("two_factor_recovery_codes");
            $table->dropColumn("two_factor_confirmed_at");
            $table->dropColumn("two_factor_expires_at");
        });
    }
};
