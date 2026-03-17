<?php

namespace App\Console\Commands;
require_once env("APP_ROOT_DIRECTORY")."\legacy\scripts\SLabsEmailer.php";
use App\Exports\ShortStockExport;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use InternalScripts\SLabsEmailer;
use InternalScripts\SLabsEmailerType;

class ShortStockEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:shortstockemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Short Stock Emails';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $f = (new ShortStockExport(+11))->file();
        $users = User::where("disabled",0)->get()->reject(function (User $user){
            return !$user->hasPermissionClarified(Permission::find(1));
        });
        $u =$users->pluck("actual_email")->toArray();
        SLabsEmailer::send_email(-1,SLabsEmailerType::ShortStock,$u,"Short Dated Stock","Please see attached",Storage::disk("public")->path(""),$f,null,true);
        return Command::SUCCESS;
    }
}
