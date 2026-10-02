@extends('layouts.app')

@section('title', 'Data Peminjaman')

@section('content')

@php
    $warna = [
        'pending'               => 'warning',
        'disetujui'             => 'primary',
        'ditolak'               => 'danger',
        'menunggu_pengembalian' => 'info',
        'dikembalikan'          => 'success',
    ];
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <span class="badge rounded-pill badge-brand px-3 py-2 mb-2">Area Admin</span>
        <h1 class="h4 mb-0">Data Peminjaman</h1>
    </div>
    <a href="{{ route('admin.peminjaman.create') }}" class="btn btn-success">Tambah Peminjaman</a>
</div>

<form method="get" action="{{ route('admin.peminjaman.index') }}" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label class="form-label" for="q">Cari kode</label>
        <input type="text" id="q" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="PJM-...">
    </div>
    <div class="col-auto">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-select form-select-sm">
            <option value="">Semua</option>
            @foreach ($warna as $s => $w)
                <option value="{{ $s }}" {{ $statusFilter === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
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
                    <th>Jumlah</th>
                    <th>Tgl Pinjam</th>
                    <th>Rencana Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $no = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                @forelse ($data as $peminjaman)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $peminjaman->kode_peminjaman }}</td>
                    <td>{{ $peminjaman->peminjam->username ?? '-' }}</td>
                    <td>{{ $peminjaman->alat->nama_alat ?? '-' }}</td>
                    <td>{{ $peminjaman->jumlah_pinjam }}</td>
                    <td>{{ $peminjaman->tanggal_pinjam }}</td>
                    <td>{{ $peminjaman->tanggal_kembali_rencana }}</td>
                    <td><span class="badge bg-{{ $warna[$peminjaman->status_peminjaman] ?? 'secondary' }}">{{ $peminjaman->status_peminjaman }}</span></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('admin.peminjaman.edit', ['id' => $peminjaman->id_peminjaman]) }}" class="btn btn-sm btn-outline-brand">Edit</a>

                            @if (in_array($peminjaman->status_peminjaman, ['disetujui', 'menunggu_pengembalian'], true))
                                <form action="{{ route('admin.peminjaman.batalkan', ['id' => $peminjaman->id_peminjaman]) }}" method="post" onsubmit="return confirm('Batalkan peminjaman ini? Stok akan dikembalikan.')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-outline-warning">Batalkan</button>
                                </form>
                            @else
                                <form action="{{ route('admin.peminjaman.destroy', ['id' => $peminjaman->id_peminjaman]) }}" method="post" onsubmit="return confirm('Hapus peminjaman ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-secondary">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{!! $data->links() !!}
@endsection