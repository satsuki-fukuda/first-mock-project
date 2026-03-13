@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail__content">
  <div class="detail__flex">
    <!-- 左側：商品画像 -->
    <div class="detail__img-wrapper">
    @if($item->is_sold)
      <div class="sold-label">SOLD</div>
    @endif
      <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="detail__img">
    </div>

    <!-- 右側：商品情報 -->
    <div class="detail__info">
      <div class="detail__header">
        <h1 class="detail__name">{{ $item->name }}</h1>
          <p class="detail__brand">{{ $item->brand }}</p>
          <p class="detail__price">¥{{ number_format($item->price) }}<span>(税込)</span></p>

          <div class="detail__actions">
            <div class="action-item">
              <form action="{{ route('like', ['item_id' => $item->id]) }}" method="POST" id="like-form">
              @csrf
                <button type="submit" class="btn-like">
                @if(Auth::check() && $item->isLikedBy(Auth::user()))
                  <img src="{{ asset('img/ハートロゴ_ピンク.png') }}" alt="お気に入り解除" class="icon-img">
                @else
                  <img src="{{ asset('img/ハートロゴ_デフォルト.png') }}" alt="お気に入り登録" class="icon-img">
                @endif
                </button>
              </form>
              <p>{{ $item->likes->count() }}</p>
            </div>

            <div class="action-item">
              <img src="{{ asset('img/ふきだしロゴ.png') }}" alt="コメント" class="icon-img">
              <p>{{ $item->comments->count() }}</p>
            </div>
          </div>
      </div>

      <div class="detail__purchase">
        <a href="/purchase/{{ $item->id }}" class="btn-purchase">購入手続きへ</a>
      </div>

      <div class="detail__description">
        <h2>商品説明</h2>
          <p>{!! nl2br(e($item->description)) !!}</p>
      </div>

      <div class="detail__meta">
        <h2>商品の情報</h2>
          <div class="meta-item">
            <span class="meta-label">カテゴリー</span>
            <div class="meta-tags">
            @foreach($item->categories as $category)
              <span class="tag">{{ $category->content }}</span>
            @endforeach
            </div>
          </div>

          <div class="meta-item">
            <span class="meta-label">商品の状態</span>
            <span class="meta-status">{{ $item->condition->content ?? '不明' }}</span>
          </div>
      </div>

      <!-- コメント -->
      <div class="detail__comments">
        <h2>コメント({{ $item->comments->count() }})</h2>
        @foreach($item->comments as $comment)
          <div class="comment-item">
            <div class="comment-user">
              <div class="user-icon" style="background-image: url('{{ $comment->user->profile_image ? asset('storage/' . $comment->user->profile_image) : asset('img/default-user.png') }}')"></div>
              <p class="user-name">{{ $comment->user->name }}</p>
            </div>
            <div class="comment-body">
              <p>{{ $comment->content }}</p>
            </div>
          </div>
        @endforeach
      </div>

      <div class="detail__comment-form">
        <h2>商品へのコメント</h2>
        @error('comment')
          <p class="error-message">{{ $message }}</p>
        @enderror

          <form action="{{ route('comment', ['item_id' => $item->id]) }}" method="post">
          @csrf
            <textarea name="comment" rows="6">{{ old('comment') }}</textarea>
            <button type="submit" class="btn-comment">コメントを送信する</button>
          </form>
      </div>
    </div>
  </div>
</div>

@endsection
