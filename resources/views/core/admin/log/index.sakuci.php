@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <span class="badge rounded-pill badge-brand px-3 py-2 mb-2">Area Admin</span>
            <h1 class="h4 mb-0">Log Aktivitas</h1>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $log)
                    <tr>
                        <td>{{ $log->waktu }}</td>
                        <td>{{ $log->user->username ?? '(user dihapus)' }}</td>
                        <td>{{ $log->aktivitas }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-secondary">Belum ada aktivitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {!! $data->links() !!}
@endsection