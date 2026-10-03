@extends('layouts.app')

@section('title', config('app.name') . ' -- Data Alat')

@section('content')
@php
    $tone = ['Baik' => 'ok', 'Rusak Ringan' => 'warn', 'Rusak Berat' => 'danger'];
@endphp
@include('partials.page-head', ['judul' => 'Data Alat', 'aksi' => ['Tambah Alat', route('alat.create')]])

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Alat</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Kondisi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $alat)
            <tr>
                <td>
                    @if ($alat->foto_alat)
                        <img src="/uploads/foto_alat/{{ $alat->foto_alat }}" alt="Foto {{ $alat->nama_alat }}" class="thumb">
                    @else
                        <span class="thumb"><i class="bi bi-image"></i></span>
                    @endif
                </td>
                <td><span class="fw-medium">{{ $alat->nama_alat }}</span><span class="sub mono">{{ $alat->kode_alat }}</span></td>
                <td>{{ $alat->kategori->nama_kategori ?? '-' }}</td>
                <td>Rp{{ number_format($alat->harga_alat, 0, ',', '.') }}</td>
                <td class="{{ $alat->stok <= 0 ? 'text-danger fw-semibold' : '' }}">{{ $alat->stok }}</td>
                <td><span class="pill tone-{{ $tone[$alat->kondisi] ?? 'muted' }}">{{ $alat->kondisi }}</span></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('alat.edit', ['id' => $alat->id_alat]) }}" class="btn btn-sm btn-outline-brand">Edit</a>
                        <form action="{{ route('alat.destroy', ['id' => $alat->id_alat]) }}" method="POST" onsubmit="return confirm('Hapus data alat ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty">Belum ada alat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{!! $data->links() !!}</div>
@endsection