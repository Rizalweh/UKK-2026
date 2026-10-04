{{-- Kartu alat di katalog. Variabel: $alat, $urutan (jeda animasi muncul), $stokTertinggi (pembanding meter) --}}
@php
    $tersedia   = $alat->tersedia();
    $urlFoto    = $alat->fotoUrl();
    $persenStok = $alat->persenStok($stokTertinggi);
@endphp
<a href="{{ route('peminjam.alat.show', ['id' => $alat->id_alat]) }}"
   class="panel kartu-alat muncul {{ $tersedia ? '' : 'kartu-alat-habis' }}"
   style="--urutan: {{ $urutan }}">
    <div class="kartu-media">
        @if ($urlFoto)
            <img src="{{ $urlFoto }}" alt="Foto {{ $alat->nama_alat }}" loading="lazy">
        @else
            <i class="bi bi-tools"></i>
        @endif

        @if (! $tersedia)
            <span class="kartu-chip kartu-chip-habis">Habis</span>
        @endif
        <span class="kartu-chip kartu-chip-kondisi tone-{{ $alat->nadaKondisi() }}"><i class="titik"></i>{{ $alat->kondisi }}</span>
        <span class="kartu-chip kartu-chip-kode">{{ $alat->kode_alat }}</span>
    </div>

    <div class="kartu-isi">
        <span class="kartu-kategori">{{ $alat->kategori->nama_kategori ?? 'Tanpa kategori' }}</span>
        <h3 class="kartu-nama">{{ $alat->nama_alat }}</h3>

        <div class="kartu-metrik">
            <div>
                <span class="angka-besar">{{ $alat->stok }}</span>
                <span class="label-mono">{{ $tersedia ? 'unit tersedia' : 'stok habis' }}</span>
            </div>
            <span class="kartu-pergi" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span>
        </div>
        <div class="meter" aria-hidden="true" style="--isi: {{ $persenStok }}%"><span></span></div>
    </div>
</a>