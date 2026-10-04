<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 -- {{ config('app.name') }}</title>

    {{-- Halaman ini berdiri sendiri (tanpa sidebar dan tanpa database) supaya tetap
         tampil walau penyebab error-nya database mati. --}}
    <script>
        (function () {
            var saved = localStorage.getItem('sakuci-theme');
            var theme = saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/peminjaman.css') }}">
</head>
<body class="bg-body-tertiary">

<div class="latar-peminjam latar-tetap">
    <div class="d-flex flex-column align-items-center justify-content-center text-center min-vh-100 px-3">
        <span class="label-mono">kesalahan</span>
        <h1 class="teks-display" style="font-size: clamp(5rem, 18vw, 10rem)">500</h1>
        <h2 class="h4 fw-semibold mt-3">Terjadi kesalahan di server</h2>
        <p class="katalog-sub mx-auto">Ada masalah di pihak kami. Coba lagi beberapa saat lagi.</p>
        <a href="{{ route('home') }}" class="btn btn-brand rounded-pill px-4 mt-4">Ke beranda</a>
    </div>
</div>

</body>
</html>