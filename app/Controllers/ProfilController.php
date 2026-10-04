<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use Sakuci\Database\Connection;
use App\Models\LogAktivitas;
use App\Models\Pengembalian;
use App\Models\User;

/**
 * Halaman profil peminjam: lihat data diri, ubah data diri, ganti password.
 * NIS tampil tapi terkunci: hanya admin yang boleh mengubahnya.
 */
class ProfilController extends Controller
{
    private const ATURAN_DATA_DIRI = [
        'nama_lengkap' => 'required|min:3|max:100',
        'no_hp'        => 'required|numeric',
        'alamat'       => 'nullable|max:255',
    ];

    private const PANJANG_USERNAME = 'required|min:3|max:50|alpha_dash';

    public function index()
    {
        $pengguna = User::current();

        $barisStatus     = Connection::select(
            'SELECT status_peminjaman, COUNT(*) AS total FROM peminjaman WHERE id_peminjam = ? GROUP BY status_peminjaman',
            [$pengguna->id]
        );
        $jumlahPerStatus = array_map('intval', array_column($barisStatus, 'total', 'status_peminjaman'));

        return view('peminjam.profil.index', [
            'pengguna'  => $pengguna,
            'profil'    => $pengguna->profil,
            'ringkasan' => [
                'totalPengajuan' => array_sum($jumlahPerStatus),
                'selesai'        => $jumlahPerStatus['dikembalikan'] ?? 0,
                'denda'          => Pengembalian::tunggakan($pengguna->id),
            ],
        ]);
    }

    // Simpan username, nama lengkap, no. HP, dan alamat (NIS tidak ikut diubah)
    public function update(Request $request)
    {
        $pengguna = User::current();
        $profil   = $pengguna->profil;

        if (! $profil) {
            return redirect(route('peminjam.profil'))
                ->with('error', 'Profil kamu belum dilengkapi. Hubungi admin untuk mengisi NIS.');
        }

        $data = $request->validate(array_merge(
            ['username' => self::PANJANG_USERNAME . '|unique:users,username,' . $pengguna->id . ',id'],
            self::ATURAN_DATA_DIRI
        ));

        Connection::transaction(function () use ($pengguna, $profil, $data) {
            $pengguna->update(['username' => $data['username']]);

            $profil->update([
                'nama_lengkap' => $data['nama_lengkap'],
                'no_hp'        => $data['no_hp'],
                'alamat'       => $data['alamat'] ?: null,
            ]);
        });

        LogAktivitas::catat($pengguna->id, 'Memperbarui profil');

        return redirect(route('peminjam.profil'))->with('success', 'Profil berhasil diperbarui.');
    }

    // Ganti password: password lama harus cocok, password baru harus berbeda
    public function ubahPassword(Request $request)
    {
        $pengguna = User::current();

        $data = $request->validate([
            'password_lama' => 'required',
            'password'      => 'required|min:6|confirmed|different:password_lama',
        ], [
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ]);

        if (! password_verify($data['password_lama'], $pengguna->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak cocok.']);
        }

        $pengguna->update(['password' => password_hash($data['password'], PASSWORD_DEFAULT)]);

        LogAktivitas::catat($pengguna->id, 'Mengganti password');

        return redirect(route('peminjam.profil'))->with('success', 'Password berhasil diganti.');
    }
}