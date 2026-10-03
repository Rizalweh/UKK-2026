@extends('layouts.app')

@section('title', 'Data Peminjam')

@section('content')
@include('partials.page-head', ['judul' => 'Data Peminjam', 'aksi' => ['Tambah Peminjam', route('admin.peminjam.create')]])

<div class="tbl-card table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Username</th><th>Nama Lengkap</th><th>NIS</th><th>No. HP</th><th>Alamat</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @forelse ($data as $u)
            <tr>
                <td class="fw-medium">{{ $u->username }}</td>
                <td>{{ $u->profil->nama_lengkap ?? '-' }}</td>
                <td class="mono">{{ $u->profil->nis ?? '-' }}</td>
                <td>{{ $u->profil->no_hp ?? '-' }}</td>
                <td class="text-secondary">{{ $u->profil->alamat ?? '-' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.peminjam.edit', ['user' => $u->id]) }}" class="btn btn-sm btn-outline-brand">Edit</a>
                        <form action="{{ route('admin.peminjam.destroy', ['user' => $u->id]) }}" method="post" onsubmit="return confirm('Hapus peminjam ini?')">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="empty">Belum ada peminjam.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pager">{!! $data->links() !!}</div>
@endsection