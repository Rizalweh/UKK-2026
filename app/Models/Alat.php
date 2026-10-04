<?php

namespace App\Models;

use Sakuci\Database\Connection;
use Sakuci\Database\Model;

class Alat extends Model
{
    /** foto alat ke folder public. */
    public const FOLDER_FOTO = 'uploads/foto_alat';

    /** design kondisi alat nada warna di theme.css (kelas tone-*). */
    public const NADA_KONDISI = [
        'Baik'         => 'ok',
        'Rusak Ringan' => 'warn',
        'Rusak Berat'  => 'danger',
    ];

    protected static ?string $table = 'alat';
    protected string $primaryKey = 'id_alat';
    protected array $fillable = ['id_alat', 'kode_alat', 'nama_alat', 'harga_alat', 'stok', 'kondisi', 'foto_alat', 'id_kategori'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /** URL foto alat, atau null bila alat belum punya foto. */
    public function fotoUrl(): ?string
    {
        return $this->foto_alat ? asset(self::FOLDER_FOTO . '/' . $this->foto_alat) : null;
    }

    public function tersedia(): bool
    {
        return (int) $this->stok > 0;
    }

    /** Nada warna untuk pill kondisi: ok | warn | danger | muted. */
    public function nadaKondisi(): string
    {
        return self::NADA_KONDISI[$this->kondisi] ?? 'muted';
    }

    /** Stok paling banyak di seluruh alat; dipakai sebagai pembanding meter stok. */
    public static function stokTertinggi(): int
    {
        $baris = Connection::selectOne('SELECT COALESCE(MAX(stok), 0) AS total FROM alat');

        return (int) ($baris['total'] ?? 0);
    }

    /** Persentase stok alat ini terhadap stok tertinggi (0 sampai 100), untuk meter. */
    public function persenStok(int $stokTertinggi): int
    {
        if ($stokTertinggi <= 0) {
            return 0;
        }

        return (int) round(min(1, (int) $this->stok / $stokTertinggi) * 100);
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