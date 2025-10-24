# Dokumentasi Fitur History, Profile & Settings

## Overview

Sistem AquaMonitor kini dilengkapi dengan 3 fitur baru yang terintegrasi penuh dengan fungsi monitoring sensor:

1. **History** - Riwayat pembacaan sensor dengan filter dan export
2. **Profile** - Manajemen profil pengguna dan password
3. **Settings** - Pengaturan threshold (batas atas/bawah) untuk suhu, pH, dan oksigen

## 📋 Fitur yang Telah Dibuat

### 1. DATABASE & MIGRATIONS

#### Tabel: `user_settings`

```sql
CREATE TABLE `user_settings` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `temp_min` DECIMAL(5,2) DEFAULT 24.00,
    `temp_max` DECIMAL(5,2) DEFAULT 30.00,
    `ph_min` DECIMAL(4,2) DEFAULT 6.50,
    `ph_max` DECIMAL(4,2) DEFAULT 8.50,
    `oxygen_min` DECIMAL(4,2) DEFAULT 5.00,
    `oxygen_max` DECIMAL(4,2) DEFAULT 8.00,
    `email_notifications` BOOLEAN DEFAULT TRUE,
    `push_notifications` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP,
    `updated_at` TIMESTAMP,
    UNIQUE(`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
```

### 2. MODELS

#### App\Models\UserSettings.php

**Methods:**

-   `user()` - Relasi ke User
-   `isTempNormal($value)` - Cek apakah suhu dalam batas normal
-   `isPhNormal($value)` - Cek apakah pH dalam batas normal
-   `isOxygenNormal($value)` - Cek apakah oksigen dalam batas normal

**Relasi di User.php:**

```php
public function settings()
{
    return $this->hasOne(UserSettings::class);
}
```

### 3. CONTROLLERS

#### App\Http\Controllers\UserController.php

**Methods:**

##### `history(Request $request)`

-   Menampilkan riwayat data sensor dengan pagination (20 per halaman)
-   **Filter tersedia:**
    -   Tanggal mulai & akhir
    -   Tipe data (Semua, Suhu, pH, Oksigen)
-   Menampilkan status (Normal/Perhatian) berdasarkan threshold
-   Indikator warning jika nilai melewati batas

##### `exportHistory(Request $request)`

-   Export data history ke format CSV
-   Filter sama dengan halaman history
-   Format: `sensor_data_YYYY-MM-DD_HHmmss.csv`

##### `profile()`

-   Menampilkan halaman profil user

##### `updateProfile(Request $request)`

-   Update nama, email, no. telepon
-   Validasi email unique
-   Support avatar upload (future feature)

##### `updatePassword(Request $request)`

-   Ubah password dengan validasi password lama
-   Minimum 8 karakter
-   Memerlukan konfirmasi password

##### `settings()`

-   Menampilkan halaman pengaturan threshold
-   Auto-create settings jika belum ada dengan nilai default

##### `updateSettings(Request $request)`

-   Update threshold suhu, pH, oksigen
-   Update preferensi notifikasi
-   **Validasi:**
    -   Batas max >= batas min
    -   Suhu: 0-50°C
    -   pH: 0-14
    -   Oksigen: 0-20 mg/L

### 4. ROUTES

```php
// User History, Profile, Settings
Route::get('/user/history', [UserController::class, 'history'])->name('user.history');
Route::get('/user/history/export', [UserController::class, 'exportHistory'])->name('user.history.export');

Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile');
Route::post('/user/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
Route::post('/user/password', [UserController::class, 'updatePassword'])->name('user.password.update');

Route::get('/user/settings', [UserController::class, 'settings'])->name('user.settings');
Route::post('/user/settings', [UserController::class, 'updateSettings'])->name('user.settings.update');
```

### 5. VIEWS

#### resources/views/user/history.blade.php

**Fitur:**

-   Tabel data dengan kolom: Waktu, Suhu, pH, Oksigen, Status
-   Icon warning (⚠️) jika nilai melewati threshold
-   Badge status: Normal (hijau) / Perhatian (oranye)
-   Filter form: tanggal mulai, tanggal akhir, tipe data
-   Tombol Export CSV
-   Pagination
-   Empty state jika tidak ada data

#### resources/views/user/profile.blade.php

**Fitur:**

-   Card profil dengan avatar (initial letter)
-   Info: Role, No. Telepon, Tanggal bergabung, Login terakhir
-   Form update profil: Nama, Email, No. Telepon
-   Form ubah password: Password lama, baru, konfirmasi
-   Notifikasi success/error
-   Validasi client-side & server-side

#### resources/views/user/settings.blade.php

**Fitur:**

-   3 Card pengaturan threshold: Suhu, pH, Oksigen
-   Range slider interaktif dengan real-time display
-   Visual indicator nilai yang di-set
-   Info box dengan nilai ideal/rekomendasi
-   Card notifikasi: Email & Push notifications
-   Tombol Reset & Simpan
-   Validasi range (min < max)

### 6. INTEGRASI DASHBOARD

#### Dashboard User (dashboard/user.blade.php)

**Update:**

-   Status badge dinamis berdasarkan threshold settings
-   Tampilan range threshold di bawah nilai sensor
-   Warna badge:
    -   ✅ Hijau: Normal (dalam batas)
    -   ⚠️ Oranye: Perhatian (melewati batas)
    -   ➖ Abu-abu: N/A (tidak ada data)
-   Menu sidebar terintegrasi ke History, Profile, Settings

## 🎨 UI/UX Features

### Sidebar Menu

-   Dashboard (active indicator)
-   History
-   Profile
-   Settings
-   Logout

### Color Scheme

-   Suhu: Orange (🟠)
-   pH: Teal (🔵)
-   Oksigen: Green (🟢)
-   Normal status: Green (#10B981)
-   Warning status: Orange (#F59E0B)
-   Primary: Purple (#667eea)

### Icons (Font Awesome 6.4)

-   Dashboard: fa-th-large
-   History: fa-history
-   Profile: fa-user
-   Settings: fa-cog
-   Temperature: fa-thermometer-half
-   pH: fa-flask
-   Oxygen: fa-wind
-   Notifications: fa-bell
-   Warning: fa-exclamation-triangle

## 📊 Default Threshold Values

```php
Temperature: 24.00°C - 30.00°C
pH Level: 6.50 - 8.50
Oxygen: 5.00 - 8.00 mg/L
Email Notifications: ON
Push Notifications: ON
```

## 🔧 Cara Testing

### 1. Setup Database

```bash
# Run migrations
php artisan migrate

# Seed devices dan sensor data
php artisan db:seed --class=DeviceSeeder
php artisan db:seed --class=SensorDataSeeder
```

### 2. Register User Baru

1. Buka: `http://127.0.0.1:8000/register`
2. Isi form registrasi
3. Login dengan akun baru

### 3. Test Settings Page

1. Akses: `http://127.0.0.1:8000/user/settings`
2. Ubah threshold suhu: min=25°C, max=28°C
3. Ubah threshold pH: min=7.0, max=8.0
4. Ubah threshold oksigen: min=6.0, max=7.5
5. Klik "Simpan Pengaturan"
6. Verifikasi notifikasi success muncul

### 4. Test Dashboard Integration

1. Kembali ke Dashboard: `http://127.0.0.1:8000/user/dashboard`
2. Periksa card sensor:
    - Badge status berubah sesuai threshold baru
    - Range threshold tampil di bawah nilai
    - Icon warning muncul jika melewati batas

### 5. Test History Page

1. Akses: `http://127.0.0.1:8000/user/history`
2. Periksa:
    - Tabel menampilkan data sensor
    - Icon warning di kolom yang melewati threshold
    - Badge status (Normal/Perhatian)
3. Test filter:
    - Pilih tanggal: 7 hari terakhir
    - Pilih tipe: Suhu saja
    - Klik Filter
4. Test export:
    - Klik tombol "Export"
    - File CSV ter-download
    - Buka CSV, verifikasi data sesuai

### 6. Test Profile Page

1. Akses: `http://127.0.0.1:8000/user/profile`
2. Test update profil:
    - Ubah nama
    - Ubah no. telepon
    - Klik "Simpan Perubahan"
3. Test ubah password:
    - Masukkan password lama
    - Masukkan password baru (min 8 karakter)
    - Konfirmasi password baru
    - Klik "Update Password"
4. Logout dan login kembali dengan password baru

## 🔄 Flow Integrasi Settings

```
┌─────────────────┐
│  User Settings  │
│  (Database)     │
└────────┬────────┘
         │
         ├──────> Dashboard Controller
         │        ├─ Load settings
         │        └─ Pass to view
         │
         ├──────> Dashboard View
         │        ├─ Check isTempNormal()
         │        ├─ Check isPhNormal()
         │        ├─ Check isOxygenNormal()
         │        └─ Display status badge
         │
         └──────> History View
                  ├─ Check thresholds
                  ├─ Show warnings
                  └─ Display status
```

## 📱 API Integration (Ready for Mobile App)

### Endpoint untuk check status

```php
GET /api/sensor-data?hours=24

Response:
{
    "success": true,
    "data": [...],
    "latest": {
        "temperature": 27.5,
        "ph": 7.2,
        "oxygen": 6.8,
        "status": {
            "temperature": "normal",
            "ph": "normal",
            "oxygen": "warning"
        }
    }
}
```

## 🚀 Future Enhancements

### Notification System (Planned)

-   [ ] Email alerts saat threshold terlampaui
-   [ ] Push notifications via Firebase
-   [ ] SMS alerts (Twilio integration)
-   [ ] Notification history

### Advanced Features (Planned)

-   [ ] Custom alert schedules
-   [ ] Multiple device support per user
-   [ ] Data visualization: trend analysis
-   [ ] Export format: PDF, Excel
-   [ ] Data backup & restore
-   [ ] Mobile app (Flutter)

## 📖 Use Cases

### Use Case 1: Monitoring Harian

**Scenario:** User ingin memantau kondisi kolam setiap hari

1. Login ke dashboard
2. Lihat status real-time (Normal/Perhatian)
3. Jika ada warning, cek detail di History
4. Sesuaikan aerator/feeder berdasarkan data

### Use Case 2: Analisis Mingguan

**Scenario:** User ingin analisis performa kolam seminggu terakhir

1. Buka History page
2. Set filter: 7 hari terakhir
3. Export data ke CSV
4. Analisis di Excel/Google Sheets
5. Identifikasi pola dan tren

### Use Case 3: Custom Threshold

**Scenario:** Jenis ikan berbeda memerlukan kondisi berbeda

1. Buka Settings
2. Sesuaikan threshold:
    - Nila: pH 6.5-8.5, Suhu 25-32°C
    - Lele: pH 6.0-8.0, Suhu 24-30°C
    - Koi: pH 7.0-8.0, Suhu 15-25°C
3. Simpan settings
4. Dashboard otomatis menyesuaikan status

## ✅ Testing Checklist

### Database & Models

-   [x] Migration user_settings berhasil
-   [x] Model UserSettings dengan relasi
-   [x] Seeder UserSettingsSeeder
-   [x] Methods: isTempNormal, isPhNormal, isOxygenNormal

### Controllers

-   [x] UserController dengan semua methods
-   [x] DashboardController load settings
-   [x] Validasi input settings
-   [x] Export CSV functionality

### Views

-   [x] user/history.blade.php dengan filter & pagination
-   [x] user/profile.blade.php dengan 2 forms
-   [x] user/settings.blade.php dengan range sliders
-   [x] Dashboard integration dengan status badges

### Routes

-   [x] GET /user/history
-   [x] GET /user/history/export
-   [x] GET /user/profile
-   [x] POST /user/profile
-   [x] POST /user/password
-   [x] GET /user/settings
-   [x] POST /user/settings

### Integration

-   [x] Sidebar menu di semua halaman
-   [x] Dashboard menampilkan status sesuai threshold
-   [x] History menampilkan warning indicators
-   [x] Settings auto-create dengan default values
-   [x] Notifikasi success/error

## 🐛 Known Issues & Solutions

### Issue 1: Settings not auto-created

**Solution:** Settings dibuat otomatis saat pertama kali akses settings page atau dashboard

### Issue 2: Old threshold values cached

**Solution:** Refresh browser atau clear cache

### Issue 3: CSV export encoding issue

**Solution:** Gunakan UTF-8 BOM untuk Excel compatibility

## 📞 Support

Untuk pertanyaan atau issue, hubungi:

-   Developer: [Your Name]
-   Email: [Your Email]
-   GitHub: [Your Repo]

---

**Last Updated:** 12 Oktober 2025
**Version:** 1.0.0
**Status:** ✅ Production Ready
