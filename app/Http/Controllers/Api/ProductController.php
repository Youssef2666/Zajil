<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    use ResponseTrait;
    public function index(Request $request)
    {
        $products = Product::query()
            ->where('store_id', Auth::user()?->store?->id)
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->when($request->product_category_id, function ($query) use ($request) {
                $query->where('product_category_id', $request->product_category_id);
            })
            ->when($request->min_price && $request->max_price, function ($query) use ($request) {
                $query->whereBetween('price', [$request->min_price, $request->max_price]);
            })
            ->when($request->min_price && !$request->max_price, function ($query) use ($request) {
                $query->where('price', '>=', $request->min_price);
            })
            ->when(!$request->min_price && $request->max_price, function ($query) use ($request) {
                $query->where('price', '<=', $request->max_price);
            })
            ->when($request->most_popular, function ($query) {
                $query->withCount('favouritedByUsers')->orderBy('favourited_by_users_count', 'desc');
            })
            ->with(['productCategory', 'variationOptions'])
            ->get();

        return $this->success([
            'total_products' => $products->count(),
            'products' => ProductResource::collection($products),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->store) {
            return response()->json(['error' => 'هذا المستخدم ليس لديه متجر'], 400);
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'product_category_id' => $request->product_category_id,
            'store_id' => $user->store->id,
        ]);

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function show(string $id)
    {
        $product = Product::with('productCategory', 'variationOptions')->find($id);
        return $this->success($product);
    }

    /**
     * Update the specified resource in storage.
     */
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

    public function addVariationOptionsToProduct(Request $request, $productId)
    {
        $request->validate([
            'variation_option_ids' => 'required|array',
            'variation_option_ids.*' => 'exists:variation_options,id',
        ]);

        $product = Product::findOrFail($productId);

        // Attach variation options to the product without extra data
        $product->variationOptions()->syncWithoutDetaching($request->input('variation_option_ids'));

        return response()->json([
            'message' => 'Variation options added successfully.',
            'product' => $product->load('variationOptions'),
        ]);
    }

    public function addProductToFavourite(Request $request, $productId)
    {
        $request->validate([
            'product_id' => 'exists:products,id',
        ]);

        $user = Auth::user();

        $user->favouriteProducts()->toggle([$productId]);

        return $this->success(message: 'تمت العملية بنجاح');
    }

    public function rateProduct(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $product = Product::findOrFail($data['product_id']);

        $existingRating = $product->ratings()->where('user_id', Auth::id())->exists();

        if ($existingRating) {
            return $this->error('You have already rated this product', 400);
        }

        $product->ratings()->attach(Auth::id(), ['rating' => $data['rating']]);
        return $this->success($product, 'course rated successfully');
    }

}