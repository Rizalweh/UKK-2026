@extends('layouts.app')

@section('title', config('app.name') . ' -- Sedang Dipinjam')

@section('content')
@php $hariIni = strtotime(date('Y-m-d')); @endphp
@include('partials.page-head', ['judul' => 'Alat yang Sedang Saya Pinjam', 'sub' => 'Ajukan pengembalian saat alat selesai dipakai. Petugas akan memverifikasi kondisinya.'])

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Kode</th><th>Alat</th><th>Jumlah</th><th>Pinjam</th><th>Rencana Kembali</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($dipinjam as $p)
            @php $sisa = (int) floor((strtotime($p->tanggal_kembali_rencana) - $hariIni) / 86400); @endphp
            <tr>
                <td class="mono">{{ $p->kode_peminjaman }}</td>
                <td class="fw-medium">{{ $p->alat->nama_alat ?? '-' }}</td>
                <td>{{ $p->jumlah_pinjam }}</td>
                <td>{{ $p->tanggal_pinjam }}</td>
                <td>
                    {{ $p->tanggal_kembali_rencana }}
                    @if ($sisa < 0)
                        <span class="pill tone-danger ms-1">Telat {{ abs($sisa) }} hari</span>
                    @elseif ($sisa <= 1)
                        <span class="pill tone-warn ms-1">{{ $sisa === 0 ? 'Hari ini' : 'Besok' }}</span>
                    @else
                        <span class="pill tone-muted ms-1">{{ $sisa }} hari lagi</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('peminjam.kembali', ['id' => $p->id_peminjaman]) }}" method="post" onsubmit="return confirm('Ajukan pengembalian alat ini?')">
                        @csrf
                        @method('put')
                        <button type="submit" class="btn btn-sm btn-brand">Ajukan Pengembalian</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="empty">Tidak ada alat yang sedang dipinjam.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h2 class="h6 mt-4 mb-3">Menunggu verifikasi petugas</h2>
<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Kode</th><th>Alat</th><th>Jumlah</th><th>Rencana Kembali</th></tr>
        </thead>
        <tbody>
            @forelse ($menunggu as $p)
            <tr>
                <td class="mono">{{ $p->kode_peminjaman }}</td>
                <td class="fw-medium">{{ $p->alat->nama_alat ?? '-' }}</td>
                <td>{{ $p->jumlah_pinjam }}</td>
                <td>{{ $p->tanggal_kembali_rencana }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="empty">Tidak ada pengajuan pengembalian yang menunggu.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection