<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\CommentProduct;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    use ResponseTrait;
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
        $comments = DB::table('comment_store')
            ->where('store_id', $storeId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
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

    public function addCommentToProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'comment' => 'required|string|max:255',
        ]);
        $userId = Auth::id();

        $comment = CommentProduct::create([
            'user_id' => $userId,
            'product_id' => $request->product_id,
            'comment' => $request->comment,
        ]);

        return $this->success(message: 'تم إضافة التعليق بنجاح', status: 201);
    }

    public function getProductComments($productId)
    {
        $product = Product::findOrFail($productId);

        $comments = \App\Models\CommentProduct::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($comments);
    }

}
