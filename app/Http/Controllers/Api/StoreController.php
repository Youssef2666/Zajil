<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        if ($request->has('with') && in_array('products', explode(',', $request->input('with')))) {
            $stores->with('products');
        }

        $stores = $stores->get();

        return $this->success($stores);
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
