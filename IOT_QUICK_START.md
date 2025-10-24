# 🔌 Panduan Singkat: Hubungkan pH Sensor ESP32 ke Web

## 📦 Yang Dibutuhkan

### Hardware:

-   ✅ ESP32-S3 Development Board
-   ✅ pH Sensor Analog
-   ✅ Buffer pH 4.01 dan 7.00 (untuk kalibrasi)
-   ✅ 3 kabel jumper
-   ✅ USB-C cable

### Software:

-   ✅ Arduino IDE (download di arduino.cc)
-   ✅ ESP32 Board Support
-   ✅ Laravel server (sudah ada)

---

## 🔌 Wiring (Sambungan Kabel)

```
pH Sensor       →    ESP32-S3
VCC (Merah)     →    3.3V  ⚠️ JANGAN 5V!
GND (Hitam)     →    GND
OUT (Biru)      →    GPIO 4
```

---

## 💻 Setup ESP32

### 1. Download Code

File sudah dibuat: `ESP32_pH_WiFi_Code.ino`

### 2. Edit WiFi & Server

Buka file `.ino` dan ganti bagian ini:

```cpp
#define WIFI_SSID "NAMA_WIFI_ANDA"           // 👈 Ganti!
#define WIFI_PASSWORD "PASSWORD_WIFI_ANDA"   // 👈 Ganti!
#define SERVER_URL "http://192.168.1.100:8000/api/sensor-data/store" // 👈 Ganti IP!
```

**Cara dapat IP komputer:**

```powershell
ipconfig
```

Lihat "IPv4 Address" (contoh: 192.168.1.100)

### 3. Upload ke ESP32

1. Hubungkan ESP32 ke USB
2. Buka Arduino IDE
3. Tools → Board → ESP32S3 Dev Module
4. Tools → Port → pilih port COM
5. Click Upload (→)

### 4. Buka Serial Monitor

-   Tools → Serial Monitor
-   Set: **115200 baud**

Harusnya lihat:

```
✅ WiFi Terhubung!
IP Address: 192.168.1.200
🚀 System Ready!
```

### 5. Kalibrasi Sensor

#### Step A: pH 7

```
1. Celupkan sensor ke buffer pH 7
2. Tunggu stabil
3. Ketik: save7
4. Enter
```

#### Step B: pH 4

```
1. Bilas sensor
2. Celupkan ke buffer pH 4
3. Tunggu stabil
4. Ketik: save4
5. Enter
```

Harusnya lihat:

```
💾 Kalibrasi disimpan ke EEPROM
```

---

## 🌐 Setup Server Laravel

### 1. Jalankan Server

Di folder project:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Test API

Buka browser:

```
http://127.0.0.1:8000/api/health
```

Harusnya dapat response JSON.

### 3. Pastikan Device Ada

Di phpMyAdmin, check:

```sql
SELECT * FROM devices WHERE id = 1;
```

Kalau tidak ada, insert:

```sql
INSERT INTO devices (id, device_id, name, location, is_active, created_at, updated_at)
VALUES (1, 'ESP32-001', 'pH Sensor', 'Kolam 1', 1, NOW(), NOW());
```

---

## 🧪 Testing

### Test 1: Manual Send

Di Serial Monitor ESP32:

```
sendnow
```

Harusnya lihat:

```
✅ Data terkirim!
Response Code: 201
```

### Test 2: Auto Send

Tunggu 30 detik, ESP32 otomatis kirim data.

### Test 3: Check Database

```sql
SELECT * FROM sensor_data ORDER BY recorded_at DESC LIMIT 5;
```

Harusnya ada data baru!

### Test 4: Check Dashboard

Buka:

```
http://127.0.0.1:8000/user/dashboard
```

Login:

```
Email: user@test.com
Password: password123
```

Grafik pH harusnya update dengan data baru!

---

## 📱 Perintah ESP32

Ketik di Serial Monitor:

| Perintah   | Fungsi                |
| ---------- | --------------------- |
| `save7`    | Simpan kalibrasi pH 7 |
| `save4`    | Simpan kalibrasi pH 4 |
| `showcal`  | Lihat data kalibrasi  |
| `clearcal` | Hapus kalibrasi       |
| `sendnow`  | Kirim data sekarang   |
| `showip`   | Lihat IP ESP32        |

---

## ❌ Troubleshooting

### WiFi Tidak Connect

```
✅ Check SSID dan Password
✅ ESP32 dekat dengan router
✅ WiFi 2.4GHz (bukan 5GHz)
✅ Restart ESP32
```

### Data Tidak Terkirim

```
✅ Server Laravel running
✅ IP address benar
✅ Firewall tidak block port 8000
✅ Device ID exists di database
```

### pH Selalu NAN

```
✅ Lakukan kalibrasi: save7 dan save4
✅ Check buffer pH masih bagus
✅ Sensor terendam air (bukan udara)
```

---

## 📊 Alur Data

```
pH Sensor → ESP32 → WiFi → Laravel API → Database → Web Dashboard
         (Voltage) (HTTP)  (JSON)     (MySQL)    (Chart)
```

---

## ✅ Checklist

Sebelum start, pastikan:

### ESP32:

-   [ ] Code sudah diupload
-   [ ] WiFi SSID & Password benar
-   [ ] IP server benar
-   [ ] Sensor terhubung ke GPIO 4
-   [ ] VCC ke 3.3V (BUKAN 5V!)
-   [ ] Kalibrasi sudah dilakukan

### Server:

-   [ ] Laravel server running
-   [ ] Database connected
-   [ ] Device exists (id = 1)
-   [ ] API endpoint tersedia

### Hardware:

-   [ ] pH sensor connected
-   [ ] Power supply OK
-   [ ] Buffer pH 4 & 7 ready

---

## 🎉 Selesai!

Jika semua OK, Anda akan lihat:

**Di Serial Monitor:**

```
📊 DATA SENSOR
   pH: 7.18
   Status: ✅ NORMAL
   WiFi: ✅ Terhubung
   Data Terkirim: 10 kali
✅ Data terkirim!
```

**Di Dashboard Web:**

```
🔵 pH: 7.2
✅ Status: Normal
📊 Grafik update otomatis
```

---

## 📚 Dokumentasi Lengkap

Lihat file: **IOT_CONNECTION_GUIDE.md** untuk panduan detail.

---

**Status**: ✅ **READY!**
**Update**: 15 Oktober 2025

Selamat monitoring! 🐟📊
