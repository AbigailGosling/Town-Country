<?php

use App\Models\ClientType;
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
        Schema::connection('tandc_live')->create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedTinyInteger('address_id');
            $table->string('address_number')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('address_3')->nullable();
            $table->string('address_4')->nullable();
            $table->string('postcode')->nullable();
            $table->unsignedBigInteger('site_id');
            $table->string('client_type');

            $table->unique(['client_id', 'address_id', 'client_type'], 'client_addresses_client_address_type_unique');
            $table->index('client_id', 'client_addresses_client_idx');
            $table->index('address_id', 'client_addresses_address_idx');
        });

        DB::connection('tandc_live')
            ->table('customers')
            ->orderBy('id')
            ->chunk(500, function ($customers) {
                $rows = [];

                foreach ($customers as $customer) {
                    for ($index = 1; $index <= 9; $index++) {
                        $addressNumberField = 'address' . $index . '_number';
                        $addressLine1Field = 'address' . $index . '_1';
                        $addressLine2Field = 'address' . $index . '_2';
                        $addressLine3Field = 'address' . $index . '_3';
                        $addressLine4Field = 'address' . $index . '_4';
                        $postcodeField = 'postcode_' . $index;

                        $addressNumber = $customer->{$addressNumberField} ?? null;
                        $addressLine1 = $customer->{$addressLine1Field} ?? null;
                        $addressLine2 = $customer->{$addressLine2Field} ?? null;
                        $addressLine3 = $customer->{$addressLine3Field} ?? null;
                        $addressLine4 = $customer->{$addressLine4Field} ?? null;
                        $postcode = $customer->{$postcodeField} ?? null;

                        $hasAddressData = !is_null($addressNumber)
                            || !is_null($addressLine1)
                            || !is_null($addressLine2)
                            || !is_null($addressLine3)
                            || !is_null($addressLine4)
                            || !is_null($postcode);

                        if (!$hasAddressData) {
                            continue;
                        }

                        $rows[] = [
                            'client_id' => $customer->id,
                            'address_id' => $index,
                            'address_number' => $addressNumber,
                            'address_1' => $addressLine1,
                            'address_2' => $addressLine2,
                            'address_3' => $addressLine3,
                            'address_4' => $addressLine4,
                            'postcode' => $postcode,
                            'site_id' => $customer->site_id,
                            'client_type' => ClientType::CUSTOMER->value,
                        ];
                    }
                }

                if (!empty($rows)) {
                    DB::connection('tandc_live')->table('client_addresses')->insert($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->dropIfExists('client_addresses');
    }
};
