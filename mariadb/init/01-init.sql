-- =====================================================
--  Inisialisasi database MariaDB (dijalankan sekali,
--  hanya saat volume data masih kosong / pertama kali)
-- =====================================================

CREATE TABLE IF NOT EXISTS items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
