@extends('layouts.app')

@section('title', config('app.name') . ' -- Kerangka PHP Ringan')

@section('content')
<table class = "table table-sm align-middle"> 
<tr>
    <th>No</th>
    <th>Kode Kategori     
    <th>Nama Kategori</th>
    <th>Keterangan</th>
    <th>Aksi</th>
    <th>Aksi</th>
</tr>
@php $no = 1; @endphp
@foreach ($kategori as $kategoris)
<tr>
    <td> {{ $no++ }} </td>
    <td> {{ $kategoris->kode_kategori }} </td>
    <td> {{ $kategoris->nama_kategori }} </td>
    <td> {{ $kategoris->keterangan }} </td>
    <td><button class = "btn btn-primary btn-sm">Edit</button></td>
    <td><button class = "btn btn-danger btn-sm">Hapus</button></td>
</tr>
@endforeach
</table>
@endsection