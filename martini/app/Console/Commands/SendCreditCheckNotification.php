<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use InternalScripts\SLabsEmailer;
use InternalScripts\SLabsEmailerType;

class SendCreditCheckNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:send-credit-check-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send credit check notification to customers who have opted in for credit review';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $customers = Customer::where([['notify_credit_review', true],['disabled', false]])->get();

        if (!$customers->isEmpty()) {
            $htmlBody = '<html><body>';
            $htmlBody .= '<h1>Monthly Credit Review</h1>';
            $htmlBody .= '<p>The following customers are due for a credit review:</p>';
            $htmlBody .= '<ul>';
            foreach ($customers as $customer) {
                if ($customer->next_credit_review === null || $customer->next_credit_review->diffInDays(now()) <= 0)
                {
                    $htmlBody .= "<li>ID: {$customer->id}, Name: {$customer->businessname}</li>";
                    $customer->next_credit_review = now()->addDays($customer->credit_review_frequency ?? 28);
                    $customer->save();
                }
            }
            $htmlBody .= '</ul>';
            $htmlBody .= '</body></html>';
            SLabsEmailer::send_email(-1, SLabsEmailerType::CreditCheckNotification, ["gemma@townandcountry.co.uk"], "Monthly Credit Review", $htmlBody,"","",null,false,["abigail.gosling@tang.solutions"],false);
        }

        return Command::SUCCESS;
    }
}
