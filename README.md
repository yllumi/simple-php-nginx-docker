# PHP Hello World dengan Docker

Project "Hello World" menggunakan PHP, Nginx, dan Docker.

## Struktur Project

```
.
├── web/
│   └── index.php      # Halaman web utama
├── nginx/
│   └── default.conf   # Konfigurasi Nginx
├── index.php          # Script PHP CLI (opsional)
├── Dockerfile         # Image untuk versi CLI
├── docker-compose.yml # Definisi container (nginx + php-fpm)
└── README.md
```

## Menjalankan Web Server (Nginx + PHP-FPM)

```bash
docker compose up --build
```

Lalu buka browser: **http://localhost:8080**

Service yang berjalan:
- **nginx** (`nginx:alpine`) — web server di port `8080`
- **php** (`php:8.3-fpm`) — pemroses file PHP

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
