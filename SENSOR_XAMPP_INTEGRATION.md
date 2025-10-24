# 🔗 Panduan Lengkap: Sambungkan ESP32 pH Sensor ke Web Laravel XAMPP

**Tanggal**: 15 Oktober 2025  
**Status**: ✅ Ready to Deploy

---

## 📋 Daftar Isi

1. [Persiapan](#persiapan)
2. [Setup Laravel Server](#setup-laravel-server)
3. [Setup ESP32](#setup-esp32)
4. [Testing](#testing)
5. [Troubleshooting](#troubleshooting)

---

## 1️⃣ Persiapan

### ✅ Yang Anda Perlukan:

**Hardware:**

-   ✅ ESP32-S3 Development Board
-   ✅ pH Sensor analog module
-   ✅ pH Probe (elektroda)
-   ✅ Buffer pH 4.01 dan pH 7.00
-   ✅ Kabel jumper (3 buah: VCC, GND, OUT)
-   ✅ Kabel USB-C untuk upload code

**Software:**

-   ✅ Arduino IDE 2.0 atau lebih baru
-   ✅ XAMPP (sudah terinstall)
-   ✅ Web browser (Chrome/Edge)

**Library Arduino:**

-   ✅ WiFi.h (built-in ESP32)
-   ✅ HTTPClient.h (built-in ESP32)
-   ✅ ArduinoJson.h ← **INSTALL INI!**

### 📦 Install Library ArduinoJson

1. Buka Arduino IDE
2. Klik menu: **Tools → Manage Libraries...**
3. Cari: **ArduinoJson**
4. Pilih versi **6.x.x** (bukan 7.x.x)
5. Klik **Install**

---

## 2️⃣ Setup Laravel Server (XAMPP)

### A. Pastikan Database Berjalan

1. **Buka XAMPP Control Panel**
2. **Start Apache dan MySQL**

```
Apache:  ✅ Running (Port 80)
MySQL:   ✅ Running (Port 3306)
```

### B. Cek Database & Device ID

1. **Buka phpMyAdmin**: http://localhost/phpmyadmin
2. **Pilih database**: `monitoringikan`
3. **Buka tabel**: `devices`
4. **Cek data**:

```sql
SELECT * FROM devices;
```

**Hasil yang diharapkan:**

```
+----+-----------+-------------+--------+
| id | user_id   | name        | status |
+----+-----------+-------------+--------+
| 1  | 1         | Kolam 1     | active |
+----+-----------+-------------+--------+
```

⚠️ **PENTING**: Catat `id` device Anda (biasanya `1`)

### C. Jalankan Laravel Server

1. **Buka PowerShell/CMD**
2. **Masuk ke folder project**:

```powershell
cd D:\xampp\htdocs\monitoringikanball\monitoringikanball
```

3. **Jalankan Laravel dengan IP yang accessible**:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

**Output yang benar:**

```
INFO  Server running on [http://0.0.0.0:8000].

Press Ctrl+C to stop the server
```

### D. Cari IP Address Laptop/PC Anda

**Di PowerShell/CMD, ketik:**

```powershell
ipconfig
```

**Cari bagian WiFi atau Ethernet:**

```
Wireless LAN adapter Wi-Fi:
   IPv4 Address. . . . . . . . . . . : 192.168.1.10    ← INI IP ANDA!
```

⚠️ **CATAT IP INI!** Anda akan pakai di code ESP32.

### E. Test API Endpoint

1. **Buka browser**
2. **Akses**: http://192.168.1.10:8000/api/health (ganti IP dengan IP Anda)

**Hasilnya harus:**

```json
{
    "success": true,
    "message": "API IoT Fish Monitoring is running",
    "timestamp": "2025-10-15T10:30:00.000000Z"
}
```

✅ Jika muncul seperti ini, **SERVER SIAP!**

---

## 3️⃣ Setup ESP32

### A. Wiring pH Sensor ke ESP32

**Koneksi:**

| pH Sensor | Kabel | ESP32-S3 |
| --------- | ----- | -------- |
| VCC       | Merah | 3.3V     |
| GND       | Hitam | GND      |
| OUT       | Biru  | GPIO 4   |

⚠️ **JANGAN gunakan 5V!** Hanya 3.3V!

**Diagram:**

```
pH Sensor        ESP32-S3
┌────────┐       ┌────────┐
│ VCC    │───────│ 3.3V   │
│ GND    │───────│ GND    │
│ OUT    │───────│ GPIO 4 │
└────────┘       └────────┘
```

### B. Konfigurasi Code ESP32

1. **Buka file**: `ESP32_pH_XAMPP_Code.ino`
2. **Edit bagian konfigurasi**:

```cpp
// === WIFI & SERVER CONFIGURATION ===
const char* WIFI_SSID = "Polinela";                    // ← Ganti dengan WiFi Anda
const char* WIFI_PASSWORD = "24092005";                // ← Ganti password WiFi Anda
const char* SERVER_URL = "http://192.168.1.10:8000/api/sensor-data/store";  // ← Ganti IP!
const int DEVICE_ID = 1;                               // ← Sesuaikan dengan database
```

**Contoh konfigurasi:**

```cpp
// Jika WiFi Anda:
// SSID: "RumahKu"
// Password: "password123"
// IP Laptop: 192.168.43.100
// Device ID di database: 1

const char* WIFI_SSID = "RumahKu";
const char* WIFI_PASSWORD = "password123";
const char* SERVER_URL = "http://192.168.43.100:8000/api/sensor-data/store";
const int DEVICE_ID = 1;
```

### C. Upload Code ke ESP32

1. **Hubungkan ESP32 ke laptop via USB-C**
2. **Buka Arduino IDE**
3. **Pilih Board**: Tools → Board → ESP32 Arduino → ESP32S3 Dev Module
4. **Pilih Port**: Tools → Port → COM3 (atau sesuai port Anda)
5. **Upload**:
    - Klik tombol **Upload** (→)
    - Tunggu sampai "Done uploading"

### D. Buka Serial Monitor

1. **Tools → Serial Monitor**
2. **Set baud rate**: `115200`
3. **Anda akan lihat**:

```
╔════════════════════════════════════════════════╗
║     ESP32 pH Sensor - Laravel XAMPP v2.0      ║
╚════════════════════════════════════════════════╝

✅ ADC initialized (12-bit)
✅ EEPROM initialized

🔌 Menghubungkan ke WiFi...
   SSID: Polinela
......
✅ WiFi tersambung!
   IP ESP32: 192.168.1.25
   Signal: -45 dBm

📖 Memuat data kalibrasi...
⚠️  Belum ada data kalibrasi.
   Celupkan sensor ke buffer pH 7, lalu ketik: save7
   Celupkan sensor ke buffer pH 4, lalu ketik: save4

📋 Perintah tersedia:
   save7    - Simpan kalibrasi pH 7
   save4    - Simpan kalibrasi pH 4
   showcal  - Tampilkan data kalibrasi
   clearcal - Hapus kalibrasi
   sendnow  - Kirim data sekarang
   showip   - Tampilkan IP ESP32

==================================================
```

---

## 4️⃣ Kalibrasi pH Sensor

### Step 1: Kalibrasi pH 7

1. **Celupkan pH probe** ke dalam **buffer pH 7.00**
2. **Tunggu 30 detik** sampai pembacaan stabil
3. **Ketik di Serial Monitor**: `save7`
4. **Tekan Enter**

**Output:**

```
🔧 Perintah diterima: save7
💾 Disimpan: V_pH7 = 1.6543 V
```

### Step 2: Kalibrasi pH 4

1. **Bilas pH probe** dengan air bersih
2. **Keringkan** dengan tissue
3. **Celupkan pH probe** ke dalam **buffer pH 4.01**
4. **Tunggu 30 detik** sampai pembacaan stabil
5. **Ketik di Serial Monitor**: `save4`
6. **Tekan Enter**

**Output:**

```
🔧 Perintah diterima: save4
💾 Disimpan: V_pH4 = 1.9876 V
📊 Perhitungan kalibrasi:
   slope = -9.009009
   intercept = 21.900000
💾 Kalibrasi disimpan ke EEPROM.
```

### Step 3: Verifikasi Kalibrasi

**Ketik**: `showcal`

**Output:**

```
📖 Data Kalibrasi:
   V_pH7 = 1.6543 V
   V_pH4 = 1.9876 V
   slope = -9.009009
   intercept = 21.900000
   Status: ✅ Terkalibrasi
```

✅ **Kalibrasi SELESAI!**

---

## 5️⃣ Testing Pengiriman Data

### A. Test Manual (Perintah "sendnow")

1. **Celupkan sensor** ke air (atau buffer pH 7)
2. **Tunggu pembacaan stabil**
3. **Ketik di Serial Monitor**: `sendnow`

**Output yang benar:**

```
🔧 Perintah diterima: sendnow
📤 Mengirim data manual...

🌐 Mengirim data ke server...
   URL: http://192.168.1.10:8000/api/sensor-data/store
   Payload: {"device_id":1,"ph":"7.23","temperature":27.5,"oxygen":6.8}
   Response Code: 201
   Response: {"success":true,"message":"Data sensor berhasil disimpan","data":{...}}
✅ Data berhasil dikirim!
✅ Berhasil!
```

✅ **Jika muncul ini, koneksi BERHASIL!**

### B. Test Auto-Send (Kirim Otomatis Tiap 30 Detik)

Setelah kalibrasi, ESP32 akan **otomatis mengirim data tiap 30 detik**.

**Serial Monitor akan menampilkan:**

```
📊 Raw ADC: 2048 | V: 1.650V | pH: 7.15 ✅ Normal | WiFi: ✅ | Send: 1 | Fail: 0

🌐 Mengirim data ke server...
   URL: http://192.168.1.10:8000/api/sensor-data/store
   Payload: {"device_id":1,"ph":"7.15","temperature":27.5,"oxygen":6.8}
   Response Code: 201
   Response: {"success":true,"message":"Data sensor berhasil disimpan",...}
✅ Data berhasil dikirim!
```

### C. Cek Database

1. **Buka phpMyAdmin**: http://localhost/phpmyadmin
2. **Pilih database**: `monitoringikan`
3. **Buka tabel**: `sensor_data`

**Query:**

```sql
SELECT * FROM sensor_data ORDER BY recorded_at DESC LIMIT 10;
```

**Hasil yang diharapkan:**

```
+----+-----------+------+-------------+--------+---------------------+
| id | device_id | ph   | temperature | oxygen | recorded_at         |
+----+-----------+------+-------------+--------+---------------------+
| 1  | 1         | 7.23 | 27.50       | 6.80   | 2025-10-15 10:45:30 |
| 2  | 1         | 7.15 | 27.50       | 6.80   | 2025-10-15 10:45:00 |
+----+-----------+------+-------------+--------+---------------------+
```

✅ **Data masuk ke database!**

### D. Cek Dashboard Web

1. **Buka browser**
2. **Login ke dashboard**: http://192.168.1.10:8000/login
3. **Masuk ke Dashboard User**
4. **Pilih "Jam Kerja" filter**

**Anda akan lihat:**

-   📈 **Grafik pH** dengan data real-time dari sensor
-   🌡️ **Grafik Suhu** (dummy 27.5°C untuk sementara)
-   💧 **Grafik Oksigen** (dummy 6.8 mg/L untuk sementara)

✅ **Dashboard menampilkan data dari ESP32!**

---

## 6️⃣ Troubleshooting

### ❌ Problem: WiFi tidak connect

**Gejala:**

```
❌ WiFi gagal tersambung!
   Periksa SSID dan password.
```

**Solusi:**

1. ✅ Pastikan SSID dan password **BENAR** (case-sensitive!)
2. ✅ ESP32 hanya support **WiFi 2.4GHz** (bukan 5GHz)
3. ✅ Pastikan ESP32 dan laptop di **jaringan WiFi yang SAMA**
4. ✅ Coba restart ESP32 (tekan tombol RST)

---

### ❌ Problem: Data tidak terkirim (HTTP Error)

**Gejala:**

```
❌ Gagal kirim data. Error: connection refused
```

**Solusi:**

1. **Cek Laravel server masih running:**

    ```powershell
    php artisan serve --host=0.0.0.0 --port=8000
    ```

2. **Cek IP address di code ESP32 BENAR:**

    ```cpp
    const char* SERVER_URL = "http://192.168.1.10:8000/api/sensor-data/store";
    //                              ^^^^^^^^^^^^ IP ini harus IP laptop Anda!
    ```

3. **Test manual dari browser:**

    ```
    http://192.168.1.10:8000/api/health
    ```

    Jika tidak bisa diakses → server bermasalah

4. **Cek firewall Windows:**
    - Buka **Windows Defender Firewall**
    - Allow **PHP** dan **Apache** di firewall

---

### ❌ Problem: HTTP 422 (Validation Error)

**Gejala:**

```
Response Code: 422
Response: {"success":false,"message":"Validasi gagal","errors":{...}}
```

**Solusi:**

1. **Cek device_id ada di database:**

    ```sql
    SELECT * FROM devices WHERE id = 1;
    ```

    Jika tidak ada → buat device dulu

2. **Cek format data yang dikirim:**
    - `device_id` harus **integer** (1, 2, 3, ...)
    - `ph` harus **number** 0-14
    - `temperature` dan `oxygen` harus **number**

---

### ❌ Problem: pH selalu NAN

**Gejala:**

```
📊 Raw ADC: 2048 | V: 1.650V | pH: (belum kalibrasi ⚠️)
```

**Solusi:**

1. ✅ Lakukan kalibrasi: `save7` dan `save4`
2. ✅ Cek koneksi kabel sensor (terutama OUT ke GPIO 4)
3. ✅ Pastikan sensor dapat power 3.3V

---

### ❌ Problem: pH tidak akurat

**Solusi:**

1. ✅ **Re-kalibrasi** dengan buffer pH baru (fresh)
2. ✅ Bilas probe dengan **air distilled** sebelum kalibrasi
3. ✅ Tunggu **30-60 detik** sebelum simpan kalibrasi
4. ✅ Periksa kondisi probe (tidak rusak/kering)

---

### ❌ Problem: Data tidak muncul di dashboard

**Solusi:**

1. **Cek data masuk ke database:**

    ```sql
    SELECT * FROM sensor_data ORDER BY recorded_at DESC LIMIT 5;
    ```

2. **Cek filter waktu di dashboard:**

    - Klik tombol **"Jam Kerja"** (08:00-16:00)
    - Jika sekarang jam 20:00 → data tidak akan muncul (di luar jam kerja)

3. **Cek device_id sesuai:**

    - Device ID di ESP32 harus sama dengan device ID user yang login

4. **Refresh dashboard:**
    - Tekan **F5** atau **Ctrl+R**

---

## 7️⃣ Perintah Serial Monitor

| Perintah   | Fungsi                              |
| ---------- | ----------------------------------- |
| `save7`    | Simpan kalibrasi pH 7               |
| `save4`    | Simpan kalibrasi pH 4               |
| `showcal`  | Tampilkan data kalibrasi            |
| `clearcal` | Hapus kalibrasi dari EEPROM         |
| `sendnow`  | Kirim data ke server sekarang juga  |
| `showip`   | Tampilkan IP ESP32 dan info network |

---

## 8️⃣ Diagram Alur Data

```
┌─────────────────┐
│   pH Sensor     │
│   (Analog)      │
└────────┬────────┘
         │
         │ Voltage (0-3.3V)
         ▼
┌─────────────────┐
│    ESP32-S3     │
│  - Read ADC     │
│  - Hitung pH    │
│  - WiFi Client  │
└────────┬────────┘
         │
         │ HTTP POST (JSON)
         │ {"device_id":1,"ph":7.23}
         ▼
┌─────────────────────────────────┐
│   Laravel Server (XAMPP)        │
│   http://192.168.1.10:8000      │
│   Route: /api/sensor-data/store │
└────────┬────────────────────────┘
         │
         │ INSERT INTO sensor_data
         ▼
┌─────────────────┐
│  MySQL Database │
│  (monitoringikan)│
└────────┬────────┘
         │
         │ SELECT queries
         ▼
┌─────────────────┐
│  Dashboard Web  │
│  (Chart.js)     │
│  User login     │
└─────────────────┘
```

---

## 9️⃣ Konfigurasi untuk Deployment

### Mode Development (Testing)

✅ **Sudah selesai!** Anda sudah bisa pakai sekarang.

### Mode Production (Kolam Sesungguhnya)

Jika mau deploy di kolam:

1. **Power Supply:**

    - Gunakan adaptor 5V 2A atau power bank
    - Jangan gunakan laptop terus-menerus

2. **Waterproofing:**

    - ESP32 board → masukkan ke **box waterproof**
    - pH probe → biarkan masuk air (probe memang waterproof)

3. **Mounting:**

    - Pasang ESP32 di tempat kering (di atas permukaan air)
    - pH probe celup di air kolam

4. **Monitoring:**
    - Akses dashboard dari HP/laptop lain
    - ESP32 akan kirim data otomatis tiap 30 detik

---

## 🎯 Checklist Final

Sebelum deploy, pastikan:

```
[ ] WiFi tersambung (IP ESP32 muncul)
[ ] Kalibrasi selesai (save7 & save4)
[ ] Test sendnow berhasil (HTTP 201)
[ ] Data masuk ke database (cek phpMyAdmin)
[ ] Dashboard menampilkan data (grafik muncul)
[ ] Auto-send berjalan (tiap 30 detik)
```

Jika semua ✅ → **SIAP DEPLOY!** 🎉

---

## 📞 Bantuan Lebih Lanjut

Jika ada masalah:

1. **Cek Serial Monitor** untuk error message
2. **Cek Laravel logs**: `storage/logs/laravel.log`
3. **Cek MySQL error log** di XAMPP
4. **Screenshot error** dan hubungi developer

---

**Status**: ✅ **READY TO USE!**  
**Last Update**: 15 Oktober 2025

Happy Monitoring! 🐟📊
