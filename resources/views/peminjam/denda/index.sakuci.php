@extends('layouts.app')

@section('title', config('app.name') . ' -- Denda Saya')

@section('content')
@include('partials.page-head', ['judul' => 'Denda Saya'])

<div class="form-card mb-3" style="max-width: none">
    <div class="small text-secondary">Total belum lunas</div>
    <div class="h3 mb-0 {{ $tunggakan['total'] > 0 ? 'text-danger' : 'text-success' }}">
        Rp{{ number_format($tunggakan['total'], 0, ',', '.') }}
    </div>
    <div class="small text-secondary mt-1">Pembayaran dilakukan langsung ke petugas.</div>
</div>

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Kode</th><th>Alat</th><th>Kondisi</th><th>Telat</th><th>Kerusakan</th><th>Total</th><th>Status</th><th>Tgl Bayar</th></tr>
        </thead>
        <tbody>
            @forelse ($data as $d)
            <tr>
                <td class="mono">{{ $d->peminjaman->kode_peminjaman ?? '-' }}</td>
                <td>{{ $d->peminjaman->alat->nama_alat ?? '-' }}</td>
                <td>{{ str_replace('_', ' ', $d->kondisi_alat) }}</td>
                <td>Rp{{ number_format($d->denda_telat, 0, ',', '.') }}<span class="sub">{{ $d->hari_telat }} hari</span></td>
                <td>Rp{{ number_format($d->denda_kerusakan, 0, ',', '.') }}</td>
                <td class="fw-semibold">Rp{{ number_format($d->denda, 0, ',', '.') }}</td>
                <td><span class="pill tone-{{ $d->status_denda === 'lunas' ? 'ok' : 'danger' }}">{{ $d->status_denda === 'lunas' ? 'Lunas' : 'Belum lunas' }}</span></td>
                <td>{{ $d->tanggal_bayar ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="empty">Kamu tidak punya denda.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{!! $data->links() !!}</div>
@endsection