<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SendReservation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:send_reservation {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Reservation Email';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::where('id',57)->first();
        Auth::login($user);
        $request = Request::create(route('legacy',['path'=>'legacy/ajax/generatePDFreservation.php']),'GET',['id' => $this->argument('id')]);
        $response = app()->handle($request);
        Auth::logout();
        return Command::SUCCESS;
    }
}
