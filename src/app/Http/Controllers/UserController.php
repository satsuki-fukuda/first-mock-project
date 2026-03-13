<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ProfileRequest;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        if (empty($user->postal_code) || empty($user->address)) {
        return redirect()->route('profile.edit')->with('message', '最初にプロフィールを設定してください');
        }

        $tab = $request->query('tab', 'sell');

        if ($tab === 'buy') {
            $items = \App\Models\Item::whereIn('id', $user->purchases()->pluck('item_id'))->get();
        } else {
            $items = $user->items()->get();
        }
        return view('index', compact('user', 'items'));
    }

    public function edit()
    {
        return view('edit', ['user' => \Auth::user()]);
    }
    public function update(ProfileRequest $request)
    {
        $data = $request->validated();
        $user = \Auth::user();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $user->profile_image = $path;
        }

        $user->name = $data['name'];
        $user->postal_code = $data['postal_code'];
        $user->address = $data['address'];
        $user->building = $request->building;

        $user->save();

        return redirect()->route('index')->with('message', '更新しました');
    }

}


