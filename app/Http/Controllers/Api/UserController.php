<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ResponseTrait;
    public function update(Request $request)
    {
        // return $request->profile_photo_path;
        // Validate the input fields, including the image
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo_path' => 'nullable|image|mimes:jpg,jpeg,png|max:10048', // Validate the image file
        ]);

        $user = Auth::user();

        // If a password is provided, hash it before saving
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($request->password);
        }

        // If an image file is uploaded, store it and save the path to the user
        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos-new', 'public');
            // return $path;
            $validatedData['profile_photo_path'] = $path;
            $user->profile_photo_path = $path;
            $user->save();
        }

        $user->update($validatedData);

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function getFavouriteProducts()
    {
        $user = Auth::user();

        $favouriteProducts = $user->favouriteProducts()->get();

        return response()->json([
            'message' => 'Favourite products retrieved successfully.',
            'favourite_products' => $favouriteProducts,
        ]);
    }
    public function getFavouriteStores()
    {
        $user = Auth::user();

        $favouriteStores = $user->favouriteStores()->get();

        return response()->json([
            'message' => 'تم جلب المتاجر المفضلة',
            'favourite_products' => $favouriteStores,
        ]);
    }

    public function addLocation(Request $request)
    {
        $user = Auth::user();
        $user->locations()->create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'city' => $request->city,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'is_default' => $request->is_default,
            'user_id' => $user->id,
        ]);
        return $this->success(message: 'تمت العملية بنجاح');
    }

    public function whoAmI(Request $request)
    {
        $userQuery = User::query()->where('id', Auth::id());

        if ($request->has('with') && in_array('locations', explode(',', $request->input('with')))) {
            $userQuery->with('locations');
        }

        $user = $userQuery->first();

        if ($user->image) {
            $user->image = asset('storage/' . $user->image);
        }

        return $this->success($user);
    }

    public function storePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|unique:user_phone,phone',
            'is_default' => 'sometimes|boolean',
            'is_verified' => 'sometimes|boolean',
        ]);

        $user = Auth::user();

        if ($request->is_default) {
            $user->phones()->update(['is_default' => false]);
        }

        $phone = $user->phones()->create([
            'phone' => $request->phone,
            'is_default' => $request->input('is_default', false),
            'is_verified' => $request->input('is_verified', false),
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'تمت اضافة رقم جوال بنجاح',
            'phone' => $phone,
        ], 201);
    }

}
