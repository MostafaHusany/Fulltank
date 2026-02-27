<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\StationWorker;

class StationWorkerService
{
    public function create(array $data): StationWorker
    {
        return DB::transaction(function () use ($data) {
            $dummyEmail = $this->generateDummyEmail($data['username'], $data['station_id']);

            $user = User::create([
                'name'      => $data['full_name'],
                'username'  => $data['username'],
                'email'     => $dummyEmail,
                'password'  => Hash::make($data['password']),
                'category'  => 'worker',
                'is_active' => true,
            ]);

            $worker = StationWorker::create([
                'user_id'    => $user->id,
                'station_id' => $data['station_id'],
                'full_name'  => $data['full_name'],
                'phone'      => $data['phone'] ?? null,
                'is_active'  => true,
            ]);

            $worker->load(['user', 'station']);

            return $worker;
        });
    }

    public function update(StationWorker $worker, array $data): StationWorker
    {
        return DB::transaction(function () use ($worker, $data) {
            $worker->update([
                'station_id' => $data['station_id'],
                'full_name'  => $data['full_name'],
                'phone'      => $data['phone'] ?? null,
            ]);

            $userData = [
                'name'     => $data['full_name'],
                'username' => $data['username'],
            ];

            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            if ($worker->user) {
                if ($data['username'] !== $worker->user->username) {
                    $userData['email'] = $this->generateDummyEmail($data['username'], $data['station_id']);
                }
                $worker->user->update($userData);
            }

            $worker->load(['user', 'station']);

            return $worker;
        });
    }

    public function toggleStatus(StationWorker $worker): StationWorker
    {
        return DB::transaction(function () use ($worker) {
            $worker->is_active = !$worker->is_active;
            $worker->save();

            if ($worker->user) {
                $worker->user->is_active = $worker->is_active;
                $worker->user->save();
            }

            return $worker;
        });
    }

    public function delete(StationWorker $worker): bool
    {
        return DB::transaction(function () use ($worker) {
            $user = $worker->user;
            $worker->delete();

            if ($user) {
                $user->delete();
            }

            return true;
        });
    }

    private function generateDummyEmail(string $username, int $stationId): string
    {
        return "{$username}_at_{$stationId}@system.local";
    }
}
