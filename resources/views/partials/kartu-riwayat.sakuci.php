{{-- Satu peminjaman di halaman riwayat. Variabel: $p, $urutan --}}
@php
    $alat     = $p->alat;
    $kembali  = $p->pengembalian;
    $urlFoto  = $alat ? $alat->fotoUrl() : null;
    $tgl      = fn ($ymd) => date('d-m-Y', strtotime($ymd));
    $punyaDenda = $kembali && $kembali->denda > 0;
    $labelCatatan = $p->status_peminjaman === 'ditolak' ? 'Alasan penolakan' : 'Keperluan';
    $bisaBatal = $p->status_peminjaman === 'pending';
@endphp
<article class="panel riwayat-kartu muncul" style="--urutan: {{ $urutan }}">
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

    <div>@include('partials.jalur-status', ['status' => $p->status_peminjaman])</div>

    <dl class="riwayat-tgl">
        <div><dt class="label-mono">pinjam</dt><dd>{{ $tgl($p->tanggal_pinjam) }}</dd></div>
        <div><dt class="label-mono">rencana kembali</dt><dd>{{ $tgl($p->tanggal_kembali_rencana) }}</dd></div>
        @if ($kembali)
            <div><dt class="label-mono">kembali</dt><dd>{{ $tgl($kembali->tanggal_kembali_aktual) }}</dd></div>
            <div><dt class="label-mono">telat</dt><dd class="{{ $kembali->hari_telat > 0 ? 'text-danger' : '' }}">{{ $kembali->hari_telat }} hari</dd></div>
        @endif
    </dl>

    @if ($punyaDenda || $p->catatan || $bisaBatal)
    <div class="riwayat-bawah">
        <div class="riwayat-info">
            @if ($punyaDenda)
                <span class="pill tone-{{ $kembali->status_denda === 'lunas' ? 'ok' : 'danger' }}">
                    Denda Rp{{ number_format($kembali->denda, 0, ',', '.') }} &middot; {{ $kembali->status_denda === 'lunas' ? 'lunas' : 'belum lunas' }}
                </span>
            @endif
            @if ($p->catatan)
                <span class="riwayat-catatan">{{ $labelCatatan }}: {{ $p->catatan }}</span>
            @endif
        </div>
        @if ($bisaBatal)
            <form method="POST" action="{{ route('peminjam.batalkan', ['id' => $p->id_peminjaman]) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Batalkan pengajuan</button>
            </form>
        @endif
    </div>
    @endif
</article>