@extends('layouts.app')

@section('title', config('app.name') . ' -- Pengembalian Alat')

@section('content')
@php $hariIni = strtotime(date('Y-m-d')); @endphp
@include('partials.page-head', ['judul' => 'Pengembalian Alat', 'sub' => 'Peminjaman yang sudah diajukan kembali oleh peminjam dan menunggu diverifikasi.'])

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Kode</th><th>Peminjam</th><th>Alat</th><th>Jumlah</th><th>Rencana Kembali</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($data as $p)
            @php $telat = (int) floor(($hariIni - strtotime($p->tanggal_kembali_rencana)) / 86400); @endphp
            <tr>
                <td class="mono">{{ $p->kode_peminjaman }}</td>
                <td>{{ $p->peminjam->username ?? '-' }}</td>
                <td class="fw-medium">{{ $p->alat->nama_alat ?? '-' }}</td>
                <td>{{ $p->jumlah_pinjam }}</td>
                <td>
                    {{ $p->tanggal_kembali_rencana }}
                    @if ($telat > 0)<span class="pill tone-danger ms-1">Telat {{ $telat }} hari</span>@endif
                </td>
                <td><a href="{{ route('petugas.pengembalian.create', ['id' => $p->id_peminjaman]) }}" class="btn btn-sm btn-brand">Proses</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="empty">Tidak ada pengembalian yang menunggu.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{!! $data->links() !!}</div>
@endsection