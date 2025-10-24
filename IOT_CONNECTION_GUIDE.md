# 🔌 Cara Menghubungkan Sensor pH IoT ESP32-S3 ke Web Monitoring

## 📋 Daftar Isi

1. [Hardware yang Dibutuhkan](#hardware-yang-dibutuhkan)
2. [Software yang Dibutuhkan](#software-yang-dibutuhkan)
3. [Wiring Diagram](#wiring-diagram)
4. [Setup ESP32](#setup-esp32)
5. [Setup Laravel Server](#setup-laravel-server)
6. [Testing & Troubleshooting](#testing--troubleshooting)

---

## 🛠️ Hardware yang Dibutuhkan

### 1. **ESP32-S3 Development Board**

-   Minimum 1 unit
-   Dengan WiFi built-in
-   USB-C untuk programming

### 2. **pH Sensor Analog**

-   pH Sensor Module (Analog output)
-   Buffer pH 4.01 (untuk kalibrasi)
-   Buffer pH 7.00 (untuk kalibrasi)
-   Elektroda pH

### 3. **Kabel & Accessories**

-   Jumper wires (minimal 3 buah)
-   USB-C cable untuk power & programming
-   Power supply 5V (opsional untuk deployment)

---

## 💻 Software yang Dibutuhkan

### 1. **Arduino IDE**

-   Download: https://www.arduino.cc/en/software
-   Versi: 2.0 atau lebih baru

### 2. **ESP32 Board Support**

-   Di Arduino IDE:
    -   File → Preferences
    -   Additional Board Manager URLs:
        ```
        https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
        ```
    -   Tools → Board → Boards Manager
    -   Cari "ESP32" by Espressif Systems
    -   Install

### 3. **Library yang Dibutuhkan**

Semua sudah built-in di ESP32, tidak perlu install tambahan:

-   WiFi.h (Built-in)
-   HTTPClient.h (Built-in)
-   EEPROM.h (Built-in)

---

## 🔌 Wiring Diagram

### Koneksi pH Sensor ke ESP32-S3

```
pH Sensor Module          ESP32-S3
┌──────────────┐         ┌────────────┐
│              │         │            │
│  VCC (Red)   ├─────────┤ 3.3V       │
│              │         │            │
│  GND (Black) ├─────────┤ GND        │
│              │         │            │
│  OUT (Blue)  ├─────────┤ GPIO 4     │ 👈 Pin Analog Input
│              │         │  (ADC1_3)  │
└──────────────┘         └────────────┘
```

### ⚠️ Catatan Penting:

-   **Gunakan 3.3V**, JANGAN 5V! ESP32 tidak tahan 5V
-   **Pin GPIO 4** adalah ADC yang sudah ditest berfungsi
-   Pin ADC lain yang bisa digunakan: GPIO 5, 6, 7, 8, 9, 10
-   Hindari pin ADC2 (GPIO 0, 2, 4, 12-15) karena konflik dengan WiFi

---

## 🚀 Setup ESP32

### Langkah 1: Download Code

File: `ESP32_pH_WiFi_Code.ino` (sudah dibuat di project folder)

### Langkah 2: Konfigurasi WiFi & Server

Buka file `.ino` dan edit bagian ini:

```cpp
// ========== KONFIGURASI WIFI & SERVER ==========
#define WIFI_SSID "NAMA_WIFI_ANDA"        // 👈 GANTI INI!
#define WIFI_PASSWORD "PASSWORD_WIFI_ANDA" // 👈 GANTI INI!
#define SERVER_URL "http://192.168.1.100:8000/api/sensor-data/store" // 👈 GANTI IP INI!
#define DEVICE_ID 1  // ID device di database
```

#### Cara Mendapatkan IP Komputer:

**Windows:**

```powershell
ipconfig
```

Cari "IPv4 Address" di adapter WiFi Anda (contoh: 192.168.1.100)

**Mac/Linux:**

```bash
ifconfig
```

### Langkah 3: Upload ke ESP32

1. **Connect ESP32 ke Komputer** via USB-C
2. **Buka Arduino IDE**
3. **Pilih Board:**
    - Tools → Board → ESP32 Arduino → ESP32S3 Dev Module
4. **Pilih Port:**
    - Tools → Port → COMx (Windows) atau /dev/tty.xxx (Mac/Linux)
5. **Upload:**
    - Click tombol Upload (→)
    - Tunggu sampai "Done uploading"

### Langkah 4: Monitor Serial

1. **Buka Serial Monitor:**

    - Tools → Serial Monitor
    - Set Baud Rate: **115200**

2. **Lihat Output:**

    ```
    ╔════════════════════════════════════════╗
    ║   SENSOR pH IoT - ESP32-S3             ║
    ║   Monitoring Kualitas Air Kolam Ikan   ║
    ╚════════════════════════════════════════╝

    ✅ ADC Resolution: 12-bit
    ✅ EEPROM Initialized
    ⚠️  Belum ada data kalibrasi!

    📡 Menghubungkan ke WiFi...
    ✅ WiFi Terhubung!
    IP Address: 192.168.1.200

    🚀 System Ready!
    ```

### Langkah 5: Kalibrasi Sensor

#### Step 1: Kalibrasi pH 7

```
1. Celupkan sensor ke buffer pH 7.00
2. Tunggu nilai voltage stabil (±0.01V)
3. Ketik di Serial Monitor: save7
4. Tekan Enter
```

Output:

```
💾 Disimpan: V_pH7 = 1.6543
```

#### Step 2: Kalibrasi pH 4

```
1. Bilas sensor dengan air bersih
2. Celupkan sensor ke buffer pH 4.01
3. Tunggu nilai voltage stabil
4. Ketik di Serial Monitor: save4
5. Tekan Enter
```

Output:

```
💾 Disimpan: V_pH4 = 2.1234
📊 Perhitungan kalibrasi:
   slope = -6.401828
   intercept = 17.500000
💾 Kalibrasi disimpan ke EEPROM.
```

#### Verifikasi Kalibrasi:

Ketik: `showcal`

Output:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📖 DATA KALIBRASI
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   V_pH7 = 1.6543
   V_pH4 = 2.1234
   slope = -6.401828
   intercept = 17.500000
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## 🌐 Setup Laravel Server

### Langkah 1: Pastikan Server Running

Di folder project Laravel:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Output:

```
Laravel development server started: http://0.0.0.0:8000
```

### Langkah 2: Test API Endpoint

Buka browser atau Postman, test:

```
GET http://127.0.0.1:8000/api/health
```

Response:

```json
{
    "success": true,
    "message": "API IoT Fish Monitoring is running",
    "timestamp": "2025-10-15T10:30:00.000000Z"
}
```

### Langkah 3: Pastikan Device ID Exists

Check di database:

```sql
SELECT * FROM devices WHERE id = 1;
```

Kalau tidak ada, create dulu di phpMyAdmin atau:

```sql
INSERT INTO devices (id, device_id, name, location, is_active, created_at, updated_at)
VALUES (1, 'ESP32-001', 'pH Sensor Kolam 1', 'Kolam Utama', 1, NOW(), NOW());
```

### Langkah 4: Disable CSRF untuk API (sudah otomatis)

File `bootstrap/app.php` sudah mengecualikan `/api/*` dari CSRF protection.

---

## 🧪 Testing & Troubleshooting

### Test 1: Manual Send Data

Di Serial Monitor ESP32, ketik:

```
sendnow
```

Output yang diharapkan:

```
📤 Mengirim data ke server...
   URL: http://192.168.1.100:8000/api/sensor-data/store
   Payload: {"device_id":1,"ph":7.23,"temperature":27.5,"oxygen":6.8}
✅ Data terkirim!
   Response Code: 201
   Response: {"success":true,"message":"Data sensor berhasil disimpan","data":{...}}
```

### Test 2: Auto Send (setiap 30 detik)

Tunggu 30 detik, ESP32 akan otomatis kirim data.

Output:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 DATA SENSOR
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   Voltage: 1.678 V
   pH: 7.18
   Status: ✅ NORMAL
   WiFi: ✅ Terhubung
   Data Terkirim: 5 kali
   Gagal Kirim: 0 kali
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📤 Mengirim data ke server...
✅ Data terkirim!
```

### Test 3: Check Data di Database

```sql
SELECT * FROM sensor_data
ORDER BY recorded_at DESC
LIMIT 10;
```

Harusnya ada data baru dari ESP32:

```
| id | device_id | ph   | temperature | oxygen | recorded_at         |
|----|-----------|------|-------------|--------|---------------------|
| 45 | 1         | 7.18 | 27.5        | 6.8    | 2025-10-15 10:35:22 |
| 44 | 1         | 7.20 | 27.5        | 6.8    | 2025-10-15 10:35:00 |
```

### Test 4: Check Web Dashboard

Buka browser:

```
http://127.0.0.1:8000/user/dashboard
```

Login dengan:

```
Email: user@test.com
Password: password123
```

Cek apakah grafik pH update dengan data terbaru!

---

## ❌ Troubleshooting

### Problem 1: WiFi Tidak Connect

**Symptoms:**

```
📡 Menghubungkan ke WiFi...
..................
❌ WiFi Gagal Terhubung!
```

**Solutions:**

1. Check SSID dan Password di code
2. Pastikan ESP32 dekat dengan router
3. Check WiFi 2.4GHz (ESP32 tidak support 5GHz)
4. Restart ESP32 (tekan tombol RST)

### Problem 2: HTTP Error 404

**Symptoms:**

```
❌ Gagal mengirim data!
   Error Code: -1
   Error: connection refused
```

**Solutions:**

1. **Check Server Running:**

    ```powershell
    php artisan serve --host=0.0.0.0
    ```

2. **Check IP Address:**

    - Di code ESP32, pastikan IP benar
    - Test di browser: `http://IP_ANDA:8000/api/health`

3. **Check Firewall:**
    - Windows Firewall mungkin block port 8000
    - Tambah exception untuk PHP

### Problem 3: HTTP Error 422 (Validation Failed)

**Symptoms:**

```
❌ Gagal mengirim data!
   Error Code: 422
```

**Solutions:**

1. **Check Device ID:**

    - Pastikan device_id exists di database
    - Check: `SELECT * FROM devices WHERE id = 1;`

2. **Check Data Format:**
    - pH: 0-14
    - Temperature & Oxygen: numeric

### Problem 4: Data Tidak Muncul di Dashboard

**Symptoms:**

-   Data terkirim (HTTP 201)
-   Tapi grafik tidak update

**Solutions:**

1. **Check Database:**

    ```sql
    SELECT * FROM sensor_data ORDER BY recorded_at DESC LIMIT 5;
    ```

2. **Refresh Dashboard:**

    - Tekan Ctrl + F5 di browser
    - Atau click tombol "Refresh"

3. **Check Filter Jam:**
    - Dashboard menampilkan jam 08:00-16:00
    - Pastikan waktu server sesuai

### Problem 5: pH Value Always NAN

**Symptoms:**

```
   pH: ❌ (Belum dikalibrasi)
```

**Solutions:**

1. **Lakukan Kalibrasi:**

    ```
    save7
    save4
    ```

2. **Check EEPROM:**

    ```
    showcal
    ```

3. **Reset Kalibrasi:**
    ```
    clearcal
    ```
    Lalu ulangi kalibrasi

---

## 📊 Data Flow Diagram

```
┌─────────────────┐
│   pH Sensor     │
│   (Analog)      │
└────────┬────────┘
         │ Voltage (0-3.3V)
         ↓
┌─────────────────┐
│   ESP32-S3      │
│   - Read ADC    │
│   - Calibrate   │
│   - Calculate pH│
└────────┬────────┘
         │ WiFi
         │ HTTP POST
         │ JSON: {device_id, ph, temp, oxygen}
         ↓
┌─────────────────┐
│  Laravel API    │
│  /api/sensor-   │
│  data/store     │
└────────┬────────┘
         │ Validate & Save
         ↓
┌─────────────────┐
│   Database      │
│   sensor_data   │
│   table         │
└────────┬────────┘
         │ Query
         ↓
┌─────────────────┐
│  Web Dashboard  │
│  - Charts       │
│  - Cards        │
│  - Real-time    │
└─────────────────┘
```

---

## 🎯 Command Reference

### ESP32 Serial Commands:

| Command    | Fungsi                       | Contoh Output               |
| ---------- | ---------------------------- | --------------------------- |
| `save7`    | Simpan kalibrasi pH 7        | 💾 Disimpan: V_pH7 = 1.6543 |
| `save4`    | Simpan kalibrasi pH 4        | 💾 Disimpan: V_pH4 = 2.1234 |
| `showcal`  | Tampilkan data kalibrasi     | V_pH7 = 1.6543...           |
| `clearcal` | Hapus kalibrasi dari EEPROM  | ✅ Kalibrasi dihapus        |
| `sendnow`  | Kirim data sekarang juga     | 📤 Mengirim data...         |
| `showip`   | Tampilkan info jaringan & IP | IP Address: 192.168.1.200   |

---

## 📝 Configuration Checklist

Sebelum deploy, pastikan sudah set:

### ESP32 Code:

-   [ ] `WIFI_SSID` - Nama WiFi Anda
-   [ ] `WIFI_PASSWORD` - Password WiFi
-   [ ] `SERVER_URL` - IP komputer + :8000/api/sensor-data/store
-   [ ] `DEVICE_ID` - Sesuai dengan database (default: 1)
-   [ ] `PH_PIN` - Pin GPIO untuk pH sensor (default: 4)
-   [ ] `SEND_INTERVAL` - Interval kirim data (default: 30000 = 30 detik)

### Laravel Server:

-   [ ] Server running: `php artisan serve --host=0.0.0.0`
-   [ ] Database connected: Check `.env` DB settings
-   [ ] Device exists: Check `devices` table
-   [ ] API route exists: `POST /api/sensor-data/store`
-   [ ] CSRF disabled untuk `/api/*` (sudah default)

### Hardware:

-   [ ] pH sensor connected ke GPIO 4
-   [ ] VCC ke 3.3V (BUKAN 5V!)
-   [ ] GND ke GND
-   [ ] USB-C connected untuk power
-   [ ] Buffer pH 4 & 7 tersedia

---

## 🚀 Production Deployment

### Untuk deployment jangka panjang:

1. **Power Supply:**

    - Gunakan power supply 5V 2A
    - Atau power bank untuk portabel

2. **Waterproofing:**

    - ESP32 di dalam box waterproof
    - Hanya sensor & kabel keluar

3. **Security:**

    - Tambah API Key authentication
    - Gunakan HTTPS (SSL/TLS)
    - Enkripsi WiFi password

4. **Reliability:**

    - Tambah watchdog timer
    - Auto-restart jika WiFi disconnect lama
    - Log errors ke SD card

5. **Maintenance:**
    - Kalibrasi ulang setiap 1 bulan
    - Bersihkan sensor setiap minggu
    - Check battery/power supply rutin

---

## 📚 Additional Resources

### Dokumentasi:

-   ESP32 Arduino Core: https://docs.espressif.com/projects/arduino-esp32/
-   pH Sensor Guide: Check sensor datasheet
-   Laravel API: https://laravel.com/docs/routing#api-routes

### Tools:

-   Arduino IDE: https://www.arduino.cc/
-   Postman: https://www.postman.com/ (untuk test API)
-   phpMyAdmin: http://localhost/phpmyadmin (untuk check database)

---

## ✅ Success Indicators

Jika semuanya berhasil, Anda akan lihat:

### Di Serial Monitor ESP32:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 DATA SENSOR
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   Voltage: 1.678 V
   pH: 7.18
   Status: ✅ NORMAL
   WiFi: ✅ Terhubung
   Data Terkirim: 25 kali
   Gagal Kirim: 0 kali
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📤 Mengirim data ke server...
✅ Data terkirim!
   Response Code: 201
```

### Di Web Dashboard:

-   📊 Grafik pH menampilkan data terbaru
-   🔵 Card pH shows nilai real-time
-   ✅ Status badge: "Normal" atau "Perhatian"
-   🔄 Auto-refresh setiap 30 detik

### Di Database:

```sql
SELECT COUNT(*) FROM sensor_data WHERE device_id = 1;
-- Hasilnya terus bertambah setiap 30 detik
```

---

## 🎉 Selamat!

Jika semua langkah di atas sudah diikuti, sistem IoT pH monitoring Anda sudah terhubung dan berjalan!

**Next Steps:**

1. Monitor data selama beberapa jam
2. Verifikasi akurasi pH sensor
3. Setup alert notifications (opsional)
4. Deploy ke kolam ikan real

**Support:**

-   Check dokumentasi jika ada error
-   Test satu-satu component
-   Gunakan Serial Monitor untuk debug

---

**Status**: ✅ **READY TO DEPLOY!**
**Last Updated**: October 15, 2025
**Version**: 1.0.0

Selamat monitoring! 🐟📊
