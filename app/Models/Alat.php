<?php

namespace App\Models;

use Sakuci\Database\Connection;
use Sakuci\Database\Model;

class Alat extends Model
{
    protected static ?string $table = 'alat';
    protected string $primaryKey = 'id_alat';
    protected array $fillable = ['id_alat', 'kode_alat', 'nama_alat', 'harga_alat', 'stok', 'kondisi', 'foto_alat', 'id_kategori'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Tambah (perubahanStok > 0) atau kurangi (perubahanStok < 0) stok secara atomik di level SQL,
     * jadi aman dari race condition. Pengurangan ditolak (return false) bila stok tidak cukup.
     */
    public static function ubahStok(int $idAlat, int $perubahanStok): bool
    {
        if ($perubahanStok === 0) {
            return true;
        }

        if ($perubahanStok > 0) {
            return Connection::statement(
                'UPDATE alat SET stok = stok + ? WHERE id_alat = ?',
                [$perubahanStok, $idAlat]
            ) > 0;
        }

        $jumlah = abs($perubahanStok);

        return Connection::statement(
            'UPDATE alat SET stok = stok - ? WHERE id_alat = ? AND stok >= ?',
            [$jumlah, $idAlat, $jumlah]
        ) > 0;
    }
}