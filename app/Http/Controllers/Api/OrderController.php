<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($user, $cart, $totalPrice, $request) {
            // Create the order
            $order = new Order();
            $order->user_id = $user->id;
            $order->total = $totalPrice;
            $order->shipment_method_id = $request->shipment_method_id;
            $order->save();

            foreach ($cart->products as $product) {
                // Check if the product has enough stock
                if ($product->stock < $product->pivot->quantity) {
                    throw new \Exception("The product {$product->name} does not have enough stock.");
                }

                // Attach the product to the order with quantity
                $order->products()->attach($product->id, ['quantity' => $product->pivot->quantity]);

                // Decrease the stock of the product
                $product->decrement('stock', $product->pivot->quantity);
            }

            // Deduct from user's wallet
            $user->wallet->balance -= $totalPrice;
            $user->wallet->save();

            // Clear the cart
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
