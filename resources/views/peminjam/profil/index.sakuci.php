@extends('layouts.app')

@section('title', config('app.name') . ' -- Profil Saya')

@section('content')
@php
    $namaTampil = $profil->nama_lengkap ?? $pengguna->username;
    $inisial    = mb_strtoupper(mb_substr($namaTampil, 0, 1));
    $bergabung  = $pengguna->created_at ? date('d-m-Y', strtotime($pengguna->created_at)) : '-';
@endphp

<div class="latar-peminjam">

    <header class="pinjam-header">
        <h1 class="teks-display teks-display-sedang">Profil saya</h1>
        <p class="katalog-sub">Kelola data diri dan keamanan akunmu.</p>
    </header>

    <div class="row g-4">

        {{-- Identitas: di atas pada layar kecil, di kiri dan menempel pada layar besar --}}
        <div class="col-lg-4">
            <aside class="profil-sisi">

                <div class="panel profil-identitas muncul" style="--urutan: 0">
                    <div class="profil-avatar" aria-hidden="true">{{ $inisial }}</div>
                    <h2 class="profil-nama">{{ $namaTampil }}</h2>
                    <span class="font-mono profil-username">{{ $pengguna->username }}</span>

                    <dl class="pinjam-ringkasan-daftar">
                        <div>
                            <dt>Peran</dt>
                            <dd><span class="pill tone-muted">{{ $pengguna->role }}</span></dd>
                        </div>
                        <div>
                            <dt>NIS</dt>
                            <dd class="font-mono"><i class="bi bi-lock-fill" title="Hanya admin yang bisa mengubah NIS"></i> {{ $profil->nis ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt>Bergabung</dt>
                            <dd>{{ $bergabung }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="metrik-grid">
                    <div class="panel metrik metrik-lebar muncul" style="--urutan: 1">
                        <span class="label-mono">total pengajuan</span>
                        <span class="angka-besar">{{ $ringkasan['totalPengajuan'] }}</span>
                    </div>

                    <div class="panel metrik muncul" style="--urutan: 2">
                        <span class="label-mono">selesai</span>
                        <span class="angka-besar metrik-nilai-sedang">{{ $ringkasan['selesai'] }}</span>
                    </div>

                    <a href="{{ route('peminjam.denda') }}" class="panel metrik metrik-tautan muncul" style="--urutan: 3">
                        <span class="label-mono">denda belum lunas</span>
                        <span class="angka-besar metrik-nilai-sedang {{ $ringkasan['denda']['jumlah'] > 0 ? 'text-danger' : '' }}">{{ $ringkasan['denda']['jumlah'] }}</span>
                        @if ($ringkasan['denda']['jumlah'] > 0)
                            <span class="metrik-satuan">Rp{{ number_format($ringkasan['denda']['total'], 0, ',', '.') }}</span>
                        @endif
                    </a>
                </div>

            </aside>
        </div>

        {{-- Form: data diri dan ganti password dipisah supaya tidak saling memengaruhi --}}
        <div class="col-lg-8">
            <div class="profil-form-susun">

                @if ($profil)
                    <form method="POST" action="{{ route('peminjam.profil.update') }}" class="panel panel-form muncul" style="--urutan: 1">
                        @csrf
                        @method('PUT')

                        <h2 class="panel-form-judul">Data diri</h2>
                        <p class="panel-form-sub">Perubahan username berlaku untuk login berikutnya.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="medan panel-dalam {{ errors()->has('username') ? 'medan-galat' : '' }}" for="username">
                                    <span class="label-mono">username</span>
                                    <input type="text" id="username" name="username" value="{{ old('username', $pengguna->username) }}" autocomplete="username" required>
                                </label>
                                @error('username') <div class="medan-pesan">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="medan panel-dalam {{ errors()->has('nama_lengkap') ? 'medan-galat' : '' }}" for="nama_lengkap">
                                    <span class="label-mono">nama lengkap</span>
                                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $profil->nama_lengkap) }}" required>
                                </label>
                                @error('nama_lengkap') <div class="medan-pesan">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="medan panel-dalam {{ errors()->has('no_hp') ? 'medan-galat' : '' }}" for="no_hp">
                                    <span class="label-mono">no. hp</span>
                                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $profil->no_hp) }}" inputmode="numeric" required>
                                </label>
                                @error('no_hp') <div class="medan-pesan">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="medan medan-terkunci panel-dalam" for="nis">
                                    <span class="label-mono">nis (dikunci, hanya admin)</span>
                                    <input type="text" id="nis" value="{{ $profil->nis }}" disabled>
                                </label>
                            </div>

                            <div class="col-12">
                                <label class="medan panel-dalam {{ errors()->has('alamat') ? 'medan-galat' : '' }}" for="alamat">
                                    <span class="label-mono">alamat (opsional)</span>
                                    <textarea id="alamat" name="alamat" rows="2" maxlength="255">{{ old('alamat', $profil->alamat) }}</textarea>
                                </label>
                                @error('alamat') <div class="medan-pesan">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="panel-form-aksi">
                            <button type="submit" class="btn btn-brand pinjam-tombol-utama">Simpan perubahan</button>
                        </div>
                    </form>
                @else
                    <div class="panel panel-form pinjam-habis muncul" style="--urutan: 1">
                        <i class="bi bi-person-exclamation"></i>
                        <h2>Profil belum dilengkapi</h2>
                        <p>Data diri dan NIS kamu belum tercatat. Hubungi admin untuk melengkapinya.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('peminjam.profil.password') }}" class="panel panel-form muncul" style="--urutan: 2">
                    @csrf
                    @method('PUT')

                    <h2 class="panel-form-judul">Ganti password</h2>
                    <p class="panel-form-sub">Masukkan password lama, lalu pilih password baru minimal 6 karakter.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="medan panel-dalam {{ errors()->has('password_lama') ? 'medan-galat' : '' }}" for="password_lama">
                                <span class="label-mono">password lama</span>
                                <input type="password" id="password_lama" name="password_lama" autocomplete="current-password" required>
                            </label>
                            @error('password_lama') <div class="medan-pesan">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="medan panel-dalam {{ errors()->has('password') ? 'medan-galat' : '' }}" for="password">
                                <span class="label-mono">password baru</span>
                                <input type="password" id="password" name="password" autocomplete="new-password" required>
                            </label>
                            @error('password') <div class="medan-pesan">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="medan panel-dalam" for="password_confirmation">
                                <span class="label-mono">ulangi password baru</span>
                                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                            </label>
                        </div>
                    </div>

                    <div class="panel-form-aksi">
                        <button type="submit" class="btn btn-outline-brand">Perbarui password</button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection