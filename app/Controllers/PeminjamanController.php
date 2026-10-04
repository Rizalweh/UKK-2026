<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use App\Models\Peminjaman;
use App\Models\Alat;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Models\Pengembalian;
use Sakuci\Database\Connection;



class PeminjamanController extends Controller
{
    // Petugas: daftar pengajuan pending
    public function index(Request $request)
    {

        $data = Peminjaman::where('status_peminjaman', 'pending')
            ->OrderBy('tanggal_pengajuan', 'desc')
            ->paginate(10);

        return view('petugas.peminjaman.index', compact('data'));
    }


    // Petugas: setujui pengajuan -> stok dikurangi di sini
    public function setujui(Request $request, $id)
    {
        $peminjaman = Peminjaman::FindOrFail($id);
        $petugas = User::current();

        LogAktivitas::catat(User::current()->id, "Menyetujui pengajuan peminjaman {$peminjaman->kode_peminjaman} oleh user ID {$peminjaman->id_peminjam}");

        if ($peminjaman->status_peminjaman !== 'pending') {
            return redirect(route('petugas.peminjaman.index'))
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $alat = Alat::FindOrFail($peminjaman->id_alat);

        if ($alat->stok < $peminjaman->jumlah_pinjam) {
            return redirect(route('petugas.peminjaman.index'))
                ->with('error', "Stok {$alat->nama_alat} tidak mencukupi.");
        }

        // Update status DULU (semacam kunci manual, karena tidak ada transaction),
        // baru kurangi stok setelahnya.
        $peminjaman->update(['status_peminjaman' => 'disetujui']);
        $alat->update(['stok' => $alat->stok - $peminjaman->jumlah_pinjam]);

        return redirect(route('petugas.peminjaman.index'))
            ->with('success', 'Peminjaman disetujui, stok diperbarui.');
    }

    // Petugas: tolak pengajuan
    public function tolak(Request $request, $id)
    {
        $peminjaman = Peminjaman::FindOrFail($id);
        $petugas = User::current();

        LogAktivitas::catat(User::current()->id, "Menolak pengajuan peminjaman {$peminjaman->kode_peminjaman} oleh user ID {$peminjaman->id_peminjam}");

        if ($peminjaman->status_peminjaman !== 'pending') {
            return redirect(route('petugas.peminjaman.index'))
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $peminjaman->update([
            'status_peminjaman' => 'ditolak',
            'catatan' => $request->input('catatan'),
        ]);

        return redirect(route('petugas.peminjaman.index'))
            ->with('success', 'Peminjaman ditolak.');
    }

        private const ATURAN_PENGAJUAN = [
        'id_alat'                 => 'required|exists:alat,id_alat',
        'jumlah_pinjam'           => 'required|integer|min:1',
        'tanggal_pinjam'          => 'required|date',
        'tanggal_kembali_rencana' => 'required|date',
        'catatan'                 => 'nullable|max:255',
    ];

    // Peminjam: kirim pengajuan dari halaman alat
    public function ajukan(Request $request)
    {
        $dataPengajuan = $request->validate(self::ATURAN_PENGAJUAN);
        $peminjam      = User::current();
        $alat          = Alat::findOrFail($dataPengajuan['id_alat']);
        $halamanAlat   = route('peminjam.alat.show', ['id' => $alat->id_alat]);
        $jumlahPinjam  = (int) $dataPengajuan['jumlah_pinjam'];

        if (strtotime($dataPengajuan['tanggal_pinjam']) < strtotime(date('Y-m-d'))) {
            return redirect($halamanAlat)
                ->with('error', 'Tanggal pinjam tidak boleh sebelum hari ini.')
                ->withInput();
        }

        if (strtotime($dataPengajuan['tanggal_kembali_rencana']) < strtotime($dataPengajuan['tanggal_pinjam'])) {
            return redirect($halamanAlat)
                ->with('error', 'Tanggal kembali tidak boleh sebelum tanggal pinjam.')
                ->withInput();
        }

        if ((int) $alat->stok < $jumlahPinjam) {
            return redirect($halamanAlat)
                ->with('error', "Stok {$alat->nama_alat} hanya {$alat->stok}.")
                ->withInput();
        }

        $peminjaman = Peminjaman::create([
            'kode_peminjaman'         => 'PJM-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'id_peminjam'             => $peminjam->id,
            'id_alat'                 => $alat->id_alat,
            'jumlah_pinjam'           => $jumlahPinjam,
            'tanggal_pengajuan'       => date('Y-m-d'),
            'tanggal_pinjam'          => $dataPengajuan['tanggal_pinjam'],
            'tanggal_kembali_rencana' => $dataPengajuan['tanggal_kembali_rencana'],
            'status_peminjaman'       => 'pending',
            'catatan'                 => $dataPengajuan['catatan'] ?: null,
        ]);

        LogAktivitas::catat($peminjam->id, "Mengajukan peminjaman {$alat->nama_alat} (jumlah: {$jumlahPinjam})");

        return redirect(route('peminjam.riwayat'))
            ->with('success', "Pengajuan {$peminjaman->kode_peminjaman} berhasil dikirim, menunggu persetujuan petugas.");
    }

    public function sedangDipinjam(Request $request)
{
    $user  = User::current();
    $milik = fn () => Peminjaman::where('id_peminjam', $user->id);

    $dipinjam = $milik()->where('status_peminjaman', 'disetujui')
        ->with('alat')->OrderBy('tanggal_kembali_rencana', 'asc')->get();

    $menunggu = $milik()->where('status_peminjaman', 'menunggu_pengembalian')
        ->with('alat')->OrderBy('tanggal_kembali_rencana', 'asc')->get();

    $hariIni = strtotime(date('Y-m-d'));
    $sisa    = fn ($p) => (int) floor((strtotime($p->tanggal_kembali_rencana) - $hariIni) / 86400);

    $ringkasan = [
        'dipinjam' => count($dipinjam),
        'tempo'    => count(array_filter($dipinjam, fn ($p) => $sisa($p) >= 0 && $sisa($p) <= 1)),
        'telat'    => count(array_filter($dipinjam, fn ($p) => $sisa($p) < 0)),
    ];

    return view('peminjam.dipinjam.index', compact('dipinjam', 'menunggu', 'ringkasan'));
}
public function ajukanPengembalian(Request $request, $id)
{
    $user = User::current();
    $peminjaman = Peminjaman::FindOrFail($id);

    if ((int) $peminjaman->id_peminjam !== (int) $user->id) {
        return redirect(route('peminjam.dipinjam'))->with('error', 'Peminjaman ini bukan milik kamu.');
    }

    if ($peminjaman->status_peminjaman !== 'disetujui') {
        return redirect(route('peminjam.dipinjam'))->with('error', 'Peminjaman ini tidak bisa diajukan pengembaliannya.');
    }

    $peminjaman->update(['status_peminjaman' => 'menunggu_pengembalian']);

    LogAktivitas::catat($user->id, "Mengajukan pengembalian {$peminjaman->kode_peminjaman}");

    return redirect(route('peminjam.dipinjam'))
        ->with('success', 'Pengajuan pengembalian dikirim, menunggu verifikasi Petugas.');
}

// Peminjam: riwayat semua peminjamannya (termasuk denda final)
private const PER_HALAMAN_RIWAYAT = 8;

public function riwayat(Request $request)
{
    $user         = User::current();
    $statusFilter = (string) $request->input('status', '');
    $kode         = trim((string) $request->input('q', ''));
    $milik        = fn () => Peminjaman::where('id_peminjam', $user->id);

    $query = $milik()->OrderBy('id_peminjaman', 'desc');
    if (isset(Peminjaman::STATUS[$statusFilter])) {
        $query = $query->where('status_peminjaman', $statusFilter);
    }
    if ($kode !== '') {
        $query = $query->where('kode_peminjaman', 'like', '%' . $kode . '%');
    }

    $tunggakan = Pengembalian::tunggakan($user->id);

    return view('peminjam.riwayat.index', [
        'data'         => $query->with(['alat', 'pengembalian'])->paginate(self::PER_HALAMAN_RIWAYAT),
        'statusFilter' => $statusFilter,
        'kode'         => $kode,
        'ringkasan'    => [
            'menunggu' => $milik()->where('status_peminjaman', 'pending')->count(),
            'dipinjam' => $milik()->whereIn('status_peminjaman', ['disetujui', 'menunggu_pengembalian'])->count(),
            'selesai'  => $milik()->where('status_peminjaman', 'dikembalikan')->count(),
            'denda'    => $tunggakan['jumlah'],
            'dendaRp'  => $tunggakan['total'],
        ],
    ]);
}

public function batalkan(Request $request, $id)
{
    $user       = User::current();
    $peminjaman = Peminjaman::FindOrFail($id);

    if ((int) $peminjaman->id_peminjam !== (int) $user->id) {
        return redirect(route('peminjam.riwayat'))->with('error', 'Pengajuan ini bukan milik kamu.');
    }
    if ($peminjaman->status_peminjaman !== 'pending') {
        return redirect(route('peminjam.riwayat'))->with('error', 'Hanya pengajuan yang masih pending yang bisa dibatalkan.');
    }

    $peminjaman->update(['status_peminjaman' => 'dibatalkan']);

    LogAktivitas::catat($user->id, "Membatalkan pengajuan {$peminjaman->kode_peminjaman}");

    return redirect(route('peminjam.riwayat'))->with('success', 'Pengajuan dibatalkan.');
}

public function riwayatSemua(Request $request)
{
    $jumlahSelesai = count($this->idSelesai());
    $jumlahLamaRiwayat = $jumlahSelesai >= self::BATAS_RIWAYAT ? $jumlahSelesai - self::SISA_RIWAYAT : 0;
    $data = $request->all();
    $status = $data['status'] ?? null;

    $query = Peminjaman::OrderBy('id_peminjaman', 'desc');

    if (!empty($status)) {
        $query = $query->where('status_peminjaman', $status);
    }

    $riwayat = $query->paginate(15);

    return view('petugas.peminjaman.riwayat.index', [
        'data'         => $riwayat,
        'statusFilter' => $status,
        'jumlahLamaRiwayat' => $jumlahLamaRiwayat,
    ]);
}
public function hapusRiwayatLama(Request $request)
{
    $ids = $this->idSelesai();

    if (count($ids) < self::BATAS_RIWAYAT) {
        return redirect(route('petugas.peminjaman.riwayat'))
            ->with('error', 'Riwayat selesai belum mencapai ' . self::BATAS_RIWAYAT . ' baris.');
    }

    $hapus = array_slice($ids, self::SISA_RIWAYAT);

    Connection::transaction(function () use ($hapus) {
        Pengembalian::whereIn('id_peminjaman', $hapus)->delete();
        Peminjaman::whereIn('id_peminjaman', $hapus)->delete();
    });

    return redirect(route('petugas.peminjaman.riwayat'))
        ->with('success', count($hapus) . ' riwayat selesai berhasil dihapus');
}
// Peminjam: rincian denda miliknya
public function denda(Request $request)
{
    $user = User::current();

    $data = Pengembalian::select('pengembalian.*')
        ->leftJoin('peminjaman', 'peminjaman.id_peminjaman', '=', 'pengembalian.id_peminjaman')
        ->where('peminjaman.id_peminjam', $user->id)
        ->where('pengembalian.denda', '>', 0)
        ->with(['peminjaman.alat'])
        ->OrderBy('pengembalian.id_pengembalian', 'desc')
        ->paginate(10);

    return view('peminjam.denda.index', [
        'data'      => $data,
        'tunggakan' => Pengembalian::tunggakan($user->id),
    ]);
}
 // Petugas: laporan peminjaman & pengembalian (siap cetak)
public function laporan(Request $request)
{
    $dari   = $request->input('dari');
    $sampai = $request->input('sampai');
    $status = $request->input('status');

    $query = Peminjaman::OrderBy('tanggal_pinjam', 'desc');

    if (!empty($dari))   { $query = $query->where('tanggal_pinjam', '>=', $dari); }
    if (!empty($sampai)) { $query = $query->where('tanggal_pinjam', '<=', $sampai); }
    if (!empty($status)) { $query = $query->where('status_peminjaman', $status); }

    // with() = eager loading, hindari N+1; limit supaya halaman tetap cepat
    $data = $query->with(['peminjam', 'alat', 'pengembalian'])->limit(500)->get();

    $totalDenda = 0;
    foreach ($data as $p) {
        $totalDenda += $p->pengembalian->denda ?? 0;
    }

    LogAktivitas::catat(User::current()->id, 'Membuka laporan peminjaman');

    return view('petugas.laporan.index', [
        'data'       => $data,
        'totalDenda' => $totalDenda,
        'dari'       => $dari,
        'sampai'     => $sampai,
        'statusFilter' => $status,
        'petugas'    => User::current(),
    ]);
}

private const BATAS_RIWAYAT = 500;
private const SISA_RIWAYAT  = 250;

private function idSelesai(): array
{
    $rows = Connection::select(
        "SELECT p.id_peminjaman FROM peminjaman p
         WHERE p.status_peminjaman = 'ditolak'
            OR (p.status_peminjaman = 'dikembalikan' AND NOT EXISTS (
                SELECT 1 FROM pengembalian g
                WHERE g.id_peminjaman = p.id_peminjaman AND g.status_denda = 'belum_lunas'))
         ORDER BY p.id_peminjaman DESC"
    );
    return array_column($rows, 'id_peminjaman');
}

}
