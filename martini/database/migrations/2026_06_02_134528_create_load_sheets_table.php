<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('tandc_live')->create('load_sheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vehicle_id');
            $table->date('date');
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('date');
        });

        Schema::connection('tandc_live')->table('vehicle_outgoing_pallet_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('load_sheet_id')->nullable()->after('vehicle_id');
        });

        $this->backfillLoadSheets();
    }

    public function down()
    {
        Schema::connection('tandc_live')->table('vehicle_outgoing_pallet_allocations', function (Blueprint $table) {
            $table->dropColumn('load_sheet_id');
        });

        Schema::connection('tandc_live')->dropIfExists('load_sheets');
    }

    private function backfillLoadSheets(): void
    {
        $connection = DB::connection('tandc_live');
        $allocationRows = $connection->table('vehicle_outgoing_pallet_allocations as allocation')
            ->join('outgoing_pallet as pallet', 'pallet.id', '=', 'allocation.outgoing_pallet_id')
            ->whereNull('allocation.load_sheet_id')
            ->whereNotNull('allocation.vehicle_id')
            ->whereNotNull('pallet.estimated_delivery_date')
            ->orderBy('allocation.vehicle_id')
            ->orderBy('pallet.estimated_delivery_date')
            ->orderByRaw('CASE WHEN allocation.committed_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('allocation.committed_at')
            ->orderBy('allocation.id')
            ->get([
                'allocation.id',
                'allocation.vehicle_id',
                'allocation.committed_by_user_id',
                'allocation.committed_at',
                'allocation.created_at',
                'allocation.updated_at',
                'pallet.estimated_delivery_date',
            ]);

        if ($allocationRows->isEmpty()) {
            return;
        }

        $groupedRows = [];
        foreach ($allocationRows as $allocationRow) {
            $groupKey = $allocationRow->vehicle_id . '|' . $allocationRow->estimated_delivery_date;
            if (!array_key_exists($groupKey, $groupedRows)) {
                $groupedRows[$groupKey] = [];
            }

            $groupedRows[$groupKey][] = $allocationRow;
        }

        foreach ($groupedRows as $rows) {
            $firstRow = $rows[0];
            $createdAtCandidates = array_values(array_filter(array_map(function ($row) {
                return $row->committed_at ?? $row->created_at ?? null;
            }, $rows)));
            $updatedAtCandidates = array_values(array_filter(array_map(function ($row) {
                return $row->updated_at ?? $row->committed_at ?? $row->created_at ?? null;
            }, $rows)));
            $userId = null;

            foreach ($rows as $row) {
                if ($row->committed_by_user_id !== null) {
                    $userId = (int) $row->committed_by_user_id;
                    break;
                }
            }

            sort($createdAtCandidates);
            rsort($updatedAtCandidates);

            $createdAt = $createdAtCandidates[0] ?? now();
            $updatedAt = $updatedAtCandidates[0] ?? $createdAt;

            $loadSheetId = $connection->table('load_sheets')->insertGetId([
                'user_id' => $userId,
                'vehicle_id' => (int) $firstRow->vehicle_id,
                'date' => $firstRow->estimated_delivery_date,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            $connection->table('vehicle_outgoing_pallet_allocations')
                ->whereIn('id', array_map(function ($row) {
                    return (int) $row->id;
                }, $rows))
                ->update([
                    'load_sheet_id' => $loadSheetId,
                ]);
        }
    }
};
