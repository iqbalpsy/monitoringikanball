# Testing & Debugging Fitur Registrasi

## ✅ Perbaikan yang Sudah Dilakukan

### 1. **Menambahkan Kolom Phone ke Tabel Users**

-   ✅ Migration dibuat: `2025_10_12_131209_add_phone_to_users_table.php`
-   ✅ Kolom `phone` VARCHAR(20) NULLABLE ditambahkan
-   ✅ Migration berhasil dijalankan

### 2. **Update RegisterController**

-   ✅ Ganti Validator::make dengan $request->validate()
-   ✅ Tambahkan error logging dengan \Log::error()
-   ✅ Redirect ke dashboard yang sesuai berdasarkan role:
    -   Admin → `admin.dashboard`
    -   User → `user.dashboard`
-   ✅ Tampilkan pesan error yang lebih detail

### 3. **Cache Cleared**

-   ✅ Configuration cache
-   ✅ Application cache
-   ✅ Route cache

## 🧪 Cara Testing Registrasi

### Test 1: Registrasi User Baru

1. Buka browser: `http://127.0.0.1:8000/register`
2. Isi form dengan data:
    ```
    Nama: John Doe
    Email: john@example.com
    Phone: 081234567890
    Password: password123
    Confirm Password: password123
    ```
3. Centang checkbox "Setuju dengan Syarat & Ketentuan"
4. Klik "Daftar Sekarang"
5. **Expected**: Redirect ke `/user/dashboard` dengan pesan sukses

### Test 2: Email Unique Validation

1. Coba daftar lagi dengan email yang sama
2. **Expected**: Error "The email has already been taken."

### Test 3: Password Confirmation

1. Isi password dan konfirmasi password dengan nilai berbeda
2. **Expected**: Error "The password field confirmation does not match."

### Test 4: Password Minimal 8 Karakter

1. Isi password dengan kurang dari 8 karakter
2. **Expected**: Error "The password field must be at least 8 characters."

### Test 5: Phone Optional

1. Kosongkan field phone
2. **Expected**: Registrasi tetap berhasil (phone nullable)

## 📋 Checklist Debugging

### Database

-   [x] Tabel `users` exists
-   [x] Kolom `phone` ada di tabel users
-   [x] Kolom `role` dengan default 'user'
-   [x] Kolom `is_active` dengan default 1
-   [x] Email memiliki unique constraint

### Routes

-   [x] GET `/register` tersedia
-   [x] POST `/register` tersedia
-   [x] Route `user.dashboard` tersedia
-   [x] Route `admin.dashboard` tersedia

### Controller

-   [x] RegisterController dibuat
-   [x] Method `showRegistrationForm()` ada
-   [x] Method `register()` ada
-   [x] Validasi lengkap
-   [x] Error handling ada
-   [x] Auto login setelah register

### Model

-   [x] Field `phone` di `$fillable`
-   [x] Field `name` di `$fillable`
-   [x] Field `email` di `$fillable`
-   [x] Field `password` di `$fillable`
-   [x] Field `role` di `$fillable`
-   [x] Field `is_active` di `$fillable`

### View

-   [x] File `resources/views/auth/register.blade.php` ada
-   [x] Form method POST
-   [x] Action mengarah ke `route('register.post')`
-   [x] CSRF token ada
-   [x] All fields ada (name, email, phone, password, password_confirmation)

## 🔍 Cara Melihat Error

### 1. Check Laravel Log

```powershell
Get-Content "storage\logs\laravel.log" -Tail 50
```

### 2. Check Database

```powershell
& "D:\xampp\mysql\bin\mysql.exe" -u root monitoringikan -e "SELECT * FROM users ORDER BY created_at DESC LIMIT 5;"
```

### 3. Check Route List

```powershell
php artisan route:list --name=register
php artisan route:list --name=dashboard
```

### 4. Tinker Test

```powershell
php artisan tinker
```

Kemudian test:

```php
$user = new App\Models\User();
$user->name = 'Test User';
$user->email = 'test@example.com';
$user->phone = '08123456789';
$user->password = Hash::make('password');
$user->role = 'user';
$user->is_active = true;
$user->save();
```

## 🐛 Common Issues & Solutions

### Issue 1: "Column 'phone' doesn't exist"

**Solution**: Run migration

```powershell
php artisan migrate
```

### Issue 2: "Route [dashboard] not defined"

**Solution**: Update controller to use correct route:

-   `route('user.dashboard')` for regular users
-   `route('admin.dashboard')` for admins

### Issue 3: "The email has already been taken"

**Solution**: Gunakan email yang berbeda atau delete user dari database

### Issue 4: "SQLSTATE[23000]: Integrity constraint violation"

**Solution**: Pastikan semua required fields diisi

### Issue 5: Redirect loop atau session error

**Solution**:

```powershell
php artisan cache:clear
php artisan config:clear
php artisan session:clear
```

## ✨ Struktur Database Users

```sql
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 🎯 Next Steps

1. ✅ Test registrasi dengan berbagai skenario
2. ✅ Verify user tersimpan di database
3. ✅ Test auto login setelah registrasi
4. ✅ Test redirect ke dashboard yang benar
5. ⬜ (Optional) Tambahkan email verification
6. ⬜ (Optional) Tambahkan reCAPTCHA
7. ⬜ (Optional) Tambahkan welcome email

## 📞 Support

Jika masih ada error:

1. Check laravel.log untuk detail error
2. Pastikan semua migration sudah run
3. Pastikan database connection di .env benar
4. Clear all cache
5. Restart php artisan serve

---

**Status**: ✅ Siap untuk testing!
**URL**: http://127.0.0.1:8000/register
