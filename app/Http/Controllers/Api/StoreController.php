<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\ProductResource;

class StoreController extends Controller
{

    use ResponseTrait;
    public function index(Request $request)
    {
        $stores = Store::query()
            ->leftJoin('rate_store', 'stores.id', '=', 'rate_store.store_id')
            ->select([
                'stores.id',
                'stores.name',
                'stores.user_id',
                'stores.image',
                'stores.description',
                'stores.created_at',
                'stores.updated_at',
                DB::raw('AVG(rate_store.rating) as average_rating'),
            ])
            ->groupBy([
                'stores.id',
                'stores.name',
                'stores.user_id',
                'stores.image',
                'stores.description',
                'stores.created_at',
                'stores.updated_at',
            ])
            ->with(['location', 'products.productCategory' => function ($query) {
                $query->distinct();
            }]);

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

        $perPage = $request->get('per_page', 10);
        $stores = $stores->paginate($perPage);

        return StoreResource::collection($stores)->response();
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

        $perPage = $request->get('per_page', 10);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products)->response();
    }

    public function getStoreCategories(Request $request, string $id)
    {
        $query = Product::where('store_id', $id)
            ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->select('product_categories.id', 'product_categories.name', 'product_categories.image')
            ->distinct();

        $perPage = $request->get('per_page', 10); // Default to 10 items per page
        $categories = $query->paginate($perPage);

        return response()->json([
            'data' => $categories->items(),
            'links' => [
                'first' => $categories->url(1),
                'last' => $categories->url($categories->lastPage()),
                'prev' => $categories->previousPageUrl(),
                'next' => $categories->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
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

        $query = $user->store->orders()->with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_filter')) {
            $now = now();
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [
                        $now->clone()->startOfWeek(Carbon::SATURDAY),
                        $now->clone()->endOfWeek(Carbon::FRIDAY)
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $now->year);
                    break;
            }
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

        if ($request->has('min_total')) {
            $query->where('total', '>=', $request->min_total);
        }
        if ($request->has('max_total')) {
            $query->where('total', '<=', $request->max_total);
        }

        if ($request->has('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
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

        return $this->success([
            'order_count' => $ordersCount,
            'canceled_count' => $canceledOrders,
            'total_revenue' => $totalRevenue,
            'sales' => $sales,
            'orders' => OrderResource::collection($orders),
        ]);
    }

}
