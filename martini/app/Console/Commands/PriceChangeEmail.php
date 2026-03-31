<?php

namespace App\Console\Commands;

use App\Helpers\InternalCache;
use App\Models\Cut;
use App\Models\Intake;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InternalScripts\SLabsEmailer;
use InternalScripts\SLabsEmailerType;

class PriceChangeEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:pricechangeemail {cacheKey}';

    /**
     * The console command description.
     *
     * @var stringS
     */
    protected $description = 'emails the price change report to the relevant people';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cacheKey = $this->argument('cacheKey');
        /** @var Product[] $cacheArray */
        $cacheArray = InternalCache::get($cacheKey);
        /** @var User $user */
        $user = User::find(array_shift($cacheArray));
        /** @var Intake $intake */
        $intake = Intake::find(array_shift($cacheArray));
        $tableBody = "";
        Log::debug(json_encode($cacheArray));
        foreach ($cacheArray as $oldProduct) {
            $toOutput = [];
            Log::debug(json_encode($oldProduct));
            /** @var Product $newProduct */
            $newProduct = Product::find($oldProduct['id']);
            /** @var Cut $cut */
            $cut = Cut::find($newProduct->cut_id);
            if ($oldProduct['cost'] != null && $oldProduct['cost'] != $newProduct->cost) {
                $toOutput[] = "<tr><td>{$cut->name}</td><td>Cost</td><td>" . $oldProduct['cost'] . "</td><td>" . $newProduct->cost . "</td></tr>";
            }
            if ($oldProduct['price'] != null && $oldProduct['price'] != $newProduct->price) {
                $toOutput[] = "<tr><td>{$cut->name}</td><td>Actual Cost</td><td>" . $oldProduct['price'] . "</td><td>" . $newProduct->price . "</td></tr>";
            }
            if (count($toOutput) > 0) {
                foreach ($toOutput as $change) {
                    $tableBody .= $change;
                }
            }
        }
        if ($tableBody != "") {
            $emailBody = "<p>The following price changes were made to the Intake {$intake->id} by {$user->name}:</p><table border='1' cellpadding='5' cellspacing='0'><thead><tr><th>Product Name</th><th>Change Type</th><th>Old Value</th><th>New Value</th></tr></thead><tbody>{$tableBody}</tbody></table>";
            //$u = ['Hannah.Hodgkins@townandcountrymeats.co.uk','Tarsem@townandcountrymeats.co.uk','Bridget@townandcountrymeats.co.uk'];
            $u = ['abigail.gosling@tang.solutions'];
            SLabsEmailer::send_email(-1,SLabsEmailerType::PrceChnge,$u,"Price Change: Intake {$intake->id}","<html><body>{$emailBody}</body></html>");
        }
        InternalCache::forget($cacheKey);
        return Command::SUCCESS;
    }
}
