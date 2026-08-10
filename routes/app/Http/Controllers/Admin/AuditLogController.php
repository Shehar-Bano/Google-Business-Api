<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a listing of admin audit logs with filtering and sorting.
     */
    public function index(Request $request): View
    {
        $perPage = (int) $request->integer('per_page', 25);
        $perPage = in_array($perPage, [10, 15, 20, 25, 50, 100], true) ? $perPage : 25;

        $sort = in_array(
            $request->string('sort', 'id')->toString(),
            ['id', 'user', 'action', 'target_type', 'description', 'ip_address', 'created_at'],
            true
        ) ? $request->string('sort', 'id')->toString() : 'id';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search = trim($request->string('search')->toString());
        $userFilter = $request->input('user_filter');
        $dateFrom = trim($request->string('date_from')->toString());
        $dateTo = trim($request->string('date_to')->toString());

        $query = AdminAuditLog::query()
            ->select('admin_audit_logs.*')
            ->with('user');

        if ($sort === 'user') {
            $query->leftJoin('users', 'admin_audit_logs.user_id', '=', 'users.id')
                  ->orderBy('users.name', $direction);
        } else {
            $query->orderBy('admin_audit_logs.' . $sort, $direction);
        }

        $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('admin_audit_logs.action', 'like', "%{$search}%")
                    ->orWhere('admin_audit_logs.target_type', 'like', "%{$search}%")
                    ->orWhere('admin_audit_logs.description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        });

        $query->when($userFilter !== null && $userFilter !== '', function ($q) use ($userFilter) {
            $q->where('admin_audit_logs.user_id', $userFilter);
        });

        $query->when($dateFrom !== '', function ($q) use ($dateFrom) {
            $q->whereDate('admin_audit_logs.created_at', '>=', $dateFrom);
        });

        $query->when($dateTo !== '', function ($q) use ($dateTo) {
            $q->whereDate('admin_audit_logs.created_at', '<=', $dateTo);
        });

        $logs = $query->paginate($perPage)->withQueryString();

        $logUsers = \App\Models\User::whereIn('id', AdminAuditLog::distinct()->pluck('user_id'))->get();

        return view('content.admin.audit-logs.index', compact('logs', 'search', 'perPage', 'sort', 'direction', 'userFilter', 'dateFrom', 'dateTo', 'logUsers'));
    }
}
