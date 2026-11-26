<?php

use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Supplier::query()
            ->orderBy('id')
            ->chunk(500, function ($suppliers) {
                $rows = [];

                foreach ($suppliers as $supplier) {
                    $address1 = trim((string) ($supplier->address_1 ?? ''));
                    $address2 = trim((string) ($supplier->address_2 ?? ''));
                    $address3 = trim((string) ($supplier->address_3 ?? ''));
                    $address4 = trim((string) ($supplier->address_4 ?? ''));
                    $postcode = trim((string) ($supplier->postcode ?? ''));
                    $addressNumber = trim((string) ($supplier->contact_number ?? ''));

                    if (strlen($postcode) > 100) {
                        continue;
                    }

                    $hasAddressData = $address1 !== ''
                        || $address2 !== ''
                        || $address3 !== ''
                        || $address4 !== ''
                        || $postcode !== ''
                        || $addressNumber !== '';

                    if (!$hasAddressData) {
                        continue;
                    }

                    $exists = DB::connection('tandc_live')
                        ->table('client_addresses')
                        ->where('client_id', $supplier->id)
                        ->where('address_id', 1)
                        ->where('client_type', 'supplier')
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $rows[] = [
                        'client_id' => $supplier->id,
                        'address_id' => 1,
                        'address_number' => $addressNumber !== '' ? $addressNumber : null,
                        'address_1' => $address1 !== '' ? $address1 : null,
                        'address_2' => $address2 !== '' ? $address2 : null,
                        'address_3' => $address3 !== '' ? $address3 : null,
                        'address_4' => $address4 !== '' ? $address4 : null,
                        'postcode' => $postcode !== '' ? $postcode : null,
                        'site_id' => 1,
                        'client_type' => 'supplier',
                        'allowed_vehicle_types' => '',
                        'require_tail_lift' => false,
                        'require_fork_lift' => false,
                        'opening_time' => null,
                        'closing_time' => null,
                        'open_bank_holiday_mondays' => false,
                        'open_bank_holiday_fridays' => false,
                        'geocoding_tried' => false,
                        'collection' => false,
                        'lat' => null,
                        'lon' => null,
                    ];
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
        // No-op: preserve migrated supplier client address data.
    }
};
