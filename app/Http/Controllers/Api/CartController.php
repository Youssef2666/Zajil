<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    use ResponseTrait;

    public function viewCart(Request $request)
    {
        $cart = Cart::with('products')->where('user_id', Auth::id())->first();

        if (!$cart || $cart->products->isEmpty()) {
            return $this->success(data: null, message: 'السلة فارغة', status: 200);
        }

        return $this->success(new CartResource($cart));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            $cart = Cart::create(['user_id' => Auth::id()]);
        }
        $cart->products()->attach($request->product_id, ['quantity' => $request->quantity]);
        return $this->success(message: 'تم اضافة المنتج الى السلة');
    }

    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || !$cart->products()->where('product_id', $request->product_id)->exists()) {
            return $this->success(message: 'المنتج غير موجود في السلة', status: 404);
        }

        // Detach the product from the cart
        $cart->products()->detach($request->product_id);

        return $this->success(message: 'تم حذف المنتج من السلة');
    }

    public function updateProductQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return $this->error('السلة غير موجودة', 404);
        }

        $productExists = $cart->products()->where('product_id', $request->product_id)->exists();

        if ($productExists) {
            $cart->products()->updateExistingPivot($request->product_id, ['quantity' => $request->quantity]);
            return $this->success(message: 'تم تحديث كمية المنتج في السلة');
        } else {
            return $this->error('المنتج غير موجود في السلة', 404);
        }
    }

}
