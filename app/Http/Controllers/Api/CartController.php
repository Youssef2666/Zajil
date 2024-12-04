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
    use ResponseTrait;public function viewCart(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return $this->success(data: null, message: 'السلة فارغة', status: 200);
        }

        $products = $cart->products()->paginate($perPage);

        $totalPrice = $cart->products->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });

        return new CartResource($cart, $products);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);

        if ($request->quantity > $product->stock) {
            return $this->error(message: 'Requested quantity exceeds available stock.', code: 400);
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $existingProduct = $cart->products()->where('product_id', $product->id)->first();
        if ($existingProduct) {
            $newQuantity = $existingProduct->pivot->quantity + $request->quantity;

            if ($newQuantity > $product->stock) {
                return $this->error(message: 'Total quantity in the cart exceeds available stock.', code: 400);
            }

            $cart->products()->updateExistingPivot($product->id, ['quantity' => $newQuantity]);
        } else {
            $cart->products()->attach($request->product_id, ['quantity' => $request->quantity]);
        }

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

        $product = Product::findOrFail($request->product_id);

        if ($request->quantity > $product->stock) {
            return $this->error('الكمية المطلوبة تتجاوز الكمية المتوفرة في المخزون', 400);
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
