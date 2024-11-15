<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
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

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['store_id' => $product->store_id]
        );

        if ($cart->store_id === null) {
            $cart->store_id = $product->store_id;
            $cart->save();
        }

        if ($cart->store_id != $product->store_id) {
            return response()->json([
                'message' => 'You can only add products from the same store to the cart.',
            ], 400);
        }

        // Add the product to the cart with the specified quantity
        $cart->products()->attach($request->product_id, ['quantity' => $request->quantity]);

        return $this->success(message: 'Product added to cart successfully');
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
