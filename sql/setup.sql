-- =====================================================
--  Setup database & tabel untuk container MySQL
--  File ini otomatis dijalankan oleh image mysql saat
--  volume `mysql-data` PERTAMA KALI dibuat (first init).
--  Setelah volume ada, perubahan di sini TIDAK otomatis
--  diterapkan; buat ulang volume dengan `down -v`.
--  Bisa juga dijalankan manual, contoh:
--    docker compose exec -T mysql mysql -u root -p < sql/setup.sql
-- =====================================================

CREATE DATABASE IF NOT EXISTS app_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE app_db;

CREATE TABLE IF NOT EXISTS items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
