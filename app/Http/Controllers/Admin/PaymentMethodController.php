<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;

use App\Models\PaymentMethod;

use App\Http\Traits\ResponseTemplate;

class PaymentMethodController extends Controller
{
    use ResponseTemplate;

    public function index(Request $request)
    {
        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['paymentMethods_add', 'paymentMethods_edit', 'paymentMethods_delete', 'paymentMethods_show']);

        if ($request->ajax()) {
            $model = PaymentMethod::query()->orderBy('id', 'desc');
            $dt = Datatables::of($model)
                ->addColumn('status_badge', fn($row) => $row->is_active
                    ? '<span class="badge bg-success">' . __('layouts.active') . '</span>'
                    : '<span class="badge bg-warning">' . __('layouts.de-active') . '</span>')
                ->addColumn('actions', function ($row) use ($permissions) { return view('admin.payment_methods.incs._actions', compact('row', 'permissions')); })
                ->rawColumns(['status_badge', 'actions']);
            return $dt->make(true);
        }

        return redirect()->route('admin.financialSettings.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'account_details' => 'nullable|string|max:500',
            'is_active'       => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        try {
            $data = $request->only(['name', 'account_details']);
            $data['is_active'] = (bool) $request->get('is_active', true);
            $pm = PaymentMethod::create($data);
            return $this->responseTemplate($pm, true, __('deposit_requests.object_created'));
        } catch (Exception $e) {
            Log::error('PaymentMethodController@store', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('deposit_requests.object_error')]);
        }
    }

    public function update(Request $request, $id)
    {
        $pm = PaymentMethod::find($id);
        if (!$pm) {
            return $this->responseTemplate(null, false, [__('deposit_requests.object_not_found')]);
        }
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'account_details' => 'nullable|string|max:500',
            'is_active'       => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        try {
            $pm->name = $request->name;
            $pm->account_details = $request->account_details;
            $pm->is_active = (bool) $request->get('is_active', true);
            $pm->save();
            return $this->responseTemplate($pm, true, __('deposit_requests.object_updated'));
        } catch (Exception $e) {
            Log::error('PaymentMethodController@update', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('deposit_requests.object_error')]);
        }
    }

    public function destroy($id)
    {
        $pm = PaymentMethod::find($id);
        if (!$pm) {
            return $this->responseTemplate(null, false, [__('deposit_requests.object_not_found')]);
        }
        try {
            $pm->delete();
            return $this->responseTemplate(null, true, __('deposit_requests.object_deleted'));
        } catch (Exception $e) {
            Log::error('PaymentMethodController@destroy', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('deposit_requests.object_error')]);
        }
    }

    public function listActive()
    {
        $list = PaymentMethod::where('is_active', true)->orderBy('name')->get(['id', 'name', 'account_details']);
        return $this->responseTemplate($list, true, null);
    }
}
