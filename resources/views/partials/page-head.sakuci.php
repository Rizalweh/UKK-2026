{{-- Kepala halaman: judul, subjudul, tombol kembali / aksi utama. Variabel: $judul, $sub, $kembali, $aksi --}}
<div class="pg-head">
    <div>
        <span class="badge rounded-pill badge-brand px-3 py-2 mb-2">Area {{ ucfirst(\App\Models\User::current()->role ?? '') }}</span>
        <h1>{{ $judul }}</h1>
        @if (isset($sub))<p class="text-secondary small mb-0 mt-1">{{ $sub }}</p>@endif
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if (isset($kembali))<a href="{{ $kembali }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>@endif
        @if (isset($aksi))<a href="{{ $aksi[1] }}" class="btn btn-brand">{{ $aksi[0] }}</a>@endif
    </div>
</div>