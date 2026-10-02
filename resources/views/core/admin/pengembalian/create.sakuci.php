@extends('layouts.app')

@section('title', 'Catat Pengembalian')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <span class="badge rounded-pill badge-brand px-3 py-2 mb-2">Area Admin</span>
        <h1 class="h4 mb-0">Catat Pengembalian</h1>
    </div>
    <a href="{{ route('admin.pengembalian.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <p class="small text-secondary">
                    Denda dihitung otomatis: Rp{{ number_format(\App\Models\Pengembalian::TARIF_DENDA_PER_HARI, 0, ',', '.') }} per hari keterlambatan,
                    ditambah persentase harga alat sesuai kondisi (rusak ringan 25%, rusak berat 50%, hilang 100%).
                </p>

                <form method="POST" action="{{ route('admin.pengembalian.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="id_peminjaman">Peminjaman</label>
                        <select id="id_peminjaman" name="id_peminjaman" class="form-select {{ errors()->has('id_peminjaman') ? 'is-invalid' : '' }}" required>
                            <option value="">Pilih peminjaman yang sedang berjalan</option>
                            @foreach ($peminjamanList as $peminjaman)
                                <option value="{{ $peminjaman->id_peminjaman }}" {{ old('id_peminjaman', $dipilih) == $peminjaman->id_peminjaman ? 'selected' : '' }}>
                                    {{ $peminjaman->kode_peminjaman }} - {{ $peminjaman->peminjam->username ?? '-' }} - {{ $peminjaman->alat->nama_alat ?? '-' }} x{{ $peminjaman->jumlah_pinjam }} (rencana {{ $peminjaman->tanggal_kembali_rencana }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_peminjaman') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tanggal_kembali_aktual">Tanggal Kembali (Aktual)</label>
                        <input type="date" id="tanggal_kembali_aktual" name="tanggal_kembali_aktual" value="{{ old('tanggal_kembali_aktual', date('Y-m-d')) }}" class="form-control {{ errors()->has('tanggal_kembali_aktual') ? 'is-invalid' : '' }}" required>
                        @error('tanggal_kembali_aktual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="kondisi_alat">Kondisi Alat</label>
                        <select id="kondisi_alat" name="kondisi_alat" class="form-select" required>
                            @foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang'] as $val => $label)
                                <option value="{{ $val }}" {{ old('kondisi_alat', 'baik') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="catatan">Catatan (opsional)</label>
                        <input type="text" id="catatan" name="catatan" value="{{ old('catatan') }}" class="form-control" placeholder="Kondisi fisik alat, dst">
                    </div>

                    <button type="submit" class="btn btn-brand w-100">Simpan Pengembalian</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection