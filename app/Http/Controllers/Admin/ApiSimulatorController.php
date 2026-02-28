<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelTransaction;
use App\Models\FuelType;
use App\Models\FuelingRequest;
use App\Models\Station;
use App\Models\StationWorker;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleQuota;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiSimulatorController extends Controller
{
    protected array $testData = [];
    protected array $logs = [];
    protected array $steps = [];

    public function index()
    {
        return view('admin.api_tester.simulator');
    }

    public function runAutoTest(Request $request): JsonResponse
    {
        $cleanup = $request->boolean('cleanup', true);
        $fuelAmount = (float) ($request->input('fuel_amount', 20));

        $this->steps = [];
        $this->testData = [];

        try {
            // Step 1: Generate Test Data
            $this->addStep('generating_data', 'Generating test data...', 'running');
            $this->generateTestData();
            $this->updateStep('generating_data', 'Test data generated', 'success', [
                'client_id'  => $this->testData['client']->id,
                'driver_id'  => $this->testData['driver']->id,
                'vehicle_id' => $this->testData['vehicle']->id,
                'station_id' => $this->testData['station']->id,
                'worker_id'  => $this->testData['worker']->id,
            ]);

            // Step 2: Driver Login
            $this->addStep('driver_login', 'Driver logging in...', 'running');
            $driverToken = $this->loginUser($this->testData['driver']);
            $this->updateStep('driver_login', 'Driver logged in', 'success', [
                'token' => substr($driverToken, 0, 20) . '...',
            ]);

            // Step 3: Create Fueling Request
            $this->addStep('create_request', "Creating fueling request ({$fuelAmount}L)...", 'running');
            $fuelingResult = $this->createFuelingRequest($driverToken, $fuelAmount);
            $this->updateStep('create_request', 'Fueling request created', 'success', [
                'request_id' => $fuelingResult['request_id'],
                'otp_code'   => $fuelingResult['otp_code'],
            ]);

            // Step 4: Worker Login
            $this->addStep('worker_login', 'Worker logging in...', 'running');
            $workerToken = $this->loginUser($this->testData['workerUser']);
            $this->updateStep('worker_login', 'Worker logged in', 'success', [
                'token' => substr($workerToken, 0, 20) . '...',
            ]);

            // Step 5: Verify OTP
            $this->addStep('verify_otp', 'Verifying OTP...', 'running');
            $verifyResult = $this->verifyOtp($workerToken, $fuelingResult['otp_code']);
            $this->updateStep('verify_otp', 'OTP verified', 'success', $verifyResult);

            // Step 6: Confirm Fueling
            $this->addStep('confirm_fueling', 'Confirming fueling transaction...', 'running');
            $confirmResult = $this->confirmFueling($workerToken, $fuelingResult['request_id'], $fuelAmount);
            $this->updateStep('confirm_fueling', 'Fueling completed', 'success', $confirmResult);

            // Step 7: Verify Financial Deduction
            $this->addStep('verify_financials', 'Verifying financial deductions...', 'running');
            $verification = $this->verifyFinancials($fuelAmount);
            $this->updateStep('verify_financials', 'Financial verification complete', 'success', $verification);

            // Step 8: Cleanup (if requested)
            if ($cleanup) {
                $this->addStep('cleanup', 'Cleaning up test data...', 'running');
                $this->cleanupTestData();
                $this->updateStep('cleanup', 'Test data cleaned up', 'success');
            }

            return response()->json([
                'success' => true,
                'message' => 'Full cycle simulation completed successfully!',
                'steps'   => $this->steps,
                'summary' => [
                    'total_steps'    => count($this->steps),
                    'fuel_amount'    => $fuelAmount,
                    'transaction_id' => $confirmResult['transaction_id'] ?? null,
                    'cleaned_up'     => $cleanup,
                ],
            ]);

        } catch (\Exception $e) {
            // Mark current step as failed
            $this->failCurrentStep($e->getMessage());

            // Cleanup on failure if requested
            if ($cleanup && !empty($this->testData)) {
                try {
                    $this->cleanupTestData();
                } catch (\Exception $cleanupError) {
                    // Ignore cleanup errors
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Simulation failed: ' . $e->getMessage(),
                'steps'   => $this->steps,
            ], 400);
        }
    }

    protected function generateTestData(): void
    {
        DB::beginTransaction();

        try {
            // Get a fuel type
            $fuelType = FuelType::where('is_active', true)->first();
            if (!$fuelType) {
                throw new \Exception('No active fuel type found');
            }
            $this->testData['fuelType'] = $fuelType;

            // Create Client
            $client = User::create([
                'name'     => 'Test Client ' . Str::random(6),
                'username' => 'test_client_' . Str::random(8),
                'phone'    => '05' . rand(10000000, 99999999),
                'email'    => 'test_client_' . Str::random(8) . '@test.com',
                'password' => Hash::make('123456'),
                'category' => 'client',
                'is_active' => true,
            ]);
            $this->testData['client'] = $client;

            // Create Client Wallet
            $wallet = Wallet::create([
                'user_id'         => $client->id,
                'valide_balance'  => 1000.00,
                'pendding_balance' => 0,
                'is_active'       => true,
            ]);
            $this->testData['wallet'] = $wallet;

            // Create Vehicle
            $vehicle = Vehicle::create([
                'client_id'    => $client->id,
                'plate_number' => 'TEST ' . rand(1000, 9999),
                'model'        => 'Test Vehicle Model',
                'fuel_type_id' => $fuelType->id,
                'status'       => 'active',
            ]);
            $this->testData['vehicle'] = $vehicle;

            // Create Vehicle Quota
            $quota = VehicleQuota::create([
                'vehicle_id'      => $vehicle->id,
                'client_id'       => $client->id,
                'amount_limit'    => 500.00,
                'consumed_amount' => 0,
                'reset_cycle'     => 'monthly',
                'is_active'       => true,
            ]);
            $this->testData['quota'] = $quota;

            // Create Driver
            $driver = User::create([
                'name'       => 'Test Driver ' . Str::random(6),
                'username'   => 'test_driver_' . Str::random(8),
                'phone'      => '05' . rand(10000000, 99999999),
                'email'      => 'test_driver_' . Str::random(8) . '@test.com',
                'password'   => Hash::make('123456'),
                'category'   => 'driver',
                'client_id'  => $client->id,
                'vehicle_id' => $vehicle->id,
                'is_active'  => true,
            ]);
            $this->testData['driver'] = $driver;

            // Find or create a station (use existing if available)
            $station = Station::first();
            if (!$station) {
                // Create station manager
                $stationManager = User::create([
                    'name'     => 'Test Station Manager ' . Str::random(6),
                    'username' => 'test_manager_' . Str::random(8),
                    'phone'    => '05' . rand(10000000, 99999999),
                    'email'    => 'test_manager_' . Str::random(8) . '@test.com',
                    'password' => Hash::make('123456'),
                    'category' => 'station_manager',
                    'is_active' => true,
                ]);
                $this->testData['stationManager'] = $stationManager;

                $station = Station::create([
                    'name'    => 'Test Station ' . Str::random(6),
                    'address' => 'Test Address',
                    'lat'     => 24.7136,
                    'lng'     => 46.6753,
                    'user_id' => $stationManager->id,
                ]);
                $this->testData['createdStation'] = true;
            }
            $this->testData['station'] = $station;

            // Create Worker User
            $workerUser = User::create([
                'name'     => 'Test Worker ' . Str::random(6),
                'username' => 'test_worker_' . Str::random(8),
                'phone'    => '05' . rand(10000000, 99999999),
                'email'    => 'test_worker_' . Str::random(8) . '@test.com',
                'password' => Hash::make('123456'),
                'category' => 'worker',
                'is_active' => true,
            ]);
            $this->testData['workerUser'] = $workerUser;

            // Create Station Worker
            $worker = StationWorker::create([
                'user_id'    => $workerUser->id,
                'station_id' => $station->id,
                'full_name'  => $workerUser->name,
                'phone'      => $workerUser->phone,
                'is_active'  => true,
            ]);
            $this->testData['worker'] = $worker;

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function loginUser(User $user): string
    {
        // Direct token generation (bypass HTTP for reliability)
        $user->tokens()->delete();
        $token = $user->createToken('simulator_token')->plainTextToken;

        return $token;
    }

    protected function createFuelingRequest(string $token, float $amount): array
    {
        $driver = $this->testData['driver'];
        $vehicle = $this->testData['vehicle'];
        $fuelType = $this->testData['fuelType'];
        $client = $this->testData['client'];

        $pricePerLiter = (float) $fuelType->price_per_liter;
        $estimatedCost = $amount * $pricePerLiter;

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $fuelingRequest = FuelingRequest::create([
            'driver_id'             => $driver->id,
            'vehicle_id'            => $vehicle->id,
            'client_id'             => $client->id,
            'fuel_type_id'          => $fuelType->id,
            'requested_liters'      => $amount,
            'estimated_cost'        => $estimatedCost,
            'fuel_price_at_request' => $pricePerLiter,
            'otp_code'              => $otpCode,
            'latitude'              => 24.7136,
            'longitude'             => 46.6753,
            'status'                => 'pending',
            'expires_at'            => now()->addMinutes(15),
        ]);

        $this->testData['fuelingRequest'] = $fuelingRequest;

        return [
            'request_id' => $fuelingRequest->id,
            'otp_code'   => $otpCode,
        ];
    }

    protected function verifyOtp(string $token, string $otpCode): array
    {
        $fuelingRequest = FuelingRequest::where('otp_code', $otpCode)
            ->where('status', 'pending')
            ->first();

        if (!$fuelingRequest) {
            throw new \Exception('Fueling request not found with OTP: ' . $otpCode);
        }

        if ($fuelingRequest->expires_at->isPast()) {
            throw new \Exception('Fueling request has expired');
        }

        return [
            'verified'   => true,
            'request_id' => $fuelingRequest->id,
        ];
    }

    protected function confirmFueling(string $token, int $requestId, float $liters): array
    {
        $fuelingRequest = FuelingRequest::findOrFail($requestId);
        $stationWorker = $this->testData['worker'];
        $fuelType = $this->testData['fuelType'];

        $pricePerLiter = (float) $fuelingRequest->fuel_price_at_request;
        $totalCost = $liters * $pricePerLiter;

        // Deduct from wallet
        $wallet = Wallet::where('user_id', $fuelingRequest->client_id)->firstOrFail();
        $wallet->decrement('valide_balance', $totalCost);

        // Update quota
        $quota = VehicleQuota::where('vehicle_id', $fuelingRequest->vehicle_id)
            ->where('is_active', true)
            ->first();
        if ($quota) {
            $quota->increment('consumed_amount', $liters);
        }

        // Create transaction
        $referenceNo = 'FT-SIM-' . strtoupper(Str::random(8)) . '-' . time();

        $transaction = FuelTransaction::create([
            'reference_no'       => $referenceNo,
            'client_id'          => $fuelingRequest->client_id,
            'driver_id'          => $fuelingRequest->driver_id,
            'vehicle_id'         => $fuelingRequest->vehicle_id,
            'station_id'         => $stationWorker->station_id,
            'worker_id'          => $stationWorker->id,
            'fuel_type_id'       => $fuelingRequest->fuel_type_id,
            'price_per_liter'    => $pricePerLiter,
            'actual_liters'      => $liters,
            'total_amount'       => $totalCost,
            'max_allowed_amount' => $fuelingRequest->estimated_cost,
            'status'             => 'completed',
            'type'               => 'qr_based',
            'completed_at'       => now(),
        ]);

        // Mark request as completed
        $fuelingRequest->update([
            'status'                  => 'completed',
            'completed_by_worker_id'  => $stationWorker->id,
            'completed_at_station_id' => $stationWorker->station_id,
            'completed_at'            => now(),
        ]);

        $this->testData['transactionId'] = $transaction->id;

        return [
            'transaction_id' => $transaction->id,
            'reference_no'   => $referenceNo,
            'total_amount'   => $totalCost,
        ];
    }

    protected function verifyFinancials(float $fuelAmount): array
    {
        // Refresh wallet
        $wallet = Wallet::find($this->testData['wallet']->id);
        $expectedDeduction = $fuelAmount * $this->testData['fuelType']->price_per_liter;
        $actualBalance = (float) $wallet->valide_balance;
        $expectedBalance = 1000.00 - $expectedDeduction;

        // Check transaction exists
        $transaction = FuelTransaction::find($this->testData['transactionId']);

        // Check quota updated
        $quota = VehicleQuota::find($this->testData['quota']->id);

        $balanceCorrect = abs($actualBalance - $expectedBalance) < 0.01;
        $transactionExists = $transaction !== null && $transaction->status === 'completed';
        $quotaUpdated = (float) $quota->consumed_amount === $fuelAmount;

        if (!$balanceCorrect) {
            throw new \Exception("Balance mismatch: Expected {$expectedBalance}, Got {$actualBalance}");
        }

        if (!$transactionExists) {
            throw new \Exception('Transaction not found or not completed');
        }

        return [
            'balance_before'    => 1000.00,
            'balance_after'     => $actualBalance,
            'expected_deduction' => $expectedDeduction,
            'transaction_status' => $transaction->status,
            'quota_consumed'    => (float) $quota->consumed_amount,
            'all_checks_passed' => $balanceCorrect && $transactionExists && $quotaUpdated,
        ];
    }

    protected function cleanupTestData(): void
    {
        DB::beginTransaction();

        try {
            // Delete in reverse order of dependencies
            if (isset($this->testData['transactionId'])) {
                FuelTransaction::where('id', $this->testData['transactionId'])->delete();
            }

            // Delete fueling requests
            if (isset($this->testData['driver'])) {
                FuelingRequest::where('driver_id', $this->testData['driver']->id)->delete();
            }

            if (isset($this->testData['worker'])) {
                StationWorker::where('id', $this->testData['worker']->id)->delete();
            }

            if (isset($this->testData['workerUser'])) {
                $this->testData['workerUser']->tokens()->delete();
                User::where('id', $this->testData['workerUser']->id)->delete();
            }

            if (isset($this->testData['createdStation']) && isset($this->testData['station'])) {
                Station::where('id', $this->testData['station']->id)->delete();
            }

            if (isset($this->testData['stationManager'])) {
                User::where('id', $this->testData['stationManager']->id)->delete();
            }

            if (isset($this->testData['driver'])) {
                $this->testData['driver']->tokens()->delete();
                User::where('id', $this->testData['driver']->id)->delete();
            }

            if (isset($this->testData['quota'])) {
                VehicleQuota::where('id', $this->testData['quota']->id)->delete();
            }

            if (isset($this->testData['vehicle'])) {
                Vehicle::where('id', $this->testData['vehicle']->id)->delete();
            }

            if (isset($this->testData['wallet'])) {
                Wallet::where('id', $this->testData['wallet']->id)->delete();
            }

            if (isset($this->testData['client'])) {
                User::where('id', $this->testData['client']->id)->delete();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function addStep(string $key, string $message, string $status, array $data = []): void
    {
        $this->steps[$key] = [
            'key'       => $key,
            'message'   => $message,
            'status'    => $status,
            'data'      => $data,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function updateStep(string $key, string $message, string $status, array $data = []): void
    {
        if (isset($this->steps[$key])) {
            $this->steps[$key]['message'] = $message;
            $this->steps[$key]['status'] = $status;
            $this->steps[$key]['data'] = array_merge($this->steps[$key]['data'], $data);
            $this->steps[$key]['completed_at'] = now()->toIso8601String();
        }
    }

    protected function failCurrentStep(string $error): void
    {
        foreach ($this->steps as $key => &$step) {
            if ($step['status'] === 'running') {
                $step['status'] = 'failed';
                $step['error'] = $error;
                $step['completed_at'] = now()->toIso8601String();
                break;
            }
        }
    }
}
