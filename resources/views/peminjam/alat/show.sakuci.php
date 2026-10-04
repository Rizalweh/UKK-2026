@extends('layouts.app')

@section('title', config('app.name') . ' -- Pinjam ' . $alat->nama_alat)

@section('content')
@php
    $tersedia          = $alat->tersedia();
    $namaKategori      = $alat->kategori->nama_kategori ?? 'Tanpa kategori';
    $urlFoto           = $alat->fotoUrl();
    $persenStok        = $alat->persenStok($stokTertinggi);
    $tanggalPinjamAwal = old('tanggal_pinjam', $hariIni);
    $tarifDendaPerHari = number_format(\App\Models\Pengembalian::TARIF_DENDA_PER_HARI, 0, ',', '.');
@endphp

<div class="latar-peminjam">

    <header class="pinjam-header">
        <a href="{{ route('peminjam.alat.index') }}" class="pinjam-kembali"><i class="bi bi-chevron-left"></i>Katalog alat</a>
        <h1 class="teks-display teks-display-sedang">Pinjam {{ $alat->nama_alat }}</h1>
        <div class="pinjam-subjudul">
            <span class="pill tone-muted">{{ $namaKategori }}</span>
            <span>Isi tanggal dan jumlah, lalu kirim pengajuan ke petugas.</span>
        </div>
    </header>

    <div class="row g-4">

        {{-- Panel samping: di atas pada layar kecil, di kanan dan menempel pada layar besar --}}
        <div class="col-lg-5 order-1 order-lg-2">
            <aside class="pinjam-sisi">

                <div class="panel panel-inspeksi muncul" style="--urutan: 1">
                    <div class="inspeksi-media">
                        @if ($urlFoto)
                            <img src="{{ $urlFoto }}" alt="Foto {{ $alat->nama_alat }}">
                        @else
                            <i class="bi bi-tools"></i>
                        @endif
                    </div>
                    <div class="inspeksi-bar">
                        <span class="label-mono">{{ $alat->kode_alat }}</span>
                        <span class="inspeksi-kondisi tone-{{ $alat->nadaKondisi() }}"><i class="titik"></i>{{ $alat->kondisi }}</span>
                    </div>
                </div>

                <div class="metrik-grid">
                    <div class="panel metrik metrik-lebar muncul" style="--urutan: 2">
                        <span class="label-mono">stok tersedia</span>
                        <span class="angka-besar {{ $tersedia ? '' : 'text-danger' }}">{{ $alat->stok }}</span>
                        <div class="meter" aria-hidden="true" style="--isi: {{ $persenStok }}%"><span></span></div>
                    </div>

                    <div class="panel metrik muncul" style="--urutan: 3">
                        <span class="label-mono">denda telat</span>
                        <span class="angka-besar metrik-nilai-sedang">Rp{{ $tarifDendaPerHari }}</span>
                        <span class="metrik-satuan">per hari</span>
                    </div>

                    <div class="panel metrik muncul" style="--urutan: 4">
                        <span class="label-mono">maksimal pinjam</span>
                        <span class="angka-besar metrik-nilai-sedang">{{ $alat->stok }}</span>
                        <span class="metrik-satuan">unit per pengajuan</span>
                    </div>
                </div>

            </aside>
        </div>

        {{-- Panel form: tokoh utama halaman --}}
        <div class="col-lg-7 order-2 order-lg-1">
            @if ($tersedia)
                <form method="POST" action="{{ route('peminjam.peminjaman.ajukan') }}" class="panel panel-form muncul" style="--urutan: 0">
                    @csrf
                    <input type="hidden" name="id_alat" value="{{ $alat->id_alat }}">

                    <h2 class="panel-form-judul">Detail peminjaman</h2>
                    <p class="panel-form-sub">Petugas akan memeriksa pengajuanmu. Stok baru berkurang setelah disetujui.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="medan panel-dalam {{ errors()->has('tanggal_pinjam') ? 'medan-galat' : '' }}" for="tanggal_pinjam">
                                <span class="label-mono">tanggal pinjam</span>
                                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" min="{{ $hariIni }}" value="{{ $tanggalPinjamAwal }}" required>
                            </label>
                            @error('tanggal_pinjam') <div class="medan-pesan">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="medan panel-dalam {{ errors()->has('tanggal_kembali_rencana') ? 'medan-galat' : '' }}" for="tanggal_kembali_rencana">
                                <span class="label-mono">tanggal kembali</span>
                                <input type="date" id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" min="{{ $tanggalPinjamAwal }}" value="{{ old('tanggal_kembali_rencana') }}" required>
                            </label>
                            @error('tanggal_kembali_rencana') <div class="medan-pesan">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="medan medan-angka panel-dalam {{ errors()->has('jumlah_pinjam') ? 'medan-galat' : '' }}" for="jumlah_pinjam">
                                <span class="label-mono">jumlah (maksimal {{ $alat->stok }} unit)</span>
                                <input type="number" id="jumlah_pinjam" name="jumlah_pinjam" min="1" max="{{ $alat->stok }}" value="{{ old('jumlah_pinjam', 1) }}" required>
                            </label>
                            @error('jumlah_pinjam') <div class="medan-pesan">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="medan panel-dalam" for="catatan">
                                <span class="label-mono">keperluan (opsional)</span>
                                <textarea id="catatan" name="catatan" rows="3" maxlength="255" placeholder="Contoh: praktik jaringan kelas XI">{{ old('catatan') }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="panel-form-aksi">
                        <button type="submit" class="btn btn-brand pinjam-tombol-utama">Kirim pengajuan</button>
                        <a href="{{ route('peminjam.alat.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            @else
                <div class="panel panel-form pinjam-habis muncul" style="--urutan: 0">
                    <i class="bi bi-slash-circle"></i>
                    <h2>Sedang tidak tersedia</h2>
                    <p>Semua unit alat ini sedang dipinjam. Coba lagi nanti atau pilih alat lain.</p>
                    <a href="{{ route('peminjam.alat.index') }}" class="btn btn-brand rounded-pill px-4">Kembali ke katalog</a>
                </div>
            @endif

            @include('partials.aturan-denda')
        </div>

    </div>
</div>
@endsection