<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Wallet;

class UserService
{
    /**
     * Create a client user and their wallet. Optionally handle picture upload.
     */
    public function createClient(array $data): User
    {
        $data['category'] = 'client';
        $data['password'] = isset($data['password']) && $data['password']
            ? bcrypt($data['password'])
            : bcrypt('12345678');

        $user = User::create($data);

        $user->wallet()->create([
            'valide_balance'   => 0,
            'pendding_balance' => 0,
        ]);

        return $user;
    }

    /**
     * Update a client user. Optionally handle new picture upload.
     */
    public function updateClient(User $user, array $data): User
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }

    /**
     * Handle picture upload for client. Returns path to store in user.picture.
     */
    public function handleClientPicture(Request $request, string $field = 'picture'): ?string
    {
        if (!$request->hasFile($field) || !$request->file($field)->isValid()) {
            return null;
        }

        return str_replace('public/', '', $request->file($field)->store('public/media/clients_pictures'));
    }

    /**
     * Create a driver user (category=driver), link to client, optional vehicle, and init wallet with 0 balance.
     */
    public function createDriver(array $data): User
    {
        $data['category'] = 'driver';
        $data['password'] = isset($data['password']) && $data['password']
            ? bcrypt($data['password'])
            : bcrypt('12345678');

        if (empty($data['vehicle_id'])) {
            $data['vehicle_id'] = null;
        }

        $user = User::create($data);

        $user->wallet()->create([
            'valide_balance'   => 0,
            'pendding_balance' => 0,
        ]);

        return $user;
    }

    /**
     * Update a driver user.
     */
    public function updateDriver(User $user, array $data): User
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        if (array_key_exists('vehicle_id', $data) && empty($data['vehicle_id'])) {
            $data['vehicle_id'] = null;
        }

        $user->update($data);

        return $user;
    }
}
