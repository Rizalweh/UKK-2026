<?php

namespace App\Controllers\Core;

use Sakuci\Controller;
use Sakuci\Http\Request;
use Sakuci\Database\Connection;
use App\Models\Alat;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;

class AdminPeminjamanController extends Controller
{
    private const ATURAN = [
        'id_peminjam'             => 'required|exists:users,id',
        'id_alat'                 => 'required|exists:alat,id_alat',
        'jumlah_pinjam'           => 'required|integer|min:1',
        'tanggal_pinjam'          => 'required|date',
        'tanggal_kembali_rencana' => 'required|date',
        'catatan'                 => 'nullable|max:255',
    ];

    // penuh = pending | perpanjang = disetujui | catatan = status lain
    private function mode($peminjaman): string
    {
        if ($peminjaman->status_peminjaman === 'pending') return 'penuh';
        if ($peminjaman->status_peminjaman === 'disetujui') return 'perpanjang';
        return 'catatan';
    }

    public function index(Request $request)
    {
        $status = $request->input('status');
        $q = trim((string) $request->input('q', ''));

        $query = Peminjaman::OrderBy('id_peminjaman', 'desc');
        if (!empty($status)) { $query = $query->where('status_peminjaman', $status); }
        if ($q !== '')       { $query = $query->where('kode_peminjaman', 'like', '%' . $q . '%'); }

        $data = $query->with(['peminjam', 'alat'])->paginate(10);
        return view('core.admin.peminjaman.index', ['data' => $data, 'statusFilter' => $status, 'q' => $q]);
    }

    public function create(Request $request)
    {
        $peminjamList = User::where('role', 'peminjam')->orderBy('username')->with('profil')->get();
        $alatList = Alat::where('stok', '>', 0)->orderBy('nama_alat')->get();
        return view('core.admin.peminjaman.create', compact('peminjamList', 'alatList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(self::ATURAN);

        if (strtotime($data['tanggal_kembali_rencana']) < strtotime($data['tanggal_pinjam'])) {
            return back()->with('error', 'Tanggal kembali tidak boleh sebelum tanggal pinjam.')->withInput();
        }

        $alat = Alat::FindOrFail($data['id_alat']);
        if ($alat->stok < $data['jumlah_pinjam']) {
            return back()->with('error', "Stok {$alat->nama_alat} hanya {$alat->stok}.")->withInput();
        }

        $data['kode_peminjaman']   = 'PJM-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $data['tanggal_pengajuan'] = date('Y-m-d');
        $data['status_peminjaman'] = 'pending'; // tetap lewat persetujuan petugas
        $data['catatan']           = $data['catatan'] ?: null;

        Peminjaman::create($data);

        LogAktivitas::catat(User::current()->id, "Admin menambah peminjaman {$data['kode_peminjaman']}");
        return redirect(route('admin.peminjaman.index'))->with('success', 'Peminjaman ditambahkan (pending).');
    }

    public function edit(Request $request, $id)
    {
        $peminjaman = Peminjaman::FindOrFail($id);
        $mode = $this->mode($peminjaman);
        $peminjamList = $mode === 'penuh' ? User::where('role', 'peminjam')->orderBy('username')->with('profil')->get() : [];
        $alatList = $mode === 'penuh' ? Alat::orderBy('nama_alat')->get() : [];
        return view('core.admin.peminjaman.edit', compact('peminjaman', 'mode', 'peminjamList', 'alatList'));
    }

    public function update(Request $request, $id)
    {
        $peminjaman = Peminjaman::FindOrFail($id);
        $mode = $this->mode($peminjaman);

        if ($mode === 'penuh') {
            $data = $request->validate(self::ATURAN);
            $alat = Alat::FindOrFail($data['id_alat']);

            if (strtotime($data['tanggal_kembali_rencana']) < strtotime($data['tanggal_pinjam'])) {
                return back()->with('error', 'Tanggal kembali tidak boleh sebelum tanggal pinjam.')->withInput();
            }
            if ($alat->stok < $data['jumlah_pinjam']) {
                return back()->with('error', "Stok {$alat->nama_alat} hanya {$alat->stok}.")->withInput();
            }
        } elseif ($mode === 'perpanjang') {
            // stok sudah terpotong -> alat & jumlah dikunci, hanya boleh perpanjang
            $data = $request->validate([
                'tanggal_kembali_rencana' => 'required|date',
                'catatan'                 => 'nullable|max:255',
            ]);
            if (strtotime($data['tanggal_kembali_rencana']) < strtotime($peminjaman->tanggal_pinjam)) {
                return back()->with('error', 'Tanggal kembali tidak boleh sebelum tanggal pinjam.')->withInput();
            }
        } else {
            $data = $request->validate(['catatan' => 'nullable|max:255']);
        }

        $data['catatan'] = $data['catatan'] ?: null;
        $peminjaman->update($data);

        LogAktivitas::catat(User::current()->id, "Admin mengubah peminjaman {$peminjaman->kode_peminjaman}");
        return redirect(route('admin.peminjaman.index'))->with('success', 'Data peminjaman diperbarui.');
    }

    // Batalkan peminjaman yang sedang berjalan: stok dikembalikan
    public function batalkan(Request $request, $id)
    {
        $peminjaman = Peminjaman::FindOrFail($id);

        if (!in_array($peminjaman->status_peminjaman, ['disetujui', 'menunggu_pengembalian'], true)) {
            return back()->with('error', 'Hanya peminjaman yang sedang berjalan yang bisa dibatalkan.');
        }

        Connection::transaction(function () use ($peminjaman) {
            $alat = Alat::FindOrFail($peminjaman->id_alat);
            $alat->update(['stok' => $alat->stok + $peminjaman->jumlah_pinjam]);
            $peminjaman->update(['status_peminjaman' => 'ditolak', 'catatan' => 'Dibatalkan admin']);
        });

        LogAktivitas::catat(User::current()->id, "Admin membatalkan peminjaman {$peminjaman->kode_peminjaman} (stok dikembalikan)");
        return back()->with('success', 'Peminjaman dibatalkan dan stok dikembalikan.');
    }

    public function destroy(Request $request, $id)
    {
        $peminjaman = Peminjaman::FindOrFail($id);

        if (in_array($peminjaman->status_peminjaman, ['disetujui', 'menunggu_pengembalian'], true)) {
            return back()->with('error', 'Peminjaman yang sedang berjalan tidak bisa dihapus. Gunakan Batalkan.');
        }

        $kembali = Pengembalian::firstWhere('id_peminjaman', $peminjaman->id_peminjaman);
        if ($kembali && $kembali->status_denda === 'belum_lunas') {
            return back()->with('error', 'Denda peminjaman ini belum lunas, tidak bisa dihapus.');
        }

        Connection::transaction(function () use ($peminjaman, $kembali) {
            if ($kembali) { $kembali->delete(); }
            $peminjaman->delete();
        });

        LogAktivitas::catat(User::current()->id, "Admin menghapus peminjaman {$peminjaman->kode_peminjaman}");
        return back()->with('success', 'Peminjaman dihapus.');
    }
}