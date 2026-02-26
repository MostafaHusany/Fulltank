<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;

use App\Models\Wallet;
use App\Models\User;

use App\Services\WalletService;

use App\Http\Traits\ResponseTemplate;

class WalletController extends Controller
{
    use ResponseTemplate;

    private $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(Request $request)
    {
        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['wallets_add', 'wallets_edit', 'wallets_delete', 'wallets_show']);

        if ($request->ajax()) {
            $model = Wallet::query()
                ->with('user:id,name,company_name,category')
                ->whereHas('user', function ($q) {
                    $q->where('category', 'client');
                })
                ->when($request->filled('client_id'), function ($q) use ($request) {
                    $q->where('user_id', $request->client_id);
                })
                ->orderBy('id', 'desc');

            $datatable_model = Datatables::of($model)
                ->addColumn('client_name', function ($row_object) {
                    $user = $row_object->user;
                    return $user ? e($user->company_name ?: $user->name) : '---';
                })
                ->addColumn('current_balance', function ($row_object) {
                    return number_format((float) $row_object->valide_balance, 2);
                })
                ->addColumn('wallet_status', function ($row_object) use ($permissions) {
                    return view('admin.wallets.incs._wallet_status', compact('row_object', 'permissions'));
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.wallets.incs._actions', compact('row_object', 'permissions'));
                });

            return $datatable_model->make(true);
        }

        return view('admin.wallets.index', compact('permissions'));
    }

    /**
     * Deposit (add balance) into a wallet.
     */
    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|exists:wallets,id',
            'amount'    => 'required|numeric|min:0.01',
            'notes'     => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $transaction = $this->walletService->deposit(
                (int) $request->wallet_id,
                (float) $request->amount,
                $request->notes
            );
            $wallet = $transaction->wallet;
            return $this->responseTemplate([
                'transaction' => $transaction,
                'new_balance' => (float) $wallet->valide_balance,
            ], true, __('wallets.deposit_success'));
        } catch (Exception $exception) {
            Log::error('WalletController@deposit Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('wallets.object_error')]);
        }
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
            $wallet = $this->walletService->toggleStatus((int) $walletId);
            return $this->responseTemplate($wallet, true, __('wallets.status_updated'));
        } catch (Exception $exception) {
            Log::error('WalletController@toggleStatus Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('wallets.object_error')]);
        }
    }

    /**
     * Transaction history for a wallet (for slide-over).
     */
    public function transactions(Request $request, $walletId)
    {
        $wallet = Wallet::with('user:id,name,company_name')->find($walletId);
        if (!$wallet) {
            return $this->responseTemplate(null, false, __('wallets.wallet_not_found'));
        }

        $transactions = $wallet->transactions()
            ->with('creator:id,name')
            ->orderBy('id', 'desc')
            ->get();

        $data = $transactions->map(function ($t) {
            return [
                'id'             => $t->id,
                'amount'         => (float) $t->amount,
                'type'           => $t->type,
                'notes'          => $t->notes,
                'before_balance' => (float) $t->before_balance,
                'after_balance'  => (float) $t->after_balance,
                'created_at'     => $t->created_at?->format('Y-m-d H:i'),
                'performer_name' => $t->creator ? e($t->creator->name) : '---',
            ];
        });

        return $this->responseTemplate($data, true, null);
    }
}
