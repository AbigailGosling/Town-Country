<?php

namespace Tests\Feature;

use App\Helpers\GraphHopperHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GraphHopperServiceWindowTest extends TestCase
{
    public function test_service_window_latest_is_adjusted_by_service_duration(): void
    {
        $address = new class {
            public int $client_id = 10;
            public int $address_id = 1;
            public string $address_1 = 'Test Customer';
            public string $postcode = 'AB1 2CD';
            public ?float $lat = 52.5;
            public ?float $lon = -1.9;
            public bool $collection = false;
            public int $site_id = 1;
            public bool $geocoding_tried = false;
            public ?string $allowed_vehicle_types = '3';
            public bool $require_tail_lift = false;
            public ?Carbon $opening_time = null;
            public ?Carbon $closing_time = null;

            public function __construct()
            {
                $this->opening_time = Carbon::createFromTime(7, 0, 0);
                $this->closing_time = Carbon::createFromTime(14, 0, 0);
            }

            public function save(): void
            {
            }
        };

        $pallet = new class {
            public int $id = 1;
            public int $customer_id = 10;
            public int $address_id = 1;
            public int $type_id = 2;

            public function __get(string $name)
            {
                if ($name === 'pickWeightOuts') {
                    return new class {
                        public function isEmpty(): bool
                        {
                            return false;
                        }
                    };
                }

                return null;
            }

            public function normalizePlanningTemperatureCategory(): string
            {
                return 'fresh';
            }

            public function getTotalWeight(): int
            {
                return 100;
            }
        };

        $customer = new class {
            public string $businessname = 'Customer';
        };

        $services = GraphHopperHelper::servicesFromPallets(
            1,
            new Collection([$pallet]),
            new Collection([10 => $customer]),
            new Collection(['10-1' => $address]),
            1800,
            Carbon::parse('2026-09-18'),
            [[
                'vehicle_id' => '3-12-12-0',
                'type_id' => '3-12-12',
            ]]
        );

        $this->assertCount(1, $services);
        $window = $services[0]['time_windows'][0] ?? null;
        $this->assertIsArray($window);
        $this->assertSame(strtotime('2026-09-18 07:00:00'), (int) ($window['earliest'] ?? 0));
        $this->assertSame(strtotime('2026-09-18 13:30:00'), (int) ($window['latest'] ?? 0));
    }
}
