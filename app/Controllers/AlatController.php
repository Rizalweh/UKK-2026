<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use App\Models\Alat;
use App\Models\Kategori;

class AlatController extends Controller
{
    private const PER_HALAMAN_KATALOG = 9;

    // path absolut ke folder upload, dihitung sekali
    private function uploadPath(): string
    {
        return dirname(__DIR__, 2) . '/public/uploads/foto_alat/';
    }

    public function index(Request $request)
    {
        $data = Alat::OrderBy('id_alat', 'desc')->paginate(5);
        return view('alat.index', compact('data'));
    }

    public function create(Request $request)
    {
        $kategori = Kategori::all();
        return view('alat.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_alat' => 'required|string|max:255|unique:alat,kode_alat',
            'nama_alat' => 'required|string|max:255',
            'harga_alat' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'kondisi' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'id_kategori' => 'nullable|exists:kategori,id_kategori',
            'foto_alat' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('foto_alat')) {
            $file = $request->file('foto_alat');
            $filename = time() . '_' . $file['name'];

            if (!is_dir($this->uploadPath())) {
                mkdir($this->uploadPath(), 0775, true);
            }

            move_uploaded_file($file['tmp_name'], $this->uploadPath() . $filename);
            $data['foto_alat'] = $filename;
        }

        Alat::create($data);
        return redirect(route('alat.index'))->with('success', 'Data berhasil disimpan');
    }

    public function edit(Request $request, $id)
    {
        $data = Alat::FindOrFail($id);
        $kategori = Kategori::all();
        return view('alat.edit', compact('data', 'kategori'));
    }

    public function update(Request $request, $id)
    {
        $data = Alat::FindOrFail($id);
        $validatedData = $request->validate([
            'kode_alat' => 'required|string|max:255|unique:alat,kode_alat,' . $data->id_alat . ',id_alat',
            'nama_alat' => 'required|string|max:255',
            'stok' => 'required|numeric|min:0',
            'harga_alat' => 'required|numeric|min:0',
            'kondisi' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'id_kategori' => 'nullable|exists:kategori,id_kategori',
            'foto_alat' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('foto_alat')) {

            if ($data->foto_alat && file_exists($this->uploadPath() . $data->foto_alat)) {
                unlink($this->uploadPath() . $data->foto_alat);
            }

            $file = $request->file('foto_alat');
            $fileName = time() . '_' . $file['name'];

            if (!is_dir($this->uploadPath())) {
                mkdir($this->uploadPath(), 0775, true);
            }

            move_uploaded_file($file['tmp_name'], $this->uploadPath() . $fileName);
            $validatedData['foto_alat'] = $fileName;
        } else {
            $validatedData['foto_alat'] = $data->foto_alat;
        }

        $data->update($validatedData);
        return redirect(route('alat.index'))->with('success', 'Data berhasil diubah');
    }

    public function destroy(Request $request, $id)
    {
        $data = Alat::FindOrFail($id);

        if ($data->foto_alat && file_exists($this->uploadPath() . $data->foto_alat)) {
            unlink($this->uploadPath() . $data->foto_alat);
        }

        $data->delete();
        return redirect(route('alat.index'))->with('success', 'Data berhasil dihapus');
    }

    // Peminjam: katalog alat. Semua alat tampil; yang stoknya habis diredupkan di view.
    public function daftarAlat(Request $request)
    {
        $kataKunci       = trim((string) $request->input('q', ''));
        $kategoriDipilih = (string) $request->input('kategori', '');

        $queryAlat = Alat::with('kategori')->orderBy('nama_alat', 'asc');

        if ($kataKunci !== '') {
            $queryAlat = $queryAlat->where(function ($kondisiCari) use ($kataKunci) {
                $kondisiCari->where('nama_alat', 'like', '%' . $kataKunci . '%')
                            ->orWhere('kode_alat', 'like', '%' . $kataKunci . '%');
            });
        }

        if ($kategoriDipilih !== '') {
            $queryAlat = $queryAlat->where('id_kategori', $kategoriDipilih);
        }

        return view('peminjam.alat.index', [
            'daftarAlat'      => $queryAlat->paginate(self::PER_HALAMAN_KATALOG),
            'kategoriList'    => Kategori::orderBy('nama_kategori')->get(),
            'kataKunci'       => $kataKunci,
            'kategoriDipilih' => $kategoriDipilih,
        ]);
    }

    // Peminjam: halaman satu alat berisi form peminjaman
    public function detail(Request $request, $id)
    {
        return view('peminjam.alat.show', [
            'alat'    => Alat::findOrFail($id),
            'hariIni' => date('Y-m-d'),
        ]);
    }
}