<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function store($item_id)
    {
        $user_id = Auth::id();

        $already_liked = Like::where('user_id', $user_id)
            ->where('item_id', $item_id)
            ->first();

        if (!$already_liked) {
            Like::create([
                'user_id' => $user_id,
                'item_id' => $item_id,
            ]);
        } else {
            $already_liked->delete();
        }

        return back();
    }
}
