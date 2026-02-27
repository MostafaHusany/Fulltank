<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;

use App\Models\Wallet;
use App\Models\Station;

use App\Services\StationWalletService;
use App\Http\Traits\ResponseTemplate;

class StationWalletController extends Controller
{
    use ResponseTemplate;

    private $walletModel;
    private $stationWalletService;

    public function __construct(StationWalletService $stationWalletService)
    {
        $this->walletModel = new Wallet;
        $this->stationWalletService = $stationWalletService;
    }

    public function index(Request $request)
    {
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';

        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['stationWallets_show', 'stationWallets_edit']);

        if ($request->ajax()) {
            $model = $this->walletModel->query()
                ->with(['user:id,name,category', 'station.governorate', 'station.district'])
                ->whereHas('user', function ($q) {
                    $q->where('category', 'station_manager');
                })
                ->when($request->filled('station_name'), function ($q) use ($request) {
                    $q->whereHas('station', fn ($s) => $s->where('name', 'like', '%' . $request->station_name . '%'));
                })
                ->when($request->filled('governorate_id'), function ($q) use ($request) {
                    $q->whereHas('station', fn ($s) => $s->where('governorate_id', $request->governorate_id));
                })
                ->when($request->filled('district_id'), function ($q) use ($request) {
                    $q->whereHas('station', fn ($s) => $s->where('district_id', $request->district_id));
                })
                ->when($request->filled('is_active'), function ($q) use ($request) {
                    $q->where('is_active', $request->is_active == '1');
                })
                ->orderBy('id', 'desc');

            $datatable_model = Datatables::of($model)
                ->addColumn('station_name', function ($row_object) {
                    return $row_object->station ? e($row_object->station->name) : '---';
                })
                ->addColumn('governorate_name', function ($row_object) {
                    return $row_object->station && $row_object->station->governorate
                        ? e($row_object->station->governorate->name)
                        : '---';
                })
                ->addColumn('district_name', function ($row_object) {
                    return $row_object->station && $row_object->station->district
                        ? e($row_object->station->district->name)
                        : '---';
                })
                ->addColumn('current_balance', function ($row_object) {
                    return number_format((float) $row_object->valide_balance, 2);
                })
                ->addColumn('wallet_status', function ($row_object) use ($permissions) {
                    return view('admin.station_wallets.incs._wallet_status', compact('row_object', 'permissions'));
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.station_wallets.incs._actions', compact('row_object', 'permissions'));
                })
                ->rawColumns(['wallet_status', 'actions']);

            return $datatable_model->make(true);
        }

        return view('admin.station_wallets.index', compact('permissions', 'is_ar'));
    }

    /**
     * Toggle wallet is_active status.
     */
    public function toggleStatus(Request $request, $walletId)
    {
        $validator = Validator::make(['wallet_id' => $walletId], [
            'wallet_id' => 'required|exists:wallets,id',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $wallet = $this->stationWalletService->toggleStatus((int) $walletId);
            return $this->responseTemplate($wallet, true, __('station_wallets.status_updated'));
        } catch (Exception $exception) {
            Log::error('StationWalletController@toggleStatus Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('station_wallets.object_error')]);
        }
    }

    /**
     * Transaction history for a wallet (for slide-over).
     */
    public function transactions(Request $request, $walletId)
    {
        $wallet = Wallet::with(['user:id,name', 'station:id,name,user_id'])->find($walletId);
        if (!$wallet) {
            return $this->responseTemplate(null, false, __('station_wallets.wallet_not_found'));
        }

        try {
            $data = $this->stationWalletService->getTransactions((int) $walletId);
            return $this->responseTemplate($data, true, null);
        } catch (Exception $exception) {
            Log::error('StationWalletController@transactions Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('station_wallets.object_error')]);
        }
    }
}
