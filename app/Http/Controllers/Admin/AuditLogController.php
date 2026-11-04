<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model - use model_type
        if ($request->filled('model')) {
            $query->where('model_type', 'LIKE', '%' . $request->model . '%');
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'LIKE', "%{$search}%")
                  ->orWhere('model_type', 'LIKE', "%{$search}%")
                  ->orWhere('ip_address', 'LIKE', "%{$search}%")
                  ->orWhere('url', 'LIKE', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter data
        $users = User::orderBy('name')->get();
        $actions = AuditLog::distinct()->pluck('action');

        // Get models from model_type
        $models = AuditLog::distinct()->pluck('model_type')
            ->map(function($item) {
                $parts = explode('\\', $item);
                return end($parts);
            })
            ->filter()
            ->unique()
            ->values();

        return view('admin.audit-logs.index', compact('logs', 'users', 'actions', 'models'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('admin.audit-logs.show', compact('auditLog'));
    }

    public function clear(Request $request)
    {
        $days = $request->input('days', 30);
        $deleted = AuditLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->route('audit-logs.index')
            ->with('success', "Deleted {$deleted} logs older than {$days} days.");
    }

    public function destroy(AuditLog $auditLog)
    {
        $auditLog->delete();
        return redirect()->route('audit-logs.index')
            ->with('success', 'Audit log deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = AuditLog::with('user');

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('model')) {
            $query->where('model_type', 'LIKE', '%' . $request->model . '%');
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_logs_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($logs) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                'User',
                'Action',
                'Model',
                'Model ID',
                'Old Values',
                'New Values',
                'IP Address',
                'URL',
                'Method',
                'Created At'
            ]);

            foreach ($logs as $log) {
                $modelName = $log->model_type;
                $parts = explode('\\', $modelName);
                $shortName = end($parts);

                fputcsv($handle, [
                    $log->id,
                    $log->user->name ?? 'System',
                    $log->action,
                    $shortName ?? 'N/A',
                    $log->model_id ?? 'N/A',
                    json_encode($log->old_values ?? []),
                    json_encode($log->new_values ?? []),
                    $log->ip_address ?? 'N/A',
                    $log->url ?? 'N/A',
                    $log->method ?? 'N/A',
                    $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
