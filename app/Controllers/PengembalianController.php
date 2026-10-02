<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use App\Models\User;
use App\Models\LogAktivitas;
use Sakuci\Database\Connection;

class PengembalianController extends Controller
{
    // Petugas: memantau peminjaman yang menunggu verifikasi pengembalian
    public function index(Request $request)
    {
        $data = Peminjaman::where('status_peminjaman', 'menunggu_pengembalian')
            ->OrderBy('tanggal_kembali_rencana', 'asc')
            ->with(['peminjam', 'alat'])
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

    // Petugas: simpan hasil pengembalian (rumus denda ada di Pengembalian::hitung)
    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'tanggal_kembali_aktual' => 'required|date',
            'kondisi_alat'           => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'catatan'                => 'nullable|string|max:500',
        ]);

        $petugas    = User::current();
        $peminjaman = Peminjaman::FindOrFail($id);

        if ($peminjaman->status_peminjaman !== 'menunggu_pengembalian') {
            return redirect(route('petugas.pengembalian.index'))
                ->with('error', 'Peminjaman ini tidak dalam status menunggu pengembalian.');
        }

        if (strtotime($data['tanggal_kembali_aktual']) < strtotime($peminjaman->tanggal_pinjam)) {
            return back()->with('error', 'Tanggal kembali tidak boleh sebelum tanggal pinjam.')->withInput();
        }

        $alat    = Alat::FindOrFail($peminjaman->id_alat);
        $kondisi = $data['kondisi_alat'];

        $h = Pengembalian::hitung(
            $peminjaman->tanggal_kembali_rencana,
            $data['tanggal_kembali_aktual'],
            $kondisi,
            (float) $alat->harga_alat,
            (int) $peminjaman->jumlah_pinjam
        );

        Connection::transaction(function () use ($data, $peminjaman, $alat, $petugas, $kondisi, $h) {
            Pengembalian::create([
                'id_peminjaman'          => $peminjaman->id_peminjaman,
                'id_petugas'             => $petugas->id,
                'tanggal_kembali_aktual' => $data['tanggal_kembali_aktual'],
                'kondisi_alat'           => $kondisi,
                'hari_telat'             => $h['hari_telat'],
                'denda_telat'            => $h['denda_telat'],
                'denda_kerusakan'        => $h['denda_kerusakan'],
                'denda'                  => $h['total'],
                'status_denda'           => $h['total'] > 0 ? 'belum_lunas' : 'tidak_ada',
                'catatan'                => $data['catatan'] ?? null,
            ]);

            $peminjaman->update(['status_peminjaman' => 'dikembalikan']);

            // Alat rusak berat / hilang tidak masuk stok lagi
            if (Pengembalian::masukStok($kondisi)) {
                Alat::ubahStok((int) $alat->id_alat, (int) $peminjaman->jumlah_pinjam);
            }
        });

        LogAktivitas::catat($petugas->id,
            "Memproses pengembalian {$peminjaman->kode_peminjaman} (kondisi: {$kondisi}), total denda Rp" . number_format($h['total'], 0, ',', '.'));

        $pesan = $h['total'] > 0
            ? 'Pengembalian diproses. Total denda Rp' . number_format($h['total'], 0, ',', '.') . ' (belum lunas).'
            : 'Pengembalian diproses tanpa denda.';

        return redirect(route('petugas.pengembalian.index'))->with('success', $pesan);
    }

    // Daftar denda yang belum dibayar
    public function denda(Request $request)
    {
        $data = Pengembalian::where('status_denda', 'belum_lunas')
            ->with(['peminjaman.peminjam', 'peminjaman.alat'])
            ->OrderBy('id_pengembalian', 'desc')
            ->paginate(10);

        return view('petugas.pengembalian.denda', compact('data'));
    }

    // Tandai lunas
    public function bayar(Request $request, $id)
    {
        $pengembalian = Pengembalian::FindOrFail($id);

        if ($pengembalian->status_denda !== 'belum_lunas') {
            return back()->with('error', 'Denda ini sudah lunas atau tidak ada denda.');
        }

        $petugas = User::current();

        $pengembalian->update([
            'status_denda'      => 'lunas',
            'tanggal_bayar'     => date('Y-m-d H:i:s'),
            'id_penerima_bayar' => $petugas->id,
        ]);

        LogAktivitas::catat($petugas->id,
            "Menerima pembayaran denda Rp" . number_format($pengembalian->denda, 0, ',', '.') . " (pengembalian ID {$pengembalian->id_pengembalian})");

        return back()->with('success', 'Pembayaran denda dicatat sebagai lunas.');
    }
}