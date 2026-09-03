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
│   └── setup.sql       # Skrip setup db & tabel (auto-run saat volume mysql pertama dibuat)
├── .env.example        # Contoh kredensial container MySQL (salin ke .env)
├── index.php           # Script PHP CLI (opsional)
├── docker-compose.yml  # Definisi container (nginx + php-fpm + redis + mysql)
└── README.md
```

## Menjalankan Web Server (Nginx + PHP-FPM + Redis + MySQL Container)

```bash
cp .env.example .env   # opsional: sesuaikan kredensial MySQL
docker compose up --build
```

Lalu buka browser: **http://localhost:8082**

Service yang berjalan:
- **nginx** (`nginx:alpine`) — web server di port `80` (host: `8082`)
- **php** (custom `php:8.3-fpm` + ekstensi `redis` & `pdo_mysql`) — pemroses file PHP
- **redis** (`redis:7-alpine`) — penyimpanan data in-memory di port `6379` (host: `6381`)
- **mysql** (`mysql:8.4`) — database MySQL, data persisten di volume `mysql-data`

Database `app_db` beserta tabel `items` dibuat otomatis dari `sql/setup.sql`
saat container MySQL pertama kali dijalankan. Data tersimpan di volume
`mysql-data`, jadi tetap ada meski container di-restart atau dihapus.

> Catatan: host port untuk nginx (`8082`) dan redis (`6381`) dipilih agar tidak
> bentrok dengan proyek Docker lain yang sudah berjalan di `8080`/`6380`.
> Port MySQL (`3306`) sengaja tidak dipublish agar tidak bentrok dengan MySQL
> lokal di mesin Anda.

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
dengan penyimpanan di database container MySQL:

- **Tambah** — isi form nama & deskripsi lalu klik Simpan
- **Lihat** — semua data tampil dalam tabel
- **Edit** — klik tombol Edit pada baris data
- **Hapus** — klik tombol Hapus (dengan konfirmasi)

Data disimpan di database `app_db` pada tabel `items` (kolom `id`, `name`,
`description`, `created_at`). Database & tabel dibuat otomatis oleh container
MySQL dari `sql/setup.sql` saat volume `mysql-data` pertama kali dibuat —
tidak perlu setup manual.

Kredensial database (atur di file `.env`, salin dari `.env.example`):
- Database: `app_db`
- User: `app_user`
- Password: sesuai `.env` (default: `app_password`)

### Mengecek data langsung di container MySQL

```bash
# Masuk ke mysql client di dalam container
# (password sesuai .env, default: app_password)
docker compose exec mysql mysql -u app_user -p app_db

# Contoh perintah
SHOW TABLES;                # lihat daftar tabel
SELECT * FROM items;        # lihat semua data
```

Mau mengakses MySQL container dari terminal host? Buka (uncomment) blok
`ports` pada service `mysql` di `docker-compose.yml` (mis. `"3307:3306"`),
jalankan `docker compose up -d`, lalu:

```bash
mysql -u app_user -p -h 127.0.0.1 -P 3307 app_db
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

