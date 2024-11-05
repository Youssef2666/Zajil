<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $orders = Auth::user()->orders()->with(['products' => function ($query) {
            $query->select('products.id', 'products.name', 'products.price')
                ->withPivot('quantity');
        }])->get();
        return $this->success($orders);
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'coupon_code' => 'nullable|string',
        'shipment_method_id' => 'required|integer',
    ]);

    $user = Auth::user();
    $cart = $user->cart;

    if (!$cart || $cart->products->isEmpty()) {
        return response()->json(['message' => 'السلة فارغة'], 400);
    }

    // Calculate the original total price
    $totalPrice = $cart->products->sum(function ($product) {
        return $product->price * $product->pivot->quantity;
    });
    $originalTotalPrice = $totalPrice;

    // Apply coupon if available
    if (!empty($data['coupon_code'])) {
        $coupon = Coupon::where('code', $data['coupon_code'])->first();

        if (!$coupon || !$coupon->isValid() || !$coupon->hasRemainingUsage()) {
            return response()->json(['message' => 'كود الخصم غير صالح'], 400);
        }

        // Apply discount
        $totalPrice = $coupon->applyDiscount($totalPrice);
        $coupon->increment('usage_count');
    }

    if ($user->wallet->balance < $totalPrice) {
        return response()->json(['message' => 'ليس لديك رصيد كافي'], 400);
    }

    DB::transaction(function () use ($user, $cart, $totalPrice, $request) {
        // Create the order
        $order = new Order();
        $order->user_id = $user->id;
        $order->total = $totalPrice;
        $order->shipment_method_id = $request->shipment_method_id;
        $order->save();

        foreach ($cart->products as $product) {
            $order->products()->attach($product->id, ['quantity' => $product->pivot->quantity]);
        }

        $user->wallet->balance -= $totalPrice;
        $user->wallet->save();

        $cart->products()->detach();
    });

    return response()->json([
        'message' => 'تم انشاء الطلب بنجاح',
        'original_total_price' => $originalTotalPrice,
        'discounted_total_price' => number_format($totalPrice, 2),
    ], 201);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
