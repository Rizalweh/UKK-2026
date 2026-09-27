<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use App\Models\LogAktivitas;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $data = LogAktivitas::OrderBy('waktu', 'desc')->paginate(20);
        return view('core.admin.log.index', compact('data'));
    }
}