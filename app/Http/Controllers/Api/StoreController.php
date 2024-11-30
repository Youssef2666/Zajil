<?php

namespace App\Http\Controllers\Api;

<<<<<<< HEAD
=======
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Models\Product;
>>>>>>> e61fbfd5853581671ef8ec7081cd8c55a236ca3c
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
use App\Http\Resources\OrderResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\ProductResource;
=======
use Illuminate\Support\Facades\DB;
>>>>>>> e61fbfd5853581671ef8ec7081cd8c55a236ca3c

class StoreController extends Controller
{

    use ResponseTrait;
    public function index(Request $request)
    {
        $stores = Store::query()
            ->leftJoin('rate_store', 'stores.id', '=', 'rate_store.store_id')
            ->select('stores.*', DB::raw('AVG(rate_store.rating) as average_rating'))
            ->groupBy('stores.id')

            ->with('products', 'location');

        if ($request->has('search')) {
            $stores->where('stores.name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('min_rating')) {
            $stores->having('average_rating', '>=', $request->min_rating);
        }

        if ($request->has('max_rating')) {
            $stores->having('average_rating', '<=', $request->max_rating);
        }

        if ($request->has('most_rated')) {
            $stores->orderByDesc('average_rating');
        }

        $stores = $stores->get();

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

        if ($request->has('most_rated') && $request->most_rated) {
            $query->withCount('ratings')
                  ->orderBy('ratings_count', 'desc');
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

        if ($request->has('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%');
            });
        }

        if ($request->has('user_email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->user_email . '%');
            });
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->get()->sortByDesc('created_at');
        $ordersCount = $orders->count();
        $totalRevenue = $orders->where('status', 'delivered')->sum('total');
        $sales = $orders
            ->where('status', 'delivered')
            ->flatMap(function ($order) {
                return $order->products->pluck('pivot.quantity');
            })
            ->sum();
        $canceledOrders = $orders->where('status', 'canceled')->count();

<<<<<<< HEAD
=======
        // Return the order data
>>>>>>> e61fbfd5853581671ef8ec7081cd8c55a236ca3c
        return $this->success([
            'order_count' => $ordersCount,
            'canceled_count' => $canceledOrders,
            'total_revenue' => $totalRevenue,
            'sales' => $sales,
            'orders' => OrderResource::collection($orders),
        ]);
    }
<<<<<<< HEAD
    //state products
=======

>>>>>>> e61fbfd5853581671ef8ec7081cd8c55a236ca3c
}
