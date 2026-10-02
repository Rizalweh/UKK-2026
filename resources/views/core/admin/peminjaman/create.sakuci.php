@extends('layouts.app')

@section('title', 'Tambah Peminjaman')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <span class="badge rounded-pill badge-brand px-3 py-2 mb-2">Area Admin</span>
        <h1 class="h4 mb-0">Tambah Peminjaman</h1>
    </div>
    <a href="{{ route('admin.peminjaman.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <p class="small text-secondary">Peminjaman baru berstatus <strong>pending</strong> dan tetap menunggu persetujuan petugas.</p>

                <form method="POST" action="{{ route('admin.peminjaman.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="id_peminjam">Peminjam</label>
                        <select id="id_peminjam" name="id_peminjam" class="form-select {{ errors()->has('id_peminjam') ? 'is-invalid' : '' }}" required>
                            <option value="">Pilih peminjam</option>
                            @foreach ($peminjamList as $u)
                                <option value="{{ $u->id }}" {{ old('id_peminjam') == $u->id ? 'selected' : '' }}>{{ $u->username }}{{ $u->profil->nama_lengkap ?? '' ? ' - ' . $u->profil->nama_lengkap : '' }}</option>
                            @endforeach
                        </select>
                        @error('id_peminjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="id_alat">Alat</label>
                        <select id="id_alat" name="id_alat" class="form-select {{ errors()->has('id_alat') ? 'is-invalid' : '' }}" required>
                            <option value="">Pilih alat</option>
                            @foreach ($alatList as $a)
                                <option value="{{ $a->id_alat }}" {{ old('id_alat') == $a->id_alat ? 'selected' : '' }}>{{ $a->nama_alat }} (stok {{ $a->stok }})</option>
                            @endforeach
                        </select>
                        @error('id_alat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="jumlah_pinjam">Jumlah</label>
                        <input type="number" id="jumlah_pinjam" name="jumlah_pinjam" min="1" value="{{ old('jumlah_pinjam', 1) }}" class="form-control {{ errors()->has('jumlah_pinjam') ? 'is-invalid' : '' }}" required>
                        @error('jumlah_pinjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_pinjam">Tanggal Pinjam</label>
                            <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" class="form-control {{ errors()->has('tanggal_pinjam') ? 'is-invalid' : '' }}" required>
                            @error('tanggal_pinjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_kembali_rencana">Rencana Kembali</label>
                            <input type="date" id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" value="{{ old('tanggal_kembali_rencana') }}" class="form-control {{ errors()->has('tanggal_kembali_rencana') ? 'is-invalid' : '' }}" required>
                            @error('tanggal_kembali_rencana') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="catatan">Catatan / Keperluan (opsional)</label>
                        <input type="text" id="catatan" name="catatan" value="{{ old('catatan') }}" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-brand w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection