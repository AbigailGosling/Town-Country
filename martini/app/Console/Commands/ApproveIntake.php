<?php

namespace App\Console\Commands;

use App\Helpers\InternalCache;
use App\Models\CommentLogging;
use App\Models\ContainerProduct;
use App\Models\Customer;
use App\Models\InboundContainer;
use App\Models\Intake;
use App\Models\Pallet;
use App\Models\PickerItem;
use App\Models\PickerSheet;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationProduct;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApproveIntake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:approve_intake {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approve an intake in the background, triggered by legacy script';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cachedID = InternalCache::get($this->argument('key'));
        $intake = Intake::find($cachedID);
        if ($intake == null || $intake->approved == true || $intake->approving_start == null || $intake->approved_by == null) {
            Log::error("Invalid or already processed intake approval attempted.", ['intake_id' => $cachedID]);
            InternalCache::forget($this->argument('key'));
            return Command::FAILURE;
        }
        if (isset($intake->container_id)) {
            $container = InboundContainer::find($intake->container_id);
            $products = $container->getProducts();
            /**
             * @var ContainerProduct $containerProduct
            */
            foreach ($products as $containerProduct)
            {
                if ($containerProduct->deleted == 1) continue;
                $prod = Product::find($containerProduct->product_id);
                if ($prod == null) continue;
                $pallet=Pallet::find($prod->pallet_id);
                $pallet->user_id = $intake->approved_by;
                $pallet->save();
            }
            $products = $products->pluck("product_id")->toArray();
            $reservationProducts = ReservationProduct::whereIn("product_id",$products)->where("deleted",0)->groupBy("reservation_id")->pluck("reservation_id")->toArray();
            $reservations = Reservation::whereIn("id",$reservationProducts)->where([["deleted",0],["processed",0]])->get();
            $today = date('Y-m-d');
            /**
             * @var Reservation $reservation
             */
            foreach ($reservations as $reservation)
            {
                $customer = Customer::find($reservation->customer_id);
                $site = Site::find($customer->site_id);
                $siteCutOffHoursAndMinutes = explode(":",$site->cutoff);
                /**
                 * @var Carbon $targetDate
                 * @var Carbon $delDate
                 * @var Carbon $sitesCutOffToday
                 */
                $sitesCutOffToday = Carbon::now()->hour($siteCutOffHoursAndMinutes[0])->minute($siteCutOffHoursAndMinutes[1])->second(0)->micro(0);
                $targetDate = ($reservation->eta->timestamp > Carbon::now()->timestamp)?$reservation->eta:Carbon::now();
                if ($targetDate->dayOfWeek == Carbon::FRIDAY || $targetDate->dayOfWeek == Carbon::SATURDAY || $targetDate->dayOfWeek == Carbon::SUNDAY){
                    $targetDate->next(Carbon::MONDAY);
                }
                if ($targetDate->timestamp > $sitesCutOffToday->timestamp){
                    $delDate =  $targetDate->copy();
                }
                else {
                    $delDate = $sitesCutOffToday->copy();
                    $delDate->addDay();
                }
                $weekdayLookup = [1			,64			,32			,16			,8			,4			,2			];
                $weekdayInt = $weekdayLookup[$delDate->dayOfWeek];

                while ($customer->delivery_day_checking == 1 && ($weekdayInt & $customer->delivery_days) == 0) {
                    $delDate->addDay();
                    $weekdayInt = $weekdayLookup[$delDate->dayOfWeek];
                }
                $pickerSheet = new PickerSheet();
                $pickerSheet->picker_id = $intake->approved_by;
                $pickerSheet->user_from_id = $reservation->user_id;
                $pickerSheet->customer_id = $reservation->customer_id;
                $pickerSheet->estimated_delivery_date = $delDate->format("d/m/Y");
                $pickerSheet->orderReferenceNumber = $reservation->order_reference_number;
                $pickerSheet->date_completed = Carbon::now();
                $pickerSheet->addressid = $reservation->address_id;
                $pickerSheet->picksheet_note = $reservation->picksheet_note;
                $pickerSheet->transaction_id = null;
                $pickerSheet->save();

                $pickersheet_id = $pickerSheet->id;

                if ((int)$pickersheet_id !== $pickersheet_id)
                {
                    abort(500);
                    die();
                }
                $this->loggedDataChange($reservation->user_id,"picksheet_note",$pickersheet_id,$reservation->picksheet_note);
                $this->loggedDataChange($reservation->user_id,"picksheet_orderReferenceNumber",$pickersheet_id,$reservation->order_reference_number);

                foreach (ReservationProduct::where([["reservation_id",$reservation->id],["deleted",0]])->get() as $resProduct)
                {
                    $product_id = $resProduct->product_id;
                    $quantity = $resProduct->target_count;
                    $target_weight = 0;
                    $comment = null;


                    $price = $resProduct->price;
                    $price_type = null;
                    for($i=0;$i<$quantity;$i++){
                        $pickerItem = new PickerItem();
                        $pickerItem->pickersheet_id = $pickersheet_id;
                        $pickerItem->product_id = $product_id;
                        $pickerItem->price = $price;
                        $pickerItem->price_type = $price_type;
                        $pickerItem->comment = $comment;
                        $pickerItem->target_weight = $target_weight;
                        $pickerItem->save();
                    }
                }
                $reservation->pickersheet_id = $pickersheet_id;
                $reservation->processed = true;
                $reservation->save();
                $artisanLocation = base_path('artisan');
                pclose(popen('start /B cmd /C "php '.$artisanLocation.'  run:send_sale_confirmation '.$pickersheet_id.' >NUL 2>NUL"', 'r'));
            }

        }
        $intake->approved = 1;
        $intake->approved_date = Carbon::now();
        $intake->save();
        InternalCache::forget($this->argument('key'));
        InternalCache::forget("approve_intake_start_".$intake->id);
        return Command::SUCCESS;
    }
    private function loggedDataChange(int $user_id, string $type, int $id, string $body = "")
    {
        $log = new CommentLogging();
        $log->entity_id = $id;
        $log->type = $type;
        $log->body = $body;
        $log->user_id = $user_id;
        $log->save();
    }
}
