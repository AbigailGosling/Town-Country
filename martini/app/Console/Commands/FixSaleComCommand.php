<?php
namespace App\Console\Commands;
require_once "D:\wwwroot\martini\legacy\scripts\PDFRenderer.php";

use App\Models\PickerSheet;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use InternalScripts\PDFRenderer;

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
        $artisanLocation = 'D:\\wwwroot\\martini\\artisan';
        foreach ($l as $s){
            pclose(popen('php '.$artisanLocation.' run:send_sale_confirmation '.$s->id.' >NUL 2>NUL', 'r'));
        }
        //$response = app()->handle($request);
        Auth::logout();
        return Command::SUCCESS;
    }
}
