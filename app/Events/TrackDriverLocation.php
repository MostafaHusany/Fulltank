<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Database\Eloquent\Model\DriverLiveLocation;

class TrackDriverLocation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $geo_lat;
    public $geo_lng;
    public $user_id;
    public $group_id;

    /**
     * Create a new event instance.
     */
    public function __construct($geo_lat, $geo_lng, $user_id, $group_id)
    {
        $this->geo_lat  = $geo_lat;
        $this->geo_lng  = $geo_lng;
        $this->user_id  = $user_id;
        $this->group_id = $group_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('truckerManager.' . $this->group_id),
        ];
    }

    public function broadcastAs () {
        return 'truckerTracker';
    }

    public function broadcastWith () {
        return ['user_id' => $this->user_id, 'geo_lat' => $this->geo_lat, 'geo_lng' => $this->geo_lng];
    }
}
