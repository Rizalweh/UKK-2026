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

class AdminPengembalianController extends Controller
{
    private const ATURAN = [
        'tanggal_kembali_aktual' => 'required|date',
        'kondisi_alat'           => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
        'catatan'                => 'nullable|max:500',
    ];

    public function index(Request $request)
    {
        $status = $request->input('status');

        $query = Pengembalian::OrderBy('id_pengembalian', 'desc');
        if (!empty($status)) {
            $query = $query->where('status_denda', $status);
        }

        $data = $query->with(['peminjaman.peminjam', 'peminjaman.alat'])->paginate(10);
        return view('core.admin.pengembalian.index', ['data' => $data, 'statusFilter' => $status]);
    }

    public function create(Request $request)
    {
        $peminjamanList = Peminjaman::whereIn('status_peminjaman', ['disetujui', 'menunggu_pengembalian'])
            ->orderBy('tanggal_kembali_rencana', 'asc')
            ->with(['peminjam', 'alat'])
            ->limit(200)
            ->get();
        $dipilih = $request->input('id');
        return view('core.admin.pengembalian.create', compact('peminjamanList', 'dipilih'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(array_merge(
            ['id_peminjaman' => 'required|exists:peminjaman,id_peminjaman'],
            self::ATURAN
        ));

        $peminjaman = Peminjaman::FindOrFail($data['id_peminjaman']);

        if (!in_array($peminjaman->status_peminjaman, ['disetujui', 'menunggu_pengembalian'], true)) {
            return back()->with('error', 'Peminjaman ini tidak sedang dipinjam.')->withInput();
        }
        if (strtotime($data['tanggal_kembali_aktual']) < strtotime($peminjaman->tanggal_pinjam)) {
            return back()->with('error', 'Tanggal kembali tidak boleh sebelum tanggal pinjam.')->withInput();
        }

        $alat = Alat::FindOrFail($peminjaman->id_alat);
        $h = Pengembalian::hitung(
            $peminjaman->tanggal_kembali_rencana,
            $data['tanggal_kembali_aktual'],
            $data['kondisi_alat'],
            (float) $alat->harga_alat,
            (int) $peminjaman->jumlah_pinjam
        );

        Connection::transaction(function () use ($data, $peminjaman, $alat, $h) {
            Pengembalian::create([
                'id_peminjaman'          => $peminjaman->id_peminjaman,
                'id_petugas'             => User::current()->id,
                'tanggal_kembali_aktual' => $data['tanggal_kembali_aktual'],
                'kondisi_alat'           => $data['kondisi_alat'],
                'hari_telat'             => $h['hari_telat'],
                'denda_telat'            => $h['denda_telat'],
                'denda_kerusakan'        => $h['denda_kerusakan'],
                'denda'                  => $h['total'],
                'status_denda'           => $h['total'] > 0 ? 'belum_lunas' : 'tidak_ada',
                'catatan'                => $data['catatan'] ?: null,
            ]);

            $peminjaman->update(['status_peminjaman' => 'dikembalikan']);

            if (Pengembalian::masukStok($data['kondisi_alat'])) {
                $alat->update(['stok' => $alat->stok + $peminjaman->jumlah_pinjam]);
            }
        });

        LogAktivitas::catat(User::current()->id, "Admin mencatat pengembalian {$peminjaman->kode_peminjaman}, total denda Rp" . number_format($h['total'], 0, ',', '.'));
        return redirect(route('admin.pengembalian.index'))
            ->with('success', 'Pengembalian dicatat. Total denda Rp' . number_format($h['total'], 0, ',', '.') . '.');
    }

    public function edit(Request $request, $id)
    {
        $pengembalian = Pengembalian::FindOrFail($id);
        $peminjaman = $pengembalian->peminjaman;
        $kunci = $pengembalian->status_denda === 'lunas';
        return view('core.admin.pengembalian.edit', compact('pengembalian', 'peminjaman', 'kunci'));
    }

    public function update(Request $request, $id)
    {
        $pengembalian = Pengembalian::FindOrFail($id);

        if ($pengembalian->status_denda === 'lunas') {
            return back()->with('error', 'Denda sudah lunas, data pengembalian dikunci.');
        }

        $data = $request->validate(self::ATURAN);
        $peminjaman = $pengembalian->peminjaman;
        $alat = Alat::FindOrFail($peminjaman->id_alat);
        $n = (int) $peminjaman->jumlah_pinjam;

        if (strtotime($data['tanggal_kembali_aktual']) < strtotime($peminjaman->tanggal_pinjam)) {
            return back()->with('error', 'Tanggal kembali tidak boleh sebelum tanggal pinjam.')->withInput();
        }

        // stok menyesuaikan bila kondisi pindah antara "masuk stok" dan "tidak masuk stok"
        $delta = (Pengembalian::masukStok($data['kondisi_alat']) ? $n : 0)
            - (Pengembalian::masukStok($pengembalian->kondisi_alat) ? $n : 0);

        if ($alat->stok + $delta < 0) {
            return back()->with('error', 'Stok alat tidak mencukupi untuk penyesuaian ini.')->withInput();
        }

        $h = Pengembalian::hitung(
            $peminjaman->tanggal_kembali_rencana,
            $data['tanggal_kembali_aktual'],
            $data['kondisi_alat'],
            (float) $alat->harga_alat,
            $n
        );

        Connection::transaction(function () use ($pengembalian, $alat, $data, $h, $delta) {
            $alat->update(['stok' => $alat->stok + $delta]);
            $pengembalian->update([
                'tanggal_kembali_aktual' => $data['tanggal_kembali_aktual'],
                'kondisi_alat'           => $data['kondisi_alat'],
                'hari_telat'             => $h['hari_telat'],
                'denda_telat'            => $h['denda_telat'],
                'denda_kerusakan'        => $h['denda_kerusakan'],
                'denda'                  => $h['total'],
                'status_denda'           => $h['total'] > 0 ? 'belum_lunas' : 'tidak_ada',
                'catatan'                => $data['catatan'] ?: null,
            ]);
        });

        LogAktivitas::catat(User::current()->id, "Admin mengubah pengembalian {$peminjaman->kode_peminjaman}, denda dihitung ulang Rp" . number_format($h['total'], 0, ',', '.'));
        return redirect(route('admin.pengembalian.index'))
            ->with('success', 'Pengembalian diperbarui. Denda dihitung ulang: Rp' . number_format($h['total'], 0, ',', '.') . '.');
    }

    // "Hapus" = batalkan pengembalian: stok ditarik, status kembali ke menunggu_pengembalian
    public function destroy(Request $request, $id)
    {
        $pengembalian = Pengembalian::FindOrFail($id);

        if ($pengembalian->status_denda === 'lunas') {
            return back()->with('error', 'Denda sudah lunas, pengembalian tidak bisa dibatalkan.');
        }

        $peminjaman = $pengembalian->peminjaman;
        $alat = Alat::FindOrFail($peminjaman->id_alat);
        $tarikStok = Pengembalian::masukStok($pengembalian->kondisi_alat);

        if ($tarikStok && $alat->stok < $peminjaman->jumlah_pinjam) {
            return back()->with('error', 'Stok saat ini lebih kecil dari jumlah yang harus ditarik, pembatalan ditolak.');
        }

        Connection::transaction(function () use ($pengembalian, $peminjaman, $alat, $tarikStok) {
            if ($tarikStok) {
                $alat->update(['stok' => $alat->stok - $peminjaman->jumlah_pinjam]);
            }
            $pengembalian->delete();
            $peminjaman->update(['status_peminjaman' => 'menunggu_pengembalian']);
        });

        LogAktivitas::catat(User::current()->id, "Admin membatalkan pengembalian {$peminjaman->kode_peminjaman} (kembali ke menunggu pengembalian)");
        return back()->with('success', 'Pengembalian dibatalkan. Peminjaman kembali ke status menunggu pengembalian.');
    }
}
