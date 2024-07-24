<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $comment = Comment::create([
            'unique_id' => $request->unique_id,
            'user_id' => $request->user_id,
            'comment' => $request->comment
        ]);

        return response()->json($comment, 201);
    }

    public function reply(Request $request, Comment $comment)
    {
        $comment->update(['replied' => true]);
        return response()->json($comment, 200);
    }
}

