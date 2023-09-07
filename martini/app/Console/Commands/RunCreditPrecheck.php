<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class RunCreditPrecheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:credit_precheck {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recheck customer credit in background';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::where('id',57)->first();
        Auth::login($user);
        require_once(__DIR__."/../../../legacy/functions.php");
        require_once(__DIR__."/../../../legacy/ajax/customer_soa_results_function.php");
        precredit_check($this->argument('id'));
        Auth::logout();
        return Command::SUCCESS;
    }
}
