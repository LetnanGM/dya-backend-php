# DyA — Landing Page (PHP Vanilla)

Landing page DyA dalam **PHP murni**: tanpa framework, tanpa Composer, tanpa Node/Vite,
tanpa template engine. Cukup PHP ≥ 8.1.

## Menjalankan

```bash
php -S localhost:8000 -t public
# buka http://localhost:8000
```

Untuk development lokal (belum punya server mail), simpan pesan form ke file log:

```bash
MAIL_DRIVER=log php -S localhost:8000 -t public
# pesan masuk ke storage/messages.log
```

Di Apache/XAMPP/hosting: arahkan **document root ke folder `public/`**.
Aplikasi juga tetap jalan bila ditaruh di subfolder (mis. `htdocs/dya/public/`) —
semua URL aset dihitung otomatis lewat `asset()` / `url()`.

## Struktur

```
public/                 <- document root (satu-satunya yang boleh diakses web)
├── index.php           halaman utama
├── contact.php         handler form kontak (POST)
└── assets/
    ├── css/main.css    hasil kompilasi scss/ (sudah di-commit)
    ├── js/script.js    jam live, animasi teks, spotlight, submit form via fetch
    └── images/
scss/                   sumber SCSS
src/
├── bootstrap.php       session, timezone, load data
├── config.php          konfigurasi (bisa di-override via env var)
├── helpers.php         e(), url(), asset(), partial(), view(), CSRF, flash
├── data/               site.php, team.php, project.php  (array biasa: `return [...]`)
└── views/              layout.php, home.php, components/, partials/
storage/                log pesan (di-gitignore, tidak ikut ter-deploy ke web)
```

## Konfigurasi (environment variable, opsional)

| Variabel      | Default                      | Keterangan                                  |
|---------------|------------------------------|---------------------------------------------|
| `MAIL_DRIVER` | `mail`                       | `mail` = fungsi `mail()` PHP, `log` = tulis ke `storage/messages.log` |
| `MAIL_TO`     | email di `src/data/site.php` | tujuan pesan form kontak                    |
| `MAIL_FROM`   | `no-reply@<host>`            | alamat pengirim                             |
| `APP_DEBUG`   | (mati)                       | isi `1` untuk menampilkan error PHP         |

> `mail()` butuh MTA/SMTP yang terkonfigurasi di server. Jika belum ada, form akan
> menampilkan pesan gagal (HTTP 500). Untuk produksi, SMTP via PHPMailer/Symfony Mailer
> lebih andal, tapi sengaja tidak dipakai agar proyek tetap vanilla.

## Mengubah isi

- Teks situs, email, sosial media → `src/data/site.php`
- Anggota tim → `src/data/team.php` (`'status' => false` menampilkan slot "Future DyA Member")
- Proyek → `src/data/project.php`
- Gambar → taruh di `public/assets/images/`, tulis `'images/nama.webp'` di data (atau URL penuh)

## Mengubah gaya (SCSS)

CSS hasil kompilasi sudah ada, jadi **tidak perlu build** untuk menjalankan situs.
Jika mengedit `scss/`, kompilasi ulang dengan [Sass](https://sass-lang.com/install):

```bash
sass scss/main.scss public/assets/css/main.css --no-source-map --style=compressed
```

## Keamanan form kontak

CSRF token, honeypot anti-bot, throttle 30 detik per sesi, validasi + batas panjang,
pembersihan CR/LF (anti header injection), dan semua output di-escape dengan `e()`.

## Belum diimplementasikan

- `newsletter.php` — form "Stay Updated" di footer sudah ada, tetapi belum ada handler-nya.
- Tautan footer Services, Blog, Documentation, Support, Privacy, Terms masih `#`.
- `views/components/timeline.php` sudah dikonversi tetapi sengaja belum dipasang di `home.php`
  (sama seperti versi awal).
