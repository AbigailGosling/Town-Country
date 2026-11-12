<?php

use App\Models\Customer;
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
        Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
            $table->timestamp('next_credit_review')->nullable();
            $table->integer('credit_review_frequency')->nullable();
        });
        $customers = Customer::where([['notify_credit_review', true],['disabled', false]])->get();
        foreach ($customers as $customer) {
            $c = Carbon::now();
            $c->addDays(28);
            $customer->next_credit_review = $c;
            $customer->credit_review_frequency = 28;
            $customer->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
            $table->dropColumn('next_credit_review');
            $table->dropColumn('credit_review_frequency');
        });
    }
};
