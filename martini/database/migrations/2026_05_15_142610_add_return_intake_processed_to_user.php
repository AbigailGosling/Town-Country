<?php

use App\Models\User;
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
            $table->boolean('receive_return_intake')->default(false);
        });
        $defaultEmails = [
            'shane@townandcountrymeats.co.uk',
            'louis@townandcountrymeats.co.uk',
            'john.walker@townandcountrymeats.co.uk',
            'stuart.spencer@townandcountrymeats.co.uk',
            'sophie.smith@townandcountrymeats.co.uk',
            'gemma@townandcountrymeats.co.uk',
            'ross.whetton@townandcountrymeats.co.uk'
        ];
        $users = User::all();
        foreach ($users as $user)
        {
            if (in_array(strtolower($user->actual_email), $defaultEmails))
            {
                $user->receive_return_intake = true;
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('receive_return_intake');
        });
    }
};
