<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Settlement;
use App\Models\Station;

use App\Services\SettlementService;
use App\Http\Traits\ResponseTemplate;

class SettlementController extends Controller
{
    use ResponseTemplate;

    private $targetModel;
    private $settlementService;

    public function __construct(SettlementService $settlementService)
    {
        $this->targetModel = new Settlement;
        $this->settlementService = $settlementService;
    }

    public function index(Request $request)
    {
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';

        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['settlements_add', 'settlements_edit', 'settlements_delete', 'settlements_show']);

        if ($request->has('stations_list')) {
            return $this->getStationsWithBalances();
        }

        if ($request->ajax()) {
            $model = $this->targetModel->query()
                ->with([
                    'station:id,name,address,phone_1,manager_name,bank_account_details,governorate_id,district_id',
                    'station.governorate:id,name',
                    'station.district:id,name',
                    'admin:id,name',
                ])
                ->adminFilter()
                ->orderBy('id', 'desc');

            $datatable_model = Datatables::of($model)
                ->addColumn('station_name', function ($row) {
                    if (!$row->station) return '---';
                    $stationData = json_encode([
                        'id'                   => $row->station->id,
                        'name'                 => $row->station->name,
                        'address'              => $row->station->address,
                        'phone'                => $row->station->phone_1,
                        'manager_name'         => $row->station->manager_name,
                        'bank_account_details' => $row->station->bank_account_details,
                        'governorate'          => $row->station->governorate->name ?? null,
                        'district'             => $row->station->district->name ?? null,
                    ]);
                    $name = e($row->station->name);
                    return '<a href="javascript:void(0)" class="view-station-details text-primary text-decoration-underline" data-station=\'' . e($stationData) . '\'>' . $name . '</a>';
                })
                ->addColumn('admin_name', function ($row) {
                    return $row->admin ? e($row->admin->name) : '---';
                })
                ->addColumn('formatted_amount', function ($row) {
                    return number_format((float) $row->amount, 2) . ' EGP';
                })
                ->addColumn('payment_method_label', function ($row) {
                    $badgeClass = match($row->payment_method) {
                        'cash'          => 'bg-success',
                        'bank_transfer' => 'bg-primary',
                        'check'         => 'bg-warning text-dark',
                        default         => 'bg-secondary',
                    };
                    return '<span class="badge ' . $badgeClass . '">' . e($row->payment_method_label) . '</span>';
                })
                ->addColumn('receipt_btn', function ($row) {
                    return view('admin.settlements.incs._receipt_btn', compact('row'));
                })
                ->addColumn('actions', function ($row) use ($permissions) {
                    return view('admin.settlements.incs._actions', compact('row', 'permissions'));
                })
                ->rawColumns(['station_name', 'payment_method_label', 'receipt_btn', 'actions']);

            return $datatable_model->make(true);
        }

        return view('admin.settlements.index', compact('permissions', 'is_ar'));
    }

    private function getStationsWithBalances()
    {
        try {
            $stations = Station::query()
                ->with(['governorate:id,name', 'district:id,name', 'wallet'])
                ->get()
                ->map(function ($station) {
                    $hasWallet = $station->wallet !== null;

                    $lastSettlement = Settlement::where('station_id', $station->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    return [
                        'id'                     => $station->id,
                        'name'                   => $station->name,
                        'governorate'            => $station->governorate->name ?? '---',
                        'district'               => $station->district->name ?? '---',
                        'address'                => $station->address ?? null,
                        'phone'                  => $station->phone_1 ?? null,
                        'manager_name'           => $station->manager_name ?? null,
                        'has_wallet'             => $hasWallet,
                        'unsettled_balance'      => $hasWallet ? number_format((float) $station->wallet->valide_balance, 2) : '0.00',
                        'unsettled_balance_raw'  => $hasWallet ? (float) $station->wallet->valide_balance : 0,
                        'wallet_is_active'       => $hasWallet ? $station->wallet->is_active : false,
                        'last_settlement_date'   => $lastSettlement ? $lastSettlement->created_at->format('Y-m-d H:i') : '---',
                        'last_settlement_amount' => $lastSettlement ? number_format((float) $lastSettlement->amount, 2) : null,
                        'bank_account_details'   => $station->bank_account_details ?? null,
                    ];
                })
                ->values();

            return response()->json(['success' => true, 'data' => $stations]);
        } catch (Exception $e) {
            Log::error('SettlementController@getStationsWithBalances Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'station_id'          => 'required|exists:stations,id',
            'amount'              => 'required|numeric|min:0.01',
            'payment_method'      => 'required|in:cash,bank_transfer,check',
            'transaction_details' => 'nullable|string|max:500',
            'receipt_image'       => 'required|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $receiptImage = $request->hasFile('receipt_image') ? $request->file('receipt_image') : null;

            $settlement = $this->settlementService->createSettlement(
                $request->only(['station_id', 'amount', 'payment_method', 'transaction_details']),
                $receiptImage,
                auth()->id()
            );

            return $this->responseTemplate($settlement, true, [__('settlements.object_created')]);
        } catch (Exception $exception) {
            Log::error('SettlementController@store Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [$exception->getMessage()]);
        }
    }

    public function show($id)
    {
        $settlement = $this->targetModel
            ->with(['station', 'admin'])
            ->find($id);

        if (!$settlement) {
            return $this->responseTemplate(null, false, __('settlements.object_not_found'));
        }

        $data = $settlement->toArray();
        $data['receipt_image_url'] = $this->settlementService->getReceiptImageUrl($settlement->receipt_image);

        return $this->responseTemplate($data, true);
    }

    public function viewReceipt($id)
    {
        $settlement = $this->targetModel->find($id);

        if (!$settlement || !$settlement->receipt_image) {
            abort(404);
        }

        $path = storage_path('app/public/' . $settlement->receipt_image);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
