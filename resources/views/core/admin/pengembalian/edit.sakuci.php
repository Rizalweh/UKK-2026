@extends('layouts.app')

@section('title', 'Edit Pengembalian')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <span class="badge rounded-pill badge-brand px-3 py-2 mb-2">Area Admin</span>
        <h1 class="h4 mb-0">{{ $kunci ? 'Detail' : 'Edit' }} Pengembalian - {{ $peminjaman->kode_peminjaman ?? '-' }}</h1>
    </div>
    <a href="{{ route('admin.pengembalian.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                @if ($kunci)
                    <div class="alert alert-success small">Denda sudah <strong>lunas</strong>, data dikunci dan hanya bisa dilihat.</div>
                @else
                    <div class="alert alert-info small">Denda dihitung ulang otomatis saat disimpan. Perubahan kondisi juga menyesuaikan stok.</div>
                @endif

                <form method="POST" action="{{ route('admin.pengembalian.update', ['id' => $pengembalian->id_pengembalian]) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="tanggal_kembali_aktual">Tanggal Kembali (Aktual)</label>
                        <input type="date" id="tanggal_kembali_aktual" name="tanggal_kembali_aktual" value="{{ old('tanggal_kembali_aktual', $pengembalian->tanggal_kembali_aktual) }}" class="form-control {{ errors()->has('tanggal_kembali_aktual') ? 'is-invalid' : '' }}" {{ $kunci ? 'disabled' : 'required' }}>
                        @error('tanggal_kembali_aktual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="kondisi_alat">Kondisi Alat</label>
                        <select id="kondisi_alat" name="kondisi_alat" class="form-select" {{ $kunci ? 'disabled' : 'required' }}>
                            @foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang'] as $val => $label)
                                <option value="{{ $val }}" {{ old('kondisi_alat', $pengembalian->kondisi_alat) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="catatan">Catatan</label>
                        <input type="text" id="catatan" name="catatan" value="{{ old('catatan', $pengembalian->catatan) }}" class="form-control" {{ $kunci ? 'disabled' : '' }}>
                    </div>

                    @if (! $kunci)
                        <button type="submit" class="btn btn-brand w-100">Simpan dan Hitung Ulang Denda</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h6 mb-3">Ringkasan</h2>
                <table class="table table-sm mb-0">
                    <tr><th>Peminjam</th><td>{{ $peminjaman->peminjam->username ?? '-' }}</td></tr>
                    <tr><th>Alat</th><td>{{ $peminjaman->alat->nama_alat ?? '-' }} ({{ $peminjaman->jumlah_pinjam }} unit)</td></tr>
                    <tr><th>Tanggal Pinjam</th><td>{{ $peminjaman->tanggal_pinjam }}</td></tr>
                    <tr><th>Rencana Kembali</th><td>{{ $peminjaman->tanggal_kembali_rencana }}</td></tr>
                    <tr><th>Hari Telat</th><td>{{ $pengembalian->hari_telat }} hari</td></tr>
                    <tr><th>Denda Telat</th><td>Rp{{ number_format($pengembalian->denda_telat, 0, ',', '.') }}</td></tr>
                    <tr><th>Denda Kerusakan</th><td>Rp{{ number_format($pengembalian->denda_kerusakan, 0, ',', '.') }}</td></tr>
                    <tr><th>Total Denda</th><td><strong>Rp{{ number_format($pengembalian->denda, 0, ',', '.') }}</strong></td></tr>
                    <tr><th>Tanggal Bayar</th><td>{{ $pengembalian->tanggal_bayar ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection