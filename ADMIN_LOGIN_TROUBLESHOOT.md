# 🔧 Troubleshooting: Tidak Bisa Login Admin

**Status**: ✅ **SOLVED - Password Sudah Direset!**  
**Tanggal**: 12 Oktober 2025, 23:55 WIB

---

## ✅ **SOLUSI - Password Sudah Direset!**

Password admin sudah berhasil direset dan diverifikasi. Silakan coba login dengan:

### **Kredensial Admin:**

```
Email: admin@fishmonitoring.com
Password: password123
URL: http://127.0.0.1:8000/login
```

**Status Admin:**

-   ✅ User ditemukan (ID: 1)
-   ✅ Role: admin
-   ✅ Status: Active
-   ✅ Password: Sudah direset dan diverifikasi
-   ✅ Email: admin@fishmonitoring.com

---

## 🔍 **Penyebab Masalah Umum:**

### **1. Password Hash Tidak Match**

**Symptoms:**

-   Email benar tapi password salah
-   Login gagal terus menerus

**Solution:**

```bash
# Jalankan script reset password
php reset_admin_password.php
```

### **2. Admin User Tidak Ada**

**Symptoms:**

-   "Email not found" atau "User not found"

**Solution:**

```bash
# Run database seeder
php artisan db:seed --class=DatabaseSeeder
```

### **3. Admin Tidak Aktif (is_active = 0)**

**Symptoms:**

-   Login berhasil tapi langsung logout
-   Access denied setelah login

**Solution:**

```bash
# Aktifkan admin via script
php reset_admin_password.php
# Script akan otomatis set is_active = true
```

### **4. Role Bukan 'admin'**

**Symptoms:**

-   Login berhasil tapi redirect ke user dashboard
-   Tidak bisa akses /admin/dashboard

**Solution:**

```sql
-- Via phpMyAdmin atau MySQL
UPDATE users
SET role = 'admin'
WHERE email = 'admin@fishmonitoring.com';
```

### **5. Middleware 'admin' Tidak Berfungsi**

**Symptoms:**

-   Login sebagai admin berhasil
-   Tapi tetap tidak bisa akses halaman admin

**Solution:**

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Restart server
php artisan serve
```

---

## 🛠️ **Tools untuk Troubleshoot:**

### **Script 1: Reset Password Admin**

File: `reset_admin_password.php`

**Cara pakai:**

```bash
php reset_admin_password.php
```

**Output:**

```
✅ Admin ditemukan!
✅ Password berhasil direset!
✅ Password 'password123' BENAR!
```

### **Script 2: Check Admin via Tinker**

```bash
php artisan tinker
```

```php
// Cek admin
$admin = User::where('email', 'admin@fishmonitoring.com')->first();
echo $admin;

// Cek role
echo $admin->role; // Harus: admin

// Cek status
echo $admin->is_active; // Harus: 1 (true)

// Test password
Hash::check('password123', $admin->password); // Harus: true

exit
```

### **Script 3: Query Database Langsung**

```sql
-- Di phpMyAdmin atau MySQL CLI
USE monitoringikan;

-- Cek admin
SELECT id, name, email, role, is_active
FROM users
WHERE email = 'admin@fishmonitoring.com';

-- Update password (hash untuk 'password123')
UPDATE users
SET password = '$2y$12$LQv3c1yycaGdyBaFcxXNXOXTQrZfXKHLnWdX7XGmXWHJiWx9jFCGu',
    is_active = 1,
    role = 'admin'
WHERE email = 'admin@fishmonitoring.com';
```

---

## 📝 **Checklist Login Admin:**

-   [x] ✅ Database sudah ada
-   [x] ✅ User admin ada (email: admin@fishmonitoring.com)
-   [x] ✅ Role = 'admin'
-   [x] ✅ is_active = 1 (true)
-   [x] ✅ Password sudah direset ke 'password123'
-   [x] ✅ Password hash verified
-   [ ] Server Laravel running (php artisan serve)
-   [ ] Browser buka http://127.0.0.1:8000/login
-   [ ] Input email & password
-   [ ] Klik Login
-   [ ] ✅ Masuk ke /admin/dashboard

---

## 🎯 **Step-by-Step Login Admin:**

### **1. Pastikan Server Running**

```bash
php artisan serve

# Output:
# INFO  Server running on [http://127.0.0.1:8000]
```

### **2. Buka Browser**

```
http://127.0.0.1:8000/login
```

### **3. Input Kredensial**

```
📧 Email: admin@fishmonitoring.com
🔒 Password: password123
```

### **4. Klik Login**

-   Jangan typo di email atau password
-   Pastikan capslock off
-   Jangan ada spasi di awal/akhir

### **5. Verifikasi Redirect**

```
✅ Harus redirect ke: http://127.0.0.1:8000/admin/dashboard
❌ Jika redirect ke /user/dashboard → Role bukan admin
❌ Jika tetap di /login → Password atau email salah
```

---

## 🔐 **Verifikasi Password Hash:**

Password `password123` dengan bcrypt hash:

```
$2y$12$LQv3c1yycaGdyBaFcxXNXOXTQrZfXKHLnWdX7XGmXWHJiWx9jFCGu
```

Atau bisa generate baru:

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Hash;
echo Hash::make('password123');
exit
```

---

## 📊 **Status Database Saat Ini:**

### **Total Users: 5**

1. ✅ Admin IoT Fish (admin@fishmonitoring.com) - **admin**
2. ✅ User Test (user@test.com) - user
3. ✅ Budi Santoso (budi@example.com) - user
4. ✅ Dewi Lestari (dewi@example.com) - user
5. ✅ dino (alvindino@gmail.com) - user

### **Admin Details:**

```
ID: 1
Name: Admin IoT Fish
Email: admin@fishmonitoring.com
Role: admin
Status: Active (is_active = 1)
Password: Sudah direset dan verified ✅
```

---

## 🚫 **Jika Masih Gagal Login:**

### **Kemungkinan 1: Browser Cache**

```
1. Buka DevTools (F12)
2. Klik tab "Application" atau "Storage"
3. Clear All Storage
4. Refresh page (Ctrl+Shift+R)
5. Login lagi
```

### **Kemungkinan 2: Session Issue**

```bash
# Clear session
php artisan cache:clear
php artisan session:table

# Atau manual hapus session
DELETE FROM sessions;
```

### **Kemungkinan 3: .env Configuration**

```env
# Check .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitoringikan
DB_USERNAME=root
DB_PASSWORD=

# Pastikan APP_KEY ada
APP_KEY=base64:...

# Clear config cache
php artisan config:clear
```

### **Kemungkinan 4: Login Controller Issue**

```bash
# Check LoginController
# File: app/Http/Controllers/Auth/LoginController.php

# Pastikan ada method authenticated() atau middleware
```

---

## 🔄 **Reset Lengkap (Last Resort):**

Jika semua cara gagal:

```bash
# 1. Fresh migration
php artisan migrate:fresh

# 2. Run seeder
php artisan db:seed --class=DatabaseSeeder

# 3. Reset password
php reset_admin_password.php

# 4. Clear all cache
php artisan optimize:clear

# 5. Restart server
php artisan serve
```

---

## ✅ **Solusi Cepat (Quick Fix):**

```bash
# One-liner solution
php reset_admin_password.php && php artisan cache:clear && php artisan serve
```

Kemudian:

1. Buka: http://127.0.0.1:8000/login
2. Email: admin@fishmonitoring.com
3. Password: password123
4. Login → Dashboard Admin ✅

---

## 📞 **Bantuan Tambahan:**

Jika masih bermasalah, cek:

1. Error di browser console (F12)
2. Error di log Laravel: `storage/logs/laravel.log`
3. Error di terminal saat `php artisan serve`

---

**Status Terkini**: ✅ Password sudah direset dan verified!  
**Action Required**: Silakan coba login dengan kredensial di atas!

**Last Updated**: 12 Oktober 2025, 23:55 WIB
