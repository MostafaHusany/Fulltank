<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StationWorker;
use App\Models\User;
use App\Models\Vehicle;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    protected array $allowedCategories = ['worker', 'driver'];

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string|min:4',
        ], [
            'username.required' => __('api.auth.username_required'),
            'password.required' => __('api.auth.password_required'),
            'password.min'      => __('api.auth.password_min'),
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = User::where('username', $request->username)
            ->orWhere('phone', $request->username)
            ->first();

        if (!$user) {
            return $this->unauthorized(__('api.auth.invalid_credentials'));
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->unauthorized(__('api.auth.invalid_credentials'));
        }

        if (!in_array($user->category, $this->allowedCategories)) {
            return $this->forbidden(__('api.auth.category_not_allowed'));
        }

        if (!$user->is_active) {
            return $this->forbidden(__('api.auth.account_inactive'));
        }

        $user->tokens()->delete();

        $token = $user->createToken('mobile_token')->plainTextToken;

        $profileData = $this->buildProfileData($user);

        if ($profileData === null) {
            return $this->error(__('api.auth.profile_incomplete'), 400);
        }

        return $this->successWithToken($profileData, $token, __('api.auth.login_success'));
    }

    protected function buildProfileData(User $user): ?array
    {
        $baseData = [
            'id'       => $user->id,
            'name'     => $user->name,
            'username' => $user->username,
            'phone'    => $user->phone,
            'email'    => $user->email,
            'category' => $user->category,
            'picture'  => $user->picture ? asset('storage/' . $user->picture) : null,
        ];

        if ($user->category === 'worker') {
            return $this->buildWorkerProfile($user, $baseData);
        }

        if ($user->category === 'driver') {
            return $this->buildDriverProfile($user, $baseData);
        }

        return null;
    }

    protected function buildWorkerProfile(User $user, array $baseData): ?array
    {
        $stationWorker = StationWorker::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('station:id,name,address,latitude,longitude')
            ->first();

        if (!$stationWorker || !$stationWorker->station) {
            return null;
        }

        return array_merge($baseData, [
            'worker_id'  => $stationWorker->id,
            'station_id' => $stationWorker->station_id,
            'station'    => [
                'id'        => $stationWorker->station->id,
                'name'      => $stationWorker->station->name,
                'address'   => $stationWorker->station->address,
                'latitude'  => $stationWorker->station->latitude,
                'longitude' => $stationWorker->station->longitude,
            ],
        ]);
    }

    protected function buildDriverProfile(User $user, array $baseData): ?array
    {
        $vehicle = $user->vehicle;

        if (!$vehicle) {
            return array_merge($baseData, [
                'vehicle_id'    => null,
                'client_id'     => $user->client_id,
                'vehicle'       => null,
                'monthly_quota' => null,
            ]);
        }

        $vehicle->load(['fuelType:id,name', 'activeQuota']);

        $quotaData = null;
        if ($vehicle->activeQuota) {
            $quotaData = [
                'amount_limit'     => (float) $vehicle->activeQuota->amount_limit,
                'consumed_amount'  => (float) $vehicle->activeQuota->consumed_amount,
                'remaining_amount' => (float) $vehicle->activeQuota->remaining_amount,
                'reset_cycle'      => $vehicle->activeQuota->reset_cycle,
                'last_reset_date'  => $vehicle->activeQuota->last_reset_date?->format('Y-m-d'),
            ];
        }

        return array_merge($baseData, [
            'vehicle_id' => $vehicle->id,
            'client_id'  => $user->client_id,
            'vehicle'    => [
                'id'           => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'model'        => $vehicle->model,
                'fuel_type'    => $vehicle->fuelType?->name,
                'status'       => $vehicle->status,
            ],
            'monthly_quota' => $quotaData,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, __('api.auth.logout_success'));
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        $profileData = $this->buildProfileData($user);

        if ($profileData === null) {
            return $this->error(__('api.auth.profile_incomplete'), 400);
        }

        return $this->success($profileData);
    }

    public function refreshProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->refresh();

        $profileData = $this->buildProfileData($user);

        if ($profileData === null) {
            return $this->error(__('api.auth.profile_incomplete'), 400);
        }

        return $this->success($profileData, __('api.auth.profile_refreshed'));
    }
}
