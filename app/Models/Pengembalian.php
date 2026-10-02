<?php

namespace App\Models;

use Sakuci\Database\Model;

class Pengembalian extends Model
{
    protected static ?string $table = 'pengembalian';
    protected string $primaryKey = 'id_pengembalian';

   protected array $fillable = [
    'id_peminjaman', 'id_petugas', 'tanggal_kembali_aktual',
    'kondisi_alat', 'hari_telat', 'denda_telat', 'denda_kerusakan', 'denda',
    'status_denda', 'tanggal_bayar', 'id_penerima_bayar', 'catatan',
];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'id_petugas', 'id');
    }

    public static function tunggakan(int $userId): array
{
    $ids = Peminjaman::where('id_peminjam', $userId)->pluck('id_peminjaman');

    if ($ids === []) {
        return ['total' => 0, 'jumlah' => 0];
    }

    $list = static::where('status_denda', 'belum_lunas')
        ->whereIn('id_peminjaman', $ids)
        ->get();

    $total = 0;
    foreach ($list as $p) {
        $total += $p->denda;
    }

    return ['total' => $total, 'jumlah' => count($list)];
}
}