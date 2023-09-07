<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SendSaleConfirmation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:send_sale_confirmation {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send a sale confirmation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::where('id',57)->first();
        Auth::login($user);
        $request = Request::create(route('legacy',['path'=>'legacy/ajax/generatePDFsaleconfirm.php']),'GET',['id' => $this->argument('id')]);
        $response = app()->handle($request);
        Auth::logout();
        return Command::SUCCESS;
    }
}
