@extends('layouts.app')

@section('title', config('app.name') . ' -- Sedang Dipinjam')

@section('content')
@php
    $ubin = [
        ['sedang dipinjam', $ringkasan['dipinjam'], null, ''],
        ['jatuh tempo', $ringkasan['tempo'], 'hari ini atau besok', ''],
        ['terlambat', $ringkasan['telat'], null, $ringkasan['telat'] > 0 ? 'text-danger' : ''],
    ];
    $urutan = 0;
@endphp

<div class="latar-peminjam latar-tetap">

    <header class="riwayat-hero">
        <div>
            <h1 class="teks-display teks-display-sedang">Sedang dipinjam</h1>
            <p class="katalog-sub">Ajukan pengembalian saat alat selesai dipakai. Petugas akan memverifikasi kondisinya.</p>
        </div>
        <a href="{{ route('peminjam.alat.index') }}" class="btn btn-brand rounded-pill px-4">Pinjam alat</a>
    </header>

    <div class="dipinjam-ubin">
        @foreach ($ubin as $u)
            <div class="panel metrik">
                <span class="angka-besar {{ $u[3] }}">{{ $u[1] }}</span>
                <span class="label-mono">{{ $u[0] }}</span>
                @if ($u[2])<span class="metrik-satuan">{{ $u[2] }}</span>@endif
            </div>
        @endforeach
    </div>

    <h2 class="dipinjam-judul">Perlu dikembalikan</h2>
    <p class="dipinjam-sub">Yang paling mendesak ada di atas. Setelah mengajukan pengembalian, kembalikan alat ke Ruang TU.</p>

    @forelse ($dipinjam as $p)
        @include('partials.kartu-dipinjam', ['p' => $p, 'urutan' => $urutan++, 'bisaKembali' => true])
    @empty
        <div class="panel katalog-kosong">
            <h2>Tidak ada alat yang sedang dipinjam</h2>
            <p>Pilih alat di katalog, lalu kirim pengajuan.</p>
            <a href="{{ route('peminjam.alat.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Lihat katalog</a>
        </div>
    @endforelse

    <h2 class="dipinjam-judul">Menunggu verifikasi petugas</h2>
    <p class="dipinjam-sub">Pengembalian yang sudah kamu ajukan dan belum diperiksa petugas.</p>

    @forelse ($menunggu as $p)
        @include('partials.kartu-dipinjam', ['p' => $p, 'urutan' => $urutan++, 'bisaKembali' => false])
    @empty
        <p class="riwayat-catatan">Tidak ada pengajuan pengembalian yang menunggu.</p>
    @endforelse

</div>
@endsection