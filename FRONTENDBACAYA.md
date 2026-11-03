# ScaffoldSync - Backend REST API

ScaffoldSync adalah platform manajemen rental alat *scaffolding* berbasis REST API yang dibangun menggunakan Laravel 11, Docker Compose, MySQL 8.0, Laravel Sanctum, dan Swagger UI via Dedoc Scramble.

---

## 🚀 Fitur & Modul Backend yang Telah Selesai (Completed Features)

Seluruh fitur inti backend ScaffoldSync telah **100% selesai**, teruji secara otomatis (*automated testing*), dan siap dikonsumsi oleh aplikasi Frontend:

1. **Authentication & RBAC (Role-Based Access Control)**
   - Autentikasi Stateless berbasis token menggunakan **Laravel Sanctum**.
   - Middleware `CheckRole` untuk pembatasan akses 4 Role: **Pemilik (Owner)**, **Admin Gudang**, **Mandor**, dan **Pelanggan**.
   - Rate limiting pada login (`throttle:login`) dan rute API (`throttle:api`).

2. **Core Business & Validation Engine (`ConflictValidationService`)**
   - Validasi bentrok stok otomatis: mencegah sewa jika stok tersedia tidak mencukupi pada rentang tanggal sewa.
   - Validasi bentrok jadwal mandor: mencegah penugasan mandor yang berbenturan dengan jadwal pengiriman/pengambilan proyek lain.

3. **Master Data & Order Management**
   - CRUD Master Barang (*scaffolding*, *jackbase*, *clamp*) beserta kalkulasi otomatis stok disewa/rusak/tersedia.
   - Transaksi Pesanan & Penawaran (*Quotation*).
   - Penjadwalan & Penerbitan Surat Jalan.
   - Audit Inspeksi Lapangan (pencatatan kondisi stok pengembalian baik/rusak/hilang).

4. **Document Generator & Financials**
   - Generator PDF (*Barryvdh DomPDF*) untuk Surat Penawaran, Surat Jalan, dan Invoice.
   - Agregasi Dashboard analytics (total pendapatan, status stok, ringkasan transaksi).

5. **Hardening & CI/CD Pipeline**
   - Global Exception Handling (format error standar JSON).
   - Automated Feature Testing (`AuthApiTest`, `ConflictValidationServiceTest`).
   - GitHub Actions CI/CD (`.github/workflows/laravel.yml`) dengan pengujian terisolasi MySQL 8.0 service container.

---

## 📖 Panduan untuk Frontend Developer (API & Swagger UI)

Untuk mempelajari seluruh *endpoint*, skema *request JSON*, *response code*, dan mencoba API secara interaktif:

### 1. Menjalankan Backend di Lokal (Local Development)
Jika kamu ingin menjalankan backend di laptop lokal:

```bash
# 1. Clone repositori
git clone https://github.com/hill-py/scaffoldsync-backend.git
cd scaffoldsync-backend

# 2. Buat file .env dari template
cp .env.example .env

# 3. Jalankan Docker Container
docker compose up -d

# 4. Generate App Key & Migrasi Database Seeder
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
```

### 2. Membuka Swagger UI Interactive Documentation
Setelah container menyala, buka browser dan akses:  
👉 **`http://localhost:8000/docs/api`**

**Cara Menguji Endpoint Ber-autentikasi di Swagger UI:**
1. Gunakan *endpoint* `POST /api/login` di Swagger UI untuk login dengan salah satu akun seeder di bawah.
2. Salin `access_token` dari *response JSON*.
3. Klik tombol **Authorize** di pojok kanan atas halaman Swagger UI.
4. Masukkan token dengan format: `Bearer <token_kamu>`
5. Kamu sekarang bisa menguji seluruh *endpoint* terproteksi langsung dari browser!

### 3. Akun Testing Default (Database Seeder)

| Role | Email | Password |
| :--- | :--- | :--- |
| **Pemilik (Owner)** | `owner@scaffoldsync.com` | `password` |
| **Admin Gudang** | `admin@scaffoldsync.com` | `password` |
| **Mandor** | `mandor@scaffoldsync.com` | `password` |
| **Pelanggan** | `pelanggan@scaffoldsync.com` | `password` |

---