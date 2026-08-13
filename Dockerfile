# Menggunakan image PHP CLI resmi
FROM php:8.3-cli

# Menentukan direktori kerja di dalam container
WORKDIR /app

# Menyalin file PHP ke dalam container
COPY index.php .

# Perintah default: jalankan index.php
CMD ["php", "index.php"]
