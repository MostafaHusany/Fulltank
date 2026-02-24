<?php

namespace App\Listeners;

use App\Events\RequestOTP;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Services\WhatAppService;

use Illuminate\Support\Facades\Http;

class SendOtp
{
    /**
     * Create the event listener.
     */
    public function __construct(private WhatAppService $whatsapp)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RequestOTP $event): void
    {
        $this->whatsapp->SmsOTP($event->user->phone, $event->verification_code);
    }
}
