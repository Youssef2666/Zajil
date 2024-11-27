<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Models\Product;
use App\Models\Store;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{

    use ResponseTrait;
    public function index(Request $request)
    {
        $stores = Store::query();

        if ($request->has('search')) {
            $stores->where('name', 'like', '%' . $request->search . '%');
        }

        $stores = $stores->with('products', 'location')->get();

        return $this->success(StoreResource::collection($stores));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->store) {
            return response()->json(['error' => 'هذا المستخدم لديه متجر'], 400);
        }
        $store = Store::create([
            'name' => $request->name,
            'description' => $request->description,
            // 'store_location_id' => $request->store_location_id,
            'user_id' => Auth::id(),
        ]);
        return $this->success($store, status: 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $storeQuery = Store::with(['location', 'products' => function ($query) use ($request) {
            if ($request->has('product_category_id')) {
                $query->where('product_category_id', $request->input('product_category_id'));
            }
            $query->with('productCategory');
        }]);

        $store = $storeQuery->findOrFail($id);

        return $this->success(StoreResource::make($store));
    }

    public function update(Request $request, string $id)
    {
        $store = Store::find($id);
        $store->update($request->all());
        return $this->success($store);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

    }

    public function addStoreToFavourite(Request $request)
    {
        $request->validate([
            'store_id' => 'exists:stores,id',
        ]);

        $user = Auth::user();

        $user->favouriteStores()->toggle([$request->store_id]);

        return $this->success(message: 'تمت العملية بنجاح');
    }

    public function rateStore(Request $request)
    {
        $data = $request->validate([
            'store_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $store = Store::findOrFail($data['store_id']);

        $existingRating = $store->ratings()->where('user_id', Auth::id())->exists();

        if ($existingRating) {
            return $this->error('You have already rated this store', 400);
        }

        $store->ratings()->attach(Auth::id(), ['rating' => $data['rating']]);
        return $this->success($store, 'course rated successfully');
    }

    public function getStoreProducts(Request $request, string $id)
    {
        $query = Product::with('productCategory', 'variationOptions')->where('store_id', $id);

        if ($request->has('product_category_id')) {
            $query->where('product_category_id', $request->product_category_id);
        }

        $products = $query->get();

        return $this->success(ProductResource::collection($products));
    }

    public function getStoreCategories(Request $request, string $id)
    {
        $categories = Product::where('store_id', $id)
            ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->select('product_categories.id', 'product_categories.name', 'product_categories.image')
            ->distinct()
            ->get();

        return $this->success($categories);
    }

    public function addStoreLocation(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:255',
        ]);

        $store = Store::findOrFail($request->store_id);

        $store->location()->create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'city' => $request->city,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
        ]);

        return $this->success(message: 'تمت إضافة الموقع بنجاح');
    }

    public function getUserStoreOrders(Request $request)
    {
        $user = Auth::user();

        $order_count = $user->store->orders()->count();
        $canceled_count = $user->store->orders()->where('status', 'canceled')->count();

        if (!$user->store) {
            return $this->error('ليس لديك متجر', 404);
        }

        $query = $user->store->orders()->with('products');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to])->orderBy('created_at', 'desc');
        }

        $orders = $query->get()->sortByDesc('created_at');
        $ordersCount = $orders->count();
        $totalRevenue = $orders->where('status', 'completed')->sum('total');
        $sales = $orders->where('status', 'completed')->sum('quantity');
        $canceledOrders = $orders->where('status', 'canceled')->count();

        return $this->success([
            'order_count' => $order_count,
            'canceled_count' => $canceled_count,
            'total_revenue' => $totalRevenue,
            'sales' => $sales,
            'orders' => OrderResource::collection($orders)
        ]);
    }
}
