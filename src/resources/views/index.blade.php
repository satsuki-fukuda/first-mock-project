@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="item-list__content">
  <div class="item-list__tabs">

    <!-- おすすめ -->
    <a href="/?keyword={{ request('keyword') }}" class="tab-item {{ request('tab') !== 'mylist' ? 'active' : '' }}">おすすめ</a>

    <!-- マイリスト -->
    <a href="/?tab=mylist&keyword={{ request('keyword') }}" class="tab-item {{ request('tab') === 'mylist' ? 'active' : '' }}">マイリスト</a>
  </div>

  <!-- 商品 -->
  <div class="item-grid">
    @foreach($items as $item)
    <div class="item-card">
      <a href="/item/{{ $item->id }}">
        <div class="item-card__img-wrapper">
        @if($item->image)
          <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
        @else
          <div class="item-card__no-img">商品画像</div>
        @endif

        <!-- SOLD -->
        @if($item->is_sold)
          <span class="sold-label">SOLD</span>
        @endif
        </div>
        <p class="item-card__name">{{ $item->name }}</p>
      </a>
    </div>
    @endforeach
  </div>
</div>
@endsection