<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function addComment(Request $request, $storeId)
    {
        $request->validate([
            'comment' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:comment_store,id',
        ]);

        $commentId = DB::table('comment_store')->insertGetId([
            'user_id' => Auth::id(),
            'store_id' => $storeId,
            'comment' => $request->input('comment'),
            'parent_id' => $request->input('parent_id'), 
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment_id' => $commentId,
        ], 201);
    }
}
