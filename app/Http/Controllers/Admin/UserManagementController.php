<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use App\Services\Admin\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(private readonly UserManagementService $userManagementService)
    {
    }

    public function index(Request $request): View
    {
        return view('content.admin.user-management.index', $this->userManagementService->indexData($request));
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'businesses']);

        return view('content.admin.user-management.show', compact('user'));
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $oldStatus = $user->status;
        $newStatus = $request->string('status')->toString();
        
        $this->userManagementService->updateStatus($user, $newStatus);

        \App\Models\AdminAuditLog::log(
            'user_status_update',
            'User',
            (string) $user->id,
            "Updated user {$user->name} status from '{$oldStatus}' to '{$newStatus}'.",
            ['user_id' => $user->id, 'old_status' => $oldStatus, 'new_status' => $newStatus]
        );

        return redirect()->route('admin.user-management.index')->with('success', 'User status updated successfully.');
    }
}
