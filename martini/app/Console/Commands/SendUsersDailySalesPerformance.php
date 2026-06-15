<?php

namespace App\Console\Commands;

use App\Helpers\FinancialOverviewSummaryHelper;
use App\Helpers\ReportHelper;
use App\Models\Permission;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InternalScripts\SLabsEmailer;
use InternalScripts\SLabsEmailerType;

class SendUsersDailySalesPerformance extends Command
{
    private const REPORT_ID = 1;
    private const REPORT_USER_ID = 54;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:send_users_daily_sales_performance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily sales performance to users';

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
        $salesPermission = Permission::find(1);
        $targetDateStart = Carbon::now()->startOfDay();
        $targetDateEnd = $targetDateStart->copy()->endOfDay();
        $weekDateStart = $targetDateStart->copy()->startOfWeek(Carbon::SUNDAY)->startOfDay();
        $diffInWeek = $targetDateStart->diffInDays($weekDateStart, true);
        $usersSorted = [];
        $saleTargets = [];
        foreach (User::where([["disabled", false],["is_hidden", false]])->get() as $user){
            $email = trim(strtolower($user->actual_email));
            if (!array_key_exists($email, $usersSorted)){
                $usersSorted[$email] = [];
                $saleTargets[$email] = 0;
            }
            $usersSorted[$email][] = $user;
            if ($user->sale_target !== null && $user->sale_target > 0){
                $saleTargets[$email] = $user->sale_target;
            }
        }
        foreach ($usersSorted as $email => $users){
            if ($email === '' || $email === null || count($users) === 0) continue;
            $user = $users[0];
            if ($saleTargets[$email] !== null && $saleTargets[$email] > 0 &&
            $user->actual_email !== null && $user->actual_email !== '' &&
            $user->hasPermission($salesPermission)){
                $this->processUser($email, array_column($users, 'id'), $saleTargets[$email], $user, $report, $targetDateStart, $targetDateEnd, $weekDateStart, $diffInWeek);
            }
        }
        return Command::SUCCESS;
    }
    private function processUser(string $email, array $user_ids, float $sale_target, User $user, Report $report, Carbon $targetDateStart, Carbon $weekDateStart, Carbon $weekDateEnd, int $diffInWeek): void
    {
        $targetSummary = ['daily' => [], 'weekly' => []];
        foreach ($user_ids as $user_id){
            $subSummary = $this->processSubUser($user_id, $report, $targetDateStart, $weekDateStart, $weekDateEnd);
            foreach ($subSummary['daily'] as $key => $value){
                if (!array_key_exists($key, $targetSummary['daily'])){
                    $targetSummary['daily'][$key] = 0;
                }
                $targetSummary['daily'][$key] += $value;
            }
            foreach ($subSummary['weekly'] as $key => $value){
                if (!array_key_exists($key, $targetSummary['weekly'])){
                    $targetSummary['weekly'][$key] = 0;
                }
                $targetSummary['weekly'][$key] += $value;
            }
        }
        if (!array_key_exists('Sell Value', $targetSummary['weekly']) || $targetSummary['weekly']['Sell Value'] == 0){
            return;
        }
        $targetLabel = $targetDateStart->format('d/m/Y');
        $subject = 'Daily Summary For ' . $user->name . ' - ' . $targetLabel;
        $htmlBody = $this->buildEmailBody($sale_target, $targetDateStart, $targetLabel, $targetSummary, $diffInWeek);

        $to = [
            $email
        ];
        $cc = [
            "Ross.Whetton@townandcountrymeats.co.uk",
            "gary@townandcountrymeats.co.uk",
            "bridget@townandcountrymeats.co.uk"
        ];
        SLabsEmailer::send_email(-1, SLabsEmailerType::IndividualDailySummary, $to, $subject, $htmlBody, '', '', null, false, $cc);
    }
    private function processSubUser(int $user_id, Report $report, Carbon $targetDateStart, Carbon $targetDateEnd, Carbon $weekDateStart): array
    {
        $interestedPicks = [];
        $filters = ReportHelper::filterBuilder(
            $interestedPicks,
            null,
            null,
            null,
            $user_id,
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

        return ["daily" => FinancialOverviewSummaryHelper::buildSummaryForRange(
            $report,
            $targetDateStart->copy(),
            $targetDateEnd->copy(),
            $filters,
            $interestedPicks
        ), "weekly" => FinancialOverviewSummaryHelper::buildSummaryForRange(
            $report,
            $weekDateStart->copy(),
            $targetDateEnd->copy(),
            $filters,
            $interestedPicks
        )];
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

    private function buildEmailBody(float $sale_target, Carbon $targetDateStart, string $targetLabel, array $targetSummary, int $diffInWeek): string
    {
        $dailyTarget = $sale_target / 5;
        if ($targetDateStart->dayOfWeek === Carbon::SUNDAY) {
            $dailyTarget = 0;
        }
        $balance = $targetSummary['daily']['Actual Profit'] - $dailyTarget;
        $isNegative = $balance < 0;
        $absBalance = abs($balance);
        $expectedForTheWeek = $dailyTarget * min(5, $diffInWeek);
        $weekBalance = $targetSummary['weekly']['Actual Profit'] - $expectedForTheWeek;
        $isWeekBalanceNegative = $weekBalance < 0;
        $absWeekBalance = abs($weekBalance);
        return "<html><body>"
            . "<p>Daily Summary.</p>"
            . "<p>Your weekly sales target is: <strong>{$this->formatMoney($sale_target)}</strong></p>"
            . "<p>Your daily sales target is: <strong>{$this->formatMoney($dailyTarget)}</strong></p>"
            . "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;'>"
            . "<thead><tr style='background:#f2f2f2;'>"
            . "<th>Report Date</th>"
            . "<th>Day</th>"
            . "<th>Week of Year</th>"
            . "<th>kg</th>"
            // . "<th>Cost</th>"
            // . "<th>Actual Cost</th>"
            . "<th>Sell</th>"
            // . "<th>Profit</th>"
            // . "<th>Profit %</th>"
            . "<th>Actual Profit</th>"
            . "<th>Actual Profit %</th>"
            . "</tr></thead><tbody>"
            . "<tr>"
            . "<td><strong>{$targetLabel}</strong></td>"
            . "<td>{$targetDateStart->format('l')}</td>"
            . "<td>Week {$targetDateStart->isoWeek()}</td>"
            . "<td>{$this->formatKg($targetSummary['daily']['kg'])}</td>"
            // . "<td>{$this->formatMoney($targetSummary['Cost Value'])}</td>"
            // . "<td>{$this->formatMoney($targetSummary['Actual Cost Value'])}</td>"
            . "<td>{$this->formatMoney($targetSummary['daily']['Sell Value'])}</td>"
            // . "<td>{$this->formatMoney($targetSummary['Profit'])}</td>"
            // . "<td>" . number_format($targetSummary['Profit %'], 3, '.', '') . "%</td>"
            . "<td>{$this->formatMoney($targetSummary['daily']['Actual Profit'])}</td>"
            . "<td>" . number_format($targetSummary['daily']['Actual Profit %'], 3, '.', '') . "%</td>"
            . "</tr>"
            . "</tbody></table>"
            . "<p style='margin-top:20px;'>Your balance against daily target is: <strong style='color:" . ($isNegative ? 'red' : 'green') . "'>".($isNegative ? '-': '+')."{$this->formatMoney($absBalance)}</strong></p>"
            . "<p style='margin-top:20px;'>Your balance against weekly target is: <strong style='color:" . ($isWeekBalanceNegative ? 'red' : 'green') . "'>".($isWeekBalanceNegative ? '-': '+')."{$this->formatMoney($absWeekBalance)}</strong></p>"
            . "</body></html>";
    }
}
