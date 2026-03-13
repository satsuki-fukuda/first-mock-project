@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="mypage__content">
  <div class="mypage__user-info">
    <div class="user-info__image">
    @if($user->profile_image)
      <img src="{{ asset('storage/' . $user->profile_image) }}" alt="プロフィール画像" class="user-info__img">
    @else
      <div class="user-info__img-placeholder"></div>
    @endif
    </div>
    <div class="user-info__name">
      <h1>{{ $user->name ?? 'ユーザー名' }}</h1>
    </div>
    <div class="user-info__edit">
      <a href="/mypage/profile" class="btn-edit">プロフィールを編集</a>
    </div>
  </div>

  <!-- タブ切り替え -->
  <div class="mypage__tabs">
    <a href="/mypage?tab=sell" class="tab-item {{ request()->get('tab') != 'buy' ? 'active' : '' }}">出品した商品</a>
    <a href="/mypage?tab=buy" class="tab-item {{ request()->get('tab') == 'buy' ? 'active' : '' }}">購入した商品</a>
  </div>

  <!-- 商品一覧 -->
  <div class="item-grid">
    @foreach($items as $item)
    <div class="item-card">
      <a href="/item/{{ $item->id }}">
        <div class="item-card__img-wrapper">
          <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="item-card__img">
        </div>
        <p class="item-card__name">{{ $item->name ?? '商品名' }}</p>
      </a>
    </div>
    @endforeach
  </div>
</div>
@endsection