<?php

namespace App\Services\Admin;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderManagementService
{
    public function indexData(Request $request): array
    {
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 15, 20, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array(
            $request->string('sort', 'created_at')->toString(),
            ['order_id', 'status', 'created_at'],
            true
        ) ? $request->string('sort', 'created_at')->toString() : 'created_at';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search    = trim($request->string('search')->toString());
        $status    = trim($request->string('status')->toString());
        $dateFrom  = trim($request->string('date_from')->toString());
        $dateTo    = trim($request->string('date_to')->toString());

        $orders = Order::query()
            ->with(['user:id,name,email', 'detail:order_id,price'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $sub) use ($search): void {
                    $sub->where('order_id', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn (Builder $q) => $q->where('status', $status))
            ->when($dateFrom !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $stats = $this->buildStats();

        return compact('orders', 'stats', 'search', 'status', 'dateFrom', 'dateTo', 'sort', 'direction', 'perPage');
    }

    public function show(Order $order): Order
    {
        return $order->loadMissing(['user', 'detail']);
    }

    public function updateStatus(Order $order, string $status, ?int $price = null): Order
    {
        $order->update(['status' => $status]);

        // Update price in order_details when provided
        if ($price !== null && $order->detail) {
            $order->detail->update(['price' => $price]);
        } elseif ($price !== null && !$order->detail) {
            $order->detail()->create(['price' => $price]);
        }

        return $order->refresh();
    }

    private function buildStats(): array
    {
        $stats = ['total' => Order::count()];

        foreach (Order::STATUSES as $s) {
            $stats[$s] = Order::where('status', $s)->count();
        }

        return $stats;
    }
}
