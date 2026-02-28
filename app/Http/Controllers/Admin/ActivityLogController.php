<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use DataTables;
use LaravelLocalization;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\ActivityLog;
use App\Http\Traits\ResponseTemplate;

class ActivityLogController extends Controller
{
    use ResponseTemplate;

    private array $loggedModels = [
        'App\\Models\\User'            => 'User',
        'App\\Models\\Wallet'          => 'Wallet',
        'App\\Models\\FuelTransaction' => 'Fuel Transaction',
        'App\\Models\\Station'         => 'Station',
        'App\\Models\\Vehicle'         => 'Vehicle',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->category !== 'admin' && !$user->hasRole('admin')) {
            return redirect()->route('admin.error.no_permission');
        }

        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';
        $permissions = $user->category == 'admin' ? 'admin' : $user->allPermissions()->pluck('name')->toArray();

        $admins = User::whereIn('category', ['admin', 'technical'])->orderBy('name')->get(['id', 'name']);
        $modelTypes = $this->loggedModels;

        if ($request->ajax()) {
            $query = ActivityLog::with(['causer:id,name,email'])
                ->adminFilter()
                ->orderByDesc('id');

            return DataTables::of($query)
                ->addColumn('causer_name', function ($row) {
                    if ($row->causer) {
                        return '<span class="badge bg-primary"><i class="fas fa-user me-1"></i>' . e($row->causer->name) . '</span>';
                    }
                    return '<span class="badge bg-secondary">System</span>';
                })
                ->addColumn('event_badge', function ($row) {
                    $badges = [
                        'created' => '<span class="badge bg-success"><i class="fas fa-plus me-1"></i>Created</span>',
                        'updated' => '<span class="badge bg-warning text-dark"><i class="fas fa-edit me-1"></i>Updated</span>',
                        'deleted' => '<span class="badge bg-danger"><i class="fas fa-trash me-1"></i>Deleted</span>',
                    ];
                    return $badges[$row->event] ?? '<span class="badge bg-secondary">' . ucfirst($row->event) . '</span>';
                })
                ->addColumn('subject_label', function ($row) {
                    $shortType = class_basename($row->subject_type ?? '');
                    $icon = $this->getModelIcon($row->subject_type);
                    return '<span class="badge bg-info"><i class="fas ' . $icon . ' me-1"></i>' . $shortType . ' #' . $row->subject_id . '</span>';
                })
                ->addColumn('formatted_date', function ($row) {
                    return $row->created_at->format('d/m/Y H:i:s');
                })
                ->addColumn('ip_address', function ($row) {
                    return $row->ip_address ?? '---';
                })
                ->addColumn('actions', function ($row) {
                    return view('admin.activity_logs.incs._actions', ['row' => $row])->render();
                })
                ->rawColumns(['causer_name', 'event_badge', 'subject_label', 'actions'])
                ->make(true);
        }

        return view('admin.activity_logs.index', compact('is_ar', 'permissions', 'admins', 'modelTypes'));
    }

    public function show(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->category !== 'admin' && !$user->hasRole('admin')) {
            return $this->responseTemplate(null, false, [__('activity_logs.access_denied')]);
        }

        try {
            $log = ActivityLog::with(['causer:id,name,email,phone'])->findOrFail($id);

            $data = [
                'id'          => $log->id,
                'log_name'    => $log->log_name,
                'description' => $log->description,
                'event'       => $log->event,
                'subject'     => $log->subject_label,
                'causer'      => $log->causer ? [
                    'id'    => $log->causer->id,
                    'name'  => $log->causer->name,
                    'email' => $log->causer->email,
                ] : null,
                'ip_address'  => $log->ip_address,
                'user_agent'  => $log->user_agent,
                'changes'     => $log->changes,
                'old'         => $log->old,
                'new'         => $log->new,
                'created_at'  => $log->created_at->format('d/m/Y H:i:s'),
            ];

            return $this->responseTemplate($data, true, [__('activity_logs.object_loaded')]);
        } catch (Exception $e) {
            Log::error('ActivityLogController@show Exception', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }

    public function stats(Request $request)
    {
        $user = auth()->user();

        if ($user->category !== 'admin' && !$user->hasRole('admin')) {
            return $this->responseTemplate(null, false, [__('activity_logs.access_denied')]);
        }

        try {
            $today = now()->startOfDay();
            $thisWeek = now()->startOfWeek();
            $thisMonth = now()->startOfMonth();

            $stats = [
                'today'      => ActivityLog::where('created_at', '>=', $today)->count(),
                'this_week'  => ActivityLog::where('created_at', '>=', $thisWeek)->count(),
                'this_month' => ActivityLog::where('created_at', '>=', $thisMonth)->count(),
                'total'      => ActivityLog::count(),
                'by_event'   => [
                    'created' => ActivityLog::where('event', 'created')->count(),
                    'updated' => ActivityLog::where('event', 'updated')->count(),
                    'deleted' => ActivityLog::where('event', 'deleted')->count(),
                ],
            ];

            return $this->responseTemplate($stats, true, [__('activity_logs.stats_loaded')]);
        } catch (Exception $e) {
            Log::error('ActivityLogController@stats Exception', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }

    private function getModelIcon(?string $modelType): string
    {
        $icons = [
            'App\\Models\\User'            => 'fa-user',
            'App\\Models\\Wallet'          => 'fa-wallet',
            'App\\Models\\FuelTransaction' => 'fa-exchange-alt',
            'App\\Models\\Station'         => 'fa-gas-pump',
            'App\\Models\\Vehicle'         => 'fa-car',
        ];

        return $icons[$modelType] ?? 'fa-file';
    }
}
