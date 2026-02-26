<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Vehicle;
use App\Models\User;
use App\Services\QuotaService;
use App\Http\Traits\ResponseTemplate;

class VehicleQuotaController extends Controller
{
    use ResponseTemplate;

    public function __construct(
        protected QuotaService $quotaService
    ) {}

    public function index()
    {
        return view('admin.vehicle_quotas.index');
    }

    /**
     * AJAX: Load vehicles for a client with their quotas.
     */
    public function vehicles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $clientId = (int) $request->client_id;
        $client = User::where('category', 'client')->find($clientId);
        if (!$client) {
            return $this->responseTemplate(null, false, [__('vehicle_quotas.client_not_found')]);
        }

        $vehicles = Vehicle::where('client_id', $clientId)
            ->with(['activeQuota'])
            ->orderBy('plate_number')
            ->get();

        $data = $vehicles->map(function ($v) {
            $q = $v->activeQuota;
            return [
                'id'                => $v->id,
                'plate_number'      => $v->formatted_plate_number ?? $v->plate_number,
                'model'             => $v->model ?? '—',
                'amount_limit'      => $q ? (float) $q->amount_limit : 0,
                'consumed_amount'   => $q ? (float) $q->consumed_amount : 0,
                'remaining'         => $q ? (float) $q->remaining_amount : 0,
                'reset_cycle'       => $q ? $q->reset_cycle : 'one_time',
                'is_active'         => $q ? $q->is_active : false,
                'quota_id'          => $q ? $q->id : null,
            ];
        });

        return $this->responseTemplate($data->values()->all(), true, null);
    }

    /**
     * Single vehicle quota update.
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'            => 'required|exists:vehicles,id',
            'amount_limit'  => 'required|numeric|min:0',
            'reset_cycle'   => 'required|in:daily,weekly,monthly,one_time',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $vehicle = Vehicle::findOrFail($id);
        if ($vehicle->client_id != $request->client_id) {
            return $this->responseTemplate(null, false, [__('vehicle_quotas.vehicle_not_client')]);
        }

        try {
            $quota = $this->quotaService->upsertQuota(
                $id,
                (int) $request->client_id,
                (float) $request->amount_limit,
                $request->reset_cycle
            );
            $data = [
                'quota_id'       => $quota->id,
                'amount_limit'   => (float) $quota->amount_limit,
                'consumed_amount'=> (float) $quota->consumed_amount,
                'remaining'      => (float) $quota->remaining_amount,
                'reset_cycle'    => $quota->reset_cycle,
            ];
            return $this->responseTemplate($data, true, __('vehicle_quotas.updated'));
        } catch (Exception $e) {
            Log::error('VehicleQuotaController@update', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }

    /**
     * Bulk allocate quotas.
     */
    public function bulkAllocate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id'    => 'required|exists:users,id',
            'vehicle_ids'  => 'required|array',
            'vehicle_ids.*'=> 'integer|exists:vehicles,id',
            'amount_limit' => 'required|numeric|min:0',
            'reset_cycle'  => 'required|in:daily,weekly,monthly,one_time',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $clientId = (int) $request->client_id;
        $vehicleIds = array_map('intval', (array) $request->vehicle_ids);

        try {
            $count = $this->quotaService->bulkAllocate(
                $clientId,
                $vehicleIds,
                (float) $request->amount_limit,
                $request->reset_cycle
            );
            return $this->responseTemplate(['count' => $count], true, __('vehicle_quotas.bulk_updated', ['count' => $count]));
        } catch (Exception $e) {
            Log::error('VehicleQuotaController@bulkAllocate', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }
}
