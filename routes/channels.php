<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('truckerManager.{id}', function ($user, $id) {
    // Trucking company can see there own drivers locations
    // return (int) $user->id === (int) $id;
    return true;
});

Broadcast::channel('TruckingOffer.{id}', function ($user, $id) {
    // share offers to the driver
    // return (int) $user->id === (int) $id;
    return true;
});

Broadcast::channel('TruckingTrip.{order_id}', function ($user, $order_id) {
    // Link between the driver & client
    return true;
    if (auth()->user()->category == 'client')
        return auth()->user()->client->orders()->where('id', $order_id)->count();

    if (auth()->user()->category == 'truckers_drivers')
        return auth()->user()->truckDriver->truckingOrders()->where('id', $order_id)->count();

});