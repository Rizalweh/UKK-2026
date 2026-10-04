{{-- Satu alat di halaman sedang dipinjam. Variabel: $p, $urutan, $bisaKembali --}}
@php
    $alat     = $p->alat;
    $urlFoto  = $alat ? $alat->fotoUrl() : null;
    $tgl      = fn ($ymd) => date('d-m-Y', strtotime($ymd));
    $hariIni  = date('Y-m-d');
    $sisa     = (int) floor((strtotime($p->tanggal_kembali_rencana) - strtotime($hariIni)) / 86400);
    $telat    = $sisa < 0;
    $nada     = $telat ? 'danger' : ($sisa <= 1 ? 'warn' : 'muted');
    $teksSisa = $telat ? 'Telat ' . abs($sisa) . ' hari' : ($sisa === 0 ? 'Hari ini' : ($sisa === 1 ? 'Besok' : $sisa . ' hari lagi'));

    $lamaPinjam = max(1, (strtotime($p->tanggal_kembali_rencana) - strtotime($p->tanggal_pinjam)) / 86400);
    $terpakai   = (strtotime($hariIni) - strtotime($p->tanggal_pinjam)) / 86400;
    $persen     = (int) round(min(1, max(0, $terpakai / $lamaPinjam)) * 100);

    $dendaTelat = ($bisaKembali && $telat)
        ? \App\Models\Pengembalian::hitung($p->tanggal_kembali_rencana, $hariIni, 'baik', 0.0, (int) $p->jumlah_pinjam)['denda_telat']
        : 0;
@endphp
<article class="panel dipinjam-kartu {{ $bisaKembali ? 'tone-' . $nada : 'dipinjam-menunggu' }} muncul" style="--urutan: {{ $urutan }}">
    <div class="riwayat-alat">
        <div class="riwayat-foto">
            @if ($urlFoto)
                <img src="{{ $urlFoto }}" alt="Foto {{ $alat->nama_alat }}" loading="lazy">
            @else
                <i class="bi bi-tools"></i>
            @endif
        </div>
        <div>
            <h3 class="riwayat-nama">{{ $alat->nama_alat ?? '-' }} <span class="riwayat-jumlah">x{{ $p->jumlah_pinjam }}</span></h3>
            <span class="label-mono">{{ $p->kode_peminjaman }}</span>
        </div>
    </div>

    <div>
        @if ($bisaKembali)
            <p class="dipinjam-sisa">{{ $teksSisa }}</p>
            <div class="meter" aria-hidden="true" style="--isi: {{ $persen }}%"><span></span></div>
        @else
            @include('partials.jalur-status', ['status' => $p->status_peminjaman])
        @endif
        <dl class="riwayat-tgl mt-3">
            <div><dt class="label-mono">pinjam</dt><dd>{{ $tgl($p->tanggal_pinjam) }}</dd></div>
            <div><dt class="label-mono">rencana kembali</dt><dd>{{ $tgl($p->tanggal_kembali_rencana) }}</dd></div>
        </dl>
    </div>

    @if ($bisaKembali)
    <form method="POST" action="{{ route('peminjam.kembali', ['id' => $p->id_peminjaman]) }}" onsubmit="return confirm('Ajukan pengembalian alat ini?')">
        @csrf
        @method('PUT')
        <button type="submit" class="btn btn-brand rounded-pill px-4">Ajukan pengembalian</button>
    </form>
    @endif

    @if ($dendaTelat > 0)
    <div class="riwayat-bawah">
        <span class="pill tone-danger">Perkiraan denda telat Rp{{ number_format($dendaTelat, 0, ',', '.') }}</span>
        <span class="riwayat-catatan">Belum termasuk denda kerusakan. Angka pasti dihitung petugas saat alat dikembalikan.</span>
    </div>
    @endif
</article>