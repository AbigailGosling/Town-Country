<?php

namespace App\Helpers;

use App\Models\Report;
use Carbon\Carbon;

class FinancialOverviewSummaryHelper
{
    private const SUMMARY_KEYS = [
        'kg',
        'Cost Value',
        'Actual Cost Value',
        'Sell Value',
        'Profit',
        'Actual Profit',
    ];

    private const COLUMN_MAPPINGS = [
        'Salesman' => 'User',
        'DATE' => 'Date Assembled',
        'Inv ID' => 'Invoice',
        'Cust.' => 'Customer',
        'Int ID' => 'Intake ID',
        'Plt ID' => 'Pallet ID',
        'Site' => 'Site Name',
        'Nation.' => 'Nationality',
        'Temp.' => 'Temp',
        'Prod.' => 'Cut',
        'Brand' => 'Brand',
        'Supp.' => 'Supplier',
        'Qty' => 'qty',
        'Unit' => 'unit',
        'kg' => 'kg',
        'Cost p kg' => 'Cost/Unit',
        'Cost' => 'Cost Value',
        'Act Cost' => 'Actual Cost Value',
        'Sell p kg' => 'Sell/Unit',
        'Sell' => 'Sell Value',
        'Profit' => 'Profit',
        'Act Profit' => 'Actual Profit',
    ];

    public static function buildSummaryForRange(
        Report $report,
        Carbon $dateStart,
        Carbon $dateEnd,
        ?array $filters = null,
        array $interestedPicks = [],
        string $dateType = ReportHelper::DATE_TYPE_ASSEMBLED
    ): array {
        $dataRanges = ReportHelper::getCollectionsForReportRange(
            $report,
            $dateType,
            $dateStart,
            $dateEnd,
            $interestedPicks,
            null,
            null,
            $filters
        );

        $tableRows = [];
        foreach ($dataRanges as $key => $range) {
            $tableRows[] = ReportHelper::resolveTableBody($report->getTables()[$key], $range);
        }

        while (count($tableRows) < 4) {
            $tableRows[] = [];
        }

        $tableSums = [];
        foreach ($report->getTables() as $index => $table) {
            if ($table->name === 'Supplier Returns') {
                continue;
            }

            $processed = [];
            $rollingQty = 0;
            $columnLookup = [];
            $reportColumns = $table->getColumns();

            foreach ($reportColumns as $reportCol) {
                $label = $reportCol->getLabel("debits");
                $columnLookup[$label] = $reportCol;
            }

            foreach ($tableRows[$index] as $row) {
                $workingRow = new \stdClass();
                foreach (self::COLUMN_MAPPINGS as $column) {
                    if ($column === 'qty') {
                        $qty = 0;
                        if ($row['Cases'] !== '0') {
                            $qty = (float) $row['Cases'];
                        } elseif ($row['PPC'] !== '0') {
                            $qty = (float) $row['PPC'];
                        }
                        $rollingQty += $qty;
                        continue;
                    }

                    if (!isset($columnLookup[$column])) {
                        continue;
                    }

                    $reportCol = $columnLookup[$column];
                    $t = ReportHelper::finaliseCell($reportCol, $row, $table->mode);
                    $col = $reportCol->getLabel($table->mode);
                    $workingRow->$col = self::parseNumber($t);

                    if ($index % 2 === 1 && strpos($column, 'Profit') === false && isset($workingRow->$col) && (float) $workingRow->$col > 0) {
                        $workingRow->$col = -(float) $workingRow->$col;
                    }
                }
                $processed[] = $workingRow;
            }

            $summary = new \stdClass();
            foreach (self::COLUMN_MAPPINGS as $column) {
                $resolved = '';
                if ($column === 'qty') {
                    $resolved = (string) $rollingQty;
                } elseif (isset($columnLookup[$column])) {
                    $reportCol = $columnLookup[$column];
                    $resolved = ReportHelper::resolveFooter($reportCol, (array)$summary, $processed, $table->mode);
                }
                $summary->$column = self::parseNumber($resolved);
            }
            $tableSums[] = $summary;
        }

        $precision = 3;
        $magShift = pow(10, $precision);
        $summaryTotals = [];
        foreach (self::SUMMARY_KEYS as $key) {
            $columnData = array_column($tableSums, $key);
            $result = 0;
            for ($i = 0; $i < count($columnData); $i++) {
                $rolling = FuncHelper::floorDec((float) $columnData[$i] * $magShift, 0);
                $result += $rolling;
            }
            $summaryTotals[$key] = FuncHelper::floorDec($result / $magShift, $precision);
        }

        $summaryTotals['Profit %'] = $summaryTotals['Cost Value'] == 0
            ? 0
            : FuncHelper::floorDec(($summaryTotals['Profit'] / $summaryTotals['Cost Value']) * 100, 3);
        $summaryTotals['Actual Profit %'] = $summaryTotals['Actual Cost Value'] == 0
            ? 0
            : FuncHelper::floorDec(($summaryTotals['Actual Profit'] / $summaryTotals['Actual Cost Value']) * 100, 3);
        $summaryTotals['Cost p kg'] = $summaryTotals['kg'] == 0
            ? 0
            : FuncHelper::floorDec($summaryTotals['Cost Value'] / $summaryTotals['kg'], 3);
        $summaryTotals['Sell p kg'] = $summaryTotals['kg'] == 0
            ? 0
            : FuncHelper::floorDec($summaryTotals['Sell Value'] / $summaryTotals['kg'], 3);

        return $summaryTotals;
    }

    private static function parseNumber(?string $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $sanitised = str_replace([',', '£', ' '], '', $value);
        if (!is_numeric($sanitised)) $sanitised = $value;
        return (float) $sanitised;
    }
}
