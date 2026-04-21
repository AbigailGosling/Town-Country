<?php

namespace App\Listeners;
use App\Models\MailTracking;
use App\Notifications\TwoFactorCode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;

class NotificationSendingListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Illuminate\Notifications\Events\NotificationSending $event
     * @return void
     */
    public function handle(NotificationSending $event)
    {
        if ($event->notification instanceof TwoFactorCode)
        {
            $this->processTFC($event->notification);
        }
    }
    private function processTFC(TwoFactorCode $tfc)
    {
        $mt = new MailTracking();
        $mt->customer_id = -1;
        $mt->document_id = null;
        $mt->addressee = "";
        $mt->message_id = $tfc->getMessageID();
        $mt->type = "TWO_FACTOR_CODE";
        $mt->status = "SENDING";
        $mt->attachments = null;
        $mt->date_sent = Carbon::now();
        $mt->save();
    }
}
