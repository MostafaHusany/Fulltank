<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class FinancialSettingsController extends Controller
{
    public function index()
    {
        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['financialSettings_*', 'paymentMethods_*']);
        return view('admin.financial_settings.index', compact('permissions'));
    }
}
