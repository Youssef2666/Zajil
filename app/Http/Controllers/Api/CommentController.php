<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function addComment(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'comment' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:comment_store,id',
        ]);

        $commentId = DB::table('comment_store')->insertGetId([
            'user_id' => Auth::id(),
            'store_id' => $request->input('store_id'),
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

    public function getStoreComments($storeId)
    {
        // Retrieve the main comments for the store and their replies
        $comments = DB::table('comment_store')
            ->where('store_id', $storeId)
            ->whereNull('parent_id') // Only get top-level comments
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                // Retrieve replies for each top-level comment
                $comment->replies = DB::table('comment_store')
                    ->where('parent_id', $comment->id)
                    ->orderBy('created_at', 'asc')
                    ->get();

                return $comment;
            });

        return response()->json([
            'store_id' => $storeId,
            'comments' => $comments,
        ]);
    }

}
