@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
    .db { max-width: 1120px; margin: 0 auto; }
    .db-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.75rem; }
    .db-head h1 { font-size: clamp(1.8rem, 4vw, 2.6rem); font-weight: 600; letter-spacing: -.035em; margin: .75rem 0 .2rem; }
    .db-date { margin: 0; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: .8rem; color: var(--bs-secondary-color); }

    /* nada per status */
    .tone-muted  { --tc: var(--bs-secondary-color); --tb: rgba(127, 127, 127, .14); }
    .tone-warn   { --tc: var(--brand-text); --tb: var(--brand-subtle); }
    .tone-danger { --tc: var(--bs-danger); --tb: rgba(var(--bs-danger-rgb), .12); }
    .tone-ok     { --tc: #1F9D55; --tb: rgba(31, 157, 85, .13); }

    .db-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; }
    .db-tile { display: flex; flex-direction: column; gap: .15rem; padding: 1.35rem 1.4rem; border-radius: var(--bs-card-border-radius); background: var(--bs-card-bg); border: 1px solid var(--bs-border-color); box-shadow: var(--bs-card-box-shadow); color: var(--bs-body-color); text-decoration: none; transition: transform .2s ease; }
    .db-tile:hover { transform: translateY(-3px); color: var(--bs-body-color); }
    .db-tile .top { display: flex; align-items: center; gap: .6rem; margin-bottom: .6rem; font-size: .88rem; color: var(--bs-secondary-color); }
    .db-tile .ic { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 11px; background: var(--tb); color: var(--tc); }
    .db-tile b { font-size: 2rem; line-height: 1.1; font-weight: 600; letter-spacing: -.03em; color: var(--tc); }
    .db-tile small { color: var(--bs-secondary-color); font-size: .84rem; }
    .db-tile.tone-muted b { color: var(--bs-body-color); }

    .db-panels { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-top: 1rem; }
    .db-card { padding: 1.5rem 1.5rem 1rem; border-radius: var(--bs-card-border-radius); background: var(--bs-card-bg); border: 1px solid var(--bs-border-color); box-shadow: var(--bs-card-box-shadow); }
    .db-card header { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-bottom: .6rem; }
    .db-card h2 { font-size: 1.1rem; font-weight: 600; margin: 0; letter-spacing: -.02em; }
    .db-card header a { font-size: .84rem; color: var(--brand-text); text-decoration: none; white-space: nowrap; }
    .db-card header a:hover { text-decoration: underline; }
    .db-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .8rem 0; border-top: 1px solid var(--bs-border-color); color: var(--bs-body-color); text-decoration: none; }
    .db-row:hover { color: var(--bs-body-color); }
    .db-row:hover .t { text-decoration: underline; }
    .db-row .t { display: block; font-weight: 500; font-size: .95rem; }
    .db-row .s { display: block; font-size: .8rem; color: var(--bs-secondary-color); margin-top: .1rem; }
    .db-pill { flex-shrink: 0; padding: .22rem .75rem; border-radius: 9999px; background: var(--tb); color: var(--tc); font-size: .76rem; font-weight: 600; }
    .db-empty { margin: 0; padding: .9rem 0 .6rem; border-top: 1px solid var(--bs-border-color); font-size: .9rem; color: var(--bs-secondary-color); }

    .db-short { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: 1.75rem; }
    .db-short a { display: inline-flex; align-items: center; gap: .5rem; padding: .55rem 1.1rem; border-radius: 9999px; border: 1px solid var(--bs-border-color); background: var(--bs-card-bg); color: var(--bs-body-color); font-size: .9rem; font-weight: 500; text-decoration: none; transition: border-color .2s ease; }
    .db-short a:hover { border-color: var(--brand-text); color: var(--brand-text); }
    .db-short span { width: 100%; font-size: .8rem; color: var(--bs-secondary-color); }

    @media (max-width: 767.98px) { .db-panels { grid-template-columns: 1fr; } }
    @media (prefers-reduced-motion: reduce) { .db-tile { transition: none; } }
</style>

<div class="db">

    <header class="db-head">
        <div>
            <span class="badge rounded-pill badge-brand px-3 py-2">{{ ucfirst($user->role) }}</span>
            <h1>Halo, {{ $user->username }}</h1>
            <p class="db-date">{{ $tanggal }}</p>
        </div>
        @if ($aksi)
            <a href="{{ $aksi[1] }}" class="btn btn-brand rounded-pill px-4 py-2">{{ $aksi[0] }}</a>
        @endif
    </header>

    @if (count($tiles))
        <div class="db-tiles">
            @foreach ($tiles as $t)
            <a href="{{ $t['url'] }}" class="db-tile tone-{{ $t['tone'] }}">
                <span class="top"><span class="ic"><i class="bi {{ $t['icon'] }}"></i></span>{{ $t['label'] }}</span>
                <b>{{ $t['n'] }}</b>
                <small>{{ $t['sub'] }}</small>
            </a>
            @endforeach
        </div>

        <div class="db-panels">
            @foreach ($panels as $p)
            <section class="db-card">
                <header>
                    <h2>{{ $p['judul'] }}</h2>
                    <a href="{{ $p['url'] }}">{{ $p['urlLabel'] }}</a>
                </header>
                @forelse ($p['rows'] as $r)
                    <a href="{{ $r['url'] ?: $p['url'] }}" class="db-row">
                        <span>
                            <span class="t">{{ $r['judul'] }}</span>
                            <span class="s">{{ $r['sub'] }}</span>
                        </span>
                        @if ($r['badge'])
                            <span class="db-pill tone-{{ $r['tone'] }}">{{ $r['badge'] }}</span>
                        @endif
                    </a>
                @empty
                    <p class="db-empty">{{ $p['kosong'] }}</p>
                @endforelse
            </section>
            @endforeach
        </div>

        @if (count($shortcuts))
        <nav class="db-short" aria-label="Akses cepat">
            <span>Akses cepat</span>
            @foreach ($shortcuts as $s)
                <a href="{{ $s[2] }}"><i class="bi {{ $s[0] }}"></i>{{ $s[1] }}</a>
            @endforeach
        </nav>
        @endif
    @else
        <div class="db-card">
            <h2>Akunmu belum punya halaman khusus</h2>
            <p class="db-empty border-0 pb-3">Role {{ $user->role }} belum punya ringkasan di dashboard. Hubungi admin kalau kamu butuh akses ke fitur peminjaman.</p>
        </div>
    @endif

</div>

@endsection