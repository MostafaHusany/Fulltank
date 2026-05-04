<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Timezone for grouping vehicle locations into daily routes
    |--------------------------------------------------------------------------
    */
    'timezone' => env('VEHICLE_TRACKING_TZ', 'Africa/Cairo'),

    /*
    |--------------------------------------------------------------------------
    | OSRM public router (override for self-hosted OSRM in production)
    |--------------------------------------------------------------------------
    */
    'osrm_base_url' => rtrim(env('OSRM_BASE_URL', 'https://router.project-osrm.org'), '/'),

];
