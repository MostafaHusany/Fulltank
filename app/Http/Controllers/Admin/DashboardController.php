<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;
use Illuminate\Support\Facades\Validator;

use App\Services\DashboardService;
use App\Http\Traits\ResponseTemplate;

class DashboardController extends Controller
{
    use ResponseTemplate;

    private $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';

        $statCards = $this->dashboardService->getStatCards();
        $governorates = $this->dashboardService->getGovernorates();

        return view('admin.dashboard.index', compact('is_ar', 'statCards', 'governorates'));
    }

    public function getChartData(Request $request)
    {
        try {
            $weeklyTrend = $this->dashboardService->getWeeklyConsumptionTrend();
            $fuelDistribution = $this->dashboardService->getFuelTypeDistribution();
            $monthlyTrend = $this->dashboardService->getMonthlyTrend();

            return response()->json([
                'success'           => true,
                'weekly_trend'      => $weeklyTrend,
                'fuel_distribution' => $fuelDistribution,
                'monthly_trend'     => $monthlyTrend,
            ]);
        } catch (Exception $e) {
            Log::error('DashboardController@getChartData Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function getMapData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'governorate_id' => 'nullable|exists:governorates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'msg' => $validator->errors()]);
        }

        try {
            $governorateId = $request->filled('governorate_id') ? (int) $request->governorate_id : null;

            $stations = $this->dashboardService->getStationsForMap($governorateId);
            $center = null;

            if ($governorateId) {
                $center = $this->dashboardService->getGovernorateCenter($governorateId);
            }

            return response()->json([
                'success'  => true,
                'stations' => $stations,
                'center'   => $center,
            ]);
        } catch (Exception $e) {
            Log::error('DashboardController@getMapData Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function getStats(Request $request)
    {
        try {
            $statCards = $this->dashboardService->getStatCards();

            return response()->json([
                'success' => true,
                'stats'   => $statCards,
            ]);
        } catch (Exception $e) {
            Log::error('DashboardController@getStats Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}
