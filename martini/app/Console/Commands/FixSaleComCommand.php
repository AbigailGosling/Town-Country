<?php
namespace App\Console\Commands;
require_once env("APP_ROOT_DIRECTORY")."\legacy\scripts\PDFRenderer.php";

use App\Helpers\ProcessHelper;
use App\Models\PickerSheet;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class FixSaleComCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:fix_sale_coms {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::where('id',57)->first();
        Auth::login($user);
        $l = PickerSheet::where("id",">=",$this->argument('id'))->where("sent","<>",1)->get();
        foreach ($l as $s){
            ProcessHelper::runInBackground('run:send_sale_confirmation '.$s->id);
        }
        //$response = app()->handle($request);
        Auth::logout();
        return Command::SUCCESS;
    }
}
