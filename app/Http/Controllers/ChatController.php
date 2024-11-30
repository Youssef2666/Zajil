<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $chats = Auth::user()->chats;
        return $this->success($chats);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $authUser = $request->user();

        $store = Store::find($data['store_id']);

        if ($authUser->id !== $store->user_id && !$data['user_id']) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chat = Chat::firstOrCreate([
            'store_id' => $data['store_id'],
        ]);

        $participants = [$store->user_id]; 
        if ($data['user_id']) {
            $participants[] = $data['user_id'];
        } else {
            $participants[] = $authUser->id;
        }

        $chat->participants()->syncWithoutDetaching($participants);

        return response()->json($chat, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
