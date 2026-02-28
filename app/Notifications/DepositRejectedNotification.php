<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\DepositRequest;

class DepositRejectedNotification extends Notification
{
    use Queueable;

    protected DepositRequest $deposit;

    public function __construct(DepositRequest $deposit)
    {
        $this->deposit = $deposit;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'deposit_rejected',
            'icon'       => 'fa-times-circle',
            'color'      => 'danger',
            'title'      => __('notifications.deposit_rejected_title'),
            'message'    => __('notifications.deposit_rejected_message', [
                'amount' => number_format($this->deposit->amount, 2),
                'ref'    => $this->deposit->ref_number,
            ]),
            'link'       => route('client.deposits.index'),
            'deposit_id' => $this->deposit->id,
        ];
    }
}
