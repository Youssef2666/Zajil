<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentProductResource;
use App\Models\CommentProduct;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->join('users', 'comment_store.user_id', '=', 'users.id')
            ->where('comment_store.store_id', $storeId)
            ->whereNull('comment_store.parent_id')
            ->orderBy('comment_store.created_at', 'desc')
            ->select(
                'comment_store.id',
                'comment_store.comment',
                'comment_store.created_at',
                'users.id as user_id',
                'users.name as user_name',
                'users.profile_photo_path'
            )
            ->get();

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

        $comments = \App\Models\CommentProduct::with('user')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(CommentProductResource::collection($comments));
    }

}
