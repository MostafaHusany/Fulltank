<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowBalanceNotification extends Notification
{
    use Queueable;

    protected float $balance;
    protected float $threshold;

    public function __construct(float $balance, float $threshold = 100)
    {
        $this->balance = $balance;
        $this->threshold = $threshold;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'low_balance',
            'icon'    => 'fa-wallet',
            'color'   => 'danger',
            'title'   => __('notifications.low_balance_title'),
            'message' => __('notifications.low_balance_message', [
                'balance'   => number_format($this->balance, 2),
                'threshold' => number_format($this->threshold, 2),
            ]),
            'link'    => route('client.wallet.index'),
            'balance' => $this->balance,
        ];
    }
}
