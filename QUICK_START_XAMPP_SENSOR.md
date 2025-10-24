# 🚀 Quick Start: ESP32 pH Sensor → Web XAMPP

**Tanggal**: 15 Oktober 2025

---

## ⚡ 5 Langkah Cepat

### 1️⃣ Sambungkan Kabel (2 menit)

```
pH Sensor → ESP32
VCC   (Merah)  → 3.3V
GND   (Hitam)  → GND
OUT   (Biru)   → GPIO 4
```

⚠️ **JANGAN pakai 5V!** Hanya 3.3V!

---

### 2️⃣ Setup Laravel Server (1 menit)

**Buka PowerShell:**

```powershell
# 1. Masuk folder project
cd D:\xampp\htdocs\monitoringikanball\monitoringikanball

# 2. Jalankan server
php artisan serve --host=0.0.0.0 --port=8000
```

**Cari IP laptop Anda:**

```powershell
ipconfig
```

**Contoh output:**

```
IPv4 Address. . . . : 192.168.1.10    ← CATAT IP INI!
```

---

### 3️⃣ Upload Code ESP32 (3 menit)

**Edit file `ESP32_pH_XAMPP_Code.ino`:**

```cpp
// Ganti 3 baris ini:
const char* WIFI_SSID = "Polinela";              // ← WiFi Anda
const char* WIFI_PASSWORD = "24092005";          // ← Password WiFi
const char* SERVER_URL = "http://192.168.1.10:8000/api/sensor-data/store";  // ← IP Anda!
```

**Upload ke ESP32:**

1. Hubungkan ESP32 ke laptop (USB-C)
2. Pilih Board: **ESP32S3 Dev Module**
3. Pilih Port: **COM3** (atau sesuai)
4. Klik **Upload** (→)

---

### 4️⃣ Kalibrasi Sensor (3 menit)

**Buka Serial Monitor (Baud: 115200)**

**Kalibrasi pH 7:**

1. Celupkan probe ke **buffer pH 7**
2. Tunggu 30 detik
3. Ketik: `save7` → Enter

**Kalibrasi pH 4:**

1. Bilas probe, keringkan
2. Celupkan probe ke **buffer pH 4**
3. Tunggu 30 detik
4. Ketik: `save4` → Enter

✅ **Selesai!** Kalibrasi tersimpan.

---

### 5️⃣ Test Kirim Data (1 menit)

**Di Serial Monitor, ketik:**

```
sendnow
```

**Output yang benar:**

```
✅ Data berhasil dikirim!
```

**Cek database:**

```sql
SELECT * FROM sensor_data ORDER BY recorded_at DESC LIMIT 1;
```

✅ **Data masuk!**

---

## 🔍 Troubleshooting Cepat

### ❌ WiFi tidak connect

```cpp
// Cek SSID dan password BENAR (case-sensitive!)
// ESP32 hanya support WiFi 2.4GHz
```

### ❌ HTTP Error

```powershell
# Pastikan server Laravel running:
php artisan serve --host=0.0.0.0 --port=8000

# Pastikan IP di code ESP32 SAMA dengan IP laptop
ipconfig
```

### ❌ pH = NAN

```
// Lakukan kalibrasi: save7 dan save4
// Cek kabel sensor (OUT ke GPIO 4)
```

---

## 📊 Perintah Serial Monitor

| Perintah  | Fungsi                |
| --------- | --------------------- |
| `save7`   | Simpan kalibrasi pH 7 |
| `save4`   | Simpan kalibrasi pH 4 |
| `showcal` | Lihat data kalibrasi  |
| `sendnow` | Kirim data sekarang   |
| `showip`  | Lihat IP ESP32        |

---

## ✅ Checklist

```
[ ] Kabel tersambung (VCC → 3.3V, GND → GND, OUT → GPIO 4)
[ ] Server Laravel running (php artisan serve)
[ ] IP di code ESP32 sudah BENAR
[ ] WiFi tersambung (IP ESP32 muncul)
[ ] Kalibrasi selesai (save7 & save4)
[ ] Test sendnow berhasil (HTTP 201)
[ ] Data masuk database
```

Jika semua ✅ → **SIAP PAKAI!** 🎉

---

## 🎯 Auto-Send

Setelah kalibrasi, ESP32 akan **otomatis kirim data tiap 30 detik**.

**Cek dashboard web:**

```
http://192.168.1.10:8000/login
```

Login → Dashboard User → Grafik akan update otomatis!

---

**Status**: ✅ READY  
**Mode**: Auto-send every 30 seconds  
**Last Update**: 15 Oktober 2025

Selamat monitoring! 🐟📈
