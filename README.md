# PHP Hello World dengan Docker

Project "Hello World" menggunakan PHP, Nginx, Redis, dan Docker.

## Struktur Project

```
.
├── web/
│   ├── index.php       # Halaman web utama
│   └── redis_crud.php  # CRUD sederhana dengan penyimpanan Redis
├── nginx/
│   └── default.conf    # Konfigurasi Nginx
├── php/
│   └── Dockerfile      # Image PHP-FPM + ekstensi Redis
├── index.php           # Script PHP CLI (opsional)
├── docker-compose.yml  # Definisi container (nginx + php-fpm + redis)
└── README.md
```

## Menjalankan Web Server (Nginx + PHP-FPM + Redis)

```bash
docker compose up --build
```

Lalu buka browser: **http://localhost:8080**

Service yang berjalan:
- **nginx** (`nginx:alpine`) — web server di port `8080`
- **php** (custom `php:8.3-fpm` + ekstensi `redis`) — pemroses file PHP
- **redis** (`redis:7-alpine`) — penyimpanan data in-memory di port `6379`

## Halaman CRUD Redis

Buka **http://localhost:8080/redis_crud.php** untuk mencoba CRUD sederhana:

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
| `docker compose logs -f` | Lihat log secara real-time |

