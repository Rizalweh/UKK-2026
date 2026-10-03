{{-- Aturan sebelum meminjam. Semua angka dibaca dari konstanta model Pengembalian. --}}
@php
    $tarifDendaPerHari = number_format(\App\Models\Pengembalian::TARIF_DENDA_PER_HARI, 0, ',', '.');
    $persenKerusakan   = \App\Models\Pengembalian::PERSEN_KERUSAKAN;
@endphp
<section class="pinjam-aturan" aria-label="Aturan peminjaman">
    <h3>Sebelum meminjam</h3>
    <ul>
        <li>Pengajuan diperiksa petugas. Stok baru berkurang setelah disetujui.</li>
        <li>Terlambat dikembalikan: denda Rp{{ $tarifDendaPerHari }} per hari.</li>
        @foreach ($persenKerusakan as $namaKondisi => $persenDenda)
            @if ($persenDenda > 0)
                <li>{{ ucfirst(str_replace('_', ' ', $namaKondisi)) }}: denda {{ (int) round($persenDenda * 100) }}% dari harga alat.</li>
            @endif
        @endforeach
        <li>Denda dibayar langsung ke petugas.</li>
    </ul>
</section>