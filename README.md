# PHP Hello World dengan Docker

Project "Hello World" menggunakan PHP, Nginx, Redis, MariaDB, dan Docker.

## Struktur Project

```
.
├── web/
│   ├── index.php       # Halaman web utama
│   ├── redis_crud.php  # CRUD sederhana dengan penyimpanan Redis
│   └── mariadb_crud.php# CRUD sederhana dengan penyimpanan MariaDB
├── nginx/
│   └── default.conf    # Konfigurasi Nginx
├── php/
│   └── Dockerfile      # Image PHP-FPM + ekstensi Redis & PDO MySQL
├── mariadb/
│   └── init/
│       └── 01-init.sql # Skrip inisialisasi tabel (dijalankan saat pertama kali)
├── index.php           # Script PHP CLI (opsional)
├── docker-compose.yml  # Definisi container (nginx + php-fpm + redis + mariadb)
└── README.md
```

## Menjalankan Web Server (Nginx + PHP-FPM + Redis + MariaDB)

```bash
docker compose up --build
```

Lalu buka browser: **http://localhost:8082**

Service yang berjalan:
- **nginx** (`nginx:alpine`) — web server di port `80` (host: `8082`)
- **php** (custom `php:8.3-fpm` + ekstensi `redis` & `pdo_mysql`) — pemroses file PHP
- **redis** (`redis:7-alpine`) — penyimpanan data in-memory di port `6379` (host: `6381`)
- **mariadb** (`mariadb:11`) — database relasional di port `3306` (host: `3307`)

> Catatan: host port untuk nginx (`8082`) dan redis (`6381`) dipilih agar tidak
> bentrok dengan proyek Docker lain yang sudah berjalan di `8080`/`6380`.

## Halaman CRUD Redis

Buka **http://localhost:8082/redis_crud.php** untuk mencoba CRUD sederhana:

- **Tambah** — isi form nama & deskripsi lalu klik Simpan
- **Lihat** — semua data tampil dalam tabel
- **Edit** — klik tombol Edit pada baris data
- **Hapus** — klik tombol Hapus (dengan konfirmasi)

Data disimpan sebagai **Redis Hash** dengan pola `item:{id}`, plus counter
otomatis `item:counter` dan set `items` untuk melacak seluruh ID.

### Mengecek data langsung di Redis

```bash
# Masuk ke shell container redis
docker compose exec redis redis-cli

# Contoh perintah
KEYS *          # lihat semua key
HGETALL item:1  # lihat detail data ID 1
```

## Halaman CRUD MariaDB

Buka **http://localhost:8082/mariadb_crud.php** untuk mencoba CRUD sederhana
dengan penyimpanan di database relasional MariaDB:

- **Tambah** — isi form nama & deskripsi lalu klik Simpan
- **Lihat** — semua data tampil dalam tabel
- **Edit** — klik tombol Edit pada baris data
- **Hapus** — klik tombol Hapus (dengan konfirmasi)

Data disimpan di database `app_db` pada tabel `items` (kolom `id`, `name`,
`description`, `created_at`). Tabel dibuat otomatis saat pertama kali MariaDB
dijalankan melalui skrip `mariadb/init/01-init.sql`.

Kredensial database (bisa diubah di `docker-compose.yml`):
- Database: `app_db`
- User: `app_user` / password: `app_pass`
- Root password: `rootpass`

### Mengecek data langsung di MariaDB

```bash
# Masuk ke shell container mariadb
docker compose exec mariadb mariadb -uapp_user -papp_pass app_db

# Contoh perintah
SHOW TABLES;                # lihat daftar tabel
SELECT * FROM items;        # lihat semua data
```

## Menjalankan Versi CLI (Hello World di terminal)

```bash
docker compose --profile cli run --rm app
```

Output yang diharapkan:

```
Hello, World! 👋
PHP version: 8.3.x
```

## Perintah Lain

| Perintah | Fungsi |
| --- | --- |
| `docker compose up --build -d` | Jalankan di background |
| `docker compose ps` | Lihat status container |
| `docker compose down` | Hentikan & hapus container |
| `docker compose down -v` | Hentikan & hapus container + volume data |
| `docker compose logs -f` | Lihat log secara real-time |

