@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
<div class="purchase__content">
  <div class="purchase__container">
    <!-- 左側：詳細入力エリア -->
    <div class="purchase__main">
      <!-- 商品概要 -->
      <div class="item-summary">
        <div class="item-summary__img-wrapper">
          <img src="{{ asset('storage/' . $item->image) }}" alt="商品画像" class="summary-img">
          @if($item->is_sold)
          <div class="sold-label" >
            SOLD OUT
          </div>
          @endif
        </div>
        <div class="item-summary__info">
          <h1>{{ $item->name }}</h1>
          <p>¥ {{ number_format($item->price) }}</p>
        </div>
      </div>

      <!-- 支払い方法選択 -->
      <div class="payment-selection">
        <h2>支払い方法</h2>
          <div class="select-wrapper" >
            <select name="payment_method" id="payment-select">
              <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>選択してください</option>
              <option value="konbini" {{ old('payment_method') == 'konbini' ? 'selected' : '' }}>コンビニ払い</option>
              <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>クレジットカード</option>
            </select>
          </div>
          @error('payment_method')
          <div class="error-message">{{ $message }}</div>
          @enderror
      </div>

      <!-- 配送先 -->
      <div class="shipping-address">
        <div class="shipping-address__header">
          <h2>配送先</h2>
          <a href="/purchase/address/{{ $item->id }}" class="btn-change">変更する</a>
        </div>
        @error('address')
        <div class="error-message">{{ $message }}</div>
        @enderror
        <div class="shipping-address__detail">
          <p>〒 {{ $address->postal_code }}</p>
          <p>{{ $address->address }}{{ $address->building }}</p>
        </div>
      </div>
    </div>

    <!-- 右側：確認・購入エリア -->
    <aside class="purchase__side">
      <div class="order-card">
        <table class="order-table">
          <tr>
            <th>商品代金</th>
            <td>¥ {{ number_format($item->price) }}</td>
          </tr>
          <tr>
            <th>支払い方法</th>
            <td id="display-payment">
                @if(old('payment_method') == 'konbini') コンビニ払い
                @elseif(old('payment_method') == 'card') クレジットカード
                @else 選択してください
                @endif
            </td>
          </tr>
        </table>
      </div>

      <div class="purchase__button">
        <form action="/purchase/{{ $item->id }}" method="post">
        @csrf
          <input type="hidden" name="payment_method" id="hidden-payment">
          <button type="submit" class="btn-purchase" {{ $item->is_sold ? 'disabled' : '' }}>{{ $item->is_sold ? '売り切れました' : '購入する' }}
          </button>
        </form>
      </div>
    </aside>
  </div>
</div>

<script>
document.querySelector('select[name="payment_method"]').addEventListener('change', function() {
    const selectedText = this.options[this.selectedIndex].text;
    const selectedValue = this.value;

    document.getElementById('display-payment').textContent = selectedText;
    document.getElementById('hidden-payment').value = selectedValue;
});
</script>

@endsection
