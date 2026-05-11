<?php

namespace App\Console\Commands;

use App\Http\Controllers\IntakeScanningController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessIntakeScanningQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intake-scanning:process-queue {--attempts=12 : Maximum retry attempts per queued job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queued intake scanning OCR jobs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(IntakeScanningController $controller)
    {
        $attempts = max(1, (int) $this->option('attempts'));
        $result = $controller->runQueuedJobs($attempts);

        $this->info('Processed jobs: ' . (int) ($result['processedCount'] ?? 0));

        foreach (($result['jobs'] ?? []) as $job) {
            Log::info('Job processed', ['job' => $job]);
            $jobId = (string) ($job['jobId'] ?? 'unknown');
            $status = (string) ($job['status'] ?? ($job['ok'] ?? false ? 'completed' : 'failed'));
            $this->line($jobId . ' [' . $status . ']');
        }

        return Command::SUCCESS;
    }
}
