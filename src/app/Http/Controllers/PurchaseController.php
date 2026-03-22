<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use App\Models\Purchase;
use App\Models\Item;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class PurchaseController extends Controller
{
    public function purchase(PurchaseRequest $request, $itemId)
    {
        $validated = $request->validated();
        $item = Item::findOrFail($itemId);

        if ($item->is_sold) {
            return back()->with('error', 'すでに売り切れています');
        }

        // Stripe処理
        $stripe = new StripeClient(config('services.stripe.secret'));

        $checkout = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card', 'konbini'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => $item->price,
                    'product_data' => [
                        'name' => $item->name,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success', ['item_id' => $itemId]),
            'cancel_url' => route('purchase.cancel', ['item_id' => $itemId]),
            'metadata' => [
                'item_id' => $itemId,
                'buyer_id' => auth()->id(),
            ],
        ]);

        return redirect($checkout->url);
    }

    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        $shippingAddress = ShippingAddress::where('user_id', $user->id)
            ->where('item_id', $item_id)
            ->latest()
            ->first();

        $address = $shippingAddress ?: (object)[
            'postal_code' => $user->postal_code,
            'address'     => $user->address,
            'building'    => $user->building,
        ];
        return view('purchase', compact('item', 'address'));
    }

    public function editAddress($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();
                $address = ShippingAddress::where('user_id', $user->id)
            ->where('item_id', $item_id)
            ->first() ?: $user;


        return view('address', compact('item', 'user'));
    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        $data = $request->validated();
        ShippingAddress::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'item_id'     => $item_id,
                'postal_code' => $data['postal_code'],
                'address'     => $data['address'],
                'building'    => $request->building,
            ]
        );
        return redirect()->route('purchase.show', ['item_id' => $item_id])->with('message', '配送先を変更しました');
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $itemId = $session->metadata->item_id;
            $buyerId = $session->metadata->buyer_id;
            $item = Item::find($itemId);

            Purchase::create([
                'item_id'     => $itemId,
                'buyer_id'    => $buyerId,
                'seller_id'   => $item->user_id,
                'total_price' => $session->amount_total,
            ]);

            $item->update(['is_sold' => true]);
        }

        return response()->json(['status' => 'success']);
    }

    public function success($item_id)
    {
        $item = Item::findOrFail($item_id);
        $alreadyPurchased = Purchase::where('item_id', $item->id)->exists();

        if (!$alreadyPurchased) {
            Purchase::create([
                'item_id'     => $item->id,
                'buyer_id'    => auth()->id(),
                'seller_id'   => $item->user_id,
                'total_price' => $item->price,
            ]);
            $item->update(['is_sold' => true]);
        }

        return redirect()->route('index')->with('message', 'ご購入ありがとうございました！');
    }

    public function cancel($item_id)
    {
        return redirect()->route('purchase.show', ['item_id' => $item_id])->with('error', '決済がキャンセルされました');
    }
}