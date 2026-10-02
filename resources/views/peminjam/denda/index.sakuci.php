@extends('layouts.app')
@section('title', config('app.name') . ' -- Denda Saya')
@section('content')
<h1>Denda Saya</h1>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="text-secondary small">Total belum lunas</div>
        <div class="h3 mb-0 {{ $tunggakan['total'] > 0 ? 'text-danger' : 'text-success' }}">
            Rp{{ number_format($tunggakan['total'], 0, ',', '.') }}
        </div>
        <div class="small text-secondary mt-1">Pembayaran dilakukan langsung ke petugas.</div>
    </div>
</div>

<table class="table table-sm align-middle table-hover table-bordered table-striped">
<tr>
    <th>No</th><th>Kode</th><th>Alat</th><th>Kondisi</th>
    <th>Telat</th><th>Kerusakan</th><th>Total</th><th>Status</th><th>Tgl Bayar</th>
</tr>
@php $no = 1; @endphp
@forelse ($data as $d)
<tr>
    <td class="text-center">{{ $no++ }}</td>
    <td>{{ $d->peminjaman->kode_peminjaman }}</td>
    <td>{{ $d->peminjaman->alat->nama_alat }}</td>
    <td>{{ $d->kondisi_alat }}</td>
    <td>Rp{{ number_format($d->denda_telat, 0, ',', '.') }} ({{ $d->hari_telat }} hari)</td>
    <td>Rp{{ number_format($d->denda_kerusakan, 0, ',', '.') }}</td>
    <td><strong>Rp{{ number_format($d->denda, 0, ',', '.') }}</strong></td>
    <td>
        <span class="badge bg-{{ $d->status_denda === 'lunas' ? 'success' : 'danger' }}">
            {{ $d->status_denda === 'lunas' ? 'Lunas' : 'Belum lunas' }}
        </span>
    </td>
    <td>{{ $d->tanggal_bayar ?? '-' }}</td>
</tr>
@empty
<tr><td colspan="9" class="text-center text-secondary">Kamu tidak punya denda.</td></tr>
@endforelse
</table>
{!! $data->links() !!}
@endsection