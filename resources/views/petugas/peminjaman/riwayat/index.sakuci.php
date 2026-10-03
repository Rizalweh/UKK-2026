@extends('layouts.app')

@section('title', config('app.name') . ' -- Riwayat Semua Peminjaman')

@section('content')
@include('partials.page-head', ['judul' => 'Riwayat Semua Peminjaman'])

<form method="get" action="{{ route('petugas.peminjaman.riwayat') }}" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label for="status" class="form-label small mb-1">Filter status</label>
        <select id="status" name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach (['pending' => 'Pending', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'menunggu_pengembalian' => 'Menunggu pengembalian', 'dikembalikan' => 'Dikembalikan'] as $val => $label)
                <option value="{{ $val }}" {{ $statusFilter === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</form>

@if ($jumlahLamaRiwayat > 0)
<div class="alert alert-warning d-flex align-items-center justify-content-between gap-3">
    <span>Riwayat selesai sudah mencapai batas. {{ $jumlahLamaRiwayat }} yang terlama bisa dibersihkan. Peminjaman yang masih berjalan atau dendanya belum lunas tidak ikut terhapus.</span>
    <form action="{{ route('petugas.peminjaman.riwayat.hapusRiwayatLama') }}" method="post" onsubmit="return confirm('Bersihkan riwayat selesai yang terlama? Tersisa 250 terbaru. Tindakan ini tidak bisa dibatalkan.')">
        @csrf
        @method('delete')
        <button type="submit" class="btn btn-sm btn-danger">Bersihkan</button>
    </form>
</div>
@endif

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Kode</th><th>Peminjam</th><th>Alat</th><th>Jumlah</th><th>Pinjam</th><th>Rencana</th><th>Status</th><th>Kembali</th><th>Telat</th><th>Denda</th></tr>
        </thead>
        <tbody>
            @forelse ($data as $p)
            <tr>
                <td class="mono">{{ $p->kode_peminjaman }}</td>
                <td>{{ $p->peminjam->username ?? '-' }}</td>
                <td>{{ $p->alat->nama_alat ?? '-' }}</td>
                <td>{{ $p->jumlah_pinjam }}</td>
                <td>{{ $p->tanggal_pinjam }}</td>
                <td>{{ $p->tanggal_kembali_rencana }}</td>
                <td>@include('partials.status-pill', ['status' => $p->status_peminjaman])</td>
                <td>{{ $p->pengembalian->tanggal_kembali_aktual ?? '-' }}</td>
                <td>{{ $p->pengembalian ? $p->pengembalian->hari_telat . ' hari' : '-' }}</td>
                <td>{{ $p->pengembalian ? 'Rp' . number_format($p->pengembalian->denda, 0, ',', '.') : '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="empty">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{!! $data->links() !!}</div>
@endsection