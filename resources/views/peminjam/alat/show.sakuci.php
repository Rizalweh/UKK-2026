@extends('layouts.app')

@section('title', config('app.name') . ' -- Pinjam ' . $alat->nama_alat)

@section('content')
@php
    $tersedia          = $alat->tersedia();
    $namaKategori      = $alat->kategori->nama_kategori ?? 'Tanpa kategori';
    $urlFoto           = $alat->fotoUrl();
    $tanggalPinjamAwal = old('tanggal_pinjam', $hariIni);
@endphp

<div class="latar-peminjam">
    @include('partials.page-head', [
        'judul'   => 'Pinjam ' . $alat->nama_alat,
        'sub'     => 'Isi tanggal dan jumlah, lalu kirim pengajuan ke petugas.',
        'kembali' => route('peminjam.alat.index'),
    ])

    <div class="row g-4">

        {{-- Ringkasan alat: di atas pada layar kecil, di kanan dan menempel pada layar besar --}}
        <div class="col-lg-5 order-1 order-lg-2">
            <aside class="pinjam-ringkasan">
                <div class="pinjam-kartu">
                    <div class="pinjam-foto">
                        @if ($urlFoto)
                            <img src="{{ $urlFoto }}" alt="Foto {{ $alat->nama_alat }}">
                        @else
                            <i class="bi bi-tools"></i>
                        @endif
                    </div>

                    <span class="pill tone-muted">{{ $namaKategori }}</span>
                    <h2 class="pinjam-nama-alat">{{ $alat->nama_alat }}</h2>
                    <span class="font-mono pinjam-kode">{{ $alat->kode_alat }}</span>

                    <div class="pinjam-info-pill">
                        <span class="pill tone-{{ $alat->nadaKondisi() }}">{{ $alat->kondisi }}</span>
                        <span class="pill tone-{{ $tersedia ? 'ok' : 'danger' }}">{{ $tersedia ? 'Stok ' . $alat->stok : 'Stok habis' }}</span>
                    </div>

                    @if ($tersedia)
                        <dl class="pinjam-ringkasan-daftar">
                            <div>
                                <dt>Jumlah</dt>
                                <dd id="ringkasanJumlah">-</dd>
                            </div>
                            <div>
                                <dt>Lama pinjam</dt>
                                <dd id="ringkasanLama" data-satuan="hari" data-kosong="Pilih tanggal">-</dd>
                            </div>
                        </dl>
                    @endif
                </div>
            </aside>
        </div>

        {{-- Form pinjam: tokoh utama halaman --}}
        <div class="col-lg-7 order-2 order-lg-1">
            @if ($tersedia)
                <form method="POST" action="{{ route('peminjam.peminjaman.ajukan') }}" id="formPinjam" data-stok="{{ $alat->stok }}" class="pinjam-kartu pinjam-form">
                    @csrf
                    <input type="hidden" name="id_alat" value="{{ $alat->id_alat }}">

                    <h2 class="pinjam-form-judul">Detail peminjaman</h2>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="tanggal_pinjam">Tanggal pinjam</label>
                            <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" min="{{ $hariIni }}" value="{{ $tanggalPinjamAwal }}" class="form-control {{ errors()->has('tanggal_pinjam') ? 'is-invalid' : '' }}" required>
                            @error('tanggal_pinjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="tanggal_kembali_rencana">Tanggal kembali</label>
                            <input type="date" id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" min="{{ $tanggalPinjamAwal }}" value="{{ old('tanggal_kembali_rencana') }}" class="form-control {{ errors()->has('tanggal_kembali_rencana') ? 'is-invalid' : '' }}" required>
                            @error('tanggal_kembali_rencana') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="jumlah_pinjam">Jumlah</label>
                            <div class="stepper">
                                <button type="button" class="stepper-tombol" data-langkah="-1" aria-label="Kurangi jumlah"><i class="bi bi-dash-lg"></i></button>
                                <input type="number" id="jumlah_pinjam" name="jumlah_pinjam" min="1" max="{{ $alat->stok }}" value="{{ old('jumlah_pinjam', 1) }}" class="stepper-input" required>
                                <button type="button" class="stepper-tombol" data-langkah="1" aria-label="Tambah jumlah"><i class="bi bi-plus-lg"></i></button>
                            </div>
                            <div class="form-text">Maksimal {{ $alat->stok }} unit.</div>
                            @error('jumlah_pinjam') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="catatan">Keperluan (opsional)</label>
                            <textarea id="catatan" name="catatan" rows="3" maxlength="255" class="form-control" placeholder="Contoh: praktik jaringan kelas XI">{{ old('catatan') }}</textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-brand">Kirim pengajuan</button>
                        <a href="{{ route('peminjam.alat.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            @else
                <div class="pinjam-kartu pinjam-habis">
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var formulir = document.getElementById('formPinjam');

        if (!formulir) {
            return;
        }

        var MS_PER_HARI = 86400000;
        var batasStok = parseInt(formulir.dataset.stok, 10);
        var inputTanggalPinjam = document.getElementById('tanggal_pinjam');
        var inputTanggalKembali = document.getElementById('tanggal_kembali_rencana');
        var inputJumlah = document.getElementById('jumlah_pinjam');
        var teksJumlah = document.getElementById('ringkasanJumlah');
        var teksLama = document.getElementById('ringkasanLama');

        function batasiJumlah() {
            var jumlah = parseInt(inputJumlah.value, 10);

            if (isNaN(jumlah) || jumlah < 1) {
                jumlah = 1;
            }
            if (jumlah > batasStok) {
                jumlah = batasStok;
            }

            inputJumlah.value = jumlah;
        }

        function sesuaikanTanggalKembali() {
            if (!inputTanggalPinjam.value) {
                return;
            }

            inputTanggalKembali.min = inputTanggalPinjam.value;

            if (inputTanggalKembali.value && inputTanggalKembali.value < inputTanggalPinjam.value) {
                inputTanggalKembali.value = inputTanggalPinjam.value;
            }
        }

        function perbaruiRingkasan() {
            teksJumlah.textContent = inputJumlah.value || '-';

            var tanggalPinjam = inputTanggalPinjam.value;
            var tanggalKembali = inputTanggalKembali.value;

            if (!tanggalPinjam || !tanggalKembali) {
                teksLama.textContent = teksLama.dataset.kosong;
                return;
            }

            // Tanggal pinjam dan tanggal kembali sama-sama dihitung sebagai hari pinjam.
            var jumlahHari = Math.round((new Date(tanggalKembali) - new Date(tanggalPinjam)) / MS_PER_HARI) + 1;

            teksLama.textContent = jumlahHari >= 1
                ? jumlahHari + ' ' + teksLama.dataset.satuan
                : teksLama.dataset.kosong;
        }

        inputTanggalPinjam.addEventListener('change', function () {
            sesuaikanTanggalKembali();
            perbaruiRingkasan();
        });

        inputTanggalKembali.addEventListener('change', perbaruiRingkasan);

        inputJumlah.addEventListener('change', function () {
            batasiJumlah();
            perbaruiRingkasan();
        });

        formulir.querySelectorAll('[data-langkah]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                var jumlahSekarang = parseInt(inputJumlah.value, 10) || 1;

                inputJumlah.value = jumlahSekarang + parseInt(tombol.dataset.langkah, 10);
                batasiJumlah();
                perbaruiRingkasan();
            });
        });

        sesuaikanTanggalKembali();
        batasiJumlah();
        perbaruiRingkasan();
    });
</script>
@endsection