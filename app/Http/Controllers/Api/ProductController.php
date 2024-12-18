<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    use ResponseTrait;
    public function index(Request $request)
{
    $productsQuery = Product::query()
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
        ->when($request->most_rated, function ($query) {
            $query->withAvg('ratings', 'rating')->orderBy('ratings_avg_rating', 'desc');
        })
        ->with(['productCategory', 'variationOptions']);

    $perPage = $request->get('per_page', 10);
    $products = $productsQuery->paginate($perPage);

    return response()->json([
        'total_products' => $products->total(),
        'products' => ProductResource::collection($products)->response()->getData(true),
    ]);
}


    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->store) {
            return response()->json(['error' => 'هذا المستخدم ليس لديه متجر'], 400);
        }

        $request->validate([
            'name' => 'required|string|unique:products,name',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'product_category_id' => 'required|exists:product_categories,id',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'product_category_id' => $request->product_category_id,
            'discount_percentage' => $request->discount_percentage ?? null,
            'discount_start' => $request->discount_start ?? null,
            'discount_end' => $request->discount_end ?? null,
            'store_id' => $user->store->id,
        ]);

        $images = $request->file('images');
        foreach ($images as $index => $image) {
            $path = $image->store('products/images', 'public');

            $product->images()->create([
                'image' => $path,
                'is_main' => $index === 0,
            ]);
        }

        return $this->success(message: 'تمت اضافة المنتج والصور بنجاح');
    }

    public function show(string $id)
    {
        $product = Product::with('productCategory', 'variationOptions')->find($id);
        return $this->success(ProductResource::make($product));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);
        $product->update($request->all());
        return $this->success(ProductResource::make($product));
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
            return $this->error('لقد قمت بتقييم هذا المنتج من قبل', 400);
        }

        $product->ratings()->attach(Auth::id(), ['rating' => $data['rating']]);
        return $this->success(message:'تم تقييم المنتج بنجاح');
    }

    public function mostOrderedProducts()
    {
        $mostOrderedProducts = Product::getMostOrderedProducts();

        return response()->json([
            'most_ordered_products' => ProductResource::collection($mostOrderedProducts),
        ]);
    }
}
