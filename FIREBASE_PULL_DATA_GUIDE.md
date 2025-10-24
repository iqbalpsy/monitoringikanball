# 📊 **CARA MENARIK DATA DARI FIREBASE DAN MENAMPILKAN DI WEB**

## **📋 Overview**

Panduan lengkap untuk menarik data sensor dari Firebase Realtime Database dan menampilkan di web Laravel.

### **Flow Data:**

```
ESP32 Sensor → Firebase Realtime Database → Laravel Web → Display di Dashboard
```

---

## **🔧 PERSIAPAN**

### **1. Setup Firebase Database URL**

Edit file **`.env`** di root folder:

```env
FIREBASE_DATABASE_URL=https://container-kolam-default-rtdb.firebaseio.com
FIREBASE_PROJECT_ID=container-kolam
FIREBASE_DATABASE_SECRET=
```

**⚠️ PENTING:** Ganti URL dengan Database URL Anda dari Firebase Console.

Get URL: https://console.firebase.google.com/project/container-kolam/database

---

## **📝 METHOD 1: Menarik Data via Laravel Controller**

### **Cara Paling Sederhana - Langsung dari Controller**

Edit file: **`app/Http/Controllers/DashboardController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use App\Models\SensorData;

class DashboardController extends Controller
{
    /**
     * Show user dashboard dengan data dari Firebase
     */
    public function userDashboard()
    {
        // 1. Initialize Firebase Service
        $firebase = new FirebaseService();

        // 2. Ambil data dari Firebase untuk device 1
        $firebaseData = $firebase->getSensorDataFromFirebase(1);

        // 3. (Optional) Sync data ke MySQL lokal untuk backup
        $firebase->syncFirebaseToDatabase(1);

        // 4. Ambil data terbaru (latest reading)
        $latestData = null;
        if ($firebaseData && count($firebaseData) > 0) {
            $latest = $firebaseData[0];
            $latestData = [
                'ph' => $latest['ph'] ?? 0,
                'temperature' => $latest['temperature'] ?? 0,
                'oxygen' => $latest['oxygen'] ?? 0,
                'timestamp' => $latest['timestamp'] ?? now()->timestamp * 1000
            ];
        }

        // 5. Convert Firebase data ke format untuk chart
        $chartData = collect($firebaseData)->map(function($item) {
            return [
                'ph' => round((float)($item['ph'] ?? 0), 2),
                'temperature' => round((float)($item['temperature'] ?? 0), 2),
                'oxygen' => round((float)($item['oxygen'] ?? 0), 2),
                'time' => isset($item['timestamp']) ?
                    date('H:i', $item['timestamp'] / 1000) :
                    now()->format('H:i')
            ];
        });

        // 6. Pass data ke view
        return view('dashboard.user', [
            'latestData' => $latestData,
            'chartData' => $chartData,
            'sensorData' => $firebaseData
        ]);
    }
}
```

---

## **📊 METHOD 2: Menarik Data via AJAX (Real-time)**

### **Step 1: Update DashboardController untuk API Endpoint**

File: **`app/Http/Controllers/DashboardController.php`**

Tambahkan method baru:

```php
/**
 * API Endpoint: Get sensor data dari Firebase
 * URL: GET /api/dashboard/sensor-data?source=firebase
 */
public function getSensorData(Request $request)
{
    $source = $request->input('source', 'database'); // firebase atau database
    $hours = $request->input('hours', 24);

    if ($source === 'firebase') {
        // Ambil dari Firebase
        $firebase = new FirebaseService();
        $firebaseData = $firebase->getSensorDataFromFirebase(1);

        // Sync ke database (optional, untuk backup)
        $firebase->syncFirebaseToDatabase(1);

        if ($firebaseData) {
            $chartData = collect($firebaseData)->map(function($item) {
                return [
                    'ph' => round((float)($item['ph'] ?? 0), 2),
                    'temperature' => round((float)($item['temperature'] ?? 0), 2),
                    'oxygen' => round((float)($item['oxygen'] ?? 0), 2),
                    'time' => isset($item['timestamp']) ?
                        date('H:i', $item['timestamp'] / 1000) :
                        now()->format('H:i')
                ];
            });

            $latest = $firebaseData[0] ?? null;

            return response()->json([
                'success' => true,
                'data' => $chartData,
                'latest' => [
                    'ph' => $latest ? round((float)($latest['ph'] ?? 0), 1) : 0,
                    'temperature' => $latest ? round((float)($latest['temperature'] ?? 0), 1) : 0,
                    'oxygen' => $latest ? round((float)($latest['oxygen'] ?? 0), 1) : 0
                ],
                'count' => count($firebaseData),
                'source' => 'firebase'
            ]);
        }
    }

    // Fallback: ambil dari database lokal
    $sensorData = SensorData::where('device_id', 1)
        ->orderBy('recorded_at', 'desc')
        ->limit($hours)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $sensorData,
        'source' => 'database'
    ]);
}
```

### **Step 2: Update Routes**

File: **`routes/web.php`** atau **`routes/api.php`**

```php
// API route untuk ambil data sensor
Route::get('/api/dashboard/sensor-data', [DashboardController::class, 'getSensorData']);
```

### **Step 3: Update View dengan JavaScript AJAX**

File: **`resources/views/dashboard/user.blade.php`**

Tambahkan script di bagian bawah (sebelum `</body>`):

```html
<script>
    // Function untuk load data dari Firebase via AJAX
    function loadSensorDataFromFirebase() {
        console.log("Loading data from Firebase...");

        fetch("/api/dashboard/sensor-data?source=firebase&hours=24")
            .then((response) => response.json())
            .then((data) => {
                console.log("Firebase Data:", data);

                if (data.success) {
                    // Update nilai di cards (pH, Temperature, Oxygen)
                    updateSensorCards(data.latest);

                    // Update chart
                    updateChart(data.data);

                    // Update timestamp
                    document.getElementById("last-update").textContent =
                        "Last update: " + new Date().toLocaleTimeString();
                }
            })
            .catch((error) => {
                console.error("Error loading Firebase data:", error);
            });
    }

    // Function untuk update cards (pH, Temperature, Oxygen)
    function updateSensorCards(latest) {
        // Update pH card
        const phElement = document.getElementById("ph-value");
        if (phElement) {
            phElement.textContent = latest.ph.toFixed(1);

            // Update warna berdasarkan threshold (6.5 - 8.5 = normal)
            if (latest.ph < 6.5 || latest.ph > 8.5) {
                phElement.classList.add("text-danger");
            } else {
                phElement.classList.remove("text-danger");
            }
        }

        // Update Temperature card
        const tempElement = document.getElementById("temp-value");
        if (tempElement) {
            tempElement.textContent = latest.temperature.toFixed(1) + " °C";

            // Update warna berdasarkan threshold (24-30°C = normal)
            if (latest.temperature < 24 || latest.temperature > 30) {
                tempElement.classList.add("text-danger");
            } else {
                tempElement.classList.remove("text-danger");
            }
        }

        // Update Oxygen card
        const oxygenElement = document.getElementById("oxygen-value");
        if (oxygenElement) {
            oxygenElement.textContent = latest.oxygen.toFixed(1) + " mg/L";

            // Update warna berdasarkan threshold (5-8 mg/L = normal)
            if (latest.oxygen < 5 || latest.oxygen > 8) {
                oxygenElement.classList.add("text-danger");
            } else {
                oxygenElement.classList.remove("text-danger");
            }
        }
    }

    // Function untuk update chart (Chart.js)
    function updateChart(chartData) {
        // Assuming you already have Chart.js instance named 'myChart'
        if (typeof myChart !== "undefined") {
            // Update labels (time)
            myChart.data.labels = chartData.map((item) => item.time);

            // Update pH dataset
            myChart.data.datasets[0].data = chartData.map((item) => item.ph);

            // Update Temperature dataset
            myChart.data.datasets[1].data = chartData.map(
                (item) => item.temperature
            );

            // Update Oxygen dataset
            myChart.data.datasets[2].data = chartData.map(
                (item) => item.oxygen
            );

            // Refresh chart
            myChart.update();
        }
    }

    // Auto-refresh data setiap 30 detik (real-time monitoring)
    setInterval(loadSensorDataFromFirebase, 30000); // 30 seconds

    // Load data immediately on page load
    loadSensorDataFromFirebase();
</script>
```

### **Step 4: Update HTML Structure (Cards)**

Pastikan HTML card punya ID yang sesuai:

```html
<!-- pH Card -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">pH Level</h5>
        <h2 id="ph-value" class="display-4">7.0</h2>
        <p class="text-muted">Normal: 6.5 - 8.5</p>
    </div>
</div>

<!-- Temperature Card -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Temperature</h5>
        <h2 id="temp-value" class="display-4">27.0 °C</h2>
        <p class="text-muted">Normal: 24 - 30 °C</p>
    </div>
</div>

<!-- Oxygen Card -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Oxygen Level</h5>
        <h2 id="oxygen-value" class="display-4">6.5 mg/L</h2>
        <p class="text-muted">Normal: 5 - 8 mg/L</p>
    </div>
</div>

<!-- Last Update Timestamp -->
<p id="last-update" class="text-muted small">Last update: --:--:--</p>
```

---

## **🔄 METHOD 3: Auto Sync Firebase ke Database MySQL (Recommended)**

Untuk performa lebih baik, sync data Firebase ke MySQL secara otomatis.

### **Step 1: Buat Artisan Command**

```bash
php artisan make:command SyncFirebaseData
```

### **Step 2: Edit Command**

File: **`app/Console/Commands/SyncFirebaseData.php`**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class SyncFirebaseData extends Command
{
    protected $signature = 'firebase:sync {device_id=1}';
    protected $description = 'Sync Firebase Realtime Database data to local MySQL database';

    public function handle()
    {
        $deviceId = $this->argument('device_id');

        $this->info("🔄 Syncing Firebase data for device $deviceId...");

        try {
            $firebase = new FirebaseService();
            $syncedCount = $firebase->syncFirebaseToDatabase($deviceId);

            $this->info("✅ Successfully synced $syncedCount records!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Sync failed: " . $e->getMessage());
            return 1;
        }
    }
}
```

### **Step 3: Setup Laravel Scheduler**

File: **`app/Console/Kernel.php`**

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Sync Firebase data setiap 5 menit
        $schedule->command('firebase:sync 1')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();
    }
}
```

### **Step 4: Run Scheduler**

**Windows (Development):**

```bash
php artisan schedule:work
```

**Linux/Mac (Production - Cron):**

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### **Step 5: Manual Sync (Testing)**

```bash
php artisan firebase:sync 1
```

**Output:**

```
🔄 Syncing Firebase data for device 1...
✅ Successfully synced 15 records!
```

---

## **🧪 TESTING**

### **Test 1: Manual Test via PHP Script**

Run file test:

```bash
php test_firebase_pull.php
```

**Expected Output:**

```
═══════════════════════════════════════════════════════
   TEST: Pull Data dari Firebase ke Web Laravel
═══════════════════════════════════════════════════════

TEST 1: Ambil Data dari Firebase
───────────────────────────────────────────────────────
✅ SUCCESS! Berhasil ambil 10 data dari Firebase

Data Terbaru (Latest Reading):
────────────────────────────────
Firebase Key : -NhQ8xYzAbCdEfGhIjKl
Device ID    : 1
pH Level     : 7.23
Temperature  : 27.5 °C
Oxygen Level : 6.8 mg/L
```

### **Test 2: Test via Browser**

**Test API Endpoint:**

1. **Firebase Source:**

    ```
    http://localhost/api/dashboard/sensor-data?source=firebase
    ```

2. **Database Source:**
    ```
    http://localhost/api/dashboard/sensor-data?source=database
    ```

**Expected JSON Response:**

```json
{
  "success": true,
  "data": [
    {
      "ph": 7.23,
      "temperature": 27.5,
      "oxygen": 6.8,
      "time": "10:30"
    },
    ...
  ],
  "latest": {
    "ph": 7.2,
    "temperature": 27.5,
    "oxygen": 6.8
  },
  "count": 10,
  "source": "firebase"
}
```

### **Test 3: Test Dashboard Page**

Buka dashboard user:

```
http://localhost/dashboard/user
```

**Check:**

-   ✅ Cards menampilkan nilai pH, Temperature, Oxygen
-   ✅ Chart menampilkan data dari Firebase
-   ✅ Data auto-refresh setiap 30 detik
-   ✅ Timestamp "Last update" ter-update

---

## **🔍 DEBUGGING**

### **Problem 1: Data tidak muncul di dashboard**

**Check:**

1. **Firebase Console:**

    - Buka: https://console.firebase.google.com/project/container-kolam/database
    - Tab "Data" → Lihat apakah ada data di `sensor_data/device_1`

2. **Laravel Logs:**

    ```bash
    tail -f storage/logs/laravel.log
    ```

3. **Browser Console:**

    - Press F12 → Console tab
    - Check for JavaScript errors

4. **Test API Manual:**
    ```bash
    curl http://localhost/api/dashboard/sensor-data?source=firebase
    ```

### **Problem 2: "Method not found" error**

**Solution:**

Clear Laravel cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

Regenerate autoload:

```bash
composer dump-autoload
```

### **Problem 3: Firebase returns empty data**

**Check:**

1. **ESP32 Serial Monitor:**

    - Apakah ESP32 kirim data? (cek "✅ Data sent successfully")

2. **Firebase Security Rules:**

    - Buka Firebase Console → Rules tab
    - Pastikan rules allow read:

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

3. **.env Configuration:**
    ```env
    FIREBASE_DATABASE_URL=https://container-kolam-default-rtdb.firebaseio.com
    ```
    (Pastikan URL benar!)

---

## **📊 DISPLAY OPTIONS**

### **Option 1: Real-time Display (AJAX Auto-refresh)**

**Kelebihan:**

-   ✅ Real-time updates tanpa refresh halaman
-   ✅ Smooth user experience
-   ✅ Data langsung dari Firebase (no delay)

**Kekurangan:**

-   ❌ Butuh JavaScript enabled
-   ❌ Banyak HTTP requests ke server

**Use Case:** Dashboard monitoring real-time

---

### **Option 2: Laravel View (Server-side Rendering)**

**Kelebihan:**

-   ✅ SEO friendly
-   ✅ Works tanpa JavaScript
-   ✅ Simple implementation

**Kekurangan:**

-   ❌ Perlu refresh halaman untuk update data
-   ❌ Tidak real-time

**Use Case:** Reports, history page

---

### **Option 3: Hybrid (Sync + AJAX)**

**Kelebihan:**

-   ✅ Best performance (read dari MySQL)
-   ✅ Data backup di database lokal
-   ✅ Real-time updates via AJAX
-   ✅ Bisa query complex analytics

**Kekurangan:**

-   ❌ Setup lebih kompleks (need scheduler)

**Use Case:** Production environment ✅ RECOMMENDED

---

## **🎯 RECOMMENDED ARCHITECTURE**

```
┌─────────────┐
│  ESP32      │ ──→ WiFi ──→ Firebase Realtime DB
└─────────────┘                      ↓
                                     ↓ (Real-time)
                                     ↓
┌─────────────────────────────────────────────────────┐
│  Laravel Web Server                                 │
│                                                     │
│  ┌──────────────────┐     ┌─────────────────────┐ │
│  │ Laravel Scheduler│────→│ Firebase Sync       │ │
│  │ (Every 5 min)    │     │ to MySQL Database   │ │
│  └──────────────────┘     └─────────────────────┘ │
│                                     ↓               │
│  ┌──────────────────┐     ┌─────────────────────┐ │
│  │ Dashboard        │────→│ Read from MySQL     │ │
│  │ (Web View)       │     │ (Fast queries)      │ │
│  └──────────────────┘     └─────────────────────┘ │
│                                                     │
│  ┌──────────────────┐     ┌─────────────────────┐ │
│  │ AJAX Endpoint    │────→│ Read from Firebase  │ │
│  │ (Real-time API)  │     │ (Latest data)       │ │
│  └──────────────────┘     └─────────────────────┘ │
└─────────────────────────────────────────────────────┘
                      ↓
              ┌──────────────┐
              │  User Browser│
              │  (Dashboard) │
              └──────────────┘
```

---

## **✅ CHECKLIST IMPLEMENTATION**

### **Basic Implementation (Method 1):**

-   [ ] Update `.env` dengan `FIREBASE_DATABASE_URL`
-   [ ] Update `DashboardController` dengan FirebaseService
-   [ ] Test via browser: `http://localhost/dashboard/user`
-   [ ] Verify data tampil di cards dan chart

### **Real-time Implementation (Method 2):**

-   [ ] Tambah API endpoint di `DashboardController`
-   [ ] Tambah route di `routes/web.php` atau `routes/api.php`
-   [ ] Update view dengan JavaScript AJAX
-   [ ] Test auto-refresh setiap 30 detik

### **Production Implementation (Method 3):**

-   [ ] Buat Artisan command `SyncFirebaseData`
-   [ ] Setup Laravel scheduler di `Kernel.php`
-   [ ] Run scheduler: `php artisan schedule:work`
-   [ ] Verify data sync ke MySQL database
-   [ ] Dashboard read from MySQL (fast queries)
-   [ ] AJAX read from Firebase (real-time latest data)

---

## **📝 SUMMARY**

**3 Cara Menarik Data dari Firebase:**

1. **Method 1: Laravel Controller** → Paling sederhana, ambil langsung di controller
2. **Method 2: AJAX Real-time** → Auto-refresh setiap 30 detik tanpa reload page
3. **Method 3: Auto Sync + Hybrid** → Best performance, recommended untuk production

**File Yang Perlu Diedit:**

-   ✅ `.env` → Tambah `FIREBASE_DATABASE_URL`
-   ✅ `DashboardController.php` → Tambah FirebaseService
-   ✅ `routes/web.php` → Tambah API route
-   ✅ `dashboard/user.blade.php` → Tambah JavaScript AJAX
-   ✅ `SyncFirebaseData.php` → Command untuk auto-sync
-   ✅ `Kernel.php` → Setup scheduler

**Testing:**

```bash
# Test manual
php test_firebase_pull.php

# Test API
curl http://localhost/api/dashboard/sensor-data?source=firebase

# Test sync
php artisan firebase:sync 1
```

**🔥 Data dari Firebase sekarang bisa ditampilkan di web Laravel! 🚀**
