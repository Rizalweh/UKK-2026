@extends('layouts.app')

@section('title', config('app.name') . ' -- Riwayat Peminjaman')

@section('content')
@php
    $adaFilter = $statusFilter !== '' || $kode !== '';

    // URL riwayat dengan pencarian dipertahankan, status diganti sesuai pill yang diklik
    $urlRiwayat = function ($status = null) use ($kode) {
        $query = array_filter(['q' => $kode, 'status' => $status], fn ($nilai) => $nilai !== null && $nilai !== '');

        return route('peminjam.riwayat') . ($query ? '?' . http_build_query($query) : '');
    };

    $ubin = [
        ['menunggu persetujuan', $ringkasan['menunggu'], null, ''],
        ['sedang dipinjam', $ringkasan['dipinjam'], null, ''],
        ['selesai', $ringkasan['selesai'], null, ''],
        ['denda belum lunas', $ringkasan['denda'], $ringkasan['denda'] > 0 ? 'Rp' . number_format($ringkasan['dendaRp'], 0, ',', '.') : null, $ringkasan['denda'] > 0 ? 'text-danger' : ''],
    ];
@endphp

<div class="latar-peminjam latar-tetap">

    <header class="riwayat-hero">
        <div>
            <h1 class="teks-display teks-display-sedang">Riwayat peminjaman</h1>
            <p class="katalog-sub">Pantau status setiap pengajuan, dari menunggu persetujuan sampai alat dikembalikan.</p>
        </div>
        <a href="{{ route('peminjam.alat.index') }}" class="btn btn-brand rounded-pill px-4">Pinjam alat</a>
    </header>

    <div class="riwayat-ubin">
        @foreach ($ubin as $u)
            <div class="panel metrik">
                <span class="angka-besar {{ $u[3] }}">{{ $u[1] }}</span>
                <span class="label-mono">{{ $u[0] }}</span>
                @if ($u[2])<span class="metrik-satuan">{{ $u[2] }}</span>@endif
            </div>
        @endforeach
    </div>

    <div class="panel katalog-toolbar">
        <form method="get" action="{{ route('peminjam.riwayat') }}" class="katalog-cari">
            @if ($statusFilter !== '')
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="{{ $kode }}" placeholder="Cari kode peminjaman" aria-label="Cari kode">
            <button type="submit" class="btn btn-brand btn-sm rounded-pill px-3">Cari</button>
        </form>

        <nav class="filter-kategori" aria-label="Filter status">
            <a href="{{ $urlRiwayat(null) }}" class="filter-pill {{ $statusFilter === '' ? 'aktif' : '' }}">Semua</a>
            @foreach (\App\Models\Peminjaman::STATUS as $kunci => $info)
                <a href="{{ $urlRiwayat($kunci) }}" class="filter-pill {{ $statusFilter === $kunci ? 'aktif' : '' }}">{{ $info['label'] }}</a>
            @endforeach
        </nav>
    </div>

    @if (count($data) > 0)
        @php $urutan = 0; @endphp
        @foreach ($data as $p)
            @include('partials.kartu-riwayat', ['p' => $p, 'urutan' => $urutan++])
        @endforeach

        <div class="pager">{!! $data->links() !!}</div>
    @else
        <div class="panel katalog-kosong">
            <h2>{{ $adaFilter ? 'Tidak ada riwayat yang cocok' : 'Belum ada riwayat peminjaman' }}</h2>
            <p>{{ $adaFilter ? 'Ubah kode atau pilih status lain.' : 'Pengajuan yang kamu kirim akan muncul di sini.' }}</p>
            @if ($adaFilter)
                <a href="{{ route('peminjam.riwayat') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Tampilkan semua</a>
            @endif
        </div>
    @endif
</div>
@endsection