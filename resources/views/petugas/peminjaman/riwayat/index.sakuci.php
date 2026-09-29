@extends('layouts.app')

@section('title', config('app.name') . ' -- Riwayat Semua Peminjaman')

@section('content')
<h1>Riwayat Semua Peminjaman</h1>

<form method="get" action="{{ route('petugas.peminjaman.riwayat') }}" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label for="status" class="form-label">Filter Status</label>
        <select id="status" name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>Semua Status</option>
            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="disetujui" {{ $statusFilter === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
            <option value="ditolak" {{ $statusFilter === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
            <option value="menunggu_pengembalian" {{ $statusFilter === 'menunggu_pengembalian' ? 'selected' : '' }}>Menunggu Pengembalian</option>
            <option value="dikembalikan" {{ $statusFilter === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
        </select>
    </div>
</form>

@php
    $warna = [
        'pending'               => 'warning',
        'disetujui'             => 'primary',
        'ditolak'               => 'danger',
        'menunggu_pengembalian' => 'info',
        'dikembalikan'          => 'success',
    ];
@endphp

<table class="table table-sm align-middle table-hover table-bordered table-striped">
<tr>
    <th>No</th>
    <th>Kode</th>
    <th>Peminjam</th>
    <th>Alat</th>
    <th>Jumlah</th>
    <th>Tgl Pinjam</th>
    <th>Rencana Kembali</th>
    <th>Status</th>
    <th>Kembali (Aktual)</th>
    <th>Hari Telat</th>
    <th>Denda</th>
</tr>
@php $no = 1; @endphp
@forelse ($data as $p)
<tr>
    <td class="text-center">{{ $no++ }}</td>
    <td>{{ $p->kode_peminjaman }}</td>
    <td>{{ $p->peminjam->username }}</td>
    <td>{{ $p->alat->nama_alat }}</td>
    <td>{{ $p->jumlah_pinjam }}</td>
    <td>{{ $p->tanggal_pinjam }}</td>
    <td>{{ $p->tanggal_kembali_rencana }}</td>
    <td><span class="badge bg-{{ $warna[$p->status_peminjaman] ?? 'secondary' }}">{{ $p->status_peminjaman }}</span></td>
    <td>{{ $p->pengembalian->tanggal_kembali_aktual ?? '-' }}</td>
    <td>{{ $p->pengembalian->hari_telat ?? '-' }}</td>
    <td>
        @if ($p->pengembalian)
            Rp{{ number_format($p->pengembalian->denda, 0, ',', '.') }}
        @else
            -
        @endif
    </td>
</tr>
@empty
<tr><td colspan="11" class="text-center text-secondary">Tidak ada data.</td></tr>
@endforelse
</table>
{!! $data->links() !!}
@endsection