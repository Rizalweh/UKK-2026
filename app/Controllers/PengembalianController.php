<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use App\Models\User;
use App\Models\LogAktivitas;

class PengembalianController extends Controller
{
    const TARIF_DENDA_PER_HARI = 5000;

    // Petugas: daftar peminjaman yang sedang dipinjam (siap dikembalikan)
    public function index(Request $request)
    {
        $data = Peminjaman::where('status_peminjaman', 'menunggu_pengembalian')
            ->OrderBy('tanggal_kembali_rencana', 'asc')
            ->paginate(10);

        return view('petugas.pengembalian.index', compact('data'));
    }

    // Petugas: form proses pengembalian untuk 1 peminjaman
    public function create(Request $request, $id)
    {
        $peminjaman = Peminjaman::FindOrFail($id);

        if ($peminjaman->status_peminjaman !== 'menunggu_pengembalian') {
            return redirect(route('petugas.pengembalian.index'))
                ->with('error', 'Peminjaman ini tidak dalam status menunggu pengembalian.');
        }

        return view('petugas.pengembalian.create', compact('peminjaman'));
    }

    // Hitung hari telat & denda 
    private function hitungDenda(string $tanggalRencana, string $tanggalAktual): array
    {
        $rencana = strtotime($tanggalRencana);
        $aktual  = strtotime($tanggalAktual);

        $hariTelat = 0;
        if ($aktual > $rencana) {
            $hariTelat = (int) round(($aktual - $rencana) / 86400);
        }

        $denda = $hariTelat * self::TARIF_DENDA_PER_HARI;

        return [$hariTelat, $denda];
    }

    // Petugas: simpan hasil pengembalian
    public function store(Request $request, $id)
    {
        $data = $request->all();
        $petugas = User::current();

        $peminjaman = Peminjaman::FindOrFail($id);

        if ($peminjaman->status_peminjaman !== 'menunggu_pengembalian') {
            return redirect(route('petugas.pengembalian.index'))
                ->with('error', 'Peminjaman ini tidak dalam status menunggu pengembalian.');
        }

        [$hariTelat, $denda] = $this->hitungDenda(
            $peminjaman->tanggal_kembali_rencana,
            $data['tanggal_kembali_aktual']
        );

        // 1) Catat pengembalian dulu (id_peminjaman UNIQUE -> jaga-jaga kalau
        //    ada 2 percobaan proses bersamaan, yang kedua bakal gagal di sini)
        Pengembalian::create([
            'id_peminjaman'          => $peminjaman->id_peminjaman,
            'id_petugas'             => $petugas->id,
            'tanggal_kembali_aktual' => $data['tanggal_kembali_aktual'],
            'kondisi_alat'           => $data['kondisi_alat'],
            'hari_telat'             => $hariTelat,
            'denda'                  => $denda,
            'catatan'                => $data['catatan'] ?? null,
        ]);

        // 2) Update status peminjaman
        $peminjaman->update(['status_peminjaman' => 'dikembalikan']);

        // 3) Stok alat bertambah lagi
        $alat = Alat::FindOrFail($peminjaman->id_alat);
        $alat->update(['stok' => $alat->stok + $peminjaman->jumlah_pinjam]);

        $pesan = $denda > 0
            ? "Pengembalian diproses. Terlambat {$hariTelat} hari, denda Rp" . number_format($denda, 0, ',', '.')
            : "Pengembalian diproses tanpa denda.";

            LogAktivitas::catat($petugas->id, "Memproses pengembalian peminjaman {$peminjaman->kode_peminjaman} oleh user ID {$peminjaman->id_peminjam}. Terlambat: {$hariTelat} hari, denda: Rp" . number_format($denda, 0, ',', '.'));

        return redirect(route('petugas.pengembalian.index'))->with('success', $pesan);
    }
    
}