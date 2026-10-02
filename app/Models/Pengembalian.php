<?php

namespace App\Models;

use Sakuci\Database\Model;

class Pengembalian extends Model
{
    public const TARIF_DENDA_PER_HARI = 5000;

    public const PERSEN_KERUSAKAN = [
        'baik' => 0, 'rusak_ringan' => 0.25, 'rusak_berat' => 0.5, 'hilang' => 1.0,
    ];

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

    // rumus denda buat petugas DAN admin
    public static function hitung(string $rencana, string $aktual, string $kondisi, float $hargaAlat, int $jumlah): array
    {
        $selisih   = (strtotime($aktual) - strtotime($rencana)) / 86400;
        $hariTelat = $selisih > 0 ? (int) round($selisih) : 0;

        $dendaTelat     = $hariTelat * self::TARIF_DENDA_PER_HARI;
        $dendaKerusakan = (int) round($hargaAlat * $jumlah * (self::PERSEN_KERUSAKAN[$kondisi] ?? 0));

        return [
            'hari_telat'      => $hariTelat,
            'denda_telat'     => $dendaTelat,
            'denda_kerusakan' => $dendaKerusakan,
            'total'           => $dendaTelat + $dendaKerusakan,
        ];
    }

    // baik & rusak_ringan masuk stok lagi; rusak_berat & hilang tidak
    public static function masukStok(string $kondisi): bool
    {
        return in_array($kondisi, ['baik', 'rusak_ringan'], true);
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
        foreach ($list as $peminjaman) {
            $total += $peminjaman->denda;
        }

        return ['total' => $total, 'jumlah' => count($list)];
    }
}