<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\Admin\OrderManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    public function __construct(private readonly OrderManagementService $orderManagementService)
    {
    }

    public function index(Request $request): View
    {
        return view('content.admin.order-management.index', $this->orderManagementService->indexData($request));
    }

    public function show(Order $order): View
    {
        $order = $this->orderManagementService->show($order);

        return view('content.admin.order-management.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $price = $request->filled('price') ? (int) $request->input('price') : null;

        $this->orderManagementService->updateStatus(
            $order,
            $request->string('status')->toString(),
            $price
        );

        return redirect()->back()->with('success', 'Order updated successfully.');
    }
}
