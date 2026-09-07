<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $data = Kategori::paginate(2);
       return view('kategori.index', compact('data'));
    }
    public function create(Request $request)
    {
        return view('kategori.create');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => 'required|min:3|max:100',
            'kategori' => 'required|min:3|max:100',
            'keterangan' => 'required|min:3|max:100',
        ]);
        Kategori::create($data);
        return redirect(route('kategori.index'))->with('success', 'Kategori berhasil ditambahkan.');
    }
}
