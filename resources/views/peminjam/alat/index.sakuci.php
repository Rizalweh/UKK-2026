@extends('layouts.app')

@section('title', config('app.name') . ' -- Katalog Alat')

@section('content')
@php
    $adaFilter = $kataKunci !== '' || $kategoriDipilih !== '';

    // URL katalog dengan pencarian dipertahankan, kategori diganti sesuai pill yang diklik
    $urlKatalog = function ($idKategori = null) use ($kataKunci) {
        $parameterQuery = array_filter(
            ['q' => $kataKunci, 'kategori' => $idKategori],
            fn ($nilai) => $nilai !== null && $nilai !== ''
        );

        return route('peminjam.alat.index') . ($parameterQuery ? '?' . http_build_query($parameterQuery) : '');
    };
@endphp

<div class="latar-peminjam">

    <header class="katalog-hero">
        <div>
            <h1 class="teks-display">Pilih alat yang kamu butuhkan</h1>
            <p class="katalog-sub">Buka salah satu alat untuk melihat detailnya, lalu isi tanggal dan jumlah untuk mengajukan peminjaman.</p>
        </div>

        <div class="metrik-baris">
            <div class="panel metrik">
                <span class="angka-besar">{{ $ringkasanKatalog['alatTersedia'] }}</span>
                <span class="label-mono">alat tersedia</span>
            </div>
            <div class="panel metrik">
                <span class="angka-besar">{{ $ringkasanKatalog['jenisAlat'] }}</span>
                <span class="label-mono">jenis alat</span>
            </div>
            <div class="panel metrik">
                <span class="angka-besar">{{ $ringkasanKatalog['jumlahKategori'] }}</span>
                <span class="label-mono">kategori</span>
            </div>
        </div>
    </header>

    <div class="panel katalog-toolbar">
        <form method="get" action="{{ route('peminjam.alat.index') }}" class="katalog-cari">
            @if ($kategoriDipilih !== '')
                <input type="hidden" name="kategori" value="{{ $kategoriDipilih }}">
            @endif
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="{{ $kataKunci }}" placeholder="Cari nama atau kode alat" aria-label="Cari alat">
            <button type="submit" class="btn btn-brand btn-sm rounded-pill px-3">Cari</button>
        </form>

        <nav class="filter-kategori" aria-label="Filter kategori">
            <a href="{{ $urlKatalog(null) }}" class="filter-pill {{ $kategoriDipilih === '' ? 'aktif' : '' }}">Semua</a>
            @foreach ($kategoriList as $kategori)
                <a href="{{ $urlKatalog($kategori->id_kategori) }}"
                   class="filter-pill {{ $kategoriDipilih === (string) $kategori->id_kategori ? 'aktif' : '' }}">{{ $kategori->nama_kategori }}</a>
            @endforeach
        </nav>
    </div>

    @if (count($daftarAlat) > 0)
        <div class="katalog-grid">
            @php $urutanKartu = 0; @endphp
            @foreach ($daftarAlat as $alat)
                @include('partials.kartu-alat', ['alat' => $alat, 'urutan' => $urutanKartu++, 'stokTertinggi' => $stokTertinggi])
            @endforeach
        </div>

        <div class="pager">{!! $daftarAlat->links() !!}</div>
    @else
        <div class="panel katalog-kosong">
            <h2>Tidak ada alat yang cocok</h2>
            <p>Ubah kata kunci atau pilih kategori lain.</p>
            @if ($adaFilter)
                <a href="{{ route('peminjam.alat.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Tampilkan semua alat</a>
            @endif
        </div>
    @endif
</div>
@endsection