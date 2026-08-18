# PHP Hello World dengan Docker

Project "Hello World" menggunakan PHP, Nginx, Redis, MySQL, dan Docker.

## Struktur Project

```
.
├── web/
│   ├── index.php       # Halaman web utama
│   ├── redis_crud.php  # CRUD sederhana dengan penyimpanan Redis
│   └── mariadb_crud.php# CRUD sederhana dengan penyimpanan MySQL
├── nginx/
│   └── default.conf    # Konfigurasi Nginx
├── php/
│   └── Dockerfile      # Image PHP-FPM + ekstensi Redis & PDO MySQL
├── sql/
│   └── setup.sql       # Skrip setup database & tabel untuk MySQL lokal
├── .env                # Kredensial MySQL lokal (host)
├── index.php           # Script PHP CLI (opsional)
├── docker-compose.yml  # Definisi container (nginx + php-fpm + redis)
└── README.md
```

## Menjalankan Web Server (Nginx + PHP-FPM + Redis + MySQL Lokal)

```bash
docker compose up --build
```

Lalu buka browser: **http://localhost:8082**

Service yang berjalan:
- **nginx** (`nginx:alpine`) — web server di port `80` (host: `8082`)
- **php** (custom `php:8.3-fpm` + ekstensi `redis` & `pdo_mysql`) — pemroses file PHP
- **redis** (`redis:7-alpine`) — penyimpanan data in-memory di port `6379` (host: `6381`)

Database yang dipakai adalah **MySQL lokal di mesin host** (port `3306`),
diakses dari dalam container melalui `host.docker.internal` (lihat `.env`).

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

## Halaman CRUD MySQL

Buka **http://localhost:8082/mariadb_crud.php** untuk mencoba CRUD sederhana
dengan penyimpanan di database MySQL lokal (host):

- **Tambah** — isi form nama & deskripsi lalu klik Simpan
- **Lihat** — semua data tampil dalam tabel
- **Edit** — klik tombol Edit pada baris data
- **Hapus** — klik tombol Hapus (dengan konfirmasi)

Data disimpan di database `app_db` pada tabel `items` (kolom `id`, `name`,
`description`, `created_at`). Pastikan database & tabel sudah dibuat di MySQL
lokal Anda, misalnya dengan menjalankan skrip `sql/setup.sql`:

```bash
mysql -u root -p < sql/setup.sql
```

Kredensial database lokal (atur di file `.env`, bukan `docker-compose.yml`):
- Database: `app_db`
- User: `root`
- Password: sesuai MySQL lokal Anda

### Mengecek data langsung di MySQL

```bash
# Jalankan dari terminal host (bukan di dalam container)
mysql -u root -p -h 127.0.0.1 -P 3306 app_db

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

