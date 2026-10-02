@extends('layouts.app')

@section('title', 'Edit Peminjaman')

@section('content')

@php
    $penuh    = $mode === 'penuh';
    $bisaTgl  = in_array($mode, ['penuh', 'perpanjang'], true);
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <span class="badge rounded-pill badge-brand px-3 py-2 mb-2">Area Admin</span>
        <h1 class="h4 mb-0">Edit Peminjaman - {{ $peminjaman->kode_peminjaman }}</h1>
    </div>
    <a href="{{ route('admin.peminjaman.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body peminjaman-4">

                @if ($mode === 'penuh')
                    <div class="alert alert-info small">Status <strong>pending</strong>: semua field boleh diubah.</div>
                @elseif ($mode === 'perpanjang')
                    <div class="alert alert-warning small">Status <strong>disetujui</strong>: stok sudah terpotong, jadi alat dan jumlah dikunci. Anda hanya bisa memperpanjang tanggal kembali dan mengubah catatan.</div>
                @else
                    <div class="alert alert-secondary small">Status <strong>{{ $peminjaman->status_peminjaman }}</strong>: data sudah final, hanya catatan yang bisa diubah.</div>
                @endif

                <form method="POST" action="{{ route('admin.peminjaman.update', ['id' => $peminjaman->id_peminjaman]) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="id_peminjam">Peminjam</label>
                        @if ($penuh)
                            <select id="id_peminjam" name="id_peminjam" class="form-select {{ errors()->has('id_peminjam') ? 'is-invalid' : '' }}" required>
                                @foreach ($peminjamList as $u)
                                    <option value="{{ $u->id }}" {{ old('id_peminjam', $peminjaman->id_peminjam) == $u->id ? 'selected' : '' }}>{{ $u->username }}</option>
                                @endforeach
                            </select>
                            @error('id_peminjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @else
                            <input type="text" class="form-control" value="{{ $peminjaman->peminjam->username ?? '-' }}" disabled>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="id_alat">Alat</label>
                        @if ($penuh)
                            <select id="id_alat" name="id_alat" class="form-select {{ errors()->has('id_alat') ? 'is-invalid' : '' }}" required>
                                @foreach ($alatList as $a)
                                    <option value="{{ $a->id_alat }}" {{ old('id_alat', $peminjaman->id_alat) == $a->id_alat ? 'selected' : '' }}>{{ $a->nama_alat }} (stok {{ $a->stok }})</option>
                                @endforeach
                            </select>
                            @error('id_alat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @else
                            <input type="text" class="form-control" value="{{ $peminjaman->alat->nama_alat ?? '-' }}" disabled>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="jumlah_pinjam">Jumlah</label>
                        <input type="number" id="jumlah_pinjam" name="jumlah_pinjam" min="1" value="{{ old('jumlah_pinjam', $peminjaman->jumlah_pinjam) }}" class="form-control {{ errors()->has('jumlah_pinjam') ? 'is-invalid' : '' }}" {{ $penuh ? 'required' : 'disabled' }}>
                        @error('jumlah_pinjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_pinjam">Tanggal Pinjam</label>
                            <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', $peminjaman->tanggal_pinjam) }}" class="form-control {{ errors()->has('tanggal_pinjam') ? 'is-invalid' : '' }}" {{ $penuh ? 'required' : 'disabled' }}>
                            @error('tanggal_pinjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_kembali_rencana">Rencana Kembali</label>
                            <input type="date" id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" value="{{ old('tanggal_kembali_rencana', $peminjaman->tanggal_kembali_rencana) }}" class="form-control {{ errors()->has('tanggal_kembali_rencana') ? 'is-invalid' : '' }}" {{ $bisaTgl ? 'required' : 'disabled' }}>
                            @error('tanggal_kembali_rencana') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="catatan">Catatan</label>
                        <input type="text" id="catatan" name="catatan" value="{{ old('catatan', $peminjaman->catatan) }}" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-brand w-100">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection