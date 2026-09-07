@extends('layouts.app')

@section('title', config('app.name') . ' -- Kerangka PHP Ringan')

@section('content')
<h1></h1>Tambah Kategori</h1>
<form action="{{ route('kategori.create') }}" method="POST" class="d-flex flex-column gap-2">
    @csrf
    @php
    $no=1;
    @endphp
    
    <label>Nama Kategori</label>
    <input type="text" name="name" class="form-control" required>

    <label>Kode Kategori</label>
    <input type="text" name="code" class="form-control" required>

    <label>Keterangan</label>
    <input type="text" name="keterangan" class="form-control" required>

    <button type="submit" class="btn btn-primary">Simpan</button>
</form>
@endsection