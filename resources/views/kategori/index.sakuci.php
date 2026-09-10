@extends('layouts.app')

@section('title', config('app.name') . ' -- Kerangka PHP Ringan')

@section('content')
<h1>Daftar Kategori</h1>
<a href="{{ route('kategori.create') }}" class="btn btn-danger mb-3">Tambah Kategori</a>

<table class = "table table-sm align-middle table-hover table-bordered table-striped"> 
<tr>
    <th>No</th>
    <th>nama kategori</th>
    <th>kode kategori</th>
    <th>Keterangan</th>
    <th>Aksi</th>
</tr>
@php $no = 1; @endphp
@foreach ($data as $kategoris)
<tr>
    <td> {{ $no++ }} </td>
    <td> {{ $kategoris->nama_kategori }} </td>
    <td> {{ $kategoris->kode_kategori }} </td>
    <td> {{ $kategoris->keterangan }} </td>
    <td>
         <a href="{{ route('kategori.edit', ['kategori' => $kategoris->id_kategori]) }}" class="btn btn-primary btn-sm">Edit</a>
         <form action="{{ route('kategori.destroy', ['kategori' => $kategoris->id_kategori]) }}" method="POST" onsubmit="return confirm('kamu yakin banget mau hapus?')" class="d-inline" >
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Hapus</button></td>
</tr>
@endforeach
</table>
{!! $data->links() !!}
@endsection