<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Vehicle;

class QuotaWarningNotification extends Notification
{
    use Queueable;

    protected Vehicle $vehicle;
    protected float $usedPercentage;

    public function __construct(Vehicle $vehicle, float $usedPercentage)
    {
        $this->vehicle = $vehicle;
        $this->usedPercentage = $usedPercentage;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'quota_warning',
            'icon'       => 'fa-gas-pump',
            'color'      => 'warning',
            'title'      => __('notifications.quota_warning_title'),
            'message'    => __('notifications.quota_warning_message', [
                'vehicle'    => $this->vehicle->plate_number,
                'percentage' => round($this->usedPercentage),
            ]),
            'link'       => route('client.quotas.index'),
            'vehicle_id' => $this->vehicle->id,
            'percentage' => $this->usedPercentage,
        ];
    }
}
