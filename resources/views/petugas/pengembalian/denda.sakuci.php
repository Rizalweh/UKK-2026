@extends('layouts.app')

@section('title', config('app.name') . ' -- Denda Belum Lunas')

@section('content')
@include('partials.page-head', ['judul' => 'Denda Belum Lunas', 'sub' => 'Tandai lunas setelah uang denda diterima langsung dari peminjam.'])

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Kode</th><th>Peminjam</th><th>Alat</th><th>Kondisi</th><th>Denda Telat</th><th>Denda Kerusakan</th><th>Total</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($data as $d)
            <tr>
                <td class="mono">{{ $d->peminjaman->kode_peminjaman ?? '-' }}</td>
                <td>{{ $d->peminjaman->peminjam->username ?? '-' }}</td>
                <td>{{ $d->peminjaman->alat->nama_alat ?? '-' }}</td>
                <td>{{ str_replace('_', ' ', $d->kondisi_alat) }}</td>
                <td>Rp{{ number_format($d->denda_telat, 0, ',', '.') }}<span class="sub">{{ $d->hari_telat }} hari</span></td>
                <td>Rp{{ number_format($d->denda_kerusakan, 0, ',', '.') }}</td>
                <td class="fw-semibold text-danger">Rp{{ number_format($d->denda, 0, ',', '.') }}</td>
                <td>
                    <form action="{{ route('petugas.denda.bayar', ['id' => $d->id_pengembalian]) }}" method="post" onsubmit="return confirm('Konfirmasi: uang denda sudah diterima?')">
                        @csrf
                        @method('put')
                        <button class="btn btn-sm btn-success" type="submit">Tandai Lunas</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="empty">Tidak ada denda tertunggak.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{!! $data->links() !!}</div>
@endsection