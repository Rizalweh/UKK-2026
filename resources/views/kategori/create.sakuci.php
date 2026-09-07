@extends('layouts.app')

@section('title', config('app.name') . ' -- Kerangka PHP Ringan')

@section('content')
<h1>Tambah Kategori</h1>
<form action="{{ route('kategori.store') }}" method="POST" class="d-flex flex-column gap-2">
    @csrf

    <label>Nama Kategori</label>
    <input type="text" name="nama_kategori" id="nama_kategori" class="form-control" value="{{ old('nama_kategori') }}" required>

    <label>Kode Kategori</label>
    <input type="text" name="kode_kategori" id="kode_kategori" class="form-control" value="{{ old('kode_kategori') }}" required>

    <label>Keterangan</label>
    <input type="text" name="keterangan" id="keterangan" class="form-control" value="{{ old('keterangan') }}" required>
    <button type="submit" class="btn btn-primary">Simpan</button>
</form>
@endsection