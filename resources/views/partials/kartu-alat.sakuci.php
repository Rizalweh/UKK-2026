{{-- Kartu alat di katalog. Variabel: $alat, $urutan (untuk jeda animasi muncul) --}}
@php
    $tersedia = $alat->tersedia();
    $urlFoto  = $alat->fotoUrl();
@endphp
<a href="{{ route('peminjam.alat.show', ['id' => $alat->id_alat]) }}"
   class="kartu-alat muncul {{ $tersedia ? '' : 'kartu-alat-habis' }}"
   style="--urutan: {{ $urutan }}">
    <div class="kartu-alat-foto">
        @if ($urlFoto)
            <img src="{{ $urlFoto }}" alt="Foto {{ $alat->nama_alat }}" loading="lazy">
        @else
            <i class="bi bi-tools"></i>
        @endif
        @if (! $tersedia)
            <span class="kartu-alat-label-habis">Habis</span>
        @endif
    </div>
    <div class="kartu-alat-isi">
        <span class="pill tone-muted align-self-start">{{ $alat->kategori->nama_kategori ?? 'Tanpa kategori' }}</span>
        <h3 class="kartu-alat-nama">{{ $alat->nama_alat }}</h3>
        <span class="font-mono kartu-alat-kode">{{ $alat->kode_alat }}</span>
        <div class="kartu-alat-meta">
            <span class="pill tone-{{ $alat->nadaKondisi() }}">{{ $alat->kondisi }}</span>
            <span class="pill tone-{{ $tersedia ? 'ok' : 'danger' }}">{{ $tersedia ? 'Stok ' . $alat->stok : 'Stok habis' }}</span>
        </div>
        <span class="kartu-alat-aksi">{{ $tersedia ? 'Lihat dan pinjam' : 'Lihat detail' }}</span>
    </div>
</a>