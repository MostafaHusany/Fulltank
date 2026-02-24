<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Traits\NotificationGeneration;
use App\Models\WorkshopOrder;

class SchedualOrderDateReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationGeneration;

    private $order;
    /**
     * Create a new job instance.
     */
    public function __construct(WorkshopOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->createNotificstion([
            'user_id'   => $this->order->client_id,
            'title'     => 'Reservation reminder for order number '.$this->order->order_number.' is today at '.$this->order->reservation_date,
            'body'      => 'Reservation order number '.$this->order->order_number.' is today at '.$this->order->reservation_date.' at workshop '.$this->order->workshop->name,
            'ar_title'  => $this->order->reservation_date.' بتاريح'.$this->order->order_number.' تذكر ان لديك اليوم حجز برقم',
            'ar_body'   => $this->order->workshop->name.' بمركز صيانة '.$this->order->reservation_date.' بتاريح'.$this->order->order_number.' تذكر ان لديك اليوم حجز برقم',
            'type'      => 'scheduled_order_reminder',
            'target_id' => $this->order->id,
        ]);
    }
}
