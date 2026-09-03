<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $kategori = Kategori::paginate(10);
       return view('kategori.index', compact('kategori'));
    }
}
