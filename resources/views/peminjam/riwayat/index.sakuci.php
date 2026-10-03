@extends('layouts.app')

@section('title', config('app.name') . ' -- Riwayat Peminjaman')

@section('content')
@include('partials.page-head', ['judul' => 'Riwayat Peminjaman Saya', 'aksi' => ['Pinjam alat', route('peminjam.alat.index')]])

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Kode</th><th>Alat</th><th>Jumlah</th><th>Pinjam</th><th>Rencana Kembali</th><th>Status</th><th>Kembali</th><th>Telat</th><th>Denda</th></tr>
        </thead>
        <tbody>
            @forelse ($data as $p)
            <tr>
                <td class="mono">{{ $p->kode_peminjaman }}</td>
                <td class="fw-medium">{{ $p->alat->nama_alat ?? '-' }}</td>
                <td>{{ $p->jumlah_pinjam }}</td>
                <td>{{ $p->tanggal_pinjam }}</td>
                <td>{{ $p->tanggal_kembali_rencana }}</td>
                <td>@include('partials.status-pill', ['status' => $p->status_peminjaman])</td>
                <td>{{ $p->pengembalian->tanggal_kembali_aktual ?? '-' }}</td>
                <td>{{ $p->pengembalian ? $p->pengembalian->hari_telat . ' hari' : '-' }}</td>
                <td>{{ $p->pengembalian ? 'Rp' . number_format($p->pengembalian->denda, 0, ',', '.') : '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="empty">Belum ada riwayat peminjaman.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{!! $data->links() !!}</div>
@endsection