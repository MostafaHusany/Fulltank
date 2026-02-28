<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelType;
use App\Models\Station;
use App\Models\StationWorker;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiTesterController extends Controller
{
    public function index()
    {
        return view('admin.api_tester.index');
    }

    public function getDrivers(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $drivers = User::where('category', 'driver')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->with(['vehicle:id,plate_number', 'client:id,name,company_name'])
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'phone', 'username', 'vehicle_id', 'client_id', 'is_active']);

        $results = $drivers->map(function ($driver) {
            $statusBadge = $driver->is_active ? '' : ' [Inactive]';
            return [
                'id'         => $driver->id,
                'text'       => "{$driver->name} ({$driver->username}){$statusBadge} - " . 
                                ($driver->vehicle ? $driver->vehicle->plate_number : 'No Vehicle'),
                'username'   => $driver->username,
                'phone'      => $driver->phone,
                'vehicle_id' => $driver->vehicle_id,
                'client_id'  => $driver->client_id,
                'client'     => $driver->client?->company_name ?? $driver->client?->name,
                'is_active'  => $driver->is_active,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function getWorkers(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $workers = StationWorker::when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('username', 'like', "%{$search}%");
                      });
                });
            })
            ->with(['station:id,name', 'user:id,username,is_active'])
            ->orderBy('full_name')
            ->limit(30)
            ->get(['id', 'user_id', 'station_id', 'full_name', 'is_active']);

        $results = $workers->map(function ($worker) {
            $statusBadge = $worker->is_active ? '' : ' [Inactive]';
            return [
                'id'         => $worker->user_id,
                'text'       => "{$worker->full_name} ({$worker->user?->username}){$statusBadge} @ {$worker->station?->name}",
                'worker_id'  => $worker->id,
                'station_id' => $worker->station_id,
                'username'   => $worker->user?->username,
                'is_active'  => $worker->is_active,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function getVehicles(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        $clientId = $request->get('client_id');

        $vehicles = Vehicle::where('status', 'active')
            ->when($search, function ($query) use ($search) {
                $query->where('plate_number', 'like', "%{$search}%");
            })
            ->when($clientId, function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
            ->with(['client:id,name,company_name', 'fuelType:id,name'])
            ->limit(30)
            ->get(['id', 'plate_number', 'model', 'client_id', 'fuel_type_id']);

        $results = $vehicles->map(function ($vehicle) {
            return [
                'id'        => $vehicle->id,
                'text'      => "{$vehicle->plate_number} ({$vehicle->model})",
                'client_id' => $vehicle->client_id,
                'fuel_type' => $vehicle->fuelType?->name,
                'fuel_type_id' => $vehicle->fuel_type_id,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function getStations(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $stations = Station::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
            })
            ->limit(30)
            ->get(['id', 'name', 'address']);

        $results = $stations->map(function ($station) {
            return [
                'id'   => $station->id,
                'text' => "{$station->name} - {$station->address}",
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function getFuelTypes(): JsonResponse
    {
        $fuelTypes = FuelType::where('is_active', true)
            ->get(['id', 'name', 'price_per_liter']);

        $results = $fuelTypes->map(function ($type) {
            return [
                'id'    => $type->id,
                'text'  => "{$type->name} ({$type->price_per_liter} /L)",
                'price' => (float) $type->price_per_liter,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function quickLogin(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->tokens()->delete();
        $token = $user->createToken('api_tester_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'category' => $user->category,
                'username' => $user->username,
            ],
        ]);
    }
}
