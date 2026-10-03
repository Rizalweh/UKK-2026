@php
    $__peta = [
        'pending'               => ['Pending', 'warn'],
        'disetujui'             => ['Disetujui', 'info'],
        'ditolak'               => ['Ditolak', 'danger'],
        'menunggu_pengembalian' => ['Menunggu pengembalian', 'warn'],
        'dikembalikan'          => ['Dikembalikan', 'ok'],
    ];
    $__s = $__peta[$status] ?? [$status, 'muted'];
@endphp
<span class="pill tone-{{ $__s[1] }}">{{ $__s[0] }}</span>