@extends('layouts.app')

@section('title', config('app.name') . ' -- Laporan Peminjaman')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h1 class="h4 mb-0">Laporan Peminjaman</h1>
    <button type="button" class="btn btn-success" onclick="window.print()">
        <i class="bi bi-printer"></i> Cetak
    </button>
</div>

<form method="get" action="{{ route('petugas.laporan.index') }}" class="row g-2 align-items-end mb-3 no-print">
    <div class="col-auto">
        <label class="form-label">Dari</label>
        <input type="date" name="dari" value="{{ $dari }}" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <label class="form-label">Sampai</label>
        <input type="date" name="sampai" value="{{ $sampai }}" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <label class="form-label">Status</label>
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua</option>
            @foreach (['pending', 'disetujui', 'ditolak', 'menunggu_pengembalian', 'dikembalikan'] as $s)
                <option value="{{ $s }}" {{ $statusFilter === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
    </div>
</form>

{{-- kop laporan saat dicetak --}}
<div class="text-center mb-3">
    <h2 class="h5 mb-0">LAPORAN PEMINJAMAN ALAT</h2>
    <div class="small">SARPRAS TECH</div>
    <div class="small">
        Periode: {{ $dari ?: '-' }} s/d {{ $sampai ?: '-' }}
        | Status: {{ $statusFilter ?: 'Semua' }}
    </div>
</div>

<table class="table table-sm table-bordered align-middle">
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
    <th>Telat</th>
    <th>Denda</th>
</tr>
@php $no = 1; @endphp
@forelse ($data as $p)
<tr>
    <td class="text-center">{{ $no++ }}</td>
    <td>{{ $p->kode_peminjaman }}</td>
    <td>{{ $p->peminjam->username ?? '-' }}</td>
    <td>{{ $p->alat->nama_alat ?? '-' }}</td>
    <td>{{ $p->jumlah_pinjam }}</td>
    <td>{{ $p->tanggal_pinjam }}</td>
    <td>{{ $p->tanggal_kembali_rencana }}</td>
    <td>{{ $p->status_peminjaman }}</td>
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
<tr>
    <th colspan="10" class="text-end">Total Denda</th>
    <th>Rp{{ number_format($totalDenda, 0, ',', '.') }}</th>
</tr>
</table>

<div class="d-flex justify-content-end mt-4">
    <div class="text-center">
        <div>Dicetak: {{ date('d-m-Y') }}</div>
        <div class="mb-5">Petugas</div>
        <div>( {{ $petugas->username }} )</div>
    </div>
</div>
@endsection