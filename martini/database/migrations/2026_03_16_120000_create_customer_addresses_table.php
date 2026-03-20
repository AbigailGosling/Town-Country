<?php

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
                            'client_type' => "customer",
                        ];
                    }
                }

                if (!empty($rows)) {
                    DB::connection('tandc_live')->table('client_addresses')->insert($rows);
                }
            });
            Schema::connection('tandc_live')->table('customers', function ($table) {
                for ($index = 1; $index <= 9; $index++) {
                    $table->dropColumn('address' . $index . '_number');;
                    $table->dropColumn('address' . $index . '_1');
                    $table->dropColumn('address' . $index . '_2');
                    $table->dropColumn('address' . $index . '_3');
                    $table->dropColumn('address' . $index . '_4');
                    $table->dropColumn('postcode_' . $index);
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
        Schema::connection('tandc_live')->table('customers', function ($table) {
            for ($index = 1; $index <= 9; $index++) {
                $table->string('address' . $index . '_number')->default('');
                $table->string('address' . $index . '_1')->default('');
                $table->string('address' . $index . '_2')->default('');
                $table->string('address' . $index . '_3')->default('');
                $table->string('address' . $index . '_4')->default('');
                $table->string('postcode_' . $index)->default('');
            }

        });
        foreach(DB::Connection('tandc_live')->table('client_addresses')->where('client_type', 'customer')->cursor() as $clientAddress) {
            $updateData = [];
            if (!is_null($clientAddress->address_number)) {
                $updateData['address' . $clientAddress->address_id . '_number'] = $clientAddress->address_number;
            }
            if (!is_null($clientAddress->address_1)) {
                $updateData['address' . $clientAddress->address_id . '_1'] = $clientAddress->address_1;
            }
            if (!is_null($clientAddress->address_2)) {
                $updateData['address' . $clientAddress->address_id . '_2'] = $clientAddress->address_2;
            }
            if (!is_null($clientAddress->address_3)) {
                $updateData['address' . $clientAddress->address_id . '_3'] = $clientAddress->address_3;
            }
            if (!is_null($clientAddress->address_4)) {
                $updateData['address' . $clientAddress->address_id . '_4'] = $clientAddress->address_4;
            }
            if (!is_null($clientAddress->postcode)) {
                $updateData['postcode_' . $clientAddress->address_id] = $clientAddress->postcode;
            }
            if (!empty($updateData)) {
                DB::connection('tandc_live')
                    ->table('customers')
                    ->where('id', $clientAddress->client_id)
                    ->update($updateData);
            }
        }
        Schema::connection('tandc_live')->dropIfExists('client_addresses');
    }
};
