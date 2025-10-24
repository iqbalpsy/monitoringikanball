# Sinkronisasi Data Sensor - User & Admin Dashboard

## 📋 Ringkasan Perubahan

Dokumen ini menjelaskan perubahan yang dilakukan untuk **menyamakan tampilan data oksigen, pH, dan suhu** antara dashboard user dan admin menggunakan **data dummy yang konsisten**.

## ✅ Status: SELESAI

Tanggal: 12 Oktober 2025

---

## 🎯 Tujuan

1. **Sinkronisasi Data**: Memastikan data sensor yang ditampilkan di dashboard admin dan user adalah sama
2. **Konsistensi Kolom Database**: Menggunakan nama kolom yang benar (`ph`, `oxygen`, `temperature`)
3. **Data Dummy Konsisten**: Generate data dummy per jam untuk 24 jam terakhir
4. **Real-time Update**: Kedua dashboard menggunakan data real dari API yang sama

---

## 🔧 Perubahan yang Dilakukan

### 1. **Model: SensorData.php**

**File**: `app/Models/SensorData.php`

**Perubahan**:

-   ✅ Update `fillable` dari `ph_level` → `ph`
-   ✅ Update `fillable` dari `oxygen_level` → `oxygen`
-   ✅ Update casting untuk menggunakan nama kolom yang benar
-   ✅ Update method `isPhNormal()` untuk menggunakan `$this->ph`
-   ✅ Update method `isOxygenAdequate()` untuk menggunakan `$this->oxygen`

**Alasan**: Menyesuaikan dengan struktur database yang sebenarnya.

---

### 2. **Controller: DashboardController.php**

**File**: `app/Http/Controllers/DashboardController.php`

**Perubahan Admin Dashboard**:

```php
public function adminDashboard()
{
    // ... existing code ...

    // ✅ BARU: Get latest sensor data untuk real-time display
    $latestData = SensorData::latest('recorded_at')->first();

    return view('admin.dashboard', compact('user', 'devices', 'users',
        'recentSensorData', 'totalSensorData', 'latestData'));
}
```

**Perubahan API getSensorData()**:

-   ✅ Update dari `ph_level` → `ph`
-   ✅ Update dari `oxygen_level` → `oxygen`
-   ✅ Memastikan response JSON menggunakan key yang sama (`ph`, `oxygen`, `temperature`)

**Alasan**: Admin dashboard sekarang menggunakan data real sama seperti user dashboard.

---

### 3. **View: Admin Dashboard**

**File**: `resources/views/admin/dashboard.blade.php`

**Perubahan**:

#### Gauge Charts - Hardcoded → Real Data

**SEBELUM**:

```html
<div id="tempValue" class="text-3xl font-bold text-gray-900">26.3</div>
<div id="oxygenValue" class="text-3xl font-bold text-gray-900">8.5</div>
<div id="phValue" class="text-3xl font-bold text-gray-900">6.8</div>
```

**SESUDAH**:

```blade
<div id="tempValue">{{ $latestData ? number_format($latestData->temperature, 1) : '0.0' }}</div>
<div id="oxygenValue">{{ $latestData ? number_format($latestData->oxygen, 1) : '0.0' }}</div>
<div id="phValue">{{ $latestData ? number_format($latestData->ph, 1) : '0.0' }}</div>
```

#### Status Badges - Static → Dynamic

**SEBELUM**:

```html
<div id="tempStatus" class="... bg-green-100 text-green-800">Normal</div>
```

**SESUDAH**:

```blade
<div id="tempStatus" class="{{ $latestData && $latestData->isTemperatureNormal() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
    {{ $latestData && $latestData->isTemperatureNormal() ? 'Normal' : 'Warning' }}
</div>
```

#### JavaScript - Random Data → API Fetch

**SEBELUM**:

```javascript
function updateGaugeData() {
    const newTemp = (24 + Math.random() * 6).toFixed(1); // Random
    const newOxygen = (6 + Math.random() * 4).toFixed(1); // Random
    const newPh = (6.0 + Math.random() * 2.5).toFixed(1); // Random
    // ...
}
```

**SESUDAH**:

```javascript
function updateGaugeData() {
    fetch('{{ route("api.sensor-data") }}?hours=1')
        .then((response) => response.json())
        .then((result) => {
            if (result.success && result.latest) {
                const newTemp = parseFloat(result.latest.temperature).toFixed(
                    1
                );
                const newOxygen = parseFloat(result.latest.oxygen).toFixed(1);
                const newPh = parseFloat(result.latest.ph).toFixed(1);
                // Update gauges with real data
            }
        });
}
```

#### Auto-refresh Interval

-   ✅ Ditambahkan: `setInterval(updateGaugeData, 30000);`
-   ✅ Update setiap 30 detik (sama dengan user dashboard)

**Unit Label**:

-   ✅ Update dari "ppm" → "mg/L" untuk konsistensi

---

### 4. **View: User Dashboard**

**File**: `resources/views/dashboard/user.blade.php`

**Perubahan**:

-   ✅ Update dari `$latestData->ph_level` → `$latestData->ph`
-   ✅ Update dari `$latestData->oxygen_level` → `$latestData->oxygen`
-   ✅ Memastikan validasi status menggunakan kolom yang benar

---

### 5. **Migration: Refresh Sensor Data**

**File**: `database/migrations/2025_01_12_000000_refresh_sensor_data.php`

**Tujuan**: Populate database dengan data dummy konsisten per jam untuk 24 jam terakhir

**Fitur**:

```php
// Clear old sensor data
DB::table('sensor_data')->truncate();

// Create hourly sensor data for last 24 hours
$startTime = Carbon::now()->subDay()->startOfHour();

for ($i = 0; $i < 24; $i++) {
    $recordTime = $startTime->copy()->addHours($i);

    // Device 1 - Kolam A (Lele)
    DB::table('sensor_data')->insert([
        'device_id' => $device->id,
        'ph' => 7.2 + (rand(-30, 30) / 100),      // 6.9 - 7.5
        'temperature' => 27.0 + (rand(-20, 20) / 10),  // 25.0 - 29.0
        'oxygen' => 6.8 + (rand(-15, 15) / 10),   // 5.3 - 8.3
        'recorded_at' => $recordTime,
    ]);
}

// Add current data point (latest reading)
DB::table('sensor_data')->insert([
    'device_id' => $device->id,
    'ph' => 7.3,
    'temperature' => 27.5,
    'oxygen' => 6.9,
    'recorded_at' => Carbon::now(),
]);
```

**Range Nilai Realistis**:

-   **pH**: 6.75 - 7.5 (optimal untuk ikan)
-   **Temperature**: 25°C - 30.5°C (tropis)
-   **Oxygen**: 5.0 - 8.3 mg/L (cukup untuk ikan)

---

### 6. **Database Seeder Update**

**File**: `database/seeders/DatabaseSeeder.php`

**Perubahan**: Diupdate untuk generate data per jam (bukan per 10 menit) untuk konsistensi chart.

---

## 📊 Struktur Database

### Tabel: `sensor_data`

| Column      | Type         | Description              |
| ----------- | ------------ | ------------------------ |
| id          | bigint(20)   | Primary key              |
| device_id   | bigint(20)   | Foreign key ke `devices` |
| temperature | decimal(5,2) | Suhu (°C)                |
| ph          | decimal(4,2) | pH level                 |
| oxygen      | decimal(4,2) | Oksigen (mg/L)           |
| recorded_at | timestamp    | Waktu data direkam       |
| created_at  | timestamp    | Waktu data dibuat        |
| updated_at  | timestamp    | Waktu data diupdate      |

**Indexes**:

-   `device_id` (foreign key)
-   `recorded_at` (untuk sorting dan filtering)
-   `(device_id, recorded_at)` (composite untuk performa)

---

## 🔄 Alur Data

```
┌─────────────────┐
│  IoT Device     │ (simulasi dengan dummy data)
│  Sensor Data    │
└────────┬────────┘
         │
         ├─── insert setiap jam
         │
         ▼
┌─────────────────┐
│  MySQL Database │
│  sensor_data    │
│  - temperature  │
│  - ph           │
│  - oxygen       │
└────────┬────────┘
         │
         ├─── Query via API
         │
         ▼
┌─────────────────────────────────────┐
│  DashboardController::getSensorData │
│  - Group by hour                    │
│  - Average values                   │
│  - Return JSON                      │
└────────┬────────────────────────────┘
         │
         ├─────────────────┬──────────────────┐
         │                 │                  │
         ▼                 ▼                  ▼
  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
  │ User        │  │ Admin       │  │ JavaScript  │
  │ Dashboard   │  │ Dashboard   │  │ Chart.js    │
  │ - Tampil    │  │ - Tampil    │  │ - Update    │
  │   sama      │  │   sama      │  │   realtime  │
  └─────────────┘  └─────────────┘  └─────────────┘
```

---

## 🧪 Testing

### 1. Verifikasi Database

```bash
php check_sensor_table.php
```

**Output yang diharapkan**:

```
✅ Table 'sensor_data' exists

Table Columns:
=============
- id (bigint(20) unsigned)
- device_id (bigint(20) unsigned)
- temperature (decimal(5,2))
- ph (decimal(4,2))
- oxygen (decimal(4,2))
- recorded_at (timestamp)

Total records: 50
```

### 2. Test User Dashboard

1. Login sebagai user: `user@test.com` / `password123`
2. Akses dashboard
3. Verifikasi:
    - ✅ Card Suhu menampilkan nilai real
    - ✅ Card pH menampilkan nilai real
    - ✅ Card Oksigen menampilkan nilai real
    - ✅ Chart menampilkan data 24 jam terakhir
    - ✅ Auto-refresh setiap 30 detik

### 3. Test Admin Dashboard

1. Login sebagai admin: `admin@fishmonitoring.com` / `password123`
2. Akses dashboard
3. Verifikasi:
    - ✅ Gauge Suhu menampilkan nilai yang **sama** dengan user dashboard
    - ✅ Gauge pH menampilkan nilai yang **sama** dengan user dashboard
    - ✅ Gauge Oksigen menampilkan nilai yang **sama** dengan user dashboard
    - ✅ Status badges berubah sesuai threshold (Normal/Warning)
    - ✅ Auto-refresh setiap 30 detik

### 4. Test API Endpoint

```bash
curl http://localhost:8000/api/sensor-data?hours=24
```

**Response yang diharapkan**:

```json
{
  "success": true,
  "data": [
    {
      "temperature": 27.2,
      "ph": 7.15,
      "oxygen": 6.75,
      "time": "08:00"
    },
    ...
  ],
  "latest": {
    "temperature": 27.5,
    "ph": 7.3,
    "oxygen": 6.9
  },
  "count": 24,
  "hours": 24
}
```

---

## 📈 Fitur

### Real-time Monitoring

-   ✅ Auto-refresh setiap 30 detik
-   ✅ Data fetch via AJAX dari API
-   ✅ Chart update tanpa reload halaman

### Konsistensi Data

-   ✅ Admin dan user melihat data yang **sama**
-   ✅ Sumber data: database `sensor_data` yang sama
-   ✅ API endpoint: route `api.sensor-data` yang sama

### Status Monitoring

-   ✅ **Normal**: Nilai dalam range optimal
-   ✅ **Warning**: Nilai di luar range optimal
-   ✅ Color-coded badges (hijau/merah)

### Data Dummy

-   ✅ 24 jam data per jam untuk kedua device
-   ✅ Nilai realistis dengan variasi kecil
-   ✅ Latest reading untuk current display

---

## 🎨 Visual Comparison

### SEBELUM:

-   ❌ Admin: Data random (berbeda setiap reload)
-   ❌ User: Data real dari database
-   ❌ Tidak sinkron

### SESUDAH:

-   ✅ Admin: Data real dari database (sama dengan user)
-   ✅ User: Data real dari database (sama dengan admin)
-   ✅ **100% SINKRON**

---

## 🚀 Cara Run

### Pertama kali (Setup)

```bash
# 1. Jalankan migration untuk populate data dummy
php artisan migrate:refresh --path=database/migrations/2025_01_12_000000_refresh_sensor_data.php --force

# 2. Verifikasi data
php check_sensor_table.php

# 3. Start server
php artisan serve
```

### Update Data Dummy (Optional)

Jika ingin regenerate data dummy:

```bash
php artisan migrate:refresh --path=database/migrations/2025_01_12_000000_refresh_sensor_data.php --force
```

---

## 📝 File yang Diubah

1. ✅ `app/Models/SensorData.php`
2. ✅ `app/Http/Controllers/DashboardController.php`
3. ✅ `resources/views/admin/dashboard.blade.php`
4. ✅ `resources/views/dashboard/user.blade.php`
5. ✅ `database/migrations/2025_01_12_000000_refresh_sensor_data.php`
6. ✅ `database/seeders/DatabaseSeeder.php`

---

## 🔍 Troubleshooting

### Issue: Admin dashboard tidak update

**Solusi**:

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart server
php artisan serve
```

### Issue: Data tidak muncul

**Solusi**:

```bash
# Cek database
php check_sensor_table.php

# Re-run migration jika perlu
php artisan migrate:refresh --path=database/migrations/2025_01_12_000000_refresh_sensor_data.php --force
```

### Issue: JavaScript error di browser

**Solusi**:

1. Buka Developer Console (F12)
2. Cek Network tab untuk API calls
3. Verifikasi route `api.sensor-data` tersedia:
    ```bash
    php artisan route:list | grep sensor-data
    ```

---

## ✨ Kesimpulan

Sinkronisasi data sensor antara dashboard admin dan user **BERHASIL** dengan perubahan berikut:

1. ✅ **Konsistensi Kolom Database**: Menggunakan `ph`, `oxygen`, `temperature`
2. ✅ **Data Dummy Konsisten**: Generate per jam untuk 24 jam terakhir
3. ✅ **Admin Dashboard**: Sekarang menggunakan data real (bukan random)
4. ✅ **Real-time Update**: Kedua dashboard auto-refresh setiap 30 detik
5. ✅ **API Endpoint**: Satu API untuk semua dashboard

**Hasil**: Admin dan user melihat data yang **SAMA PERSIS** ✅

---

## 📧 Kontak

Jika ada pertanyaan atau issue, silakan hubungi tim development.

**Selesai**: 12 Oktober 2025
