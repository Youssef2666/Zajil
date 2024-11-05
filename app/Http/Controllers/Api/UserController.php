<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

}
