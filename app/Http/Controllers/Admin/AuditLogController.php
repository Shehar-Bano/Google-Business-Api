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
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $sort = in_array(
            $request->string('sort', 'id')->toString(),
            ['id', 'user', 'action', 'target_type', 'description', 'ip_address', 'created_at'],
            true
        ) ? $request->string('sort', 'id')->toString() : 'id';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search = trim($request->string('search')->toString());

        $query = AdminAuditLog::query()
            ->select('admin_audit_logs.*')
            ->with('user');

        if ($sort === 'user') {
            $query->leftJoin('users', 'admin_audit_logs.user_id', '=', 'users.id')
                  ->orderBy('users.name', $direction);
        } else {
            $query->orderBy('admin_audit_logs.' . $sort, $direction);
        }

        $logs = $query->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('admin_audit_logs.action', 'like', "%{$search}%")
                        ->orWhere('admin_audit_logs.target_type', 'like', "%{$search}%")
                        ->orWhere('admin_audit_logs.description', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.audit-logs.index', compact('logs', 'search', 'perPage', 'sort', 'direction'));
    }
}
