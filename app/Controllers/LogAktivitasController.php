<?php

namespace App\Controllers;

use Sakuci\Controller;
use Sakuci\Http\Request;
use App\Models\LogAktivitas;

class LogAktivitasController extends Controller
{
    private const BATAS = 500;
    private const SISA  = 250;

    public function index(Request $request)
    {
        $total      = LogAktivitas::count();
        $jumlahLama = $total >= self::BATAS ? $total - self::SISA : 0;

        $data = LogAktivitas::OrderBy('waktu', 'desc')->paginate(20);
        return view('core.admin.log.index', compact('data', 'jumlahLama'));
    }

    public function hapusLama(Request $request)
    {
        if (LogAktivitas::count() < self::BATAS) {
            return redirect(route('admin.log.index'))
                ->with('error', 'Log belum mencapai ' . self::BATAS . ' baris.');
        }

        // Log ke-250 dari yang terbaru jadi patokan; semua yang lebih lama dihapus.
        $patokan = LogAktivitas::OrderBy('id_log', 'desc')->offset(self::SISA - 1)->limit(1)->first();
        $jumlah  = LogAktivitas::where('id_log', '<', $patokan->id_log)->count();
        LogAktivitas::where('id_log', '<', $patokan->id_log)->delete();

        return redirect(route('admin.log.index'))
            ->with('success', $jumlah . ' log lama berhasil dihapus');
    }

    public function destroy(Request $request, $id)
    {
        LogAktivitas::destroy($id);
        return back()->with('success', 'Aktivitas Telah Dihapus.');
    }
}