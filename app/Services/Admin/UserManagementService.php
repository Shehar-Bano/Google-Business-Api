<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserManagementService
{
    public function indexData(Request $request): array
    {
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 15, 20, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array(
            $request->string('sort', 'created_at')->toString(),
            ['name', 'email', 'phone', 'role', 'status', 'created_at'],
            true
        ) ? $request->string('sort', 'created_at')->toString() : 'created_at';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search   = trim($request->string('search')->toString());
        $status   = trim($request->string('status')->toString());
        $phone    = trim($request->string('phone')->toString());
        $dateFrom = trim($request->string('date_from')->toString());
        $dateTo   = trim($request->string('date_to')->toString());

        $users = User::query()
            ->with('businesses')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $sub) use ($search): void {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $q) => $q->where('status', $status))
            ->when($phone !== '', fn (Builder $q) => $q->where('phone', 'like', "%{$phone}%"))
            ->when($dateFrom !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $stats = $this->buildStats();

        return compact('users', 'stats', 'search', 'status', 'phone', 'dateFrom', 'dateTo', 'sort', 'direction', 'perPage');
    }

    public function updateStatus(User $user, string $status): User
    {
        $user->update(['status' => $status]);

        return $user;
    }

    private function buildStats(): array
    {
        $stats = ['total' => User::count()];

        foreach (User::ADMIN_STATS_STATUSES as $s) {
            $stats[$s] = User::where('status', $s)->count();
        }

        return $stats;
    }
}
