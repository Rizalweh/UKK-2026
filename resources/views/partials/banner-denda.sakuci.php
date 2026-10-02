@php
    $u = \App\Models\User::current();
    $tg = ($u && $u->role === 'peminjam') ? \App\Models\Pengembalian::tunggakan($u->id) : ['jumlah' => 0, 'total' => 0];
@endphp

@if ($tg['jumlah'] > 0)
<div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert">
    <span>
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Kamu punya <strong>{{ $tg['jumlah'] }}</strong> denda belum lunas,
        total <strong>Rp{{ number_format($tg['total'], 0, ',', '.') }}</strong>.
    </span>
    <a href="{{ route('peminjam.denda') }}" class="btn btn-sm btn-warning">Lihat rincian</a>
</div>
@endif