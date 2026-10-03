<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        $user = User::current();

        $totalAlat = Alat::count();
        $tersedia  = Alat::where('stok', '>', 0)->count();

        $stat = [
            'total'    => $totalAlat,
            'tersedia' => $tersedia,
            'kategori' => Kategori::count(),
        ];

        // with() = eager loading, jadi jumlah alat per kategori tidak memicu query berulang
        $kategori    = Kategori::with('alat')->orderBy('nama_kategori')->get();
        $alatTerbaru = Alat::with('kategori')->orderBy('id_alat', 'desc')->take(4)->get();

        return view('welcome', [
            'stat'        => $stat,
            'kategori'    => $kategori,
            'alatTerbaru' => $alatTerbaru,
            'currentUser' => $user,
            'hariIni'     => $this->hariIni($user),
        ]);
    }

    /** Ringkasan singkat sesuai role yang login: [n, teks, url]. */
    protected function hariIni(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->role === 'peminjam') {
            return [
                ['n' => Peminjaman::where('id_peminjam', $user->id)->where('status_peminjaman', 'disetujui')->count(),
                 't' => 'alat sedang kamu pinjam', 'url' => route('peminjam.dipinjam')],
                ['n' => Peminjaman::where('id_peminjam', $user->id)->where('status_peminjaman', 'pending')->count(),
                 't' => 'pengajuan menunggu persetujuan', 'url' => route('peminjam.riwayat')],
                ['n' => Pengembalian::tunggakan($user->id)['jumlah'],
                 't' => 'denda belum lunas', 'url' => route('peminjam.denda')],
            ];
        }

        $pending = Peminjaman::where('status_peminjaman', 'pending')->count();
        $kembali = Peminjaman::where('status_peminjaman', 'menunggu_pengembalian')->count();
        $denda   = Pengembalian::where('status_denda', 'belum_lunas')->count();

        if ($user->role === 'petugas') {
            return [
                ['n' => $pending, 't' => 'pengajuan perlu diperiksa', 'url' => route('petugas.peminjaman.index')],
                ['n' => $kembali, 't' => 'pengembalian menunggu diproses', 'url' => route('petugas.pengembalian.index')],
                ['n' => $denda, 't' => 'denda belum lunas', 'url' => route('petugas.denda.index')],
            ];
        }

        if ($user->role === 'admin') {
            return [
                ['n' => $pending, 't' => 'pengajuan pending', 'url' => route('admin.peminjaman.index')],
                ['n' => $kembali, 't' => 'pengembalian menunggu dicatat', 'url' => route('admin.pengembalian.create')],
                ['n' => $denda, 't' => 'denda belum lunas', 'url' => route('admin.pengembalian.index')],
            ];
        }

        return [];
    }
}