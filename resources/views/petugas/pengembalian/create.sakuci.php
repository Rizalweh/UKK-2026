@extends('layouts.app')

@section('title', config('app.name') . ' -- Proses Pengembalian')

@section('content')
@include('partials.page-head', ['judul' => 'Proses Pengembalian', 'sub' => $peminjaman->kode_peminjaman, 'kembali' => route('petugas.pengembalian.index')])

<div class="form-card">
    <dl class="info-list">
        <dt>Peminjam</dt><dd>{{ $peminjaman->peminjam->username ?? '-' }}</dd>
        <dt>Alat</dt><dd>{{ $peminjaman->alat->nama_alat ?? '-' }} ({{ $peminjaman->jumlah_pinjam }} unit)</dd>
        <dt>Tanggal pinjam</dt><dd>{{ $peminjaman->tanggal_pinjam }}</dd>
        <dt>Rencana kembali</dt><dd>{{ $peminjaman->tanggal_kembali_rencana }}</dd>
    </dl>

    <form action="{{ route('petugas.pengembalian.store', ['id' => $peminjaman->id_peminjaman]) }}" method="post">
        @csrf
        <div class="mb-3">
            <label for="tanggal_kembali_aktual" class="form-label">Tanggal Kembali (Aktual)</label>
            <input type="date" id="tanggal_kembali_aktual" name="tanggal_kembali_aktual" value="{{ old('tanggal_kembali_aktual', date('Y-m-d')) }}" required class="form-control">
        </div>
        <div class="mb-3">
            <label for="kondisi_alat" class="form-label">Kondisi Alat</label>
            <select id="kondisi_alat" name="kondisi_alat" required class="form-select">
                @foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang'] as $val => $label)
                    <option value="{{ $val }}" {{ old('kondisi_alat', 'baik') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="form-text">
                Denda telat Rp{{ number_format(\App\Models\Pengembalian::TARIF_DENDA_PER_HARI, 0, ',', '.') }} per hari. Rusak ringan 25%, rusak berat 50%, hilang 100% dari harga alat. Rusak berat dan hilang tidak masuk stok lagi.
            </div>
        </div>
        <div class="mb-3">
            <label for="catatan" class="form-label">Catatan (opsional)</label>
            <input type="text" id="catatan" name="catatan" value="{{ old('catatan') }}" class="form-control" placeholder="Kondisi fisik alat, dst">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-brand">Simpan Pengembalian</button>
            <a href="{{ route('petugas.pengembalian.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection