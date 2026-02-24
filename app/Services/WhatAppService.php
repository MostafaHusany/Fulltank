<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatAppService
{
    public function SmsOTP($phone, $code)
    {
        $response = Http::withOptions([
            'verify' => false
        ])
        ->withHeaders([
            'Authorization' => 'Bearer EAAPHhBIv7CcBO7DwgQQ6imFZCICYpgaqlwZAtZCLWngnX8M7DspR3WpWo73ZA0jGDo9ckAQ3oWZAWQzHgrzbhsMZChbZBaL7XeZAtiv9HNRqbhTDJwZCZA90eI9Ei7pFoHERfxLIgdKUCGwn9r6EocuRiEWgVGbuHi4y5BmWnn8omfVCdaZCzOWgOnoQtSXIUdNnd2G0pbK40nHKm4wis8vqJkZD',
            'Content-Type' => 'application/json'
        ])
        ->post('https://graph.facebook.com/v18.0/215272051667893/messages', [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $phone,
                'type' => 'template',
                'template' => [
                    'name' => 'o_t_p',
                    'language' => [
                        'code' => 'en'
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $code
                                ]
                            ]
                        ],
                        [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => '0',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $code
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }
}