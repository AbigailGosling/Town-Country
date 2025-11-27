<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\PickerSheet;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use InternalScripts\SLabsEmailer;
use InternalScripts\SLabsEmailerType;

class CheckShortPick extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:checkshortpick {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if pick was short picked and email someone about it.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pick = PickerSheet::with(["palletsOut","pickerItems"])->find($this->argument('id'));
        $weightIDs = [];
        foreach ($pick->palletsOut as $palletOut){
            $weightIDs[] = $palletOut->weight_ids;
        }
        $weightIDs = implode(",",$weightIDs);
        $weightIDs = explode(",",$weightIDs);
        Log::debug("",[$pick->pickerItems->where("deleted",0)->count(),count($weightIDs)]);
        if ($pick->pickerItems->where("deleted",0)->count() > count($weightIDs))
        {
            $u = [User::find(Customer::find($pick->customer_id)->default_salesman_id)->actual_email];
            SLabsEmailer::send_email($pick->customer_id,SLabsEmailerType::ShortPick,$u,"Sale ".$pick->id." Short Picked","Sale ".$pick->id." has completed pick, however ".$pick->pickerItems->where("deleted",0)->count() - count($weightIDs)." items were not picked.",'','',null,true);
        }
        return Command::SUCCESS;
    }
}
