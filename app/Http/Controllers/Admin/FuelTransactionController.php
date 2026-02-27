<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\FuelTransaction;

use App\Services\FuelTransactionService;
use App\Http\Traits\ResponseTemplate;

class FuelTransactionController extends Controller
{
    use ResponseTemplate;

    private $targetModel;
    private $fuelTransactionService;

    public function __construct(FuelTransactionService $fuelTransactionService)
    {
        $this->targetModel = new FuelTransaction;
        $this->fuelTransactionService = $fuelTransactionService;
    }

    public function index(Request $request)
    {
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';

        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['fuelTransactions_add', 'fuelTransactions_edit', 'fuelTransactions_delete', 'fuelTransactions_show']);

        if ($request->ajax()) {
            $model = $this->targetModel->query()
                ->with([
                    'client:id,name,company_name,phone,email',
                    'driver:id,name,phone',
                    'vehicle:id,plate_number,model',
                    'station:id,name,address,phone_1,manager_name',
                    'station.governorate:id,name',
                    'station.district:id,name',
                    'worker:id,user_id,station_id,full_name,phone',
                    'worker.user:id,name,username,phone',
                    'admin:id,name,email,phone',
                    'fuelType:id,name',
                ])
                ->adminFilter()
                ->orderBy('id', 'desc');

            $datatable_model = Datatables::of($model)
                ->addColumn('client_name', function ($row) {
                    if (!$row->client) return '---';
                    $clientData = json_encode([
                        'id'    => $row->client->id,
                        'name'  => $row->client->company_name ?: $row->client->name,
                        'phone' => $row->client->phone,
                        'email' => $row->client->email,
                    ]);
                    $name = e($row->client->company_name ?: $row->client->name);
                    return '<a href="javascript:void(0)" class="view-client-details text-primary text-decoration-underline" data-client=\'' . e($clientData) . '\'>' . $name . '</a>';
                })
                ->addColumn('vehicle_plate', function ($row) {
                    return $row->vehicle ? e($row->vehicle->plate_number) : '---';
                })
                ->addColumn('station_name', function ($row) {
                    if (!$row->station) return '---';
                    $stationData = json_encode([
                        'id'           => $row->station->id,
                        'name'         => $row->station->name,
                        'address'      => $row->station->address,
                        'phone'        => $row->station->phone_1,
                        'manager_name' => $row->station->manager_name,
                        'governorate'  => $row->station->governorate->name ?? null,
                        'district'     => $row->station->district->name ?? null,
                    ]);
                    $name = e($row->station->name);
                    return '<a href="javascript:void(0)" class="view-station-details text-primary text-decoration-underline" data-station=\'' . e($stationData) . '\'>' . $name . '</a>';
                })
                ->addColumn('fuel_type_name', function ($row) {
                    return $row->fuelType ? e($row->fuelType->name) : '---';
                })
                ->addColumn('formatted_amount', function ($row) {
                    return $row->total_amount ? number_format((float) $row->total_amount, 2) : '---';
                })
                ->addColumn('formatted_liters', function ($row) {
                    return $row->actual_liters ? number_format((float) $row->actual_liters, 3) : '---';
                })
                ->addColumn('status_badge', function ($row) use ($permissions) {
                    return view('admin.fuel_transactions.incs._status', compact('row', 'permissions'));
                })
                ->addColumn('meter_image_btn', function ($row) {
                    return view('admin.fuel_transactions.incs._image_btn', compact('row'));
                })
                ->addColumn('processed_by', function ($row) {
                    if ($row->type === 'manual_admin' && $row->admin) {
                        $adminData = json_encode([
                            'type'  => 'admin',
                            'id'    => $row->admin->id,
                            'name'  => $row->admin->name,
                            'email' => $row->admin->email,
                            'phone' => $row->admin->phone,
                        ]);
                        return '<a href="javascript:void(0)" class="view-processor-details" data-processor=\'' . e($adminData) . '\'><span class="badge bg-info"><i class="fas fa-user-shield me-1"></i>' . e($row->admin->name) . '</span></a>';
                    } elseif ($row->worker && $row->worker->user) {
                        $workerData = json_encode([
                            'type'     => 'worker',
                            'id'       => $row->worker->id,
                            'name'     => $row->worker->full_name,
                            'username' => $row->worker->user->username,
                            'phone'    => $row->worker->phone,
                        ]);
                        return '<a href="javascript:void(0)" class="view-processor-details" data-processor=\'' . e($workerData) . '\'><span class="badge bg-secondary"><i class="fas fa-hard-hat me-1"></i>' . e($row->worker->user->name) . '</span></a>';
                    }
                    return '---';
                })
                ->addColumn('actions', function ($row) use ($permissions) {
                    return view('admin.fuel_transactions.incs._actions', compact('row', 'permissions'));
                })
                ->rawColumns(['client_name', 'station_name', 'status_badge', 'meter_image_btn', 'processed_by', 'actions']);

            return $datatable_model->make(true);
        }

        return view('admin.fuel_transactions.index', compact('permissions', 'is_ar'));
    }

    public function show($id)
    {
        $transaction = $this->targetModel
            ->with(['client', 'driver', 'vehicle', 'station', 'worker.user', 'admin', 'fuelType'])
            ->find($id);

        if (!$transaction) {
            return $this->responseTemplate(null, false, __('fuel_transactions.object_not_found'));
        }

        $data = $transaction->toArray();
        $data['meter_image_url'] = $this->fuelTransactionService->getMeterImageUrl($transaction->meter_image);

        return $this->responseTemplate($data, true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id'    => 'required|exists:vehicles,id',
            'station_id'    => 'required|exists:stations,id',
            'fuel_type_id'  => 'required|exists:fuel_types,id',
            'total_amount'  => 'required|numeric|min:0.01',
            'driver_id'     => 'nullable|exists:users,id',
            'meter_image'   => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $meterImage = $request->hasFile('meter_image') ? $request->file('meter_image') : null;

            $transaction = $this->fuelTransactionService->createManualTransaction(
                $request->only(['vehicle_id', 'station_id', 'fuel_type_id', 'total_amount', 'driver_id']),
                $meterImage,
                auth()->id()
            );

            return $this->responseTemplate($transaction, true, [__('fuel_transactions.object_created')]);
        } catch (Exception $exception) {
            Log::error('FuelTransactionController@store Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [$exception->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $transaction = $this->targetModel->find($id);

        if (!$transaction) {
            return $this->responseTemplate(null, false, __('fuel_transactions.object_not_found'));
        }

        if (isset($request->refund)) {
            return $this->refundTransaction($request, $transaction);
        }

        if (isset($request->cancel)) {
            return $this->cancelTransaction($transaction);
        }

        return $this->responseTemplate(null, false, __('fuel_transactions.invalid_action'));
    }

    public function viewMeterImage($id)
    {
        $transaction = $this->targetModel->find($id);

        if (!$transaction || !$transaction->meter_image) {
            abort(404);
        }

        $path = Storage::disk('public')->path($transaction->meter_image);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    private function refundTransaction(Request $request, FuelTransaction $transaction)
    {
        $validator = Validator::make($request->all(), [
            'refund_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $refundedTransaction = $this->fuelTransactionService->refundTransaction(
                $transaction,
                $request->refund_reason,
                auth()->id()
            );

            return $this->responseTemplate($refundedTransaction, true, [__('fuel_transactions.refund_success')]);
        } catch (Exception $exception) {
            Log::error('FuelTransactionController@refundTransaction Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [$exception->getMessage()]);
        }
    }

    private function cancelTransaction(FuelTransaction $transaction)
    {
        try {
            $cancelledTransaction = $this->fuelTransactionService->cancelTransaction(
                $transaction,
                auth()->id()
            );

            return $this->responseTemplate($cancelledTransaction, true, [__('fuel_transactions.cancel_success')]);
        } catch (Exception $exception) {
            Log::error('FuelTransactionController@cancelTransaction Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [$exception->getMessage()]);
        }
    }
}
