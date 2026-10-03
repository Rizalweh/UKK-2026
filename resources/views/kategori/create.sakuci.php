@extends('layouts.app')

@section('title', config('app.name') . ' -- Tambah Kategori')

@section('content')
@include('partials.page-head', ['judul' => 'Tambah Kategori', 'kembali' => route('kategori.index')])

<form action="{{ route('kategori.store') }}" method="post" class="form-card">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="nama_kategori">Nama Kategori</label>
        <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" value="{{ old('nama_kategori') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="kode_kategori">Kode Kategori</label>
        <input type="text" id="kode_kategori" name="kode_kategori" class="form-control" maxlength="10" value="{{ old('kode_kategori') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="keterangan">Keterangan</label>
        <textarea id="keterangan" name="keterangan" rows="3" class="form-control">{{ old('keterangan') }}</textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-brand">Simpan</button>
        <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
@endsection