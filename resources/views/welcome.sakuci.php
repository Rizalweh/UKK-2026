@extends('layouts.app')

@section('title', config('app.name') . ' -- Peminjaman Alat')

@section('content')

@php
    $role   = $currentUser->role ?? null;
    $tarif  = number_format(\App\Models\Pengembalian::TARIF_DENDA_PER_HARI, 0, ',', '.');
    $persen = \App\Models\Pengembalian::PERSEN_KERUSAKAN;
    $pct    = fn ($kondisi) => (int) round($persen[$kondisi] * 100) . '%';

    // tombol utama sesuai role
    if (! $currentUser)           { $cta = [['Masuk untuk meminjam', route('login')], ['Lihat cara kerja', '#alur']]; $linkAlat = route('login'); }
    elseif ($role === 'peminjam') { $cta = [['Lihat daftar alat', route('peminjam.alat.index')], ['Riwayat peminjaman', route('peminjam.riwayat')]]; $linkAlat = route('peminjam.alat.index'); }
    elseif ($role === 'petugas')  { $cta = [['Kelola peminjaman', route('petugas.peminjaman.index')], ['Proses pengembalian', route('petugas.pengembalian.index')]]; $linkAlat = route('dashboard'); }
    elseif ($role === 'admin')    { $cta = [['Buka dashboard admin', route('admin.dashboard')], ['Kelola alat', route('alat.index')]]; $linkAlat = route('alat.index'); }
    else                          { $cta = [['Buka dashboard', route('dashboard')], ['Lihat cara kerja', '#alur']]; $linkAlat = route('dashboard'); }

    $peran = [
        'peminjam' => ['Peminjam', 'bi-person-badge', 'Siswa yang meminjam alat.', [
            'Melihat alat yang stoknya tersedia',
            'Mengajukan peminjaman dengan tanggal dan jumlah',
            'Memantau status dan riwayat pengajuan',
            'Mengajukan pengembalian dari halaman Sedang Dipinjam',
            'Melihat rincian denda',
        ]],
        'petugas' => ['Petugas', 'bi-clipboard-check', 'Memeriksa dan mencatat setiap peminjaman.', [
            'Menyetujui atau menolak pengajuan',
            'Memproses pengembalian dan mencatat kondisi alat',
            'Menandai denda yang sudah dibayar',
            'Mencetak laporan peminjaman per periode',
        ]],
        'admin' => ['Admin', 'bi-shield-lock', 'Mengatur data dan akses seluruh aplikasi.', [
            'Mengelola alat, kategori, dan stok',
            'Mengelola pengguna, peminjam, dan role',
            'Mencatat atau mengoreksi peminjaman dan pengembalian',
            'Memperpanjang tanggal kembali',
            'Memantau log aktivitas',
        ]],
    ];
    $aktif = isset($peran[$role]) ? $role : 'peminjam';

    $faq = [
        ['Bagaimana cara mendaftar?', 'Pilih Daftar akun, lalu isi username, password, nama lengkap, NIS, dan nomor HP. Kalau pendaftaran belum dibuka, minta admin membuatkan akunmu.'],
        ['Kapan stok alat berkurang?', 'Stok baru berkurang setelah petugas menyetujui pengajuan. Selama masih berstatus pending, stok belum berubah.'],
        ['Bisakah tanggal kembali diperpanjang?', 'Bisa, selama peminjaman berstatus disetujui. Minta admin memperpanjang tanggal kembali, karena peminjam tidak bisa mengubahnya sendiri.'],
        ['Berapa denda kalau terlambat?', 'Rp' . $tarif . ' untuk setiap hari setelah tanggal rencana kembali.'],
        ['Bagaimana kalau alat rusak atau hilang?', 'Dendanya persentase dari harga alat dikali jumlah yang dipinjam: rusak ringan ' . $pct('rusak_ringan') . ', rusak berat ' . $pct('rusak_berat') . ', hilang ' . $pct('hilang') . '. Denda kerusakan dihitung terpisah dari denda telat.'],
        ['Di mana denda dibayar?', 'Langsung ke petugas. Setelah petugas menandainya lunas, peringatan di halamanmu hilang.'],
    ];
@endphp

<style>
    .lp {
        --ink: #2B2620; --dim: #5B5448; --faint: #8C8474;
        --panel: #F7F4EB; --card: #FFFFFF; --line: rgba(43, 38, 32, .09);
        --acc: #E65C00; --acct: #B94A00; --soft: #FFE9D6;
        --dark: #1E1913; --dark2: #2A231B; --ondark: #FFFFFF;
        --btn: #1E1913; --btn-ink: #FFFFFF;
        --b1: rgba(230, 92, 0, .32); --b2: rgba(255, 179, 128, .5);
        --shadow: 0 1px 0 rgba(255, 255, 255, .9) inset, 0 14px 30px -18px rgba(43, 38, 32, .3);
        --r: 28px; --rs: 18px;
        max-width: 1120px; margin: 0 auto; color: var(--ink);
    }
    [data-bs-theme="dark"] .lp {
        --ink: #F1ECFB; --dim: rgba(241, 236, 251, .7); --faint: rgba(241, 236, 251, .46);
        --panel: #1C1030; --card: #26173F; --line: rgba(189, 181, 233, .15);
        --acc: #BDB5E9; --acct: #BDB5E9; --soft: rgba(189, 181, 233, .14);
        --dark: #0E0618; --dark2: #1C1030; --btn: #BDB5E9; --btn-ink: #1B0F2E;
        --b1: rgba(91, 30, 145, .7); --b2: rgba(189, 181, 233, .22);
        --shadow: 0 1px 0 rgba(255, 255, 255, .05) inset, 0 20px 50px -28px rgba(0, 0, 0, .8);
        --r: 32px; --rs: 24px;
    }
    .lp h1, .lp h2, .lp h3 { letter-spacing: -.035em; color: var(--ink); }
    .lp .serif { font-family: 'Instrument Serif', Georgia, serif; font-style: italic; font-weight: 400; letter-spacing: -.01em; }
    [data-bs-theme="dark"] .lp .serif { font-family: inherit; font-style: normal; font-weight: 600; }
    .lp section { scroll-margin-top: 90px; }
    .lp a { text-decoration: none; }
    html { scroll-behavior: smooth; }

    /* tombol */
    .lp-btn, .lp-btn2 { display: inline-flex; align-items: center; gap: .5rem; padding: .85rem 1.7rem; border-radius: 9999px; font-weight: 600; font-size: .98rem; transition: transform .2s ease; }
    .lp-btn { background: var(--btn); color: var(--btn-ink); box-shadow: 0 14px 28px -14px rgba(0, 0, 0, .6); }
    .lp-btn2 { background: var(--card); color: var(--ink); border: 1px solid var(--line); }
    .lp-btn:hover, .lp-btn2:hover { transform: translateY(-2px); }
    .lp-btn:hover { color: var(--btn-ink); } .lp-btn2:hover { color: var(--ink); }
    .lp a:focus-visible, .lp summary:focus-visible { outline: 2px solid var(--acc); outline-offset: 3px; }

    /* nav pill melayang: lompat antar bagian */
    .lp-nav { position: sticky; top: 70px; z-index: 30; display: flex; justify-content: center; margin-bottom: -62px; pointer-events: none; }
    .lp-nav nav { pointer-events: auto; display: flex; gap: .25rem; align-items: center; max-width: 100%; overflow-x: auto; padding: .4rem; border-radius: 9999px; background: color-mix(in srgb, var(--card) 78%, transparent); backdrop-filter: blur(14px); border: 1px solid var(--line); box-shadow: var(--shadow); }
    .lp-nav a { padding: .5rem 1rem; border-radius: 9999px; font-size: .9rem; font-weight: 500; color: var(--dim); white-space: nowrap; }
    .lp-nav a:hover { background: var(--soft); color: var(--ink); }
    .lp-nav a.go { background: var(--btn); color: var(--btn-ink); }
    @media (min-width: 992px) { .lp-nav { top: 16px; } }

    /* hero */
    .lp-hero { position: relative; overflow: hidden; border-radius: var(--r); background: var(--panel); box-shadow: var(--shadow); text-align: center; padding: 8rem 1.5rem 3.5rem; }
    .lp-blob { position: absolute; border-radius: 50%; filter: blur(60px); pointer-events: none; }
    .lp-blob.a { width: 560px; height: 560px; right: -160px; top: -60px; background: radial-gradient(circle, var(--b1), transparent 68%); }
    .lp-blob.b { width: 460px; height: 460px; left: -180px; top: 120px; background: radial-gradient(circle, var(--b2), transparent 70%); }
    .lp-hero > :not(.lp-blob):not(.lp-tile):not(.lp-chip), .lp-end > :not(.lp-blob) { position: relative; z-index: 1; }
    .lp-badge { display: inline-flex; align-items: center; gap: .5rem; padding: .45rem 1rem; border-radius: 9999px; background: var(--card); border: 1px solid var(--line); color: var(--acct); font-size: .88rem; font-weight: 500; }
    .lp-h1 { font-size: clamp(2.6rem, 8vw, 5.6rem); font-weight: 600; line-height: 1.02; margin: 1.5rem auto 0; max-width: 13ch; }
    .lp-sub { max-width: 36rem; margin: 1.75rem auto 0; font-size: 1.1rem; line-height: 1.65; color: var(--dim); }
    .lp-cta { display: flex; flex-wrap: wrap; gap: .75rem; justify-content: center; margin-top: 2rem; }
    .lp-cats { margin-top: 3rem; }
    .lp-cats small { display: block; font-size: .8rem; color: var(--faint); margin-bottom: .75rem; }
    .lp-cats div { display: flex; flex-wrap: wrap; gap: .5rem 1.75rem; justify-content: center; font-weight: 600; color: var(--dim); }
    .lp-tile { position: absolute; display: none; width: 60px; height: 60px; place-items: center; border-radius: 18px; background: var(--card); border: 1px solid var(--line); box-shadow: var(--shadow); animation: lp-float 6s ease-in-out infinite; }
    .lp-tile i { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 12px; color: #fff; font-size: 1.1rem; }
    .lp-tile.t1 { right: 9%; top: 24%; --rot: 6deg; } .lp-tile.t1 i { background: #1F9D55; }
    .lp-tile.t2 { left: 8%; top: 36%; --rot: -6deg; animation-delay: -2s; } .lp-tile.t2 i { background: #2563EB; }
    .lp-tile.t3 { right: 14%; bottom: 22%; --rot: 3deg; animation-delay: -4s; } .lp-tile.t3 i { background: var(--acc); color: var(--btn-ink); }
    @media (min-width: 992px) { .lp-tile { display: grid; } }
    @keyframes lp-float { 0%, 100% { transform: translateY(0) rotate(var(--rot)); } 50% { transform: translateY(-9px) rotate(calc(var(--rot) + 2deg)); } }
    .lp-chip { position: absolute; right: 1.25rem; bottom: 1.25rem; z-index: 2; display: none; align-items: center; gap: .75rem; padding: .6rem 1rem .6rem .6rem; text-align: left; border-radius: 18px; background: var(--card); border: 1px solid var(--line); box-shadow: var(--shadow); color: var(--ink); }
    .lp-chip img, .lp-chip .ph { width: 48px; height: 40px; object-fit: cover; border-radius: 10px; background: var(--soft); display: grid; place-items: center; color: var(--acct); }
    .lp-chip b { display: block; font-size: .88rem; line-height: 1.2; } .lp-chip small { color: var(--faint); font-size: .76rem; }
    @media (min-width: 992px) { .lp-chip { display: flex; } }

    /* ringkasan role */
    .lp-today { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; margin-top: 1rem; }
    .lp-today a { display: flex; align-items: center; gap: .85rem; padding: .9rem 1.1rem; border-radius: var(--rs); background: var(--card); border: 1px solid var(--line); color: var(--ink); transition: transform .2s ease; }
    .lp-today a:hover { transform: translateY(-2px); color: var(--ink); }
    .lp-today b { font-size: 1.35rem; font-weight: 600; min-width: 1.6rem; color: var(--acct); }
    .lp-today span { font-size: .9rem; color: var(--dim); line-height: 1.35; }
    .lp-today i { margin-left: auto; color: var(--faint); }

    /* judul bagian */
    .lp-sec { padding-top: clamp(4rem, 8vw, 6.5rem); }
    .lp-k { font-size: .9rem; font-weight: 600; color: var(--acct); margin-bottom: .6rem; }
    .lp-h2 { font-size: clamp(1.9rem, 4.5vw, 3rem); font-weight: 600; line-height: 1.1; max-width: 18ch; }
    .lp-lead { max-width: 36rem; margin-top: 1rem; font-size: 1.05rem; line-height: 1.65; color: var(--dim); }

    /* kartu alat */
    .lp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1.1rem; margin-top: 2.5rem; }
    .lp-tool { display: flex; flex-direction: column; border-radius: var(--rs); background: var(--card); border: 1px solid var(--line); box-shadow: var(--shadow); overflow: hidden; color: var(--ink); transition: transform .25s ease; }
    .lp-tool:hover { transform: translateY(-4px); color: var(--ink); }
    .lp-photo { aspect-ratio: 4 / 3; background: var(--soft); display: grid; place-items: center; color: var(--acct); font-size: 2.2rem; overflow: hidden; }
    .lp-photo img { width: 100%; height: 100%; object-fit: cover; }
    .lp-tool .in { padding: 1.1rem 1.25rem 1.25rem; display: flex; flex-direction: column; gap: .35rem; flex: 1; }
    .lp-pill { align-self: flex-start; padding: .2rem .7rem; border-radius: 9999px; background: var(--soft); color: var(--acct); font-size: .76rem; font-weight: 600; }
    .lp-tool h3 { font-size: 1.12rem; font-weight: 600; margin: .25rem 0 0; }
    .lp-code { font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: .76rem; color: var(--faint); }
    .lp-meta { margin-top: auto; padding-top: .8rem; display: flex; justify-content: space-between; font-size: .85rem; color: var(--dim); border-top: 1px dashed var(--line); }
    .lp-meta .ok { color: #1F9D55; font-weight: 600; } .lp-meta .no { color: #D92D20; font-weight: 600; }

    /* daftar kategori */
    .lp-catlist { margin-top: 2.5rem; border-top: 1px solid var(--line); }
    .lp-catrow { display: grid; grid-template-columns: 1.2fr 2fr auto; gap: 1.5rem; align-items: baseline; padding: 1.1rem .25rem; border-bottom: 1px solid var(--line); }
    .lp-catrow b { font-size: 1.05rem; } .lp-catrow p { margin: 0; color: var(--dim); font-size: .93rem; }
    .lp-catrow span { font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: .8rem; color: var(--faint); white-space: nowrap; }

    /* panel gelap: alur & denda */
    .lp-dark { margin-top: 2.5rem; padding: .9rem; border-radius: calc(var(--r) + 6px); background: var(--dark); box-shadow: 0 30px 60px -32px rgba(0, 0, 0, .7); }
    .lp-bento { display: grid; grid-template-columns: repeat(3, 1fr); gap: .9rem; }
    .lp-cell { padding: 2rem; border-radius: var(--r); background: var(--dark2); border: 1px solid rgba(255, 255, 255, .06); color: rgba(255, 255, 255, .62); }
    .lp-cell h3 { color: var(--ondark); font-size: 1.45rem; font-weight: 600; margin-bottom: .6rem; }
    .lp-cell h3 em { font-style: normal; color: rgba(255, 255, 255, .45); }
    .lp-cell p { line-height: 1.6; margin: 0; }
    .lp-cell.w2 { grid-column: span 2; }
    .lp-flow { list-style: none; margin: 1.75rem 0 0; padding: 0; display: grid; grid-template-columns: repeat(4, 1fr); gap: .75rem; counter-reset: s; }
    .lp-flow li { padding: 1rem; border-radius: 18px; background: rgba(255, 255, 255, .05); border: 1px solid rgba(255, 255, 255, .07); font-size: .86rem; line-height: 1.45; counter-increment: s; }
    .lp-flow li::before { content: counter(s); display: grid; place-items: center; width: 26px; height: 26px; margin-bottom: .6rem; border-radius: 50%; background: var(--acc); color: var(--btn-ink); font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: .75rem; font-weight: 600; }
    .lp-flow b { display: block; color: var(--ondark); font-size: .95rem; margin-bottom: .15rem; }
    .lp-fine { display: inline-block; padding: .4rem .9rem; border-radius: 9999px; background: var(--acc); color: var(--btn-ink); font-weight: 600; font-size: .85rem; margin-bottom: 1.1rem; }
    .lp-rules { list-style: none; margin: 1.1rem 0 0; padding: 0; font-size: .92rem; }
    .lp-rules li { display: flex; justify-content: space-between; gap: 1rem; padding: .55rem 0; border-top: 1px solid rgba(255, 255, 255, .08); }
    .lp-rules li b { color: var(--ondark); font-weight: 600; }
    .lp-rules .tag { font-size: .75rem; padding: .1rem .55rem; border-radius: 9999px; background: rgba(255, 255, 255, .08); }

    /* peran */
    .lp-roles { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.1rem; margin-top: 2.5rem; align-items: stretch; }
    .lp-role { padding: 2rem; border-radius: var(--r); background: var(--card); border: 1px solid var(--line); box-shadow: var(--shadow); }
    .lp-role.on { background: var(--dark); color: rgba(255, 255, 255, .72); border-color: transparent; }
    .lp-role.on h3, .lp-role.on .you { color: #fff; }
    .lp-role .ic { display: grid; place-items: center; width: 46px; height: 46px; border-radius: 14px; background: var(--soft); color: var(--acct); font-size: 1.25rem; margin-bottom: 1.1rem; }
    .lp-role.on .ic { background: var(--acc); color: var(--btn-ink); }
    .lp-role h3 { font-size: 1.3rem; font-weight: 600; margin-bottom: .25rem; }
    .lp-role > p { color: var(--dim); font-size: .93rem; margin-bottom: 1.25rem; }
    .lp-role.on > p { color: rgba(255, 255, 255, .55); }
    .lp-role ul { list-style: none; margin: 0; padding: 0; display: grid; gap: .65rem; font-size: .93rem; line-height: 1.45; }
    .lp-role li { display: flex; gap: .6rem; } .lp-role li i { color: var(--acct); margin-top: .1rem; }
    .lp-role.on li i { color: var(--acc); }
    .lp-role .you { font-size: .78rem; font-weight: 600; color: var(--acct); margin-left: .4rem; }

    /* FAQ */
    .lp-faq { margin-top: 2.5rem; max-width: 52rem; border-top: 1px solid var(--line); }
    .lp-faq details { border-bottom: 1px solid var(--line); }
    .lp-faq summary { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 1.25rem .25rem; cursor: pointer; font-weight: 600; font-size: 1.05rem; list-style: none; }
    .lp-faq summary::-webkit-details-marker { display: none; }
    .lp-faq summary::after { content: "+"; font-size: 1.4rem; font-weight: 400; color: var(--acct); transition: transform .2s ease; }
    .lp-faq details[open] summary::after { transform: rotate(45deg); }
    .lp-faq details p { margin: 0 0 1.25rem; padding: 0 .25rem; max-width: 42rem; line-height: 1.65; color: var(--dim); }

    /* penutup */
    .lp-end { position: relative; overflow: hidden; margin: clamp(4rem, 8vw, 6.5rem) 0 1rem; padding: clamp(3rem, 7vw, 5rem) 1.5rem; border-radius: var(--r); background: var(--panel); box-shadow: var(--shadow); text-align: center; }
    .lp-end h2 { font-size: clamp(2rem, 5vw, 3.4rem); font-weight: 600; line-height: 1.08; max-width: 16ch; margin: 0 auto; }
    .lp-end p { max-width: 30rem; margin: 1.25rem auto 0; color: var(--dim); line-height: 1.6; }

    @media (max-width: 991.98px) {
        .lp-bento, .lp-roles { grid-template-columns: 1fr; } .lp-cell.w2 { grid-column: auto; }
        .lp-flow { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 767.98px) {
        .lp-today { grid-template-columns: 1fr; }
        .lp-catrow { grid-template-columns: 1fr; gap: .25rem; }
        .lp-hero { padding-top: 7rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        .lp-tile { animation: none; transform: rotate(var(--rot)); }
        .lp * { transition: none !important; }
    }
</style>

<div class="lp">

    <div class="lp-nav">
        <nav aria-label="Navigasi halaman">
            <a href="#alat">Alat</a>
            <a href="#alur">Alur &amp; denda</a>
            <a href="#peran">Peran</a>
            <a href="#faq">Tanya jawab</a>
            <a href="{{ $cta[0][1] }}" class="go">{{ $cta[0][0] }}</a>
        </nav>
    </div>

    {{--HERO--}}
    <header class="lp-hero">
        <span class="lp-blob a"></span><span class="lp-blob b"></span>
        <span class="lp-tile t1"><i class="bi bi-box-seam"></i></span>
        <span class="lp-tile t2"><i class="bi bi-clipboard-check"></i></span>
        <span class="lp-tile t3"><i class="bi bi-tools"></i></span>

        <span class="lp-badge"><i class="bi bi-lightning-charge"></i> Peminjaman sarana dan prasarana sekolah</span>

        <h1 class="lp-h1">
            @if ($currentUser) Halo, {{ $currentUser->username }}.<br> @endif
            Peminjaman alat sekolah <span class="serif">Sarpras Tech</span>
        </h1>
        <p class="lp-sub">Cek stok, ajukan peminjaman, dan pantau pengembalian serta denda dari satu halaman.</p>

        <div class="lp-cta">
            <a href="{{ $cta[0][1] }}" class="lp-btn">{{ $cta[0][0] }} <i class="bi bi-arrow-right"></i></a>
            <a href="{{ $cta[1][1] }}" class="lp-btn2">{{ $cta[1][0] }}</a>
            @if (! $currentUser)
                <a href="{{ route('register') }}" class="lp-btn2">Daftar akun</a>
            @endif
        </div>

        @if (count($kategori))
        <div class="lp-cats">
            <small>Kategori alat yang tersedia</small>
            <div>@foreach ($kategori as $k)<span>{{ $k->nama_kategori }}</span>@endforeach</div>
        </div>
        @endif

        @if (count($alatTerbaru))
        @php $baru = $alatTerbaru[0]; @endphp
        <a href="{{ $linkAlat }}" class="lp-chip">
            @if ($baru->foto_alat)
                <img src="/uploads/foto_alat/{{ $baru->foto_alat }}" alt="">
            @else
                <span class="ph"><i class="bi bi-tools"></i></span>
            @endif
            <span><b>{{ $baru->nama_alat }}</b><small>Alat terbaru &middot; stok {{ $baru->stok }}</small></span>
        </a>
        @endif
    </header>

    @if (count($hariIni))
    <div class="lp-today" aria-label="Ringkasan untukmu">
        @foreach ($hariIni as $h)
            <a href="{{ $h['url'] }}"><b>{{ $h['n'] }}</b><span>{{ $h['t'] }}</span><i class="bi bi-chevron-right"></i></a>
        @endforeach
    </div>
    @endif

    {{-- ================= ALAT ================= --}}
    <section id="alat" class="lp-sec">
        <p class="lp-k">Alat</p>
        <h2 class="lp-h2">Alat yang baru masuk.</h2>
        <p class="lp-lead">{{ $stat['total'] }} jenis alat terdaftar di {{ $stat['kategori'] }} kategori, {{ $stat['tersedia'] }} di antaranya stoknya masih ada.</p>

        @if (count($alatTerbaru))
        <div class="lp-grid">
            @foreach ($alatTerbaru as $a)
            <a href="{{ $linkAlat }}" class="lp-tool">
                <div class="lp-photo">
                    @if ($a->foto_alat)
                        <img src="/uploads/foto_alat/{{ $a->foto_alat }}" alt="Foto {{ $a->nama_alat }}" loading="lazy">
                    @else
                        <i class="bi bi-tools"></i>
                    @endif
                </div>
                <div class="in">
                    <span class="lp-pill">{{ $a->kategori->nama_kategori ?? 'Tanpa kategori' }}</span>
                    <h3>{{ $a->nama_alat }}</h3>
                    <span class="lp-code">{{ $a->kode_alat }}</span>
                    <div class="lp-meta">
                        <span>Kondisi {{ strtolower($a->kondisi) }}</span>
                        @if ($a->stok > 0)<span class="ok">Stok {{ $a->stok }}</span>@else<span class="no">Habis</span>@endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
            <p class="lp-lead">Belum ada alat yang terdaftar. Admin bisa menambahkannya dari menu Alat.</p>
        @endif

        @if (count($kategori))
        <div class="lp-catlist">
            @foreach ($kategori as $k)
                @php
                    $jenis = count($k->alat);
                    $stokK = 0;
                    foreach ($k->alat as $x) { $stokK += (int) $x->stok; }
                @endphp
                <div class="lp-catrow">
                    <b>{{ $k->nama_kategori }}</b>
                    <p>{{ $k->keterangan ?: 'Belum ada keterangan.' }}</p>
                    <span>{{ $jenis }} jenis &middot; {{ $stokK }} unit</span>
                </div>
            @endforeach
        </div>
        @endif
    </section>

    {{-- ================= ALUR & DENDA ================= --}}
    <section id="alur" class="lp-sec">
        <p class="lp-k">Alur &amp; denda</p>
        <h2 class="lp-h2">Dari pengajuan sampai alat kembali.</h2>
        <p class="lp-lead">Setiap peminjaman melewati status yang sama, jadi kamu selalu tahu posisinya.</p>

        <div class="lp-dark">
            <div class="lp-bento">
                <div class="lp-cell w2">
                    <h3>Empat status <em>satu peminjaman</em></h3>
                    <p>Pengajuan yang ditolak berhenti di status Ditolak dan tidak mengurangi stok.</p>
                    <ol class="lp-flow">
                        <li><b>Pending</b>Pengajuan dikirim, menunggu petugas.</li>
                        <li><b>Disetujui</b>Alat dipinjam dan stok berkurang.</li>
                        <li><b>Menunggu pengembalian</b>Peminjam sudah mengajukan kembali.</li>
                        <li><b>Dikembalikan</b>Petugas mencatat tanggal dan kondisi.</li>
                    </ol>
                </div>
                <div class="lp-cell">
                    <span class="lp-fine">Rp{{ $tarif }} / hari</span>
                    <h3>Denda <em>telat</em></h3>
                    <p>Dihitung dari tanggal rencana kembali sampai alat benar-benar dikembalikan.</p>
                </div>
                <div class="lp-cell w2">
                    <h3>Kondisi alat <em>saat kembali</em></h3>
                    <p>Denda kerusakan = harga alat &times; jumlah dipinjam &times; persentase.</p>
                    <ul class="lp-rules">
                        <li><b>Baik</b><span>tanpa denda <span class="tag">masuk stok</span></span></li>
                        <li><b>Rusak ringan</b><span>{{ $pct('rusak_ringan') }} <span class="tag">masuk stok</span></span></li>
                        <li><b>Rusak berat</b><span>{{ $pct('rusak_berat') }} <span class="tag">tidak masuk stok</span></span></li>
                        <li><b>Hilang</b><span>{{ $pct('hilang') }} <span class="tag">tidak masuk stok</span></span></li>
                    </ul>
                </div>
                <div class="lp-cell">
                    <h3>Bayar <em>ke petugas</em></h3>
                    <p>Denda dibayar langsung. Peringatan di halamanmu hilang setelah petugas menandainya lunas.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= PERAN ================= --}}
    <section id="peran" class="lp-sec">
        <p class="lp-k">Peran</p>
        <h2 class="lp-h2">Siapa mengerjakan apa.</h2>
        <p class="lp-lead">Menu yang tampil mengikuti perananmu di aplikasi.</p>

        <div class="lp-roles">
            @foreach ($peran as $key => $p)
            <article class="lp-role {{ $key === $aktif ? 'on' : '' }}">
                <span class="ic"><i class="bi {{ $p[1] }}"></i></span>
                <h3>{{ $p[0] }} @if ($currentUser && $key === $aktif)<span class="you">Anda</span>@endif</h3>
                <p>{{ $p[2] }}</p>
                <ul>
                    @foreach ($p[3] as $item)
                        <li><i class="bi bi-check2"></i><span>{{ $item }}</span></li>
                    @endforeach
                </ul>
            </article>
            @endforeach
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section id="faq" class="lp-sec">
        <p class="lp-k">Tanya jawab</p>
        <h2 class="lp-h2">Yang sering ditanyakan.</h2>

        <div class="lp-faq">
            @foreach ($faq as $f)
            <details>
                <summary>{{ $f[0] }}</summary>
                <p>{{ $f[1] }}</p>
            </details>
            @endforeach
        </div>
    </section>

    {{-- ================= PENUTUP ================= --}}
    <section class="lp-end">
        <span class="lp-blob a"></span>
        <h2>Butuh alat untuk kegiatanmu?</h2>
        <p>Pilih alatnya, tentukan tanggalnya, lalu kirim pengajuan.</p>
        <div class="lp-cta">
            <a href="{{ $cta[0][1] }}" class="lp-btn">{{ $cta[0][0] }} <i class="bi bi-arrow-right"></i></a>
            <a href="#alat" class="lp-btn2">Lihat alat</a>
        </div>
    </section>

</div>

@endsection