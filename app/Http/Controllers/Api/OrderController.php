<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Store;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ResponseTrait;
    public function index(Request $request)
    {
        $status = $request->input('status');
        $ordersQuery = Auth::user()->orders()->with(['location', 'products' => function ($query) {
            $query->withPivot('quantity')
                ->with(['productCategory']);
        }]);

        if ($status) {
            $ordersQuery->where('status', $status);
        }

        $ordersQuery->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 10);

        $orders = $ordersQuery->paginate($perPage);

        return OrderResource::collection($orders)->response();
    }

    public function store(Request $request)
    {
        $locationId = $request->input('location_id') ?? Auth::user()->locations()->where('is_default', 1)->pluck('id')->first();

        if (!$locationId) {
            return response()->json(['message' => 'لم يتم العثور على موقع افتراضي'], 400);
        }

        $data = $request->validate([
            'coupon_code' => 'nullable|string',
            'shipment_method_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart || $cart->products->isEmpty()) {
            return response()->json(['message' => 'السلة فارغة'], 400);
        }

        $groupedProducts = $cart->products->groupBy('store_id');

        $totalPrice = $cart->products->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });
        $originalTotalPrice = $totalPrice;

        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();

            if (!$coupon || !$coupon->isValid() || !$coupon->hasRemainingUsage()) {
                return response()->json(['message' => 'كود الخصم غير صالح'], 400);
            }

            $totalPrice = $coupon->applyDiscount($totalPrice);
            $coupon->increment('usage_count');
        }

        if ($user->wallet->balance < $totalPrice) {
            return response()->json(['message' => 'ليس لديك رصيد كافي'], 400);
        }

        $createdOrders = [];

        DB::transaction(function () use ($user, $cart, $groupedProducts, $totalPrice, $request, $locationId, &$createdOrders) {
            foreach ($groupedProducts as $storeId => $products) {
                $storeTotalPrice = $products->sum(function ($product) {
                    return $product->price * $product->pivot->quantity;
                });

                $order = new Order();
                $order->user_id = $user->id;
                $order->total = $storeTotalPrice;
                $order->shipment_method_id = $request->shipment_method_id;
                $order->location_id = $locationId;
                $order->store_id = $storeId;
                $order->code = 'ORD-' . uniqid();
                $order->save();

                foreach ($products as $product) {
                    if ($product->stock < $product->pivot->quantity) {
                        throw new \Exception("The product {$product->name} does not have enough stock.");
                    }

                    $order->products()->attach($product->id, [
                        'quantity' => $product->pivot->quantity,
                        'price_at_purchase' => $product->price,
                    ]);
                    $product->decrement('stock', $product->pivot->quantity);
                }

                $user->transactions()->create([
                    'wallet_id' => $user->wallet->id,
                    'type' => 'debit',
                    'amount' => $storeTotalPrice,
                    'description' => "Order #{$order->id} purchase from store ID {$storeId}",
                    'order_id' => $order->id,
                ]);

                $createdOrders[] = $order;
            }

            $user->wallet->balance -= $totalPrice;
            $user->wallet->save();

            $cart->products()->detach();
        });

        return response()->json([
            'message' => 'تم انشاء الطلبات بنجاح',
            'orders' => $createdOrders,
            'original_total_price' => $originalTotalPrice,
            'discounted_total_price' => number_format($totalPrice, 2),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();

        if (!$user->store) {
            return $this->error('ليس لديك متجر', 404);
        }

        $order = $user->store->orders()->with('products')->find($id);

        if (!$order) {
            return $this->error('الطلب غير موجود', 404);
        }

        return $this->success(new OrderResource($order));
    }

    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
