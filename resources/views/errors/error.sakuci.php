@extends('layouts.app')

@section('title', $status . ' -- ' . config('app.name'))

@section('content')
@php
    $teks = [
        403 => ['Kamu tidak punya akses', 'Halaman ini hanya untuk peran tertentu.'],
        404 => ['Halaman tidak ditemukan', 'Alamat yang kamu buka tidak ada atau sudah dipindahkan.'],
        405 => ['Cara akses tidak diizinkan', 'Halaman ini tidak bisa dibuka dengan cara tersebut.'],
        419 => ['Halaman kedaluwarsa', 'Sesi formulirmu sudah habis. Buka kembali halamannya, lalu coba lagi.'],
        500 => ['Terjadi kesalahan di server', 'Ada masalah di pihak kami. Coba lagi beberapa saat lagi.'],
    ];
    $isi   = $teks[$status] ?? ['Terjadi kesalahan', 'Permintaanmu tidak bisa diproses.'];
    $pesan = ($status === 403 && $exception->getMessage() !== 'Forbidden') ? $exception->getMessage() : $isi[1];

    $pengguna = \App\Models\User::current();
    $utama    = $pengguna
        ? ['Ke dashboard', $pengguna->role === 'admin' ? route('admin.dashboard') : route('dashboard')]
        : ['Masuk', route('login')];
@endphp

<div class="latar-peminjam latar-tetap">
    <div class="text-center py-5">
        <span class="label-mono">kesalahan</span>
        <h1 class="teks-display" style="font-size: clamp(5rem, 18vw, 10rem)">{{ $status }}</h1>
        <h2 class="h4 fw-semibold mt-3">{{ $isi[0] }}</h2>
        <p class="katalog-sub mx-auto">{{ $pesan }}</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
            <a href="{{ $utama[1] }}" class="btn btn-brand rounded-pill px-4">{{ $utama[0] }}</a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">Ke beranda</a>
        </div>
    </div>
</div>
@endsection