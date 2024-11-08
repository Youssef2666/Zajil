<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ResponseTrait;
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo_path' => 'nullable|image|mimes:jpg,jpeg,png|max:10048', // Validate the image file
        ]);

        $user = Auth::user();

        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($request->password);
        }

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

    public function whoAmI(Request $request)
    {
        $userQuery = User::query()->where('id', Auth::id());

        if ($request->has('with') && in_array('locations', explode(',', $request->input('with')))) {
            $userQuery->with('locations');
        }

        
        $user = $userQuery->first();

        if ($user && $user->profile_photo_path) {
            $user->profile_photo_path = asset('storage/' . $user->profile_photo_path);
        } else {
            $user->profile_photo_path = null;
        }

        return $this->success($user);
    }

    public function addLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:255',
        ]);
        $user = Auth::user();
        $is_default = true;
        if ($user->locations()->where('is_default', true)->exists()) {
            $is_default = false;
        }
        $user->locations()->create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'city' => $request->city,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'is_default' => $is_default,
            'user_id' => $user->id,
        ]);
        return $this->success(message: 'تمت العملية بنجاح');
    }

    public function storePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|unique:user_phones,phone',
            'is_default' => 'sometimes|boolean',
            'is_verified' => 'sometimes|boolean',
        ]);

        $user = Auth::user();

        if ($request->is_default) {
            $user->phones()->where('is_default', true)->update(['is_default' => false]);
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

    public function updateDefaultLocation(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        $user = Auth::user();
        $newDefaultLocationId = $request->location_id;

        $location = $user->locations()->where('id', $newDefaultLocationId)->first();
        if (!$location) {
            return $this->success(
                message: 'هذه الموقع غير موجودة',
                status: 403
            );
        }

        DB::transaction(function () use ($user, $newDefaultLocationId) {
            $user->locations()->update(['is_default' => false]);

            $user->locations()->where('id', $newDefaultLocationId)->update(['is_default' => true]);
        });

        return response()->json([
            'message' => 'تم تحديد الموقع الافتراضي بنجاح',
            'default_location_id' => $newDefaultLocationId,
        ]);
    }

    public function updateDefaultPhone(Request $request)
    {
        $request->validate([
            'phone_id' => 'required|exists:user_phones,id',
        ]);

        $user = Auth::user();
        $newDefaultPhoneId = $request->phone_id;

        $phone = $user->phones()->where('id', $newDefaultPhoneId)->first();
        if (!$location) {
            return $this->success(
                message: 'هذا الجوال غير موجود',
                status: 403
            );
        }

        DB::transaction(function () use ($user, $newDefaultPhoneId) {
            $user->phones()->update(['is_default' => false]);

            $user->phones()->where('id', $newDefaultPhoneId)->update(['is_default' => true]);
        });

        return response()->json([
            'message' => 'تم تحديد رقم جوال الافتراضي بنجاح',
            'default_phone_id' => $newDefaultPhoneId,
        ]);
    }

}
