<?php

namespace Tests\Feature;

use App\Helpers\GraphHopperHelper;
use Carbon\Carbon;
use Tests\TestCase;

class GraphHopperGenericSchedulingTest extends TestCase
{
    public function test_generic_vehicle_schedule_is_anchored_to_due_date(): void
    {
        $vehicles = collect([
            $this->makeVehicle(3),
        ]);

        $generifiedVehicleTypes = GraphHopperHelper::generifyVehicleTypes($vehicles, 3);
        $vrcVehicleTypes = [];
        $depotLocation = [
            'location_id' => 'depot',
            'lat' => 52.4,
            'lon' => -1.9,
        ];

        $maxOperatingSeconds = 14 * 60 * 60;
        $generated = GraphHopperHelper::vehiclesFromGenerifiedTypes(
            $generifiedVehicleTypes,
            $vrcVehicleTypes,
            $depotLocation,
            Carbon::parse('2026-09-18'),
            2,
            true,
            $maxOperatingSeconds
        );

        $this->assertNotEmpty($generated);
        $vehicle = $generated[0];
        $this->assertSame(strtotime('2026-09-18 00:00:00'), (int) ($vehicle['earliest_start'] ?? 0));
        $this->assertSame(strtotime('2026-09-18 14:00:00'), (int) ($vehicle['latest_end'] ?? 0));
        $this->assertSame($maxOperatingSeconds, (int) ($vehicle['max_driving_time'] ?? 0));
    }

    private function makeVehicle(int $vehicleTypeId): object
    {
        return new class($vehicleTypeId) {
            public int $vehicle_type_id;
            public bool $has_tail_lift = false;

            public function __construct(int $vehicleTypeId)
            {
                $this->vehicle_type_id = $vehicleTypeId;
            }

            public function planningPayloadForVehicle(): int
            {
                return 12;
            }

            public function planningCapacityForVehicle(int $planningPalletColumns): int
            {
                return 12;
            }
        };
    }
}
