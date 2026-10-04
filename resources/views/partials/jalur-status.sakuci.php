{{-- Jalur status peminjaman. Variabel: $status --}}
@php
    $__peta    = \App\Models\Peminjaman::STATUS;
    $__berhenti = in_array($status, ['ditolak', 'dibatalkan'], true);
    $__langkah = $__berhenti ? ['pending', $status] : \App\Models\Peminjaman::JALUR;
    $__posisi  = $__berhenti ? 1 : (int) array_search($status, $__langkah, true);
@endphp
<ol class="jalur tone-{{ $__peta[$status]['nada'] ?? 'muted' }}">
    @foreach ($__langkah as $__i => $__kunci)
        <li class="{{ $__i < $__posisi ? 'lewat' : ($__i === $__posisi ? 'kini' : '') }}">
            <span class="titik-jalur"></span>
            <span class="label-mono">{{ $__peta[$__kunci]['label'] }}</span>
        </li>
    @endforeach
</ol>