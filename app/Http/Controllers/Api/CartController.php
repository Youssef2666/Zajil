<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    use ResponseTrait;

    public function viewCart(Request $request)
    {
        $cart = Cart::with('products')->where('user_id', Auth::id())->first();

        if (!$cart || $cart->products->isEmpty()) {
            return $this->success(data: [], message: 'السلة فارغة', status: 200);
        }
        $totalPrice = $cart->products->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });
    
        return $this->success([
            'total_price' => number_format($totalPrice, 2),
            'cart' => $cart,
        ]);
    }

    public function addToCart(Request $request)
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            $cart = Cart::create(['user_id' => Auth::id()]);
        }
        $cart->products()->attach($request->product_id, ['quantity' => $request->quantity]);
        return $this->success(message: 'تم اضافة المنتج الى السلة');
    }

    public function removeFromCart(Request $request)
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        $cart->products()->detach($request->product_id);
        return $this->success(message: 'تم حذف المنتج من السلة');
    }
}
