<?php

namespace App\Console\Commands;

require_once env("APP_ROOT_DIRECTORY") . "\\legacy\\scripts\\SLabsEmailer.php";

use App\Helpers\FinancialOverviewSummaryHelper;
use App\Helpers\ReportHelper;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use InternalScripts\SLabsEmailer;
use InternalScripts\SLabsEmailerType;

class SendWeeklyFinancialOverviewEmail extends Command
{
    private const REPORT_ID = 1;
    private const REPORT_USER_ID = 54;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:weekly_financial_overview_email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a weekly financial overview email using turnover vs profit summary logic';

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

        $today = Carbon::now();
        $targetWeekNumber = $today->isoWeek();
        $targetStart = $today->copy()->startOfWeek(Carbon::SATURDAY);
        $targetEnd = $today->copy()->endOfWeek(Carbon::FRIDAY);

        $comparisonDate = $today->copy()->subYear();
        $comparisonStart = $comparisonDate->copy()->startOfWeek(Carbon::SATURDAY);
        $comparisonEnd = $comparisonDate->copy()->endOfWeek(Carbon::FRIDAY);

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

        $targetSummary = FinancialOverviewSummaryHelper::buildSummaryForRange(
            $report,
            $targetStart,
            $targetEnd,
            $filters,
            $interestedPicks
        );
        $comparisonSummary = FinancialOverviewSummaryHelper::buildSummaryForRange(
            $report,
            $comparisonStart,
            $comparisonEnd,
            $filters,
            $interestedPicks
        );

        $targetLabel = $targetStart->format('d/m/Y') . ' - ' . $targetEnd->format('d/m/Y');
        $comparisonLabel = $comparisonStart->format('d/m/Y') . ' - ' . $comparisonEnd->format('d/m/Y');

        $subject = 'Weekly Financial Overview - Week ' . $targetWeekNumber . ' (' . $targetStart->format('d/m/Y') . ' to ' . $targetEnd->format('d/m/Y') . ')';
        $htmlBody = $this->buildEmailBody(
            $targetWeekNumber,
            $targetLabel,
            $targetSummary,
            $targetWeekNumber,
            $comparisonLabel,
            $comparisonSummary
        );

        $to = [
            "Ross.Whetton@townandcountrymeats.co.uk",
            "gary@townandcountrymeats.co.uk",
            "bridget@townandcountrymeats.co.uk"
        ];
        SLabsEmailer::send_email(-1, SLabsEmailerType::FinancialWeeklySummary, $to, $subject, $htmlBody);

        $this->info('Weekly financial overview sent to: ' . implode(', ', $to));
        return Command::SUCCESS;
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

    private function buildEmailBody(
        int $targetWeekNumber,
        string $targetLabel,
        array $targetSummary,
        int $comparisonWeekNumber,
        string $comparisonLabel,
        array $comparisonSummary
    ): string
    {
        return "<html><body>"
            . "<p>Weekly financial overview comparison.</p>"
            . "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;'>"
            . "<thead><tr style='background:#f2f2f2;'>"
            . "<th>Week Range</th>"
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
            . "<td>Week {$targetWeekNumber}</td>"
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
            . "<td>Week {$comparisonWeekNumber}</td>"
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
