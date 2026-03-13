@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/edit.css') }}">
@endsection

@section('content')
<div class="profile-edit__content">
  <div class="profile-edit__heading">
    <h1>プロフィール設定</h1>
  </div>

  <form class="form" action="/mypage/profile" method="post" enctype="multipart/form-data">
  @csrf

    <!-- プロフィール画像 -->
    <div class="form__group-img">
      <div class="form__img-wrapper">
        <img src="{{ asset('storage/' . $user->profile_image)  }}" alt="ユーザー画像" class="form__img">
      </div>
      <label class="form__label-img">画像を選択する
        <input type="file" name="image" class="form__input-file">
        @error('image') <p class="error-message">{{ $message }}</p> @enderror
      </label>
    </div>

    <!-- ユーザー名 -->
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">ユーザー名</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" />
          @error('name') <p class="error-message">{{ $message }}</p> @enderror
        </div>
      </div>
    </div>

    <!-- 郵便番号 -->
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">郵便番号</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code ?? '') }}" />
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
          <input type="text" name="address" value="{{ old('address', $user->address ?? '') }}" />
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
          <input type="text" name="building" value="{{ old('building', $user->building ?? '') }}" />
        </div>
      </div>
    </div>

    <div class="form__button">
      <button class="form__button-submit" type="submit">更新する</button>
    </div>
  </form>
</div>

<script>
document.querySelector('.form__input-file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const reader = new FileReader();
    const preview = document.querySelector('.form__img');

    if (!file) return;

    reader.onload = function(e) {
        preview.src = e.target.result;
    }

    reader.readAsDataURL(file);
});
</script>

@endsection
