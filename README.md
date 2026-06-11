<p align="center">
  <img src="public/images/alinealogo.svg" alt="Alinea Logo" width="300" />
</p>

# Alinea

*[English Version Below](#english-version)*

Alinea adalah aplikasi web dinamis dan modern yang dibangun untuk para penggemar buku. Aplikasi ini berfungsi sebagai platform komprehensif di mana pengguna dapat menemukan buku, membaca dan menulis ulasan, serta berinteraksi dengan komunitas secara real-time.

## Fitur Utama

*   **Katalog & Pencarian Buku:** Jelajahi perpustakaan buku yang luas dengan fitur pencarian dan filter dinamis (berdasarkan genre, rating, dll.) yang ditenagai oleh AJAX tanpa perlu memuat ulang halaman.
*   **Obrolan Komunitas Real-time:** Sistem pesan global atau privat yang didukung oleh WebSockets (Laravel Echo/Reverb) yang memungkinkan komunikasi instan antar pengguna.
*   **Ulasan & Rating Interaktif:** Pengguna dapat memberikan rating pada buku, meninggalkan ulasan terperinci, serta memberikan upvote/downvote pada umpan balik komunitas yang bermanfaat.
*   **Dasbor Admin Filament:** Back-office yang kuat dan aman bagi administrator untuk mengelola pengguna, data buku, dan konten platform dengan mudah.
*   **Autentikasi Aman:** Registrasi pengguna penuh, login, dan reset kata sandi aman berbasis email dengan desain templat email yang cantik.
*   **Komponen UI Dinamis:** Dibangun menggunakan komponen Laravel Blade yang modular dan ditata dengan Tailwind CSS modern.

## Teknologi yang Digunakan

*   **Backend:** Laravel 11 (PHP)
*   **Frontend:** Blade Templates, JavaScript (Vanilla/Fetch API untuk AJAX), Tailwind CSS
*   **Panel Admin:** Filament V3
*   **Infrastruktur Real-time:** Laravel Reverb & Laravel Echo
*   **Database:** MySQL
*   **Asset Bundling:** Vite

## Panduan Instalasi Lokal

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di komputer lokal Anda.

### Persyaratan
*   PHP 8.2+
*   Composer
*   Node.js & npm
*   Database MySQL

### Instalasi

1. **Clone repositori:**
   ```bash
   git clone https://github.com/ichaxpro/Alinea.git
   cd Alinea
   ```

2. **Instal dependensi PHP:**
   ```bash
   composer install
   ```

3. **Instal dependensi frontend:**
   ```bash
   npm install
   ```

4. **Pengaturan Lingkungan (Environment):**
   * Duplikat file `.env.example` dan ubah namanya menjadi `.env`.
   * Perbarui kolom `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` agar sesuai dengan konfigurasi database lokal Anda.

5. **Buat Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Jalankan Migrasi Database:**
   ```bash
   php artisan migrate
   ```

7. **Kompilasi Aset Frontend:**
   ```bash
   npm run dev
   ```

8. **Jalankan Server Pengembangan:**
   ```bash
   php artisan serve
   ```

9. **(Opsional) Jalankan Server WebSocket untuk Obrolan:**
   ```bash
   php artisan reverb:start
   ```

Aplikasi sekarang dapat diakses di `http://127.0.0.1:8000`.

## Sorotan Struktur Proyek

*   **`app/Http/Controllers/`**: Logika inti untuk merender aplikasi web.
*   **`app/Http/Controllers/Api/`**: Endpoint ringan yang dirancang khusus untuk mengembalikan respons JSON dari permintaan AJAX di latar belakang (contoh: pencarian otomatis, fitur obrolan).
*   **`app/Filament/`**: File konfigurasi untuk dasbor Admin.
*   **`resources/views/`**: Templat HTML Blade dan komponen frontend.
*   **`routes/`**: Dibagi menjadi `web.php` untuk navigasi peramban, `api.php` untuk panggilan AJAX, dan `channels.php` untuk penyiaran pesan WebSocket secara real-time.

---
*jujur cpk*

<br><br>

---

<h1 id="english-version">Alinea (English Version)</h1>

Alinea is a modern, dynamic web application built for book enthusiasts. It serves as a comprehensive platform where users can discover books, read and write reviews, and interact with the community in real-time.

## Key Features

*   **Book Catalog & Search:** Explore a vast library of books with dynamic AJAX-powered search and filtering (by genre, rating, etc.) without page reloads.
*   **Real-time Community Chat:** A global or private messaging system powered by WebSockets (Laravel Echo/Reverb) allowing instant communication between users.
*   **Interactive Reviews & Ratings:** Users can rate books, leave detailed reviews, and upvote/downvote helpful community feedback.
*   **Filament Admin Dashboard:** A powerful, secure back-office for administrators to easily manage users, book data, and platform content.
*   **Secure Authentication:** Full user registration, login, and secure email-based password resets with custom, beautifully designed email templates.
*   **Dynamic UI Components:** Built using reusable Laravel Blade components and stylized with modern Tailwind CSS.

## Technology Stack

*   **Backend:** Laravel 11 (PHP)
*   **Frontend:** Blade Templates, JavaScript (Vanilla/Fetch API for AJAX), Tailwind CSS
*   **Admin Panel:** Filament V3
*   **Real-time Infrastructure:** Laravel Reverb & Laravel Echo
*   **Database:** MySQL
*   **Asset Bundling:** Vite

## Local Development Setup

Follow these steps to get the project up and running on your local machine.

### Prerequisites
*   PHP 8.2+
*   Composer
*   Node.js & npm
*   MySQL Database

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ichaxpro/Alinea.git
   cd Alinea
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install frontend dependencies:**
   ```bash
   npm install
   ```

4. **Environment Setup:**
   * Duplicate the `.env.example` file and rename it to `.env`.
   * Update the `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` fields to match your local database configuration.

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Run Database Migrations:**
   ```bash
   php artisan migrate
   ```

7. **Compile Frontend Assets:**
   ```bash
   npm run dev
   ```

8. **Start the Development Server:**
   ```bash
   php artisan serve
   ```

9. **(Optional) Start the WebSocket Server for Chat:**
   ```bash
   php artisan reverb:start
   ```

The application will now be available at `http://127.0.0.1:8000`.

## Project Structure Highlights

*   **`app/Http/Controllers/`**: Core logic for the web application.
*   **`app/Http/Controllers/Api/`**: Lightweight endpoints specifically designed to return JSON responses for background AJAX requests (e.g., search autocomplete, chat).
*   **`app/Filament/`**: Configuration and resource files for the Admin dashboard.
*   **`resources/views/`**: Blade HTML templates and reusable frontend components.
*   **`routes/`**: Divided into `web.php` for browser navigation, `api.php` for AJAX calls, and `channels.php` for real-time WebSocket broadcasting.

---
*want to sleep*
