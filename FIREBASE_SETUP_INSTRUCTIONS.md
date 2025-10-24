# 🔥 **FIREBASE SETUP INSTRUCTIONS - IoT Monitoring Kolam Ikan**

## **📋 Overview**

Sistem monitoring kolam ikan sekarang menggunakan **Firebase Realtime Database** sebagai perantara antara ESP32 dan Web Laravel.

### **Arsitektur Baru:**

```
┌─────────────┐      WiFi      ┌─────────────────┐      HTTP       ┌──────────────┐
│  ESP32 pH   │ ───────────────→│    Firebase     │ ←───────────────│  Laravel Web │
│   Sensor    │   HTTP PUT      │  Realtime DB    │   HTTP GET      │   Dashboard  │
└─────────────┘                 └─────────────────┘                 └──────────────┘
       ↓                                ↓                                   ↓
   30 detik                        sensor_data/                      Real-time display
   auto send                       device_1/                          + MySQL sync
                                   {timestamp}.json
```

### **Keuntungan Firebase:**

-   ✅ Real-time synchronization untuk dashboard
-   ✅ Tidak perlu public IP untuk ESP32
-   ✅ Auto-scaling & high availability
-   ✅ Free tier untuk testing (100GB/month transfer)
-   ✅ Web & Mobile app bisa subscribe real-time updates
-   ✅ Data persistence otomatis

---

## **🚀 STEP 1: Setup Firebase Project**

### **1.1 Create Firebase Project**

1. Buka **Firebase Console**: https://console.firebase.google.com/
2. Klik **"Add project"** atau **"Create a project"**
3. Masukkan nama project: **`monitoring-ikan-ball`** (atau nama lain sesuai keinginan)
4. Disable Google Analytics (optional untuk IoT project)
5. Klik **"Create project"**
6. Tunggu sampai project created (~ 1-2 menit)

---

### **1.2 Enable Firebase Realtime Database**

1. Di sidebar kiri, pilih **"Build" → "Realtime Database"**
2. Klik **"Create Database"**
3. Pilih database location:
    - **`us-central1`** (US Central - default, fast)
    - **`asia-southeast1`** (Singapore - recommended untuk Indonesia)
4. Pilih security rules mode:

    - **"Start in test mode"** (untuk development - allow read/write semua orang)
    - **"Start in locked mode"** (untuk production - perlu authentication)

    👉 **Pilih "Test mode"** untuk memudahkan development

5. Klik **"Enable"**
6. Tunggu database dibuat

---

### **1.3 Get Database URL**

Setelah database dibuat, Anda akan melihat URL seperti:

```
https://monitoring-ikan-ball-default-rtdb.firebaseio.com/
```

atau jika pakai region Singapore:

```
https://monitoring-ikan-ball-default-rtdb.asia-southeast1.firebasedatabase.app/
```

**COPY URL ini!** Akan digunakan di ESP32 dan Laravel.

📝 **Catatan:**

-   Jangan include `https://` saat paste ke ESP32 code
-   Jangan include trailing `/` di akhir

---

### **1.4 Setup Security Rules (Testing Mode)**

Di tab **"Rules"**, paste security rules berikut untuk **testing** (allow read/write tanpa auth):

```json
{
    "rules": {
        "sensor_data": {
            "device_1": {
                ".read": true,
                ".write": true
            }
        }
    }
}
```

⚠️ **PENTING:** Ini hanya untuk testing! Di production harus pakai authentication.

Klik **"Publish"** untuk apply rules.

**Alternatif rules untuk allow semua device:**

```json
{
    "rules": {
        "sensor_data": {
            "$deviceId": {
                ".read": true,
                ".write": true
            }
        }
    }
}
```

---

### **1.5 (Optional) Get Database Secret**

Untuk production dengan authentication REST API:

1. Klik **⚙️ Project Settings** (gear icon di sidebar kiri atas)
2. Pilih tab **"Service accounts"**
3. Scroll ke bawah → **"Database secrets"** section
4. Klik **"Show"** → Copy secret key (contoh: `abc123xyz456...`)
5. Simpan secret key ini dengan aman

**Catatan:** Database secret diperlukan untuk ESP32 mengirim data dengan authentication.

---

## **🔧 STEP 2: Configure ESP32**

### **2.1 Open File ESP32**

Buka file: **`ESP32_pH_Firebase.ino`** (430+ lines)

### **2.2 Install Required Libraries**

Buka **Arduino IDE** → **Tools** → **Manage Libraries**, install:

1. **WiFi** (built-in ESP32)
2. **HTTPClient** (built-in ESP32)
3. **ArduinoJson** by Benoit Blanchon (versi 6.x)
4. **EEPROM** (built-in ESP32)

### **2.3 Update WiFi Configuration**

Edit baris 28-29:

```cpp
// Line 28-29
const char* WIFI_SSID = "YOUR_WIFI_SSID";        // Ganti dengan nama WiFi Anda
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD"; // Ganti dengan password WiFi
```

**Contoh:**

```cpp
const char* WIFI_SSID = "IndiHome-Rumah";
const char* WIFI_PASSWORD = "password123";
```

### **2.4 Update Firebase Configuration**

Edit baris 32-34:

```cpp
// Line 32-34
const char* FIREBASE_HOST = "monitoring-ikan-ball-default-rtdb.firebaseio.com";
const char* FIREBASE_AUTH = "";
const int DEVICE_ID = 1;
```

**Ganti dengan:**

1. **FIREBASE_HOST**: URL Firebase Anda (TANPA `https://` dan TANPA `/` di akhir)

    **Contoh US Region:**

    ```cpp
    const char* FIREBASE_HOST = "monitoring-ikan-ball-default-rtdb.firebaseio.com";
    ```

    **Contoh Singapore Region:**

    ```cpp
    const char* FIREBASE_HOST = "monitoring-ikan-ball-default-rtdb.asia-southeast1.firebasedatabase.app";
    ```

2. **FIREBASE_AUTH**:

    - **Testing mode (no auth):** Kosongkan `""`
    - **Production mode (with auth):** Paste database secret `"abc123xyz456..."`

    **Contoh tanpa auth:**

    ```cpp
    const char* FIREBASE_AUTH = "";
    ```

    **Contoh dengan auth:**

    ```cpp
    const char* FIREBASE_AUTH = "abc123xyz456def789ghi012jkl345mno678";
    ```

3. **DEVICE_ID**: ID device Anda (1, 2, 3, dst)
    ```cpp
    const int DEVICE_ID = 1; // Kolam pertama
    ```

### **2.5 Configure pH Sensor Pin**

Edit baris 23 jika perlu:

```cpp
// Line 23
const int PH_SENSOR_PIN = 4;  // GPIO 4 (ADC1_CH3) untuk pH sensor analog
```

Sesuaikan dengan wiring hardware Anda.

### **2.6 Upload ke ESP32**

1. **Connect ESP32** ke PC via USB
2. Buka **Arduino IDE**
3. Pilih **Board**:
    - Tools → Board → ESP32 Arduino → **ESP32-S3 Dev Module**
4. Pilih **Port**:
    - Tools → Port → **COM3** (atau port ESP32 Anda)
5. **Upload** code: **Ctrl+U** atau klik tombol Upload
6. Tunggu upload selesai (~ 30 detik)

### **2.7 Monitor Serial Output**

1. **Open Serial Monitor**: Tools → Serial Monitor (atau **Ctrl+Shift+M**)
2. Set baud rate: **115200**
3. Anda akan melihat output:

```
===============================================
      ESP32 pH Sensor - Firebase Ready
===============================================

[WIFI] Connecting to IndiHome-Rumah...
[WIFI] ✅ Connected! IP: 192.168.1.100
[WIFI] Signal: -45 dBm (Excellent)

[FIREBASE] Host: monitoring-ikan-ball-default-rtdb.firebaseio.com
[FIREBASE] Auth: Enabled

[CALIBRATION] pH 7.00 = 2.50V | pH 4.01 = 3.00V

===============================================
          Monitoring Started
===============================================

[SENSOR] pH: 7.23 | Temp: 27.5°C | O2: 6.8 mg/L
[FIREBASE] ✅ Data sent successfully!
[FIREBASE] Response: {"name":"-NhQ8xYzAbCdEfGhIjKl"}

Next update in 30 seconds...
```

**Troubleshooting:**

-   Jika "WiFi connection failed" → Cek SSID dan password
-   Jika "Firebase error" → Cek FIREBASE_HOST dan security rules
-   Jika "HTTP 401 Unauthorized" → Cek FIREBASE_AUTH
-   Jika "HTTP 403 Forbidden" → Cek Firebase security rules

---

## **🌐 STEP 3: Configure Laravel Web**

### **3.1 Update .env File**

Buka file **`.env`** di root project Laravel (pakai text editor):

**Tambahkan baris berikut di bagian bawah:**

```env
# Firebase Configuration
FIREBASE_DATABASE_URL=https://monitoring-ikan-ball-default-rtdb.firebaseio.com
FIREBASE_DATABASE_SECRET=
FIREBASE_PROJECT_ID=monitoring-ikan-ball
FIREBASE_API_KEY=
```

**Ganti dengan:**

1. **FIREBASE_DATABASE_URL**: URL Firebase Anda (dengan `https://`)

    ```env
    FIREBASE_DATABASE_URL=https://monitoring-ikan-ball-default-rtdb.firebaseio.com
    ```

2. **FIREBASE_DATABASE_SECRET**:

    - **Testing mode:** Kosongkan
    - **Production mode:** Paste database secret

    ```env
    FIREBASE_DATABASE_SECRET=
    ```

    atau

    ```env
    FIREBASE_DATABASE_SECRET=abc123xyz456def789
    ```

3. **FIREBASE_PROJECT_ID**: Nama project Firebase

    ```env
    FIREBASE_PROJECT_ID=monitoring-ikan-ball
    ```

4. **FIREBASE_API_KEY** (optional untuk web push notification):
    - Ambil dari Firebase Console → Project Settings → General → Web API Key
    ```env
    FIREBASE_API_KEY=AIzaSyAbc123Xyz456Def789Ghi012Jkl345Mno
    ```

### **3.2 Verify config/services.php**

File **`config/services.php`** sudah dikonfigurasi dengan Firebase config di **line 44-56**.

**Tidak perlu diubah!**

```php
// config/services.php (Line 44-56)
'firebase' => [
    'database_url' => env('FIREBASE_DATABASE_URL'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'private_key' => env('FIREBASE_PRIVATE_KEY'),
    // ... other configs
],
```

### **3.3 Test Firebase Connection dari Laravel**

Buat file test di root folder: **`test_firebase.php`**

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FirebaseService;

$firebase = new FirebaseService();

echo "=== TESTING FIREBASE CONNECTION ===\n\n";

// Test 1: Get sensor data from Firebase
echo "1. Getting sensor data from Firebase (device 1)...\n";
$data = $firebase->getSensorDataFromFirebase(1);

if ($data) {
    echo "✅ SUCCESS! Found " . count($data) . " records\n";
    echo "\nLatest data:\n";
    print_r($data[0]);
} else {
    echo "❌ FAILED! No data found or connection error\n";
}

echo "\n\n2. Testing sync to local database...\n";
$synced = $firebase->syncFirebaseToDatabase(1);
echo "✅ Synced records: $synced\n";

echo "\n=== TEST COMPLETE ===\n";
```

**Jalankan test:**

```bash
php test_firebase.php
```

**Expected output:**

```
=== TESTING FIREBASE CONNECTION ===

1. Getting sensor data from Firebase (device 1)...
✅ SUCCESS! Found 10 records

Latest data:
Array
(
    [firebase_key] => -NhQ8xYzAbCdEfGhIjKl
    [device_id] => 1
    [ph] => 7.23
    [temperature] => 27.5
    [oxygen] => 6.8
    [timestamp] => 1729587234567
)

2. Testing sync to local database...
✅ Synced records: 10

=== TEST COMPLETE ===
```

---

## **📊 STEP 4: Verify Data in Firebase Console**

### **4.1 Open Firebase Console Data Tab**

1. Buka **Firebase Console**: https://console.firebase.google.com/
2. Pilih project **"monitoring-ikan-ball"**
3. Klik **"Realtime Database"** di sidebar
4. Pilih tab **"Data"**

### **4.2 Check Data Structure**

Setelah ESP32 mengirim data, Anda akan melihat struktur seperti ini:

```
monitoring-ikan-ball-default-rtdb
└── sensor_data
    └── device_1
        ├── 1729587234567
        │   ├── device_id: 1
        │   ├── ph: 7.23
        │   ├── temperature: 27.5
        │   ├── oxygen: 6.8
        │   ├── voltage: 2.456
        │   └── timestamp: "2024-10-22T10:27:14Z"
        │
        ├── 1729587264567
        │   ├── device_id: 1
        │   ├── ph: 7.18
        │   ├── temperature: 27.6
        │   └── ...
        │
        └── ...
```

**Keterangan:**

-   `sensor_data`: Root node untuk semua sensor data
-   `device_1`: Device ID (sesuai dengan `DEVICE_ID` di ESP32)
-   `1729587234567`: Unix timestamp dalam milliseconds (auto-generated oleh ESP32)
-   Data di dalam: nilai sensor (ph, temperature, oxygen, dll)

### **4.3 Real-time Updates**

Di Firebase Console, data akan **auto-refresh** setiap kali ESP32 kirim data baru (30 detik).

**Watch real-time:**

1. Buka tab "Data" di Firebase Console
2. Expand node `sensor_data/device_1`
3. Lihat data baru muncul setiap 30 detik dengan timestamp terbaru

---

## **🔄 STEP 5: Sync Firebase to MySQL Database (Optional)**

Jika Anda ingin **backup** data Firebase ke MySQL lokal untuk analytics dan reporting:

### **5.1 Manual Sync**

Jalankan manual sync via Artisan:

```bash
php artisan make:command SyncFirebaseData
```

### **5.2 Create Command File**

Edit file: **`app/Console/Commands/SyncFirebaseData.php`**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class SyncFirebaseData extends Command
{
    protected $signature = 'firebase:sync {device_id=1}';
    protected $description = 'Sync Firebase data to local MySQL database';

    public function handle()
    {
        $deviceId = $this->argument('device_id');

        $this->info("Syncing Firebase data for device $deviceId...");

        $firebase = new FirebaseService();
        $synced = $firebase->syncFirebaseToDatabase($deviceId);

        $this->info("✅ Successfully synced $synced records");

        return 0;
    }
}
```

### **5.3 Run Manual Sync**

```bash
php artisan firebase:sync 1
```

**Output:**

```
Syncing Firebase data for device 1...
✅ Successfully synced 15 records
```

### **5.4 Auto Sync with Laravel Scheduler**

Edit **`app/Console/Kernel.php`**:

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Sync Firebase data every 5 minutes
        $schedule->command('firebase:sync 1')->everyFiveMinutes();

        // Or sync multiple devices
        $schedule->command('firebase:sync 1')->everyFiveMinutes();
        $schedule->command('firebase:sync 2')->everyFiveMinutes();
    }
}
```

**Aktifkan scheduler:**

**Windows (Task Scheduler):**

-   Buka Task Scheduler
-   Create New Task
-   Trigger: Repeat every 1 minute
-   Action: `php "D:\xampp\htdocs\monitoringikanball\monitoringikanball\artisan" schedule:run`

**Linux/Mac (Cron):**

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## **📱 STEP 6: Update Dashboard (Optional)**

Jika ingin dashboard web ambil data dari Firebase langsung (real-time):

### **6.1 Update DashboardController**

Edit **`app/Http/Controllers/DashboardController.php`**:

```php
use App\Services\FirebaseService;

public function index(Request $request)
{
    $firebase = new FirebaseService();

    // Get real-time data from Firebase
    $sensorData = $firebase->getSensorDataFromFirebase(1);

    // Sync to database (optional)
    $firebase->syncFirebaseToDatabase(1);

    // Continue with existing code...
    return view('dashboard.index', compact('sensorData'));
}
```

---

## **🎯 TESTING CHECKLIST**

### **✅ ESP32 Testing:**

-   [ ] ESP32 connect ke WiFi successfully (Serial Monitor)
-   [ ] Serial Monitor show "✅ Data successfully sent to Firebase!"
-   [ ] Check Firebase Console → Data tab → `sensor_data/device_1` ada data baru
-   [ ] pH value sesuai dengan sensor reading
-   [ ] Data update setiap 30 detik

### **✅ Firebase Testing:**

-   [ ] Firebase Console menampilkan data real-time
-   [ ] Data structure correct: `device_id`, `ph`, `temperature`, `oxygen`, `timestamp`
-   [ ] Security rules applied (read/write allowed untuk testing)
-   [ ] Real-time updates visible saat ESP32 kirim data baru

### **✅ Laravel Testing:**

-   [ ] `php test_firebase.php` return success
-   [ ] `FirebaseService::getSensorDataFromFirebase(1)` return array of data
-   [ ] `FirebaseService::syncFirebaseToDatabase(1)` return synced count
-   [ ] Web dashboard menampilkan data dari Firebase
-   [ ] MySQL database `sensor_data` table updated

### **✅ Android Testing (Optional):**

-   [ ] `GET /api/mobile/dashboard` return data dari Firebase
-   [ ] `GET /api/mobile/latest` return latest data
-   [ ] Real-time update jika ESP32 kirim data baru

---

## **⚙️ FIREBASE SECURITY RULES (PRODUCTION)**

Untuk **production**, ganti rules dengan authentication:

```json
{
    "rules": {
        "sensor_data": {
            "$deviceId": {
                ".read": true,
                ".write": "auth != null",
                "$timestamp": {
                    ".validate": "newData.hasChildren(['device_id', 'ph', 'temperature', 'oxygen', 'timestamp'])"
                }
            }
        }
    }
}
```

**Penjelasan:**

-   **Read**: Semua orang bisa baca (untuk dashboard public)
-   **Write**: Hanya authenticated user/device
-   **Validation**: Data harus ada field wajib: `device_id`, `ph`, `temperature`, `oxygen`, `timestamp`

**Rules untuk multiple devices:**

```json
{
    "rules": {
        "sensor_data": {
            "device_1": {
                ".read": true,
                ".write": "auth.uid == 'device_1_token'"
            },
            "device_2": {
                ".read": true,
                ".write": "auth.uid == 'device_2_token'"
            }
        }
    }
}
```

---

## **🐛 TROUBLESHOOTING**

### **Problem 1: ESP32 tidak bisa connect WiFi**

**Symptoms:**

```
[WIFI] Connecting to WiFi...
[WIFI] ❌ Connection failed!
```

**Solutions:**

1. Cek SSID dan password di ESP32 code (line 28-29)
2. Pastikan WiFi 2.4 GHz (ESP32 tidak support 5 GHz)
3. Cek jarak ESP32 ke router (signal strength)
4. Restart router dan ESP32
5. Coba WiFi hotspot HP untuk test

---

### **Problem 2: ESP32 tidak bisa kirim data ke Firebase**

**Symptoms:**

```
[FIREBASE] ❌ HTTP Error Code: 401
```

**Solutions:**

1. **Error 401 (Unauthorized):**

    - Cek `FIREBASE_AUTH` di ESP32 code
    - Jika testing mode, kosongkan `FIREBASE_AUTH = ""`
    - Jika production, cek database secret benar

2. **Error 403 (Forbidden):**

    - Cek Firebase security rules (harus allow write)
    - Set rules ke testing mode:
        ```json
        {
            "rules": {
                ".read": true,
                ".write": true
            }
        }
        ```

3. **Error 404 (Not Found):**
    - Cek `FIREBASE_HOST` di ESP32 code
    - Pastikan URL benar (tanpa `https://` dan tanpa `/`)
    - Pastikan database sudah dibuat di Firebase Console

---

### **Problem 3: Laravel tidak bisa ambil data dari Firebase**

**Symptoms:**

```php
// test_firebase.php output:
❌ FAILED! No data found or connection error
```

**Solutions:**

1. Cek `.env` → `FIREBASE_DATABASE_URL` benar (dengan `https://`)
2. Cek Firebase security rules (harus allow read)
3. Test manual via browser:
    ```
    https://monitoring-ikan-ball-default-rtdb.firebaseio.com/sensor_data/device_1.json
    ```
4. Check Laravel logs:
    ```bash
    tail -f storage/logs/laravel.log
    ```

---

### **Problem 4: Data tidak update real-time di web**

**Symptoms:**

-   ESP32 kirim data (Serial Monitor show success)
-   Firebase Console menampilkan data baru
-   Web dashboard tidak update

**Solutions:**

1. Laravel ambil data via API (not real-time by default)
2. Untuk real-time, perlu implementasi:
    - **WebSocket** (Laravel WebSockets package)
    - **Server-Sent Events (SSE)**
    - **Polling** (refresh setiap N detik via JavaScript)
3. Atau pakai Firebase SDK di frontend JavaScript untuk real-time listener

---

### **Problem 5: "Permission denied" di Firebase**

**Symptoms:**

```
Error: Permission denied
```

**Solutions:**

1. Buka Firebase Console → Realtime Database → **Rules** tab
2. Set rules ke testing mode:
    ```json
    {
        "rules": {
            ".read": true,
            ".write": true
        }
    }
    ```
3. Klik **"Publish"**
4. Wait 1-2 minutes untuk rules propagate
5. Test ulang

---

## **📚 FILE LOCATIONS**

| File                 | Path                                               | Description                                 |
| -------------------- | -------------------------------------------------- | ------------------------------------------- |
| ESP32 Code           | `ESP32_pH_Firebase.ino`                            | ESP32 firmware untuk kirim ke Firebase      |
| Firebase Service     | `app/Services/FirebaseService.php`                 | Laravel service untuk read/sync Firebase    |
| Config Template      | `.env.firebase.example`                            | Example Firebase environment variables      |
| Laravel Config       | `config/services.php`                              | Firebase service configuration (line 44-56) |
| Dashboard Controller | `app/Http/Controllers/DashboardController.php`     | Web dashboard controller                    |
| Mobile API           | `app/Http/Controllers/Api/MobileApiController.php` | Mobile API endpoints                        |
| Setup Guide          | `FIREBASE_SETUP_INSTRUCTIONS.md`                   | This file                                   |

---

## **🎉 DONE! System Ready**

Sistem monitoring kolam ikan sekarang menggunakan Firebase!

**Architecture Flow:**

```
ESP32 Sensor → WiFi → Firebase Realtime DB → Laravel reads → MySQL sync → Dashboard/Mobile
```

**Next Steps:**

1. ✅ Upload ESP32 code to hardware
2. ✅ Update Laravel .env dengan Firebase credentials
3. ✅ Test dengan `php test_firebase.php`
4. ✅ Check Firebase Console data tab
5. ✅ Test web dashboard
6. ✅ Setup auto-sync scheduler (optional)
7. ✅ Test Android app (optional)

**Production Checklist:**

-   [ ] Update Firebase security rules dengan authentication
-   [ ] Add database secret untuk ESP32 authentication
-   [ ] Setup Laravel scheduler untuk auto-sync
-   [ ] Enable Firebase backup/export
-   [ ] Monitor Firebase usage (Free tier: 100GB/month)

**🔥 Selamat! Sistem IoT monitoring dengan Firebase sudah siap! 🚀**
