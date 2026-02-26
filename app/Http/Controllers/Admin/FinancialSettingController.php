<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;

use App\Models\FinancialSetting;

use App\Http\Traits\ResponseTemplate;

class FinancialSettingController extends Controller
{
    use ResponseTemplate;

    public function index(Request $request)
    {
        $setting = FinancialSetting::getActive();
        return $this->responseTemplate($setting, true, null);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_type'  => 'required|in:fixed,percentage',
            'fee_value' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        try {
            $setting = FinancialSetting::getActive();
            $setting->fee_type = $request->fee_type;
            $setting->fee_value = $request->fee_value;
            $setting->save();
            return $this->responseTemplate($setting, true, __('deposit_requests.settings_updated'));
        } catch (Exception $e) {
            Log::error('FinancialSettingController@update', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('deposit_requests.object_error')]);
        }
    }
}
