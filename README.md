<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development/)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Sistem Tracking Sales Mission

Sistem ini digunakan untuk memantau aktivitas setiap Leader Sub-Task dalam proyek Sales Mission, oleh Captain dan Steering Committee.

## Fitur Utama

- Manajemen Sub-Task (membuat, mengedit, menghapus)
- Monitoring aktivitas yang di-assign ke team member
- Pelaporan dan ekspor data (Excel & PDF)
- Notifikasi tugas selesai dan tenggat waktu

## Panduan Penggunaan

### Login
1. Buka browser dan akses URL sistem
2. Login menggunakan kredensial yang telah diberikan

### User Default
- **Admin**: admin@example.com / password
- **SC**: sc@example.com / password
- **Captain**: captain@example.com / password
- **Leader**: leader@example.com / password
- **Member**: member@example.com / password

### Mengelola User
1. Hanya Admin yang dapat menambahkan pengguna baru
2. Pengguna baru dapat ditambahkan melalui menu "User Management"
3. Setiap user harus memiliki role yang sesuai (SC, Captain, Leader, atau Member)

### Mengelola Sub-Task
1. Buka menu "Task Management" > "Sub Tasks"
2. Klik "New Sub Task" untuk membuat tugas baru
3. Isi detail tugas (nama, deskripsi, tipe, Leader, status, deadline)
4. Klik "Create" untuk menyimpan

### Mengelola Aktivitas
1. Buka menu "Task Management" > "Activities"
2. Klik "New Activity" untuk membuat aktivitas baru
3. Pilih Sub Task, assign ke Team Member, dan tentukan Leader
4. Isi detail aktivitas (judul, deskripsi, status, attachment, deadline)
5. Klik "Create" untuk menyimpan

### Laporan dan Ekspor Data
1. Pada halaman Sub Tasks atau Activities
2. Klik tombol "Export Excel" atau "Export PDF" di bagian atas
3. File akan otomatis diunduh ke komputer Anda

### Notifikasi
Notifikasi akan otomatis dikirim via email ke:
- SC, Captain, dan Leader ketika task selesai
- SC, Captain, dan Leader ketika deadline sudah dekat

## Teknis Implementasi

### Tech Stack

- **Backend**: Laravel 12
- **Database**: MySQL
- **Admin Panel**: FilamentPHP 3
- **Export Data**: Laravel Excel, DomPDF

### Struktur Database

- **users**: Menyimpan data pengguna sistem
- **sub_tasks**: Menyimpan data tugas utama
- **activities**: Menyimpan data aktivitas yang terkait dengan tugas
- **task_notifications**: Menyimpan notifikasi terkait tugas

## Pengembangan & Deployment

### Instalasi Lokal

```
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

### Environment

Pastikan database dan email notification sudah dikonfigurasi di file `.env`
