<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverTruckingOffer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $driver_id;
    public $priority = 10;

    /**
     * Create a new event instance.
     */
    public function __construct($order, $driver_id)
    {
        $this->order     = $order;
        $this->driver_id = $driver_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('TruckingOffer.' . $this->driver_id),
        ];
    }

    public function broadcastAs () {
        return 'recivingTruckingOffers';
    }

    public function broadcastWith () {
        return ['order' => $this->order];
    }

}
