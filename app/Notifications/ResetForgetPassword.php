<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Notification;

class ResetForgetPassword extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $pageUrl;
    public $token;
    public $email;
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->pageUrl = env('FRONTEND_URL');
        $this->email = $email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
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
        $token = $this->token;
        $email = $this->email;
        $pageUrl = $this->pageUrl;
        return (new MailMessage())
            ->subject('Reset application Password')
            ->markdown('email.notification.forget_password', compact('email', 'token', 'pageUrl'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
