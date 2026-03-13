<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>coachtech</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header-inner">
            <a class="header-logo" href="/"> <img src="{{ asset('img/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH" class="logo-img"></a>
        </div>
        <!-- 検索欄 -->
        @unless(Route::is('login', 'register', 'email'))
        <div class="header-search">
            <form action="{{ route('item.search') }}" method="GET">
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
                <input type="text" name="keyword" placeholder="なにをお探しですか？" value="{{ request('keyword') }}">
            </form>
        </div>

        <!-- ナビゲーション -->
        <nav class="header-nav">
            <ul class="nav-list">
            @if(Auth::check())
                <li>
                    <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="nav-link-btn">ログアウト</button>
                    </form>
                </li>
                @else
                <li><a href="/login" class="nav-link">ログイン</a></li>
                @endif
                <li><a href="{{ Auth::check() ? '/mypage' : '/login' }}" class="nav-link">マイページ</a></li>
                <li><a href="{{ Auth::check() ? '/sell' : '/login' }}" class="sell-button">出品</a></li>
            </ul>
        </nav>
        @endunless
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>