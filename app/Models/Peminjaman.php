<?php

namespace App\Models;

use Sakuci\Database\{Model, Relation};

class Peminjaman extends Model
{
    /** Satu-satunya tempat status, label, dan nada warna (kelas tone-* di theme.css). */
    public const STATUS = [
        'pending'               => ['label' => 'Pending',               'nada' => 'warn'],
        'disetujui'             => ['label' => 'Disetujui',             'nada' => 'info'],
        'menunggu_pengembalian' => ['label' => 'Menunggu pengembalian', 'nada' => 'warn'],
        'dikembalikan'          => ['label' => 'Dikembalikan',          'nada' => 'ok'],
        'ditolak'               => ['label' => 'Ditolak',               'nada' => 'danger'],
        'dibatalkan'            => ['label' => 'Dibatalkan',            'nada' => 'muted'],
    ];

    /** Urutan jalur normal; ditolak dan dibatalkan berhenti di tengah jalur. */
    public const JALUR = ['pending', 'disetujui', 'menunggu_pengembalian', 'dikembalikan'];

    protected static ?string $table = 'peminjaman';
    protected string $primaryKey = 'id_peminjaman';

    protected array $fillable = [
        'kode_peminjaman', 'id_peminjam', 'disetujui_oleh', 'id_alat', 'jumlah_pinjam',
        'tanggal_pengajuan', 'tanggal_pinjam', 'tanggal_kembali_rencana',
        'status_peminjaman', 'catatan',
    ];


    public function peminjam()
    {
        return $this->belongsTo(User::class, 'id_peminjam', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh', 'id');
    }


    public function alat()
    {
        return $this->belongsTo(Alat::class, 'id_alat', 'id_alat');
    }

    // Sakuci gak dukung scope(), jadi pakai static method biasa
    public static function pending()
    {
        return static::where('status_peminjaman', 'pending');
    }

    public function pengembalian()
    {
        return $this->hasOne(Pengembalian::class, 'id_peminjaman', 'id_peminjaman');
    }
}