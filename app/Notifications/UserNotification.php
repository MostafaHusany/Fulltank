<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kutia\Larafirebase\Messages\FirebaseMessage;
use Kutia\Larafirebase\Services\Larafirebase;

class UserNotification extends Notification
{
    private $notification;
    private $server_key;

    public function __construct($notification)
    {
        $this->notification = $notification;

        if ($notification->user->category === 'workshop_manager') {
            $this->server_key = config('larafirebase.workshop_manager_authentication_key');
        }
        
        if ($notification->user->category === 'client') {
            $this->server_key = config('larafirebase.authentication_key');
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['firebase'];
    }
    
    /**
     * Get the firebase representation of the notification.
     */
    public function toFirebase($notifiable)
    {
        $deviceTokens = $notifiable->devices->pluck('device_token')->toArray();
        
        return (new Larafirebase)
            ->withAuthenticationKey($this->server_key)
            ->withTitle($this->notification->title)
            ->withBody($this->notification->body)
            ->sendNotification($deviceTokens);
    }
}
