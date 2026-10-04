-- alter_status_peminjaman_add_dibatalkan

ALTER TABLE `peminjaman`
MODIFY COLUMN `status_peminjaman`
    ENUM('pending', 'disetujui', 'ditolak', 'menunggu_pengembalian', 'dikembalikan', 'dibatalkan')
    NOT NULL DEFAULT 'pending';