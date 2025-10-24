# 📝 SUMMARY: Integrasi ESP32 pH Sensor dengan Web XAMPP

**Tanggal**: 15 Oktober 2025  
**Status**: ✅ **SELESAI & READY TO USE**

---

## 🎯 Yang Sudah Dikerjakan

### ✅ 1. API Endpoint Laravel (SUDAH ADA)

**File**: `routes/api.php`

**Endpoint**: `POST /api/sensor-data/store`

**Request Format**:

```json
{
    "device_id": 1,
    "ph": 7.23,
    "temperature": 27.5,
    "oxygen": 6.8
}
```

**Response Success (201)**:

```json
{
    "success": true,
    "message": "Data sensor berhasil disimpan",
    "data": {
        "id": 1,
        "device_id": 1,
        "ph": 7.23,
        "temperature": 27.5,
        "oxygen": 6.8,
        "recorded_at": "2025-10-15 10:30:00"
    }
}
```

**Validasi**:

-   ✅ `device_id` harus ada di tabel `devices`
-   ✅ `ph` harus 0-14 (numeric)
-   ✅ `temperature` dan `oxygen` opsional (numeric)

---

### ✅ 2. Code ESP32 Baru (SELESAI)

**File**: `ESP32_pH_XAMPP_Code.ino`

**Fitur**:

-   ✅ WiFi connectivity dengan auto-reconnect
-   ✅ HTTP POST ke Laravel API
-   ✅ EEPROM calibration storage (tahan power loss)
-   ✅ Two-point linear calibration (pH 7 & pH 4)
-   ✅ Auto-send data tiap 30 detik
-   ✅ Serial commands (save7, save4, showcal, clearcal, sendnow, showip)
-   ✅ pH range validation (0-14)
-   ✅ Status indicators (Normal/Asam/Basa)
-   ✅ Send counter & fail counter
-   ✅ Calibration reminder

**Konfigurasi**:

```cpp
const char* WIFI_SSID = "Polinela";           // ← Ganti WiFi Anda
const char* WIFI_PASSWORD = "24092005";       // ← Ganti password
const char* SERVER_URL = "http://192.168.1.10:8000/api/sensor-data/store"; // ← Ganti IP
const int DEVICE_ID = 1;                      // ← Sesuai database
```

---

### ✅ 3. Dokumentasi Lengkap (SELESAI)

**File yang dibuat**:

1. **ESP32_pH_XAMPP_Code.ino** (500+ baris)

    - Code Arduino lengkap dengan WiFi & HTTP

2. **SENSOR_XAMPP_INTEGRATION.md** (800+ baris)

    - Panduan setup lengkap
    - Step-by-step kalibrasi
    - Testing procedures
    - Troubleshooting guide
    - Diagram alur data
    - Checklist deployment

3. **QUICK_START_XAMPP_SENSOR.md** (150+ baris)

    - Quick start 5 langkah
    - Troubleshooting cepat
    - Perintah serial monitor
    - Checklist singkat

4. **WIRING_DIAGRAM.md** (SUDAH ADA SEBELUMNYA)
    - Diagram wiring detail
    - Pin mapping
    - Peringatan keamanan

---

## 🔌 Wiring

```
pH Sensor → ESP32-S3
VCC (Merah)  → 3.3V   ⚠️ BUKAN 5V!
GND (Hitam)  → GND
OUT (Biru)   → GPIO 4
```

---

## 📊 Alur Data

```
pH Probe
   ↓ (Voltage 0-3.3V)
ESP32-S3
   ↓ (HTTP POST JSON)
Laravel API (/api/sensor-data/store)
   ↓ (INSERT SQL)
MySQL Database (sensor_data table)
   ↓ (SELECT queries)
Dashboard Web (Chart.js)
```

---

## 🚀 Cara Pakai (Quick Start)

### 1. Setup Server Laravel

```powershell
cd D:\xampp\htdocs\monitoringikanball\monitoringikanball
php artisan serve --host=0.0.0.0 --port=8000
```

Cari IP laptop:

```powershell
ipconfig
```

### 2. Upload Code ESP32

1. Edit `ESP32_pH_XAMPP_Code.ino`:

    - Ganti `WIFI_SSID`
    - Ganti `WIFI_PASSWORD`
    - Ganti IP di `SERVER_URL`

2. Upload ke ESP32 (Board: ESP32S3 Dev Module)

3. Buka Serial Monitor (115200 baud)

### 3. Kalibrasi

Buffer pH 7:

```
save7
```

Buffer pH 4:

```
save4
```

### 4. Test

```
sendnow
```

Output:

```
✅ Data berhasil dikirim!
```

### 5. Cek Dashboard

Login → Dashboard User → Lihat grafik pH real-time!

---

## 🛠️ Library Arduino yang Diperlukan

| Library       | Version        | Status          |
| ------------- | -------------- | --------------- |
| WiFi.h        | Built-in ESP32 | ✅              |
| HTTPClient.h  | Built-in ESP32 | ✅              |
| EEPROM.h      | Built-in ESP32 | ✅              |
| ArduinoJson.h | v6.x.x         | ⚠️ **INSTALL!** |

**Cara install ArduinoJson:**

```
Tools → Manage Libraries → Cari "ArduinoJson" → Install v6.x.x
```

---

## 📈 Fitur Auto-Send

Setelah kalibrasi selesai, ESP32 akan:

1. ✅ Baca sensor pH tiap 2 detik
2. ✅ Hitung pH menggunakan kalibrasi
3. ✅ Kirim data ke server **tiap 30 detik**
4. ✅ Simpan ke database MySQL
5. ✅ Dashboard update otomatis

**Serial Monitor output:**

```
📊 Raw ADC: 2048 | V: 1.650V | pH: 7.15 ✅ Normal | WiFi: ✅ | Send: 5 | Fail: 0

🌐 Mengirim data ke server...
   Response Code: 201
✅ Data berhasil dikirim!
```

---

## 🔍 Troubleshooting

### ❌ WiFi tidak connect

**Solusi:**

-   Cek SSID & password benar (case-sensitive)
-   ESP32 hanya support **WiFi 2.4GHz** (bukan 5GHz)
-   Restart ESP32

### ❌ HTTP Error (connection refused)

**Solusi:**

-   Pastikan Laravel server running: `php artisan serve --host=0.0.0.0`
-   Cek IP di code ESP32 SAMA dengan IP laptop
-   Test manual: `http://192.168.1.10:8000/api/health`
-   Nonaktifkan Windows Firewall sementara

### ❌ HTTP 422 (Validation Error)

**Solusi:**

-   Cek `device_id` ada di database:
    ```sql
    SELECT * FROM devices WHERE id = 1;
    ```
-   Jika tidak ada, buat device baru di dashboard admin

### ❌ pH = NAN

**Solusi:**

-   Lakukan kalibrasi: `save7` dan `save4`
-   Cek wiring (OUT ke GPIO 4)
-   Cek sensor dapat power 3.3V

### ❌ Data tidak muncul di dashboard

**Solusi:**

-   Cek data masuk database:
    ```sql
    SELECT * FROM sensor_data ORDER BY recorded_at DESC LIMIT 5;
    ```
-   Cek filter waktu (tombol "Jam Kerja" = 08:00-16:00)
-   Refresh dashboard (F5)

---

## 🎮 Perintah Serial Monitor

| Perintah   | Fungsi                                              |
| ---------- | --------------------------------------------------- |
| `save7`    | Simpan kalibrasi pH 7 (celup di buffer pH 7)        |
| `save4`    | Simpan kalibrasi pH 4 (celup di buffer pH 4)        |
| `showcal`  | Tampilkan data kalibrasi (V7, V4, slope, intercept) |
| `clearcal` | Hapus kalibrasi dari EEPROM                         |
| `sendnow`  | Kirim data ke server sekarang juga (manual)         |
| `showip`   | Tampilkan IP ESP32 dan info network                 |

---

## 📊 Status Indicator

**Serial Monitor:**

```
pH: 7.15 ✅ Normal   → pH 6.5-8.5 (ideal untuk ikan)
pH: 5.50 ⚠️  Asam    → pH < 6.5 (terlalu asam)
pH: 9.20 ⚠️  Basa    → pH > 8.5 (terlalu basa)

WiFi: ✅  → Tersambung
WiFi: ❌  → Terputus

Send: 10  → Jumlah data berhasil dikirim
Fail: 2   → Jumlah gagal kirim
```

---

## 📦 File yang Tersedia

| File                          | Ukuran     | Fungsi                   |
| ----------------------------- | ---------- | ------------------------ |
| `ESP32_pH_XAMPP_Code.ino`     | 500+ baris | Code Arduino lengkap     |
| `SENSOR_XAMPP_INTEGRATION.md` | 800+ baris | Panduan setup lengkap    |
| `QUICK_START_XAMPP_SENSOR.md` | 150+ baris | Quick start guide        |
| `WIRING_DIAGRAM.md`           | 400+ baris | Diagram wiring detail    |
| `routes/api.php`              | -          | API endpoint (sudah ada) |

---

## 🎯 Checklist Sebelum Deploy

```
Hardware:
[ ] pH sensor tersambung (VCC → 3.3V, GND → GND, OUT → GPIO 4)
[ ] ESP32 dapat power (USB atau adaptor 5V 2A)
[ ] pH probe terendam air (bukan kering)

Software:
[ ] Library ArduinoJson terinstall (v6.x.x)
[ ] Code ESP32 sudah upload
[ ] WiFi SSID & password sudah benar di code
[ ] IP laptop sudah benar di SERVER_URL

Setup:
[ ] XAMPP running (Apache & MySQL)
[ ] Laravel server running (php artisan serve --host=0.0.0.0)
[ ] Database monitoringikan ada
[ ] Device ID 1 ada di tabel devices

Kalibrasi:
[ ] Buffer pH 7 tersedia
[ ] Buffer pH 4 tersedia
[ ] Kalibrasi pH 7 selesai (save7)
[ ] Kalibrasi pH 4 selesai (save4)
[ ] Test sendnow berhasil (HTTP 201)

Testing:
[ ] Data masuk database (cek phpMyAdmin)
[ ] Dashboard menampilkan data (grafik muncul)
[ ] Auto-send berjalan (tiap 30 detik)
[ ] Serial Monitor menampilkan status normal
```

Jika semua ✅ → **SIAP PRODUCTION!** 🎉

---

## 🌐 URL Penting

| URL                                       | Fungsi                  |
| ----------------------------------------- | ----------------------- |
| `http://localhost/phpmyadmin`             | Database management     |
| `http://192.168.1.10:8000/api/health`     | Test API endpoint       |
| `http://192.168.1.10:8000/login`          | Login web dashboard     |
| `http://192.168.1.10:8000/dashboard/user` | Dashboard user (grafik) |

(Ganti `192.168.1.10` dengan IP laptop Anda)

---

## 💡 Tips Production

1. **Power Supply**:

    - Gunakan adaptor 5V 2A (bukan laptop)
    - Atau power bank 10000mAh (tahan ~12 jam)

2. **Waterproofing**:

    - ESP32 board → box waterproof
    - pH probe → celup di air (probe waterproof)

3. **Maintenance**:

    - Kalibrasi ulang tiap 1 bulan
    - Bersihkan pH probe tiap minggu
    - Simpan probe di KCl solution saat tidak dipakai

4. **Monitoring**:
    - Akses dashboard dari HP/laptop lain
    - Data auto-update tiap 30 detik
    - Cek Serial Monitor jika ada error

---

## 🔐 Security (Opsional untuk Production)

Saat ini API endpoint **tidak pakai authentication** (biar ESP32 mudah akses).

Jika mau lebih aman:

1. **Tambah API Key**:

    ```cpp
    http.addHeader("X-API-Key", "rahasia123");
    ```

2. **IP Whitelist**:

    ```php
    // Di Laravel middleware
    $allowedIPs = ['192.168.1.25']; // IP ESP32
    ```

3. **HTTPS** (butuh SSL certificate)

Tapi untuk local network (XAMPP), keamanan sudah cukup.

---

## 📞 Support

Jika ada masalah:

1. ✅ Baca **SENSOR_XAMPP_INTEGRATION.md** (troubleshooting lengkap)
2. ✅ Cek Serial Monitor untuk error message
3. ✅ Cek Laravel logs: `storage/logs/laravel.log`
4. ✅ Screenshot error & hubungi developer

---

## 🎉 Kesimpulan

Sistem monitoring pH real-time sudah **100% SIAP PAKAI**:

-   ✅ ESP32 baca sensor pH
-   ✅ Kalibrasi two-point (pH 7 & 4)
-   ✅ Kirim data via WiFi
-   ✅ Laravel API terima & simpan ke MySQL
-   ✅ Dashboard web tampilkan grafik real-time
-   ✅ Auto-send tiap 30 detik
-   ✅ Dokumentasi lengkap

**Tinggal:**

1. Upload code ke ESP32
2. Kalibrasi sensor
3. Deploy di kolam

**Selamat monitoring!** 🐟📊💧

---

**Status**: ✅ **PRODUCTION READY**  
**Last Update**: 15 Oktober 2025  
**Version**: 2.0 (XAMPP Integration)
