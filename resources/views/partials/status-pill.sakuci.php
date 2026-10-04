@php $__s = \App\Models\Peminjaman::STATUS[$status] ?? ['label' => $status, 'nada' => 'muted']; @endphp
<span class="pill tone-{{ $__s['nada'] }}">{{ $__s['label'] }}</span>