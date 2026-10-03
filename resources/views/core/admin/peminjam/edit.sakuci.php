@extends('layouts.app')

@section('title', 'Edit Peminjam')

@section('content')
@include('partials.page-head', ['judul' => 'Edit Peminjam', 'sub' => $user->username, 'kembali' => route('admin.peminjam.index')])

<form action="{{ route('admin.peminjam.update', ['user' => $user->id]) }}" method="post" class="form-card">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control">
            <div class="form-text">Kosongkan jika tidak diganti.</div>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $user->profil?->nama_lengkap) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="nis">NIS</label>
            <input type="text" id="nis" name="nis" class="form-control" value="{{ old('nis', $user->profil?->nis) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="no_hp">No. HP</label>
            <input type="text" id="no_hp" name="no_hp" class="form-control" value="{{ old('no_hp', $user->profil?->no_hp) }}" required>
        </div>
        <div class="col-12">
            <label class="form-label" for="alamat">Alamat</label>
            <input type="text" id="alamat" name="alamat" class="form-control" value="{{ old('alamat', $user->profil?->alamat) }}">
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-brand">Simpan</button>
        <a href="{{ route('admin.peminjam.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
@endsection