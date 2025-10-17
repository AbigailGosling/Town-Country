<?php

use App\Models\User;
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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false);
        });
        $users = User::where('disabled',true)->get();
        foreach ($users as $user)
        {
            if ($user->disabled == true)
            {
                $user->is_hidden = true;
                $user->save();
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
        Schema::connection('tandc_live')->table('users', function (Blueprint $table) {
            $table->dropColumn('is_hidden');
        });
    }
};
