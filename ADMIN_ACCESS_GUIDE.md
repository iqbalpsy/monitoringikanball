# 🔐 Panduan Akses Halaman Admin

**Tanggal**: 12 Oktober 2025, 23:20 WIB  
**Sistem**: AquaMonitor - Fish Monitoring System

---

## 📋 Cara Masuk ke Halaman Admin

### **Metode 1: Login dengan Akun Admin** (Recommended)

#### 1️⃣ **Buka Halaman Login**

```
URL: http://127.0.0.1:8000/login
```

#### 2️⃣ **Gunakan Kredensial Admin**

```
Email: admin@fishmonitoring.com
Password: password123
```

#### 3️⃣ **Klik "Login"**

-   Sistem akan otomatis mendeteksi role admin
-   Redirect otomatis ke halaman admin dashboard

#### 4️⃣ **URL Admin Dashboard**

```
http://127.0.0.1:8000/admin/dashboard
```

---

## 👤 **Akun Admin Default**

Sistem memiliki 1 akun admin yang dibuat otomatis saat seeding:

```php
Nama: Admin IoT Fish
Email: admin@fishmonitoring.com
Password: password123
Role: admin
Status: Active
```

**Lokasi Seeder**: `database/seeders/DatabaseSeeder.php`

---

## 🗺️ **Route Admin yang Tersedia**

Semua route admin menggunakan prefix `/admin` dan middleware `admin`:

### **1. Dashboard Admin**

```
GET /admin/dashboard
Route name: admin.dashboard
Controller: DashboardController@adminDashboard
```

**Features:**

-   Overview semua devices
-   Total users
-   Recent sensor data
-   Total sensor readings

### **2. User Management** (Jika ada)

```
GET /admin/users
Route name: admin.users
Controller: DashboardController@users
```

### **3. Device Management**

```
GET /admin/devices
Route name: admin.devices
Controller: DashboardController@devices
```

### **4. History**

```
GET /admin/history
Route name: admin.history
Controller: DashboardController@history
```

### **5. Monitoring**

```
GET /admin/monitoring
Route name: admin.monitoring
Controller: DashboardController@monitoring
```

### **6. Reports**

```
GET /admin/reports
Route name: admin.reports
Controller: DashboardController@reports
```

### **7. Settings**

```
GET /admin/settings
Route name: admin.settings
Controller: DashboardController@settings
```

---

## 🔒 **Middleware Admin**

File: `app/Http/Middleware/AdminMiddleware.php` (atau check di Kernel)

**Fungsi:**

-   Memeriksa apakah user memiliki `role === 'admin'`
-   Redirect ke user dashboard jika bukan admin
-   Melindungi semua route di prefix `/admin`

**Code Check:**

```php
// Di User Model
public function isAdmin(): bool
{
    return $this->role === 'admin';
}
```

---

## 🧪 **Testing Akses Admin**

### **Test 1: Login sebagai Admin**

```bash
Steps:
1. Buka http://127.0.0.1:8000/login
2. Email: admin@fishmonitoring.com
3. Password: password123
4. Klik Login

Expected:
✅ Login berhasil
✅ Redirect ke /admin/dashboard
✅ Tampil admin interface (bukan user interface)
✅ Menu admin tersedia
```

### **Test 2: Akses Langsung ke Admin URL**

```bash
Scenario A - Sudah Login sebagai Admin:
URL: http://127.0.0.1:8000/admin/dashboard

Expected:
✅ Halaman admin dashboard tampil
✅ No redirect

Scenario B - Belum Login:
URL: http://127.0.0.1:8000/admin/dashboard

Expected:
✅ Redirect ke /login
✅ Setelah login dengan admin → masuk ke /admin/dashboard

Scenario C - Login sebagai User biasa:
URL: http://127.0.0.1:8000/admin/dashboard

Expected:
✅ Access Denied atau Redirect ke /user/dashboard
✅ Admin pages protected
```

---

## 🚫 **Troubleshooting**

### **Problem 1: "Access Denied" atau 403**

**Cause**: Login dengan akun user biasa, bukan admin

**Solution**:

```bash
1. Logout dari akun user
2. Login dengan: admin@fishmonitoring.com / password123
```

### **Problem 2: Admin tidak ada di database**

**Cause**: Database belum di-seed

**Solution**:

```bash
# Run seeder
php artisan db:seed --class=DatabaseSeeder

# Atau fresh migration + seed
php artisan migrate:fresh --seed
```

### **Problem 3: Redirect loop**

**Cause**: Middleware atau DashboardController logic error

**Solution**:

```bash
# Check DashboardController
# Method: index() dan adminDashboard()

# Check middleware 'admin' terdaftar
php artisan route:list | Select-String "admin"
```

### **Problem 4: Password salah**

**Cause**: Password admin diubah atau seeder tidak jalan

**Solution (Reset Password)**:

```bash
# Via tinker
php artisan tinker

$admin = User::where('email', 'admin@fishmonitoring.com')->first();
$admin->password = Hash::make('password123');
$admin->save();
exit
```

---

## 👥 **Membuat Admin Baru**

### **Via Tinker:**

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::create([
    'name' => 'Admin Baru',
    'email' => 'admin.baru@fishmonitoring.com',
    'password' => Hash::make('password_baru'),
    'role' => 'admin',
    'is_active' => true,
]);

echo "Admin created: {$admin->email}";
exit
```

### **Via Database (MySQL):**

```sql
INSERT INTO users (name, email, password, role, is_active, created_at, updated_at)
VALUES (
    'Admin Manual',
    'admin.manual@fishmonitoring.com',
    '$2y$12$aHASHEDPASSWORDHERE', -- Hash password dengan bcrypt
    'admin',
    1,
    NOW(),
    NOW()
);
```

---

## 🔐 **Security Notes**

### **⚠️ PENTING untuk Production:**

1. **Ganti Password Default**

    ```bash
    Password default 'password123' terlalu lemah!
    Gunakan password strong minimal 12 karakter
    ```

2. **Ganti Email Admin**

    ```bash
    Email 'admin@fishmonitoring.com' mudah ditebak
    Gunakan email unik perusahaan
    ```

3. **Enable Two-Factor Authentication** (Future)

    ```bash
    Tambahkan 2FA untuk admin accounts
    ```

4. **Log Admin Activities** (Future)

    ```bash
    Track semua aksi admin (create, update, delete)
    ```

5. **Limit Login Attempts**
    ```bash
    Implementasi rate limiting untuk prevent brute force
    ```

---

## 📊 **Perbedaan Admin vs User**

| Feature                | Admin              | User                            |
| ---------------------- | ------------------ | ------------------------------- |
| **Dashboard URL**      | `/admin/dashboard` | `/user/dashboard`               |
| **Lihat Semua Device** | ✅ Yes             | ❌ No (hanya device assigned)   |
| **Manage Users**       | ✅ Yes             | ❌ No                           |
| **Create Device**      | ✅ Yes             | ❌ No                           |
| **View All Data**      | ✅ Yes             | ❌ No (hanya device sendiri)    |
| **Reports**            | ✅ Yes             | ❌ Limited                      |
| **Settings Global**    | ✅ Yes             | ❌ No (hanya personal settings) |
| **Grant Access**       | ✅ Yes             | ❌ No                           |

---

## 🎯 **Quick Access**

### **Admin Dashboard:**

```
http://127.0.0.1:8000/admin/dashboard
```

### **Admin Login:**

```
URL: http://127.0.0.1:8000/login
Email: admin@fishmonitoring.com
Password: password123
```

### **Check Admin Routes:**

```bash
php artisan route:list | Select-String "admin"
```

---

## 📝 **Step-by-Step untuk Pertama Kali**

### **1. Pastikan Database Sudah Di-Seed**

```bash
php artisan migrate:fresh --seed
```

**Output yang diharapkan:**

```
...
Database seeding completed successfully.

Users created:
Admin: admin@fishmonitoring.com / password123
User1: budi@example.com / password123
User2: dewi@example.com / password123
```

### **2. Start Server**

```bash
php artisan serve
```

### **3. Buka Browser**

```
http://127.0.0.1:8000
```

### **4. Klik "Login" (atau langsung ke /login)**

### **5. Masukkan Kredensial Admin**

```
Email: admin@fishmonitoring.com
Password: password123
```

### **6. Dashboard Admin Terbuka! 🎉**

---

## 🔍 **Verifikasi Admin di Database**

### **Via MySQL:**

```sql
-- Check semua admin
SELECT id, name, email, role, is_active
FROM users
WHERE role = 'admin';

-- Output:
+----+------------------+-----------------------------+-------+-----------+
| id | name             | email                       | role  | is_active |
+----+------------------+-----------------------------+-------+-----------+
|  1 | Admin IoT Fish   | admin@fishmonitoring.com    | admin |         1 |
+----+------------------+-----------------------------+-------+-----------+
```

### **Via Tinker:**

```bash
php artisan tinker
```

```php
User::where('role', 'admin')->get();
// Akan menampilkan semua admin users
```

---

## ✅ **Checklist Login Admin**

-   [ ] Database sudah di-migrate
-   [ ] Seeder sudah dijalankan (admin user created)
-   [ ] Server sudah running (php artisan serve)
-   [ ] Buka http://127.0.0.1:8000/login
-   [ ] Email: admin@fishmonitoring.com
-   [ ] Password: password123
-   [ ] Klik Login
-   [ ] ✅ Masuk ke /admin/dashboard

---

**Selamat! Anda sekarang sudah bisa akses halaman admin! 🎉**

---

**Last Updated**: 12 Oktober 2025, 23:20 WIB  
**System Version**: 3.1.0
