<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Store;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $orders = Auth::user()->orders()->with(['products' => function ($query) {
                $query->withPivot('quantity')
                ->with(['ratings', 'productCategory']);
        }])->get();

        return $this->success(OrderResource::collection($orders));
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
            // 'store_id' => 'required|integer|exists:stores,id',
        ]);
        
        $user = Auth::user();
        $cart = $user->cart;
        $store = $cart->products()->first()->store;

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

        DB::transaction(function () use ($user, $cart, $totalPrice, $request, $locationId, $store) {
            $order = new Order();
            $order->user_id = $user->id;
            $order->total = $totalPrice;
            $order->shipment_method_id = $request->shipment_method_id;
            $order->location_id = $locationId;
            $order->store_id = $store->id;
            $order->save();

            foreach ($cart->products as $product) {
                if ($product->stock < $product->pivot->quantity) {
                    throw new \Exception("The product {$product->name} does not have enough stock.");
                }

                $order->products()->attach($product->id, ['quantity' => $product->pivot->quantity]);
                $product->decrement('stock', $product->pivot->quantity);
            }

            $user->wallet->balance -= $totalPrice;
            $user->wallet->save();

            $user->transactions()->create([
                'wallet_id' => $user->wallet->id,
                'type' => 'debit',
                'amount' => $totalPrice,
                'description' => "Order #{$order->id} purchase from store {$store->name}",
                'order_id' => $order->id
            ]);

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
