@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')
<div class="address-change__content">
  <div class="address-change__heading">
    <h1>住所の変更</h1>
  </div>

  <form class="form" action="/purchase/address/{{ $item->id }}" method="post">
    @csrf
    <!-- 郵便番号 -->
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">郵便番号</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" />
          @error('postal_code') <p class="error-message">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    <!-- 住所 -->
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">住所</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="address" value="{{ old('address', $user->address) }}" />
          @error('address') <p class="error-message">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    <!-- 建物名 -->
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">建物名</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="building" value="{{ old('building, $user->building') }}" />
        </div>
      </div>
    </div>

    <div class="form__button">
      <button class="form__button-submit" type="submit">更新する</button>
    </div>
  </form>
</div>
@endsection