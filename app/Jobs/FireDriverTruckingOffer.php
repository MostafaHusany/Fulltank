<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Events\DriverTruckingOffer;

class FireDriverTruckingOffer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $driver_id;

    /**
     * Create a new job instance.
     */
    public function __construct($order, $driver_id)
    {
        $this->order     = $order;
        $this->driver_id = $driver_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new DriverTruckingOffer($this->order, $this->driver_id));
    }
}
