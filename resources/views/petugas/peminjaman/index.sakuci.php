@extends('layouts.app')

@section('title', config('app.name') . ' -- Pengajuan Peminjaman')

@section('content')
@include('partials.page-head', [
    'judul' => 'Pengajuan Peminjaman',
    'sub' => 'Setujui atau tolak pengajuan yang masuk. Stok berkurang setelah disetujui.',
])

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Peminjam</th>
                <th>Alat</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Keperluan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $p)
            <tr>
                <td class="mono">{{ $p->kode_peminjaman }}</td>
                <td>{{ $p->peminjam->username ?? '-' }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if ($p->alat->foto_alat ?? null)
                            <img src="/uploads/foto_alat/{{ $p->alat->foto_alat }}" alt="" class="thumb">
                        @else
                            <span class="thumb"><i class="bi bi-image"></i></span>
                        @endif
                        <span>
                            <span class="fw-medium">{{ $p->alat->nama_alat ?? '-' }}</span>
                            <span class="sub">
                                {{ $p->alat->kategori->nama_kategori ?? 'Tanpa kategori' }}
                                &middot; stok {{ $p->alat->stok ?? 0 }}
                                &middot; {{ strtolower($p->alat->kondisi ?? '') }}
                            </span>
                        </span>
                    </div>
                </td>
                <td>{{ $p->jumlah_pinjam }}</td>
                <td>
                    {{ $p->tanggal_pinjam }}
                    <span class="sub">sampai {{ $p->tanggal_kembali_rencana }}</span>
                </td>
                <td class="text-secondary">{{ $p->catatan ?: '-' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <form action="{{ route('petugas.peminjaman.setujui', ['id' => $p->id_peminjaman]) }}" method="post">
                            @csrf
                            @method('put')
                            <button class="btn btn-sm btn-success">Setujui</button>
                        </form>
                        <form action="{{ route('petugas.peminjaman.tolak', ['id' => $p->id_peminjaman]) }}" method="post" onsubmit="return confirm('Tolak pengajuan ini?')">
                            @csrf
                            @method('put')
                            <button class="btn btn-sm btn-outline-danger">Tolak</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty">Tidak ada pengajuan yang menunggu.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">
    {!! $data->links() !!}
</div>
@endsection