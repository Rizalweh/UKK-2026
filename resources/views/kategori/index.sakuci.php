@extends('layouts.app')

@section('title', config('app.name') . ' -- Kategori')

@section('content')
@include('partials.page-head', ['judul' => 'Kategori Alat', 'aksi' => ['Tambah Kategori', route('kategori.create')]])

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Kode</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse ($data as $k)
            <tr>
                <td>{{ $no++ }}</td>
                <td class="fw-medium">{{ $k->nama_kategori }}</td>
                <td class="mono">{{ $k->kode_kategori }}</td>
                <td class="text-secondary">{{ $k->keterangan ?: '-' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('kategori.edit', ['id' => $k->id_kategori]) }}" class="btn btn-sm btn-outline-brand">Edit</a>
                        <form action="{{ route('kategori.destroy', ['id' => $k->id_kategori]) }}" method="post" onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="empty">Belum ada kategori.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{!! $data->links() !!}</div>
@endsection