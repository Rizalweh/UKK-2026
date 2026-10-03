@extends('layouts.app')

@section('title', config('app.name') . ' -- Edit Alat')

@section('content')
@include('partials.page-head', ['judul' => 'Edit Alat', 'kembali' => route('alat.index')])

<form action="{{ route('alat.update', ['id' => $data->id_alat]) }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label" for="id_kategori">Kategori</label>
            <select name="id_kategori" id="id_kategori" class="form-select" required>
                <option value="">Pilih kategori alat</option>
                @foreach ($kategori as $k)
                    <option value="{{ $k->id_kategori }}" {{ old('id_kategori', $data->id_kategori) == $k->id_kategori ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="nama_alat">Nama Alat</label>
            <input type="text" name="nama_alat" id="nama_alat" class="form-control" value="{{ old('nama_alat', $data->nama_alat) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="kode_alat">Kode Alat</label>
            <input type="text" name="kode_alat" id="kode_alat" class="form-control" maxlength="10" value="{{ old('kode_alat', $data->kode_alat) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="harga_alat">Harga (Rp)</label>
            <input type="number" name="harga_alat" id="harga_alat" min="0" class="form-control" value="{{ old('harga_alat', $data->harga_alat) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="stok">Stok</label>
            <input type="number" name="stok" id="stok" min="0" class="form-control" value="{{ old('stok', $data->stok) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="kondisi">Kondisi</label>
            <select name="kondisi" id="kondisi" class="form-select" required>
                <option value="">Pilih kondisi</option>
                <option value="Baik" {{ old('kondisi', $data->kondisi) === 'Baik' ? 'selected' : '' }}>Baik</option>
                <option value="Rusak Ringan" {{ old('kondisi', $data->kondisi) === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                <option value="Rusak Berat" {{ old('kondisi', $data->kondisi) === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="foto_alat">Foto Alat</label>
            @if ($data->foto_alat)
                <div class="mb-2"><img src="/uploads/foto_alat/{{ $data->foto_alat }}" alt="Foto saat ini" class="thumb" style="width:96px;height:96px"></div>
            @endif
            <input type="file" name="foto_alat" id="foto_alat" class="form-control" accept="image/*">
            <div class="form-text">Kosongkan jika foto tidak diganti.</div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-brand">Simpan</button>
        <a href="{{ route('alat.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
@endsection