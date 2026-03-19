<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * Display a paginated listing of orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $orders = Order::with('user')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->user_id, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($request->search, fn($q, $search) => $q->where('order_number', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return OrderResource::collection($orders);
    }

    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \App\Http\Resources\OrderResource
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items']);

        return new OrderResource($order);
    }

    /**
     * Remove the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully.'], 200);
    }
}
