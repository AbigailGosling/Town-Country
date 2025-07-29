<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class SendSupplierReturn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:send_supplier_return {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send a supplier return';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::where('id',57)->first();
        Auth::login($user);
        $request = Request::create(route('legacy',['path'=>'legacy/ajax/generatePDFsupplierreturn.php']),'GET',['id' => $this->argument('id')]);
        $response = app()->handle($request);
        Auth::logout();
        return Command::SUCCESS;
    }
}
