<?php

namespace App\Notifications;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class TwoFactorCode extends Notification
{
    use Queueable;
    private User $_user;
    private string $_messageID;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function getUser():User
    {
        return $this->_user;
    }
    public function getMessageID()
    {
        if (!isset($this->_messageID)) $this->_messageID = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0C2f ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0x2Aff ),
            mt_rand( 0, 0xffD3 ),
            mt_rand( 0, 0xff4B )
        );
        return $this->_messageID;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $this->_user = $notifiable;
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $this->_user = $notifiable;
        return (new MailMessage)
            ->line('Your two factor code is: ')
            ->line(new HtmlString("<div style='font-weight: bold; font-size: 24px; font-family: consolas;'>{$notifiable->two_factor_secret}</div>"))
            ->line('The code will expire in 10 minutes')
            ->line('If you have not tried to login, ignore this message.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $this->_user = $notifiable;
        return [
            'two_factor_secret' => $notifiable->two_factor_secret,
        ];
    }
}
