<?php

namespace App\Controllers\Core;

use App\Models\Alat;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Sakuci\Controller;
use Sakuci\Database\Connection;

/**
 * Satu view (core.dashboard) dipakai semua role. Controller ini hanya menyiapkan
 * isinya: $tiles (angka ringkas), $panels (daftar yang perlu perhatian),
 * $shortcuts (akses cepat), dan $aksi (tombol utama).
 */
class DashboardController extends Controller
{
    private const AKTIF = ['disetujui', 'menunggu_pengembalian'];

    public function index()
    {
        return $this->tampil();
    }

    public function admin()
    {
        return $this->tampil();
    }

    protected function tampil()
    {
        $user = User::current();

        $isi = match ($user->role) {
            'admin'    => $this->dataAdmin(),
            'petugas'  => $this->dataPetugas(),
            'peminjam' => $this->dataPeminjam($user),
            default    => [],
        };

        return view('core.dashboard', array_merge([
            'user'      => $user,
            'tanggal'   => $this->tanggalHariIni(),
            'aksi'      => null,
            'tiles'     => [],
            'panels'    => [],
            'shortcuts' => [],
        ], $isi));
    }

    /*
    |----------------------------------------------------------------------
    | Data per role
    |----------------------------------------------------------------------
    */

    protected function dataAdmin(): array
    {
        $hariIni   = date('Y-m-d');
        $pending   = Peminjaman::where('status_peminjaman', 'pending')->count();
        $dipinjam  = Peminjaman::whereIn('status_peminjaman', self::AKTIF)->count();
        $terlambat = Peminjaman::whereIn('status_peminjaman', self::AKTIF)->where('tanggal_kembali_rencana', '<', $hariIni)->count();
        $denda     = Pengembalian::where('status_denda', 'belum_lunas')->count();
        $dendaRp   = $this->jumlah('SELECT COALESCE(SUM(denda),0) AS total FROM pengembalian WHERE status_denda = ?', ['belum_lunas']);
        $stok      = (int) $this->jumlah('SELECT COALESCE(SUM(stok),0) AS total FROM alat');
        $menipis   = Alat::where('stok', '<=', 2)->count();

        return [
            'aksi' => ['Tambah peminjaman', route('admin.peminjaman.create')],
            'tiles' => [
                $this->tile('bi-hourglass-split', 'Pengajuan pending', $pending, 'menunggu persetujuan petugas', route('admin.peminjaman.index') . '?status=pending', $pending ? 'warn' : 'muted'),
                $this->tile('bi-box-seam', 'Sedang dipinjam', $dipinjam, $terlambat . ' terlambat dikembalikan', route('admin.peminjaman.index') . '?status=disetujui', $terlambat ? 'danger' : 'muted'),
                $this->tile('bi-cash-stack', 'Denda belum lunas', $denda, $this->rp($dendaRp) . ' belum dibayar', route('admin.pengembalian.index') . '?status=belum_lunas', $denda ? 'danger' : 'ok'),
                $this->tile('bi-boxes', 'Stok tersedia', $stok, Alat::count() . ' jenis alat, ' . $menipis . ' hampir habis', route('alat.index'), $menipis ? 'warn' : 'muted'),
            ],
            'panels' => [
                $this->panel('Pengajuan menunggu', $this->barisPengajuan(
                    Peminjaman::where('status_peminjaman', 'pending')->with(['alat', 'peminjam'])->orderBy('tanggal_pengajuan', 'desc')->limit(5)->get()
                ), 'Tidak ada pengajuan yang menunggu.', route('admin.peminjaman.index') . '?status=pending'),
                $this->panel('Terlambat dikembalikan', $this->barisTerlambat(
                    Peminjaman::whereIn('status_peminjaman', self::AKTIF)->where('tanggal_kembali_rencana', '<', $hariIni)->with(['alat', 'peminjam'])->orderBy('tanggal_kembali_rencana')->limit(5)->get()
                ), 'Tidak ada peminjaman yang terlambat.', route('admin.pengembalian.create'), 'Catat pengembalian'),
                $this->panel('Stok hampir habis', array_map(
                    fn ($a) => $this->row($a->nama_alat, $a->kode_alat, 'Stok ' . $a->stok, $a->stok == 0 ? 'danger' : 'warn'),
                    Alat::where('stok', '<=', 2)->orderBy('stok')->limit(5)->get()
                ), 'Semua stok masih aman.', route('alat.index')),
                $this->panel('Aktivitas terbaru', array_map(
                    fn ($l) => $this->row($l->aktivitas, ($l->user->username ?? 'user dihapus') . ' - ' . $l->waktu),
                    LogAktivitas::with('user')->orderBy('waktu', 'desc')->limit(6)->get()
                ), 'Belum ada aktivitas.', route('admin.log.index')),
            ],
            'shortcuts' => [
                ['bi-tools', 'Alat', route('alat.index')],
                ['bi-tags', 'Kategori', route('kategori.index')],
                ['bi-person-badge', 'Peminjam', route('admin.peminjam.index')],
                ['bi-people', 'Users', route('admin.users.index')],
                ['bi-shield-lock', 'Roles', route('admin.roles.index')],
                ['bi-clock-history', 'Log aktivitas', route('admin.log.index')],
                ['bi-database-down', 'Download database', route('admin.database.export')],
            ],
        ];
    }

    protected function dataPetugas(): array
    {
        $hariIni   = date('Y-m-d');
        $pending   = Peminjaman::where('status_peminjaman', 'pending')->count();
        $kembali   = Peminjaman::where('status_peminjaman', 'menunggu_pengembalian')->count();
        $terlambat = Peminjaman::whereIn('status_peminjaman', self::AKTIF)->where('tanggal_kembali_rencana', '<', $hariIni)->count();
        $denda     = Pengembalian::where('status_denda', 'belum_lunas')->count();
        $dendaRp   = $this->jumlah('SELECT COALESCE(SUM(denda),0) AS total FROM pengembalian WHERE status_denda = ?', ['belum_lunas']);

        return [
            'aksi' => ['Kelola peminjaman', route('petugas.peminjaman.index')],
            'tiles' => [
                $this->tile('bi-hourglass-split', 'Pengajuan perlu diperiksa', $pending, 'setujui atau tolak', route('petugas.peminjaman.index'), $pending ? 'warn' : 'muted'),
                $this->tile('bi-arrow-counterclockwise', 'Menunggu pengembalian', $kembali, 'perlu dicatat kondisinya', route('petugas.pengembalian.index'), $kembali ? 'warn' : 'muted'),
                $this->tile('bi-alarm', 'Terlambat', $terlambat, 'melewati tanggal rencana kembali', route('petugas.peminjaman.riwayat') . '?status=disetujui', $terlambat ? 'danger' : 'muted'),
                $this->tile('bi-cash-stack', 'Denda belum lunas', $denda, $this->rp($dendaRp) . ' belum dibayar', route('petugas.denda.index'), $denda ? 'danger' : 'ok'),
            ],
            'panels' => [
                $this->panel('Pengajuan menunggu', $this->barisPengajuan(
                    Peminjaman::where('status_peminjaman', 'pending')->with(['alat', 'peminjam'])->orderBy('tanggal_pengajuan', 'desc')->limit(5)->get()
                ), 'Tidak ada pengajuan yang menunggu.', route('petugas.peminjaman.index')),
                $this->panel('Siap diproses pengembaliannya', array_map(
                    fn ($p) => $this->row(
                        ($p->alat->nama_alat ?? '-') . ' x' . $p->jumlah_pinjam,
                        ($p->peminjam->username ?? '-') . ' - rencana ' . $this->tgl($p->tanggal_kembali_rencana),
                        'Proses', 'ok', route('petugas.pengembalian.create', ['id' => $p->id_peminjaman])
                    ),
                    Peminjaman::where('status_peminjaman', 'menunggu_pengembalian')->with(['alat', 'peminjam'])->orderBy('tanggal_kembali_rencana')->limit(5)->get()
                ), 'Tidak ada pengembalian yang menunggu.', route('petugas.pengembalian.index')),
                $this->panel('Terlambat dikembalikan', $this->barisTerlambat(
                    Peminjaman::whereIn('status_peminjaman', self::AKTIF)->where('tanggal_kembali_rencana', '<', $hariIni)->with(['alat', 'peminjam'])->orderBy('tanggal_kembali_rencana')->limit(5)->get()
                ), 'Tidak ada peminjaman yang terlambat.', route('petugas.peminjaman.riwayat') . '?status=disetujui'),
            ],
            'shortcuts' => [
                ['bi-clock-history', 'Riwayat peminjaman', route('petugas.peminjaman.riwayat')],
                ['bi-cash-stack', 'Denda peminjam', route('petugas.denda.index')],
                ['bi-printer', 'Cetak laporan', route('petugas.laporan.index')],
            ],
        ];
    }

    protected function dataPeminjam(User $user): array
    {
        $hariIni  = date('Y-m-d');
        $tunggak  = Pengembalian::tunggakan($user->id);
        $milik    = fn () => Peminjaman::where('id_peminjam', $user->id);
        $dipinjam = $milik()->where('status_peminjaman', 'disetujui')->count();
        $pending  = $milik()->where('status_peminjaman', 'pending')->count();
        $verif    = $milik()->where('status_peminjaman', 'menunggu_pengembalian')->count();

        $status = [
            'pending' => ['Pending', 'warn'], 'disetujui' => ['Dipinjam', 'ok'], 'ditolak' => ['Ditolak', 'danger'],
            'menunggu_pengembalian' => ['Menunggu verifikasi', 'warn'], 'dikembalikan' => ['Dikembalikan', 'muted'],
        ];

        return [
            'aksi' => ['Pinjam alat', route('peminjam.alat.index')],
            'tiles' => [
                $this->tile('bi-box-seam', 'Sedang dipinjam', $dipinjam, 'ajukan pengembalian saat selesai', route('peminjam.dipinjam'), 'muted'),
                $this->tile('bi-hourglass-split', 'Menunggu persetujuan', $pending, 'diperiksa petugas', route('peminjam.riwayat'), $pending ? 'warn' : 'muted'),
                $this->tile('bi-arrow-counterclockwise', 'Menunggu verifikasi', $verif, 'pengembalian diperiksa petugas', route('peminjam.dipinjam'), $verif ? 'warn' : 'muted'),
                $this->tile('bi-cash-stack', 'Denda belum lunas', $tunggak['jumlah'], $this->rp($tunggak['total']) . ', bayar ke petugas', route('peminjam.denda'), $tunggak['jumlah'] ? 'danger' : 'ok'),
            ],
            'panels' => [
                $this->panel('Sedang kamu pinjam', array_map(function ($p) use ($hariIni) {
                    $sisa = $this->selisihHari($p->tanggal_kembali_rencana, $hariIni);
                    return $this->row(
                        ($p->alat->nama_alat ?? '-') . ' x' . $p->jumlah_pinjam,
                        $p->kode_peminjaman . ' - kembali ' . $this->tgl($p->tanggal_kembali_rencana),
                        $sisa < 0 ? 'Telat ' . abs($sisa) . ' hari' : ($sisa === 0 ? 'Hari ini' : $sisa . ' hari lagi'),
                        $sisa < 0 ? 'danger' : ($sisa <= 1 ? 'warn' : 'muted')
                    );
                }, $milik()->where('status_peminjaman', 'disetujui')->with('alat')->orderBy('tanggal_kembali_rencana')->limit(5)->get()),
                    'Tidak ada alat yang sedang dipinjam.', route('peminjam.dipinjam'), 'Ajukan pengembalian'),
                $this->panel('Riwayat terbaru', array_map(
                    fn ($p) => $this->row(
                        ($p->alat->nama_alat ?? '-') . ' x' . $p->jumlah_pinjam,
                        $p->kode_peminjaman . ' - pinjam ' . $this->tgl($p->tanggal_pinjam),
                        $status[$p->status_peminjaman][0] ?? $p->status_peminjaman,
                        $status[$p->status_peminjaman][1] ?? 'muted'
                    ),
                    $milik()->with('alat')->orderBy('id_peminjaman', 'desc')->limit(5)->get()
                ), 'Belum ada riwayat peminjaman.', route('peminjam.riwayat')),
            ],
            'shortcuts' => [
                ['bi-tools', 'Daftar alat', route('peminjam.alat.index')],
                ['bi-stopwatch', 'Sedang dipinjam', route('peminjam.dipinjam')],
                ['bi-clock-history', 'Riwayat', route('peminjam.riwayat')],
                ['bi-cash-stack', 'Denda saya', route('peminjam.denda')],
            ],
        ];
    }

    /*
    |----------------------------------------------------------------------
    | Pembantu
    |----------------------------------------------------------------------
    */

    protected function barisPengajuan(array $list): array
    {
        return array_map(fn ($p) => $this->row(
            ($p->alat->nama_alat ?? '-') . ' x' . $p->jumlah_pinjam,
            ($p->peminjam->username ?? '-') . ' - pinjam ' . $this->tgl($p->tanggal_pinjam),
            'Pending', 'warn'
        ), $list);
    }

    protected function barisTerlambat(array $list): array
    {
        $hariIni = date('Y-m-d');

        return array_map(fn ($p) => $this->row(
            ($p->alat->nama_alat ?? '-') . ' x' . $p->jumlah_pinjam,
            ($p->peminjam->username ?? '-') . ' - seharusnya ' . $this->tgl($p->tanggal_kembali_rencana),
            abs($this->selisihHari($p->tanggal_kembali_rencana, $hariIni)) . ' hari', 'danger'
        ), $list);
    }

    protected function tile(string $icon, string $label, int|string $n, string $sub, string $url, string $tone = 'muted'): array
    {
        return compact('icon', 'label', 'n', 'sub', 'url', 'tone');
    }

    protected function panel(string $judul, array $rows, string $kosong, string $url, string $urlLabel = 'Lihat semua'): array
    {
        return compact('judul', 'rows', 'kosong', 'url', 'urlLabel');
    }

    protected function row(string $judul, string $sub, ?string $badge = null, string $tone = 'muted', ?string $url = null): array
    {
        return compact('judul', 'sub', 'badge', 'tone', 'url');
    }

    protected function jumlah(string $sql, array $bindings = []): float
    {
        return (float) (Connection::selectOne($sql, $bindings)['total'] ?? 0);
    }

    protected function rp(float|int $n): string
    {
        return 'Rp' . number_format($n, 0, ',', '.');
    }

    /** Selisih hari: positif = masih ada waktu, negatif = sudah lewat. */
    protected function selisihHari(string $tanggal, string $hariIni): int
    {
        return (int) floor((strtotime($tanggal) - strtotime($hariIni)) / 86400);
    }

    protected function tgl(?string $ymd): string
    {
        if (! $ymd) {
            return '-';
        }

        $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $t     = strtotime($ymd);

        return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
    }

    protected function tanggalHariIni(): string
    {
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $hari[(int) date('w')] . ', ' . $this->tgl(date('Y-m-d'));
    }
}