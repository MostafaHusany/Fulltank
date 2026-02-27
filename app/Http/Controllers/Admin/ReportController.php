<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Station;
use App\Models\Vehicle;
use App\Models\Governorate;

use App\Services\ReportService;
use App\Http\Traits\ResponseTemplate;

class ReportController extends Controller
{
    use ResponseTemplate;

    private $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';
        $governorates = Governorate::orderBy('name')->get(['id', 'name']);

        return view('admin.reports.index', compact('is_ar', 'governorates'));
    }

    public function clientStatement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'per_page'  => 'nullable|integer|min:10|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'msg' => $validator->errors()]);
        }

        try {
            $data = $this->reportService->getClientStatement(
                $request->client_id,
                $request->date_from,
                $request->date_to,
                $request->per_page ?? 20
            );

            if (!$data) {
                return response()->json(['success' => false, 'msg' => __('reports.client_not_found')]);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Log::error('ReportController@clientStatement Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function stationReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|exists:stations,id',
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date|after_or_equal:date_from',
            'per_page'   => 'nullable|integer|min:10|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'msg' => $validator->errors()]);
        }

        try {
            $data = $this->reportService->getStationReport(
                $request->station_id,
                $request->date_from,
                $request->date_to,
                $request->per_page ?? 20
            );

            if (!$data) {
                return response()->json(['success' => false, 'msg' => __('reports.station_not_found')]);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Log::error('ReportController@stationReport Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function vehicleConsumption(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'per_page'  => 'nullable|integer|min:10|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'msg' => $validator->errors()]);
        }

        try {
            $data = $this->reportService->getVehicleConsumption(
                $request->client_id,
                $request->date_from,
                $request->date_to,
                $request->per_page ?? 20
            );

            if (!$data) {
                return response()->json(['success' => false, 'msg' => __('reports.client_not_found')]);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Log::error('ReportController@vehicleConsumption Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function vehicleDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date|after_or_equal:date_from',
            'per_page'   => 'nullable|integer|min:10|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'msg' => $validator->errors()]);
        }

        try {
            $data = $this->reportService->getVehicleDetailedReport(
                $request->vehicle_id,
                $request->date_from,
                $request->date_to,
                $request->per_page ?? 20
            );

            if (!$data) {
                return response()->json(['success' => false, 'msg' => __('reports.vehicle_not_found')]);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Log::error('ReportController@vehicleDetail Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function overallSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'msg' => $validator->errors()]);
        }

        try {
            $data = $this->reportService->getOverallSummary(
                $request->date_from,
                $request->date_to
            );

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            Log::error('ReportController@overallSummary Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function exportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:client,station,vehicle',
            'id'          => 'required|integer',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'msg' => $validator->errors()]);
        }

        try {
            $reportType = $request->report_type;
            $data = null;

            switch ($reportType) {
                case 'client':
                    $data = $this->reportService->getClientStatement($request->id, $request->date_from, $request->date_to, 1000);
                    $view = 'admin.reports.pdf.client_statement';
                    $filename = 'client_statement_' . $request->id . '.pdf';
                    break;
                case 'station':
                    $data = $this->reportService->getStationReport($request->id, $request->date_from, $request->date_to, 1000);
                    $view = 'admin.reports.pdf.station_report';
                    $filename = 'station_report_' . $request->id . '.pdf';
                    break;
                case 'vehicle':
                    $data = $this->reportService->getVehicleDetailedReport($request->id, $request->date_from, $request->date_to, 1000);
                    $view = 'admin.reports.pdf.vehicle_report';
                    $filename = 'vehicle_report_' . $request->id . '.pdf';
                    break;
            }

            if (!$data) {
                return response()->json(['success' => false, 'msg' => __('reports.not_found')]);
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, ['data' => $data]);
            return $pdf->download($filename);

        } catch (Exception $e) {
            Log::error('ReportController@exportPdf Exception', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}
