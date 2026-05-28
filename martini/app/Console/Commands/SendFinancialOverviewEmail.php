<?php

namespace App\Console\Commands;

require_once env("APP_ROOT_DIRECTORY") . "\\legacy\\scripts\\SLabsEmailer.php";

use App\Helpers\FuncHelper;
use App\Helpers\ReportHelper;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use InternalScripts\SLabsEmailer;
use InternalScripts\SLabsEmailerType;

class SendFinancialOverviewEmail extends Command
{
    private const REPORT_ID = 1;
    private const REPORT_USER_ID = 54;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:financial_overview_email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a daily financial overview email using turnover vs profit summary logic';

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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reportUser = User::find(self::REPORT_USER_ID);
        if ($reportUser === null) {
            $this->error('Report user not found.');
            return Command::FAILURE;
        }

        Auth::login($reportUser);

        $report = Report::find(self::REPORT_ID);
        if ($report === null) {
            $this->error('Report not found.');
            return Command::FAILURE;
        }

        $targetDate = Carbon::yesterday();
        $comparisonDate = Carbon::createFromDate($targetDate->year - 1, 1, 4)
            ->setISODate($targetDate->year - 1, $targetDate->isoWeek(), $targetDate->isoWeekday());

        $targetSummary = $this->runFinancialSummaryForDate($report, $targetDate->copy()->startOfDay(), $targetDate->copy()->endOfDay());
        $comparisonSummary = $this->runFinancialSummaryForDate($report, $comparisonDate->copy()->startOfDay(), $comparisonDate->copy()->endOfDay());

        $targetLabel = $targetDate->format('d/m/Y');
        $comparisonLabel = $comparisonDate->format('d/m/Y');
        $subject = 'Daily Financial Overview - ' . $targetLabel;
        $htmlBody = $this->buildEmailBody($targetDate, $targetLabel, $targetSummary, $comparisonDate, $comparisonLabel, $comparisonSummary);

        $to = ["abigail.gosling@tang.solutions",
            "Ross.Whetton@townandcountrymeats.co.uk",
            "gary@townandcountrymeats.co.uk",
            "bridget@townandcountrymeats.co.uk"];
        SLabsEmailer::send_email(-1, SLabsEmailerType::Sales, $to, $subject, $htmlBody);

        $this->info('Financial overview sent to: ' . implode(', ', $to));
        return Command::SUCCESS;
    }

    private function parseNumber(?string $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $sanitised = str_replace([',', '£', ' '], '', $value);
        return (float) $sanitised;
    }

    private function formatMoney(float $value): string
    {
        $neg = $value < 0 ? '-' : '';
        return $neg . '£' . number_format(abs($value), 2, '.', ',');
    }

    private function formatKg(float $value): string
    {
        return number_format($value, 3, '.', ',') . ' kg';
    }

    private function formatPerKg(float $value): string
    {
        return '£' . number_format($value, 3, '.', ',');
    }

    private function runFinancialSummaryForDate(Report $report, Carbon $dateStart, Carbon $dateEnd): array
    {
        $interestedPicks = [];
        $filters = ReportHelper::filterBuilder(
            $interestedPicks,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            false
        );

        if (count(array_keys($filters)) === 0) {
            $filters = null;
        }

        $dataRanges = ReportHelper::getCollectionsForReportRange(
            $report,
            ReportHelper::DATE_TYPE_ASSEMBLED,
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
                $label = $reportCol->getLabel($table->mode);
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
                    $workingRow->$col = $this->parseNumber($t);

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
                    $resolved = ReportHelper::resolveFooter($reportCol, [], $processed, $table->mode);
                }
                $summary->$column = $this->parseNumber($resolved);
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

    private function buildEmailBody(
        Carbon $targetDate,
        string $targetLabel,
        array $targetSummary,
        Carbon $comparisonDate,
        string $comparisonLabel,
        array $comparisonSummary
    ): string
    {
        return "<html><body>"
            . "<p>Daily financial overview comparison.</p>"
            . "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;'>"
            . "<thead><tr style='background:#f2f2f2;'>"
            . "<th>Report Date</th>"
            . "<th>Day</th>"
            . "<th>Week of Year</th>"
            . "<th>kg</th>"
            . "<th>Cost</th>"
            . "<th>Actual Cost</th>"
            . "<th>Sell</th>"
            . "<th>Profit</th>"
            . "<th>Profit %</th>"
            . "<th>Actual Profit</th>"
            . "<th>Actual Profit %</th>"
            . "</tr></thead><tbody>"
            . "<tr>"
            . "<td><strong>{$targetLabel}</strong></td>"
            . "<td>{$targetDate->format('l')}</td>"
            . "<td>Week {$targetDate->isoWeek()}</td>"
            . "<td>{$this->formatKg($targetSummary['kg'])}</td>"
            . "<td>{$this->formatMoney($targetSummary['Cost Value'])}</td>"
            . "<td>{$this->formatMoney($targetSummary['Actual Cost Value'])}</td>"
            . "<td>{$this->formatMoney($targetSummary['Sell Value'])}</td>"
            . "<td>{$this->formatMoney($targetSummary['Profit'])}</td>"
            . "<td>" . number_format($targetSummary['Profit %'], 3, '.', '') . "%</td>"
            . "<td>{$this->formatMoney($targetSummary['Actual Profit'])}</td>"
            . "<td>" . number_format($targetSummary['Actual Profit %'], 3, '.', '') . "%</td>"
            . "</tr>"
            . "<tr>"
            . "<td><strong>{$comparisonLabel}</strong></td>"
            . "<td>{$comparisonDate->format('l')}</td>"
            . "<td>Week {$comparisonDate->isoWeek()}</td>"
            . "<td>{$this->formatKg($comparisonSummary['kg'])}</td>"
            . "<td>{$this->formatMoney($comparisonSummary['Cost Value'])}</td>"
            . "<td>{$this->formatMoney($comparisonSummary['Actual Cost Value'])}</td>"
            . "<td>{$this->formatMoney($comparisonSummary['Sell Value'])}</td>"
            . "<td>{$this->formatMoney($comparisonSummary['Profit'])}</td>"
            . "<td>" . number_format($comparisonSummary['Profit %'], 3, '.', '') . "%</td>"
            . "<td>{$this->formatMoney($comparisonSummary['Actual Profit'])}</td>"
            . "<td>" . number_format($comparisonSummary['Actual Profit %'], 3, '.', '') . "%</td>"
            . "</tr>"
            . "</tbody></table>"
            . "</body></html>";
    }
}
