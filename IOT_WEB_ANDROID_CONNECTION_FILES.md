# 🔗 **FILE KONEKSI IoT - WEB - ANDROID**

## **📍 LOKASI FILE PENTING**

Sistem monitoring ini memiliki 3 komponen utama yang saling terhubung:

1. **IoT Hardware (ESP32)** → Kirim data ke Web
2. **Web Backend (Laravel)** → Terima & simpan data, API untuk Android
3. **Android App** → Ambil data dari Web

---

## **🔌 1. KONEKSI IoT (ESP32) → WEB**

### **A. File di ESP32 (Arduino Code)**

**📄 File:** `ESP32_pH_XAMPP_Code.ino`
**Lokasi:** `D:\xampp\htdocs\monitoringikanball\monitoringikanball\ESP32_pH_XAMPP_Code.ino`
**Fungsi:** Code yang diupload ke ESP32 untuk membaca sensor pH dan mengirim data ke web

**Bagian Penting:**

```cpp
// Line 37: URL endpoint web untuk terima data dari IoT
const char* SERVER_URL = "http://10.240.181.8/monitoringikanball/monitoringikanball/public/api/sensor-data/store";

// Line 208-239: Fungsi kirim data ke web via HTTP POST
void sendToServer() {
    HTTPClient http;
    http.begin(SERVER_URL);
    http.addHeader("Content-Type", "application/json");

    // Buat JSON data
    StaticJsonDocument<256> doc;
    doc["device_id"] = DEVICE_ID;
    doc["ph"] = phValue;
    doc["temperature"] = 27.5; // Dummy
    doc["oxygen"] = 6.8;       // Dummy

    String jsonData;
    serializeJson(doc, jsonData);

    // Kirim POST request
    int httpResponseCode = http.POST(jsonData);
    // ... response handling
}
```

**Cara Kerja:**

1. ESP32 baca sensor pH setiap 30 detik
2. ESP32 kirim data via WiFi ke URL server
3. Format: HTTP POST dengan JSON body
4. Data masuk ke database via API Laravel

---

### **B. File di Web (Laravel - API Endpoint untuk IoT)**

#### **📄 File 1: API Route**

**Lokasi:** `routes/api.php`
**Line:** 65-91
**Fungsi:** Mendefinisikan endpoint yang menerima data dari ESP32

```php
// ESP32 pH Sensor Data Receiver (Simple endpoint for ESP32)
Route::post('sensor-data/store', function (Request $request) {
    // Validate input from ESP32
    $validated = $request->validate([
        'device_id' => 'required|integer|exists:devices,id',
        'ph' => 'required|numeric|min:0|max:14',
        'temperature' => 'nullable|numeric',
        'oxygen' => 'nullable|numeric',
    ]);

    // Create sensor data record in database
    $sensorData = \App\Models\SensorData::create([
        'device_id' => $validated['device_id'],
        'ph' => $validated['ph'],
        'temperature' => $validated['temperature'] ?? 27.5,
        'oxygen' => $validated['oxygen'] ?? 6.8,
        'recorded_at' => now(),
    ]);

    // Return success response to ESP32
    return response()->json([
        'success' => true,
        'message' => 'Data sensor berhasil disimpan',
        'data' => $sensorData
    ], 201);
});
```

**Endpoint ESP32:**

```
POST /api/sensor-data/store
```

**Full URL:**

```
http://10.240.181.8/monitoringikanball/monitoringikanball/public/api/sensor-data/store
```

---

#### **📄 File 2: Model SensorData**

**Lokasi:** `app/Models/SensorData.php`
**Fungsi:** Eloquent model untuk menyimpan data sensor ke database

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    protected $table = 'sensor_data';

    protected $fillable = [
        'device_id',
        'ph',
        'temperature',
        'oxygen',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
```

---

#### **📄 File 3: Database Migration**

**Lokasi:** `database/migrations/xxxx_create_sensor_data_table.php`
**Fungsi:** Struktur tabel database untuk menyimpan data sensor

```php
Schema::create('sensor_data', function (Blueprint $table) {
    $table->id();
    $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
    $table->decimal('temperature', 5, 2)->nullable();
    $table->decimal('ph', 4, 2)->nullable();
    $table->decimal('oxygen', 4, 2)->nullable();
    $table->timestamp('recorded_at');
    $table->timestamps();
});
```

**Tabel Database:**

-   **Nama:** `sensor_data`
-   **Database:** `monitoringikan`

---

## **🌐 2. KONEKSI WEB → ANDROID**

### **A. File di Web (Laravel - API untuk Android)**

#### **📄 File 1: Mobile API Controller**

**Lokasi:** `app/Http/Controllers/Api/MobileApiController.php`
**Fungsi:** Controller yang handle semua request dari aplikasi Android

**Methods:**

```php
class MobileApiController extends Controller
{
    // Authentication
    public function register(Request $request)      // Register user baru
    public function login(Request $request)         // Login user
    public function logout(Request $request)        // Logout user

    // Profile
    public function profile(Request $request)       // Get user profile
    public function updateProfile(Request $request) // Update profile

    // Dashboard
    public function dashboard(Request $request)     // Get dashboard data
    public function latestReading(Request $request) // Get latest sensor

    // History
    public function history(Request $request)       // Get history with filter

    // Settings
    public function getSettings(Request $request)   // Get threshold settings
    public function updateSettings(Request $request)// Update settings
}
```

**Line Penting - Dashboard Method (Line 246-370):**

```php
public function dashboard(Request $request)
{
    // Get latest sensor data
    $latestData = SensorData::latest('recorded_at')->first();

    // Get chart data (08:00-17:00)
    $sensorData = SensorData::whereBetween('recorded_at', [$startTime, $endTime])
        ->orderBy('recorded_at', 'asc')
        ->get()
        ->groupBy(...) // Group by hour
        ->map(...);    // Calculate average per hour

    // Return to Android
    return response()->json([
        'success' => true,
        'data' => [
            'latest' => $latestData,
            'chart_data' => $sensorData,
            'statistics' => $stats,
            'status' => $status,
            'alerts' => $alerts
        ]
    ]);
}
```

---

#### **📄 File 2: API Routes untuk Android**

**Lokasi:** `routes/api.php`
**Line:** 9-42
**Fungsi:** Mendefinisikan endpoint API untuk aplikasi Android

```php
// MOBILE APP API ROUTES
Route::prefix('mobile/auth')->group(function () {
    Route::post('register', [MobileApiController::class, 'register']);
    Route::post('login', [MobileApiController::class, 'login']);
});

Route::prefix('mobile')->middleware('auth:sanctum')->group(function () {
    Route::post('logout', [MobileApiController::class, 'logout']);
    Route::get('profile', [MobileApiController::class, 'profile']);
    Route::put('profile', [MobileApiController::class, 'updateProfile']);
    Route::get('dashboard', [MobileApiController::class, 'dashboard']);
    Route::get('latest', [MobileApiController::class, 'latestReading']);
    Route::get('history', [MobileApiController::class, 'history']);
    Route::get('settings', [MobileApiController::class, 'getSettings']);
    Route::put('settings', [MobileApiController::class, 'updateSettings']);
});
```

**Base URL untuk Android:**

```
http://10.240.181.8/monitoringikanball/monitoringikanball/public/api/mobile
```

---

## **🔄 FLOW DATA LENGKAP**

### **Flow 1: IoT → Web (Data Masuk)**

```
┌─────────────────┐
│   ESP32 Sensor  │ Baca pH setiap 30 detik
└────────┬────────┘
         │
         │ HTTP POST JSON
         │ {device_id: 1, ph: 7.2, temp: 27.5, oxygen: 6.8}
         ↓
┌─────────────────────────────────────────────────────┐
│ routes/api.php                                       │
│ POST /api/sensor-data/store                          │
└────────┬────────────────────────────────────────────┘
         │
         │ Validate & Save
         ↓
┌─────────────────────────────────────────────────────┐
│ app/Models/SensorData.php                            │
│ SensorData::create([...])                            │
└────────┬────────────────────────────────────────────┘
         │
         │ INSERT INTO
         ↓
┌─────────────────────────────────────────────────────┐
│ DATABASE: monitoringikan                             │
│ TABLE: sensor_data                                   │
│ Record: {id:51, ph:7.2, temp:27.5, oxygen:6.8, ...} │
└─────────────────────────────────────────────────────┘
```

---

### **Flow 2: Web → Android (Data Keluar)**

```
┌─────────────────┐
│  Android App    │ Request data dashboard
└────────┬────────┘
         │
         │ GET /api/mobile/dashboard
         │ Header: Authorization: Bearer TOKEN
         ↓
┌─────────────────────────────────────────────────────┐
│ routes/api.php                                       │
│ GET /mobile/dashboard                                │
│ → MobileApiController@dashboard                     │
└────────┬────────────────────────────────────────────┘
         │
         │ Auth check (Sanctum)
         │ User verified
         ↓
┌─────────────────────────────────────────────────────┐
│ app/Http/Controllers/Api/MobileApiController.php    │
│ public function dashboard()                          │
└────────┬────────────────────────────────────────────┘
         │
         │ Query database
         ↓
┌─────────────────────────────────────────────────────┐
│ app/Models/SensorData.php                            │
│ SensorData::latest()->first()                        │
│ SensorData::whereBetween(...)->get()                 │
└────────┬────────────────────────────────────────────┘
         │
         │ SELECT * FROM sensor_data
         ↓
┌─────────────────────────────────────────────────────┐
│ DATABASE: monitoringikan                             │
│ TABLE: sensor_data                                   │
└────────┬────────────────────────────────────────────┘
         │
         │ Return data
         ↓
┌─────────────────────────────────────────────────────┐
│ Response JSON                                        │
│ {success: true, data: {latest: {...}, chart: [...]}}│
└────────┬────────────────────────────────────────────┘
         │
         ↓
┌─────────────────┐
│  Android App    │ Display data
└─────────────────┘
```

---

## **📂 STRUKTUR FOLDER FILE KONEKSI**

```
monitoringikanball/
│
├── ESP32_pH_XAMPP_Code.ino          ← 🔴 CODE ESP32 (Upload ke hardware)
│
├── routes/
│   └── api.php                       ← 🔴 ENDPOINT API (IoT & Android)
│
├── app/
│   ├── Models/
│   │   └── SensorData.php            ← 🔴 MODEL database sensor_data
│   │
│   └── Http/Controllers/
│       ├── Api/
│       │   └── MobileApiController.php  ← 🔴 CONTROLLER API Android
│       │
│       └── DashboardController.php   ← CONTROLLER dashboard web
│
├── database/
│   └── migrations/
│       └── xxxx_create_sensor_data_table.php  ← STRUKTUR tabel
│
└── dokumentasi/
    ├── API_MOBILE_DOCUMENTATION.md   ← 📖 Dokumentasi API Android
    ├── ANDROID_INTEGRATION_SUMMARY.md ← 📖 Summary integrasi
    ├── SENSOR_XAMPP_INTEGRATION.md   ← 📖 Dokumentasi ESP32
    └── THIS_FILE.md                  ← 📖 File ini
```

---

## **🎯 FILE UTAMA YANG PERLU ANDA KETAHUI**

### **Untuk Modifikasi ESP32:**

1. **`ESP32_pH_XAMPP_Code.ino`** (line 37)
    - Ganti `SERVER_URL` dengan IP server Anda
    - Ganti `WIFI_SSID` dan `WIFI_PASSWORD`
    - Ganti `DEVICE_ID` sesuai database

### **Untuk Modifikasi API IoT:**

2. **`routes/api.php`** (line 65-91)
    - Endpoint: `POST /api/sensor-data/store`
    - Validasi data dari ESP32
    - Response ke ESP32

### **Untuk Modifikasi API Android:**

3. **`app/Http/Controllers/Api/MobileApiController.php`**

    - Semua logic API untuk Android
    - Line 246: `dashboard()` method
    - Line 407: `history()` method

4. **`routes/api.php`** (line 9-42)
    - Semua route untuk Android
    - Base: `/mobile/*`

### **Untuk Modifikasi Database:**

5. **`app/Models/SensorData.php`**
    - Model Eloquent
    - Relasi dengan Device
    - Fillable fields

---

## **🔑 URL PENTING**

### **Endpoint ESP32 (IoT → Web):**

```
POST http://10.240.181.8/monitoringikanball/monitoringikanball/public/api/sensor-data/store
```

### **Endpoint Android (Web → Android):**

```
Base URL: http://10.240.181.8/monitoringikanball/monitoringikanball/public/api/mobile

POST /mobile/auth/login          - Login
GET  /mobile/dashboard           - Dashboard data
GET  /mobile/latest              - Latest sensor
GET  /mobile/history             - History with filter
```

---

## **📝 TESTING FILE**

### **Test ESP32 Connection:**

**File:** `test_esp32_api.php`
**Lokasi:** Root folder
**Fungsi:** Test apakah ESP32 bisa kirim data ke web

```php
<?php
// Simulate ESP32 sending data
$url = "http://127.0.0.1:8000/api/sensor-data/store";
$data = [
    'device_id' => 1,
    'ph' => 7.23,
    'temperature' => 27.5,
    'oxygen' => 6.8
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
echo $response;
curl_close($ch);
```

**Cara Run:**

```bash
php test_esp32_api.php
```

---

## **💡 TIPS DEBUGGING**

### **Jika ESP32 tidak bisa kirim data:**

1. Cek IP server di `ESP32_pH_XAMPP_Code.ino` line 37
2. Cek WiFi credentials di line 35-36
3. Cek firewall Windows tidak block port 80
4. Cek XAMPP Apache sudah running
5. Test dengan `test_esp32_api.php`

### **Jika Android tidak bisa connect:**

1. Cek base URL di Android Retrofit client
2. Cek token authentication sudah disimpan
3. Test dengan Postman dulu
4. Cek endpoint di `routes/api.php` line 9-42
5. Cek controller `MobileApiController.php`

### **Jika data tidak masuk database:**

1. Cek MySQL di XAMPP sudah running
2. Cek database `monitoringikan` sudah ada
3. Cek tabel `sensor_data` sudah ada
4. Cek migration sudah dirun
5. Cek Model `SensorData.php` fillable fields

---

## **🎉 KESIMPULAN**

**File Koneksi Utama:**

1. **ESP32 → Web:**

    - `ESP32_pH_XAMPP_Code.ino` (line 208-239: sendToServer)
    - `routes/api.php` (line 65-91: POST sensor-data/store)
    - `app/Models/SensorData.php` (Model database)

2. **Web → Android:**

    - `routes/api.php` (line 9-42: Mobile routes)
    - `app/Http/Controllers/Api/MobileApiController.php` (Semua logic)
    - `app/Models/SensorData.php` (Query database)

3. **Database:**
    - Database: `monitoringikan`
    - Table: `sensor_data`
    - Model: `app/Models/SensorData.php`

**Semua file sudah siap, tinggal:**

-   Upload `ESP32_pH_XAMPP_Code.ino` ke ESP32
-   Integrasi Retrofit di aplikasi Android Anda
-   Test dengan Postman sebelum integrasi

**Selamat coding! 🚀**
