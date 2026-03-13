<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
     public function store(CommentRequest $request, $item_id)
    {
        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $item_id,
            'content' => $request->comment,
        ]);

        return back()->with('message', 'コメントを投稿しました');
    }
}
