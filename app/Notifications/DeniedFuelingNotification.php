<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeniedFuelingNotification extends Notification
{
    use Queueable;

    protected string $reason;
    protected ?string $vehiclePlate;
    protected ?string $driverName;
    protected ?string $stationName;

    public function __construct(
        string $reason,
        ?string $vehiclePlate = null,
        ?string $driverName = null,
        ?string $stationName = null
    ) {
        $this->reason = $reason;
        $this->vehiclePlate = $vehiclePlate;
        $this->driverName = $driverName;
        $this->stationName = $stationName;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'denied_fueling',
            'icon'     => 'fa-ban',
            'color'    => 'danger',
            'title'    => __('notifications.denied_fueling_title'),
            'message'  => __('notifications.denied_fueling_message', [
                'reason'  => $this->reason,
                'vehicle' => $this->vehiclePlate ?? '-',
                'driver'  => $this->driverName ?? '-',
                'station' => $this->stationName ?? '-',
            ]),
            'link'     => route('client.live_monitor.index'),
            'reason'   => $this->reason,
            'vehicle'  => $this->vehiclePlate,
            'driver'   => $this->driverName,
            'station'  => $this->stationName,
        ];
    }
}
