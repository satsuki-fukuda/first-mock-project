<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Support\Facades\Auth;


class ItemController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab');
        $keyword = $request->query('keyword');

        if ($tab === 'mylist') {
            $query = Auth::check()
            ? Auth::user()->likedItems()
            : Item::whereRaw('1 = 0');
        } else {
            $query = Item::query();
            if (Auth::check()) {
                $query->where('user_id', '!=', Auth::id());
            }
        }

        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }
        $items = $query->get();
        return view('index', compact('items'));
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    public function mail()
    {
        return view('mail');
    }

    public function edit()
    {
        return view('mypage.profile_edit');
    }

    public function exhibition()
    {
        $categories = Category::all();
        $conditions = Condition::all();

        return view('exhibition', compact('categories', 'conditions'));
    }

    public function profile(Request $request)
    {
        $user = \Auth::user();
        $tab = $request->query('tab');

        if ($tab === 'buy') {
            $items = \App\Models\Purchase::where('buyer_id', $user->id)
                ->with('item')
                ->get()
                ->pluck('item');
        } else {
            $items = Item::where('user_id', $user->id)->get();
        }
        return view('profile', compact('user', 'items'));
    }

    public function address($item_id)
    {
        return view('address');
    }

    public function detail($item_id)
    {
        $item = Item::findOrFail($item_id);
        return view('detail', compact('item'));
    }

    public function purchase($item_id)
    {
        return view('purchase.index', ['item_id' => $item_id]);
    }
    public function store(ExhibitionRequest $request)
    {
        $validated = $request->validated();
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
        }

        $item = Item::create([
            'user_id'      => \Auth::id(),
            'condition_id' => $validated['condition'],
            'name'         => $validated['name'],
            'brand'        => $request->brand ?? null,
            'description'  => $validated['description'],
            'price'        => $validated['price'],
            'image'        => $imagePath,
        ]);

        if ($request->has('categories')) {
            $item->categories()->sync($request->categories);
        }

        return redirect('/')->with('message', '出品が完了しました');
    }
}
