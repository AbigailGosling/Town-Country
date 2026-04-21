<?php

namespace App\Listeners;
use App\Models\MailTracking;
use App\Notifications\TwoFactorCode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\InteractsWithQueue;

class NotificationSentListener
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
     * @param  Illuminate\Notifications\Events\NotificationSent $event
     * @return void
     */
    public function handle(NotificationSent $event)
    {
        if ($event->notification instanceof TwoFactorCode)
        {
            $this->processTFC($event->notification);
        }
    }
    private function processTFC(TwoFactorCode $tfc)
    {
        $mt = MailTracking::where("message_id",$tfc->getMessageID())->first();
        $mt->addressee = $tfc->getUser()->routeNotificationForMail();
        $mt->status = "SENT";
        $mt->save();
    }
}
