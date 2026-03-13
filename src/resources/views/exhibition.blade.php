@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/exhibition.css') }}">
@endsection

@section('content')
<div class="exhibition__content">
  <div class="exhibition__heading">
    <h1>商品の出品</h1>
  </div>

  <form class="form" action="/sell" method="post" enctype="multipart/form-data">
    @csrf

    <!-- 商品画像 -->
    <div class="form__group">
      <p class="form__label--item">商品画像</p>
      <div class="form__img-upload">
        <div id="preview" class="form__img-preview"></div>
        <label class="form__img-label">画像を選択する
          <input type="file" name="image" id="image-input" class="form__input-file" accept="image/*">
        </label>
      </div>
      @error('image')
        <p class="error-message">{{ $message }}</p>
      @enderror
    </div>

    <!-- 商品の詳細 -->
    <div class="form__section">
      <h2 class="form__section-title">商品の詳細</h2>

      <!-- カテゴリ -->
        <div class="form__group">
          <p class="form__label--item">カテゴリー</p>
          <div class="category__tags">
          @foreach($categories as $category)
            <label class="category__tag">
              <input type="checkbox" name="categories[]" value="{{ $category->id }}" {{ is_array(old('categories')) && in_array($category->id, old('categories')) ? 'checked' : '' }}>
              <span>{{ $category->content }}</span>
            </label>
          @endforeach
          </div>
          @error('categories')
            <p class="error-message">{{ $message }}</p>
          @enderror
        </div>

      <!-- 商品の状態 -->
      <div class="form__group">
        <p class="form__label--item">商品の状態</p>
        <div class="form__input--select">
          <select name="condition" class="@error('condition') is-invalid @enderror">
            <option value="" disabled {{ old('condition') ? '' : 'selected' }}>選択してください</option>
            @foreach($conditions as $condition)
            <option value="{{ $condition->id }}" {{ old('condition') == $condition->id ? 'selected' : '' }}>{{ $condition->content }}</option>
            @endforeach
          </select>
        </div>
        @error('condition')
          <p class="error-message">{{ $message }}</p>
        @enderror
      </div>
    </div>

    <!-- 商品名と説明 -->
    <div class="form__section">
      <h2 class="form__section-title">商品名と説明</h2>
        <div class="form__group">
          <p class="form__label--item">商品名</p>
          <input type="text" name="name" class="form__input-text @error('name') is-invalid @enderror" value="{{ old('name') }}">
          @error('name')
            <p class="error-message">{{ $message }}</p>
          @enderror
        </div>

        <div class="form__group">
          <p class="form__label--item">ブランド名</p>
          <input type="text" name="brand" class="form__input-text" value="{{ old('brand') }}">
        </div>

        <div class="form__group">
          <p class="form__label--item">商品の説明</p>
          <textarea name="description" rows="5" class="form__textarea @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
          @error('description')
            <p class="error-message">{{ $message }}</p>
          @enderror
        </div>

        <div class="form__group">
          <p class="form__label--item">販売価格</p>
          <div class="price-input">
            <span class="price-symbol">¥</span>
            <input type="number" name="price" class="form__input-text @error('price') is-invalid @enderror"  value="{{ old('price') }}">
          </div>
          @error('price')
            <p class="error-message">{{ $message }}</p>
          @enderror
        </div>
    </div>

    <div class="form__button">
      <button class="form__button-submit" type="submit">出品する</button>
    </div>
  </form>
</div>

<script>
document.getElementById('image-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('preview');

    preview.innerHTML = '';

    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();

        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '200px';
            img.style.display = 'block';
            preview.appendChild(img);
        }

        reader.readAsDataURL(file);
    }
});
</script>

@endsection
