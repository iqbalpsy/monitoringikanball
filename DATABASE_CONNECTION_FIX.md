# ⚠️ ERROR FIX - Database Connection Refused

## 🐛 Error yang Muncul

```
SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it
```

**Error Code**: 2002
**Penyebab**: MySQL server tidak berjalan atau menolak koneksi

---

## 🔧 Solusi - Langkah Demi Langkah

### 1️⃣ **Pastikan MySQL di XAMPP Running**

#### Cara 1: Via XAMPP Control Panel

1. **Buka XAMPP Control Panel**

    - Lokasi default: `C:\xampp\xampp-control.exe`
    - Atau cari "XAMPP" di Start Menu

2. **Start MySQL**

    - Klik tombol **"Start"** di sebelah **MySQL**
    - Tunggu hingga background berubah hijau
    - Status harus menunjukkan: **"Running on port 3306"**

3. **Jika MySQL Gagal Start**:
    - Port 3306 mungkin dipakai aplikasi lain
    - Klik **"Config"** > **"my.ini"**
    - Ganti `port = 3306` ke `port = 3307`
    - Update `.env` file Laravel (lihat bagian 2)

#### Cara 2: Via Command Line (Windows)

```powershell
# Cek status MySQL
netstat -ano | findstr :3306

# Start MySQL service
net start MySQL

# Jika error "service name invalid", coba:
net start MySQL80
# atau
net start MySQL57
```

---

### 2️⃣ **Verifikasi Koneksi Database**

**Test koneksi MySQL**:

```powershell
# Via MySQL Client
mysql -u root -p

# Atau via XAMPP Shell
cd C:\xampp
mysql\bin\mysql.exe -u root
```

**Jika berhasil connect**:

```sql
SHOW DATABASES;
USE monitoringikan;
SHOW TABLES;
```

**Expected output**:

```
Database: monitoringikan
Tables: devices, users, sensor_data, migrations, dll.
```

---

### 3️⃣ **Clear Laravel Cache**

```powershell
cd d:\xampp\htdocs\monitoringikanball\monitoringikanball

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Restart Laravel server
php artisan serve
```

---

### 4️⃣ **Cek File .env**

**Lokasi**: `d:\xampp\htdocs\monitoringikanball\monitoringikanball\.env`

**Pastikan setting berikut benar**:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitoringikan
DB_USERNAME=root
DB_PASSWORD=
```

**Jika MySQL di port lain** (misal 3307):

```env
DB_PORT=3307
```

---

## 🧪 Testing

### Test 1: Cek MySQL Running

**Via Command**:

```powershell
netstat -ano | findstr :3306
```

**Expected Output**:

```
TCP    0.0.0.0:3306           0.0.0.0:0              LISTENING       1234
TCP    [::]:3306              [::]:0                 LISTENING       1234
```

✅ Jika ada output seperti ini = MySQL running
❌ Jika kosong = MySQL tidak running

---

### Test 2: Test Koneksi dari Laravel

**Buat file**: `test_db_connection.php`

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Testing database connection...\n\n";

try {
    DB::connection()->getPdo();
    echo "✅ Database connection: SUCCESS\n";

    $dbName = DB::connection()->getDatabaseName();
    echo "✅ Connected to database: {$dbName}\n";

    $tables = DB::select('SHOW TABLES');
    echo "✅ Total tables: " . count($tables) . "\n\n";

    echo "Tables:\n";
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        echo "  - {$tableName}\n";
    }

} catch (\Exception $e) {
    echo "❌ Database connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}
```

**Run**:

```powershell
php test_db_connection.php
```

---

## 🎯 Troubleshooting

### Problem 1: Port 3306 Sudah Dipakai

**Cek aplikasi yang pakai port 3306**:

```powershell
netstat -ano | findstr :3306
```

**Dapat PID (Process ID)? Cek aplikasi**:

```powershell
tasklist | findstr <PID>
```

**Solusi**:

-   Stop aplikasi yang konflik
-   Atau ganti port MySQL di XAMPP

---

### Problem 2: MySQL Service Not Found

**Install MySQL sebagai Windows Service**:

1. Buka Command Prompt sebagai **Administrator**
2. Navigate ke MySQL bin:
    ```cmd
    cd C:\xampp\mysql\bin
    ```
3. Install service:
    ```cmd
    mysqld --install
    ```
4. Start service:
    ```cmd
    net start MySQL
    ```

---

### Problem 3: Access Denied for 'root'@'localhost'

**Solusi 1 - Reset Root Password**:

1. Stop MySQL di XAMPP Control Panel
2. Buka Command Prompt sebagai Admin
3. Navigate ke MySQL:
    ```cmd
    cd C:\xampp\mysql\bin
    ```
4. Start MySQL skip grant tables:
    ```cmd
    mysqld --skip-grant-tables
    ```
5. Buka Command Prompt baru, login:
    ```cmd
    mysql -u root
    ```
6. Reset password:
    ```sql
    USE mysql;
    UPDATE user SET password=PASSWORD('') WHERE User='root';
    FLUSH PRIVILEGES;
    EXIT;
    ```
7. Restart MySQL normal

**Solusi 2 - Ubah .env**:

```env
DB_USERNAME=root
DB_PASSWORD=yourpassword
```

---

### Problem 4: Database 'monitoringikan' Doesn't Exist

**Buat database**:

```sql
-- Login ke MySQL
mysql -u root

-- Buat database
CREATE DATABASE monitoringikan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Verifikasi
SHOW DATABASES;

-- Keluar
EXIT;
```

**Import SQL file**:

```powershell
# Via command line
mysql -u root monitoringikan < monitoringikan.sql

# Via phpMyAdmin
# http://localhost/phpmyadmin
# Import > Choose File > monitoringikan.sql
```

---

## 📋 Quick Checklist

Ikuti checklist ini untuk memastikan semuanya OK:

-   [ ] **MySQL Service Running**

    ```powershell
    netstat -ano | findstr :3306
    ```

-   [ ] **XAMPP Control Panel - MySQL Green**

    -   Status: Running
    -   Port: 3306

-   [ ] **Database Exists**

    ```sql
    SHOW DATABASES;
    ```

-   [ ] **.env File Correct**

    ```
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=monitoringikan
    DB_USERNAME=root
    ```

-   [ ] **Laravel Cache Cleared**

    ```powershell
    php artisan config:clear
    ```

-   [ ] **Test Connection Success**
    ```powershell
    php test_db_connection.php
    ```

---

## ✨ Solution Summary

**Penyebab Utama**: MySQL di XAMPP tidak running

**Solusi Cepat**:

1. ✅ Start MySQL di XAMPP Control Panel
2. ✅ Clear Laravel config cache
3. ✅ Refresh browser

**Jika Masih Error**:

1. Cek port 3306 tidak konflik
2. Verifikasi database 'monitoringikan' exists
3. Pastikan username/password di .env benar
4. Test koneksi dengan script test_db_connection.php

---

**Status**: Ikuti langkah di atas, error akan teratasi! 🚀

**Created**: October 14, 2025
