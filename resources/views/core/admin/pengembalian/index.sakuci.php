@extends('layouts.app')

@section('title', 'Data Pengembalian')

@section('content')

@php
    $labelDenda = ['tidak_ada' => 'Tidak ada', 'belum_lunas' => 'Belum lunas', 'lunas' => 'Lunas'];
    $warnaDenda = ['tidak_ada' => 'secondary', 'belum_lunas' => 'danger', 'lunas' => 'success'];
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <span class="badge rounded-pill badge-brand px-3 py-2 mb-2">Area Admin</span>
        <h1 class="h4 mb-0">Data Pengembalian</h1>
    </div>
    <a href="{{ route('admin.pengembalian.create') }}" class="btn btn-success">Catat Pengembalian</a>
</div>

<form method="get" action="{{ route('admin.pengembalian.index') }}" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label class="form-label" for="status">Status denda</label>
        <select id="status" name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua</option>
            @foreach ($labelDenda as $val => $label)
                <option value="{{ $val }}" {{ $statusFilter === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 table-responsive">
        <table class="table table-sm align-middle table-hover table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Peminjam</th>
                    <th>Alat</th>
                    <th>Kembali (Aktual)</th>
                    <th>Kondisi</th>
                    <th>Telat</th>
                    <th>Denda Telat</th>
                    <th>Denda Kerusakan</th>
                    <th>Total</th>
                    <th>Status Denda</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $no = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                @forelse ($data as $d)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $d->peminjaman->kode_peminjaman ?? '-' }}</td>
                    <td>{{ $d->peminjaman->peminjam->username ?? '-' }}</td>
                    <td>{{ $d->peminjaman->alat->nama_alat ?? '-' }}</td>
                    <td>{{ $d->tanggal_kembali_aktual }}</td>
                    <td>{{ $d->kondisi_alat }}</td>
                    <td>{{ $d->hari_telat }} hari</td>
                    <td>Rp{{ number_format($d->denda_telat, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($d->denda_kerusakan, 0, ',', '.') }}</td>
                    <td><strong>Rp{{ number_format($d->denda, 0, ',', '.') }}</strong></td>
                    <td><span class="badge bg-{{ $warnaDenda[$d->status_denda] ?? 'secondary' }}">{{ $labelDenda[$d->status_denda] ?? $d->status_denda }}</span></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @if ($d->status_denda === 'lunas')
                                <a href="{{ route('admin.pengembalian.edit', ['id' => $d->id_pengembalian]) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                            @else
                                <a href="{{ route('admin.pengembalian.edit', ['id' => $d->id_pengembalian]) }}" class="btn btn-sm btn-outline-brand">Edit</a>
                                <form action="{{ route('admin.pengembalian.destroy', ['id' => $d->id_pengembalian]) }}" method="post" onsubmit="return confirm('Batalkan pengembalian ini? Stok ditarik dan status kembali ke menunggu pengembalian.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-secondary">Belum ada data pengembalian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{!! $data->links() !!}
@endsection