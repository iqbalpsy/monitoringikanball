# 📦 Panduan Import Database ke XAMPP

**Database**: monitoringikan  
**File SQL**: `monitoringikan_database.sql`  
**Tanggal**: 12 Oktober 2025, 23:35 WIB

---

## 🎯 Cara Import Database

### **Metode 1: Via phpMyAdmin (Recommended)** ✅

#### **1. Start XAMPP**

```
- Buka XAMPP Control Panel
- Start Apache
- Start MySQL
```

#### **2. Buka phpMyAdmin**

```
URL: http://localhost/phpmyadmin
atau
http://127.0.0.1/phpmyadmin
```

#### **3. Import Database**

```
1. Klik tab "Import" di menu atas
2. Klik tombol "Choose File" atau "Pilih File"
3. Pilih file: monitoringikan_database.sql
4. Scroll ke bawah
5. Klik tombol "Go" atau "Kirim"
```

#### **4. Tunggu Proses Import**

```
⏳ Proses import akan berjalan beberapa detik
✅ Jika berhasil, akan muncul pesan sukses
❌ Jika error, cek di bagian troubleshooting
```

#### **5. Verifikasi Import**

```
1. Refresh phpMyAdmin (F5)
2. Lihat di sidebar kiri, ada database "monitoringikan"
3. Klik database tersebut
4. Lihat 14 tabel berhasil terbuat:
   ✅ users
   ✅ devices
   ✅ sensor_data
   ✅ device_controls
   ✅ user_device_access
   ✅ user_settings
   ✅ sessions
   ✅ cache
   ✅ cache_locks
   ✅ jobs
   ✅ job_batches
   ✅ failed_jobs
   ✅ migrations
```

---

### **Metode 2: Via MySQL Command Line**

#### **1. Buka Command Prompt/PowerShell**

```bash
# Navigate ke folder MySQL di XAMPP
cd C:\xampp\mysql\bin
```

#### **2. Login ke MySQL**

```bash
mysql -u root -p
```

(Tekan Enter jika tidak ada password, atau masukkan password MySQL Anda)

#### **3. Import Database**

```sql
source D:/xampp/htdocs/monitoringikanball/monitoringikanball/monitoringikan_database.sql
```

(Sesuaikan path dengan lokasi file Anda)

#### **4. Verifikasi**

```sql
SHOW DATABASES;
USE monitoringikan;
SHOW TABLES;
SELECT COUNT(*) FROM users;
```

---

### **Metode 3: Via Terminal/PowerShell Langsung**

#### **Run Import Command**

```bash
# Di folder project Laravel
cd D:\xampp\htdocs\monitoringikanball\monitoringikanball

# Import via mysql command
C:\xampp\mysql\bin\mysql -u root -p monitoringikan < monitoringikan_database.sql
```

---

## 📊 Isi Database Setelah Import

### **1. Users Table (4 users)**

| ID  | Name           | Email                    | Role  | Password    |
| --- | -------------- | ------------------------ | ----- | ----------- |
| 1   | Admin IoT Fish | admin@fishmonitoring.com | admin | password123 |
| 2   | User Test      | user@test.com            | user  | password123 |
| 3   | Budi Santoso   | budi@example.com         | user  | password123 |
| 4   | Dewi Lestari   | dewi@example.com         | user  | password123 |

### **2. Devices Table (2 devices)**

| ID  | Device ID | Name                      | Location          |
| --- | --------- | ------------------------- | ----------------- |
| 1   | DEVICE001 | Kolam A - Sensor Utama    | Kolam A, Sektor 1 |
| 2   | DEVICE002 | Kolam B - Sensor Cadangan | Kolam B, Sektor 2 |

### **3. Sensor Data Table (~22 records)**

-   Data sensor untuk 24 jam terakhir
-   Device 1: 11 records (suhu, pH, oksigen)
-   Device 2: 11 records (suhu, pH, oksigen)
-   Data dengan interval waktu berbeda (5 menit, 1 jam, 8 jam, dst)

### **4. User Device Access (4 grants)**

-   User Test → Kolam A & B (view + control)
-   Budi → Kolam A (view only)
-   Dewi → Kolam A (view only)

### **5. User Settings (4 settings)**

-   Semua user punya threshold default:
    -   Suhu: 24-30°C
    -   pH: 6.5-8.5
    -   Oksigen: 5-8 mg/L

---

## 🚫 Troubleshooting

### **Error: "Database exists"**

**Cause**: Database monitoringikan sudah ada

**Solution**:

```sql
-- Hapus database lama dulu
DROP DATABASE monitoringikan;

-- Kemudian import ulang file SQL
```

### **Error: "Access denied"**

**Cause**: User MySQL tidak punya permission

**Solution**:

```sql
-- Login sebagai root
GRANT ALL PRIVILEGES ON monitoringikan.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### **Error: "Foreign key constraint fails"**

**Cause**: Urutan insert data salah atau tabel sudah ada data

**Solution**:

```sql
-- Disable foreign key check sementara
SET FOREIGN_KEY_CHECKS = 0;

-- Import data

-- Enable kembali
SET FOREIGN_KEY_CHECKS = 1;
```

### **Error: "File too large"**

**Cause**: File SQL melebihi limit upload phpMyAdmin

**Solution**:

```
1. Edit file php.ini di C:\xampp\php\php.ini
2. Ubah:
   upload_max_filesize = 64M
   post_max_size = 64M
   max_execution_time = 300
3. Restart Apache di XAMPP
4. Import ulang
```

### **Error: "Unknown collation: utf8mb4_unicode_ci"**

**Cause**: MySQL versi terlalu lama

**Solution**:

```
Update XAMPP ke versi terbaru yang support utf8mb4
Atau ubah collation di file SQL ke utf8_general_ci
```

---

## ✅ Verifikasi Import Berhasil

### **Check via phpMyAdmin:**

```
1. Buka phpMyAdmin
2. Pilih database "monitoringikan"
3. Klik tab "Structure"
4. Lihat 14 tabel tersedia
5. Klik tabel "users" → tab "Browse"
6. Lihat 4 users terdaftar
```

### **Check via MySQL Query:**

```sql
USE monitoringikan;

-- Cek jumlah users
SELECT COUNT(*) as total_users FROM users;
-- Expected: 4

-- Cek admin
SELECT name, email, role FROM users WHERE role = 'admin';
-- Expected: Admin IoT Fish

-- Cek devices
SELECT COUNT(*) as total_devices FROM devices;
-- Expected: 2

-- Cek sensor data
SELECT COUNT(*) as total_sensor_data FROM sensor_data;
-- Expected: ~22

-- Cek user settings
SELECT COUNT(*) as total_settings FROM user_settings;
-- Expected: 4
```

### **Check via Laravel:**

```bash
# Di terminal/command prompt
cd D:\xampp\htdocs\monitoringikanball\monitoringikanball

# Cek koneksi database
php artisan db:show

# Cek tabel
php artisan db:table users
```

---

## 🔧 Konfigurasi Laravel (.env)

Pastikan file `.env` sudah sesuai:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitoringikan
DB_USERNAME=root
DB_PASSWORD=
```

**Jika password MySQL Anda tidak kosong:**

```env
DB_PASSWORD=password_mysql_anda
```

---

## 🚀 Testing Setelah Import

### **1. Start Laravel Server**

```bash
php artisan serve
```

### **2. Buka Browser**

```
http://127.0.0.1:8000
```

### **3. Test Login Admin**

```
Email: admin@fishmonitoring.com
Password: password123

Expected: Masuk ke admin dashboard
```

### **4. Test Login User**

```
Email: user@test.com
Password: password123

Expected: Masuk ke user dashboard
```

### **5. Test Dashboard**

```
- Lihat sensor data tampil
- Chart temperature, pH, oxygen tampil
- Data real-time dari database
```

---

## 📝 Notes Penting

### **Password Hash:**

-   Semua password di database sudah di-hash dengan bcrypt
-   Password plaintext: `password123`
-   Hash: `$2y$12$LQv3c1yycaGdyBaFcxXNXOXTQrZfXKHLnWdX7XGmXWHJiWx9jFCGu`

### **Data Sample:**

-   Sensor data sample dibuat dengan `DATE_SUB(NOW(), INTERVAL X)`
-   Artinya data relatif terhadap waktu import
-   Data akan selalu "fresh" sesuai waktu import

### **Foreign Keys:**

-   Semua relasi tabel menggunakan foreign key constraints
-   Cascade delete untuk data terkait
-   Pastikan tidak hapus data parent sebelum child

### **Sessions Table:**

-   Table sessions kosong saat import
-   Akan terisi otomatis saat user login
-   Laravel session driver: database

---

## 🔄 Update Database Setelah Import

Jika ada perubahan struktur di Laravel (migration baru):

```bash
# Check migration status
php artisan migrate:status

# Run pending migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (HATI-HATI: Akan hapus semua data!)
php artisan migrate:fresh

# Fresh migration + seed
php artisan migrate:fresh --seed
```

---

## 📦 Backup Database

Untuk backup database yang sudah import:

### **Via phpMyAdmin:**

```
1. Pilih database "monitoringikan"
2. Klik tab "Export"
3. Pilih "Quick" export method
4. Format: SQL
5. Klik "Go"
6. File .sql akan terdownload
```

### **Via Command Line:**

```bash
# Navigate ke mysql bin
cd C:\xampp\mysql\bin

# Export database
mysqldump -u root -p monitoringikan > backup_monitoringikan.sql

# Dengan password (jika ada)
mysqldump -u root -pYOURPASSWORD monitoringikan > backup.sql
```

---

## ✅ Checklist Import

-   [ ] XAMPP sudah installed
-   [ ] Apache dan MySQL sudah running
-   [ ] File `monitoringikan_database.sql` sudah ada
-   [ ] Buka phpMyAdmin
-   [ ] Import file SQL berhasil
-   [ ] Database "monitoringikan" terbuat
-   [ ] 14 tabel tersedia
-   [ ] 4 users terdaftar (cek tabel users)
-   [ ] 2 devices terdaftar
-   [ ] Sensor data ada (~22 records)
-   [ ] File .env Laravel sudah sesuai
-   [ ] `php artisan serve` jalan tanpa error
-   [ ] Login admin berhasil
-   [ ] Dashboard tampil data sensor
-   [ ] ✅ SELESAI!

---

**File SQL Location:**

```
D:\xampp\htdocs\monitoringikanball\monitoringikanball\monitoringikan_database.sql
```

**Database Name:** `monitoringikan`  
**Total Tables:** 14  
**Total Users:** 4  
**Total Devices:** 2  
**Total Sensor Data:** ~22 records

**Selamat! Database siap digunakan! 🎉**
