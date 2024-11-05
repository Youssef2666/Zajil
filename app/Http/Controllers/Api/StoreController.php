<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\StoreResource;

class StoreController extends Controller
{

    use ResponseTrait;
    public function index(Request $request)
    {
        $stores = Store::query();

        if ($request->has('with') && in_array('products', explode(',', $request->input('with')))) {
            $stores->with('products');
        }

        if ($request->has('search')) {
            $stores->where('name', 'like', '%' . $request->search . '%');
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
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'user_id' => Auth::id(),
        ]);
        return $this->success($store, status: 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $store = Store::with('products')->findOrFail($id);
        return $this->success($store);
    }

    /**
     * Update the specified resource in storage.
     */
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
}
