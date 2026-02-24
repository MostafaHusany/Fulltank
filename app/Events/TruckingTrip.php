<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TruckingTrip implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $user;
    public $type;
    public $order_id;
    public $priority = 100;


    /**
     * Create a new event instance.
     */
    public function __construct($user, $order_id, $data, $type = 'location')
    {
        $this->order_id = $order_id;
        $this->data = $data;
        $this->type = $type;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('TruckingTrip.' .  $this->order_id),
        ];
    }

    public function broadcastAs () {
        return 'truckingTrip';
    }

    public function broadcastWith () {
        return ['type' => $this->type, 'user' => $this->user, 'data' => $this->data];
    }
}
