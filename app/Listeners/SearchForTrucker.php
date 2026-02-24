<?php

namespace App\Listeners;

use Pusher\Pusher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\User;
use App\Models\TruckDriver;
use App\Models\TruckingOrder;
use App\Models\DriverLiveLocation;

use App\Events\NewTruckerOrder;

/**
 * Search for driver and send him a request to accept/reject the order
 * 
 * 1- call the live-location 
 * 
 */

class SearchForTrucker implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 5;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewTruckerOrder $event): void
    {
        $order            = $event->order;
        $search_range     = 5;
        $rejected_drivers = [];

        $order_type = $order->trucker_type !== 'all types' ? $order->trucker_type : null;
        
        while (true) {

            if ($order->status != 'waiting') {
                break;
            }

            $location = $this->getRadius($order->meeting_lat, $order->meeting_lng, $search_range);

            $driver = DriverLiveLocation::query()
            ->whereBetween('geo_lat',  $location['latitude_range'])
            ->whereBetween('geo_lng', $location['longitude_range'])
            ->whereNotIn('user_id', $rejected_drivers)
            // ->whereIn('type', !isset($order_type) ? ['hydrolic', 'manual'] : [$order_type])
            ->first();

            if (!isset($driver)) {
                $rejected_drivers   = [];
                $search_range       = $search_range < 25 ? $search_range + 5 : $search_range;
            } else {
                $rejected_drivers[] = $driver->user_id;// Not send to same driver more than once.

                $this->pushTruckingOffer($order, $driver->user_id);
                
                sleep(4);
            }
            
            $order->refresh();
        }
    }

    private function getRadius($latitude, $longitude, $radius = 5)
    {
        $earthRadius = 6371000;

        $radiusMeters = $radius * 1000;

        $maxLat = $latitude + rad2deg($radiusMeters / $earthRadius);
        $minLat = $latitude - rad2deg($radiusMeters / $earthRadius);

        $maxLon = $longitude + rad2deg(asin($radiusMeters / $earthRadius) / cos(deg2rad($latitude)));
        $minLon = $longitude - rad2deg(asin($radiusMeters / $earthRadius) / cos(deg2rad($latitude)));

        return [
            'latitude_range' => [
                'min_lat' => $minLat,
                'max_lat' => $maxLat,
            ],
            'longitude_range' => [
                'min_lon' => $minLon,
                'max_lon' => $maxLon,
            ]
        ];
    }

    private function pushTruckingOffer ($order, $user_id) {
        $options = [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => env('PUSHER_TLS'),
        ];
        
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );
    
        $channel = 'private-TruckingOffer.' . $user_id;
        $event = 'recivingTruckingOffers';
        $data = [
            'order' => $order,
        ];
    
        $pusher->trigger($channel, $event, $data);
    }
}
