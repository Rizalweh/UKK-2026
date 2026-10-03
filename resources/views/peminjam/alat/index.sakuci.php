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
    @include('partials.page-head', [
        'judul' => 'Katalog Alat',
        'sub'   => $daftarAlat->total() . ' alat ditemukan. Pilih satu alat untuk mulai meminjam.',
    ])

    <form method="get" action="{{ route('peminjam.alat.index') }}" class="katalog-cari">
        @if ($kategoriDipilih !== '')
            <input type="hidden" name="kategori" value="{{ $kategoriDipilih }}">
        @endif
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" name="q" value="{{ $kataKunci }}" class="form-control" placeholder="Cari nama atau kode alat" aria-label="Cari alat">
            <button type="submit" class="btn btn-brand">Cari</button>
        </div>
    </form>

    <nav class="filter-kategori" aria-label="Filter kategori">
        <a href="{{ $urlKatalog(null) }}" class="filter-pill {{ $kategoriDipilih === '' ? 'aktif' : '' }}">Semua</a>
        @foreach ($kategoriList as $kategori)
            <a href="{{ $urlKatalog($kategori->id_kategori) }}"
               class="filter-pill {{ $kategoriDipilih === (string) $kategori->id_kategori ? 'aktif' : '' }}">{{ $kategori->nama_kategori }}</a>
        @endforeach
    </nav>

    @if (count($daftarAlat) > 0)
        <div class="katalog-grid">
            @php $urutanKartu = 0; @endphp
            @foreach ($daftarAlat as $alat)
                @include('partials.kartu-alat', ['alat' => $alat, 'urutan' => $urutanKartu++])
            @endforeach
        </div>

        <div class="pager">{!! $daftarAlat->links() !!}</div>
    @else
        <div class="katalog-kosong">
            <h2>Tidak ada alat yang cocok</h2>
            <p>Ubah kata kunci atau pilih kategori lain.</p>
            @if ($adaFilter)
                <a href="{{ route('peminjam.alat.index') }}" class="btn btn-outline-secondary btn-sm">Tampilkan semua alat</a>
            @endif
        </div>
    @endif
</div>
@endsection