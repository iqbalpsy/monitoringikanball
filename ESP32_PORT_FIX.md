# 🔧 Fix: Port 8000 Tidak Terbaca di ESP32

**Tanggal**: 15 Oktober 2025  
**Problem**: ESP32 tidak bisa akses `http://192.168.56.1:8000`  
**Solution**: Gunakan Apache XAMPP (Port 80) instead

---

## ❌ Problem

ESP32 tidak bisa connect ke Laravel development server:

```cpp
// INI TIDAK WORK:
const char* SERVER_URL = "http://192.168.56.1:8000/api/sensor-data/store";
```

**Penyebab:**

-   Port 8000 mungkin **diblokir** oleh firewall
-   Laravel development server (`php artisan serve`) **tidak stabil** untuk production
-   ESP32 lebih cocok dengan **Apache** (port 80)

---

## ✅ Solution (SUDAH DIUPDATE!)

Gunakan **Apache XAMPP** (port 80):

```cpp
// INI YANG BENAR (SUDAH DIUPDATE):
const char* SERVER_URL = "http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store";
```

---

## 🚀 Setup Apache XAMPP untuk Laravel

### Step 1: Pastikan Apache Running

1. **Buka XAMPP Control Panel**
2. **Start Apache** (jika belum)
3. **Start MySQL** (jika belum)

```
Apache:  ✅ Running (Port 80)
MySQL:   ✅ Running (Port 3306)
```

### Step 2: Test Laravel via Apache

**Buka browser, akses:**

```
http://localhost/monitoringikanball/monitoringikanball/public/
```

**Harusnya muncul Laravel welcome page atau redirect ke login.**

✅ Jika muncul → Apache sudah benar!

---

## 🧪 Test API Endpoint

### Test 1: Health Check

**Akses di browser:**

```
http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/health
```

**Expected response:**

```json
{
    "success": true,
    "message": "API IoT Fish Monitoring is running",
    "timestamp": "2025-10-15T..."
}
```

✅ **Jika muncul JSON ini** → API endpoint sudah accessible!

---

### Test 2: Test dari Command Line (PowerShell)

**Buka PowerShell, ketik:**

```powershell
curl.exe -X POST http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store -H "Content-Type: application/json" -d '{\"device_id\":1,\"ph\":7.23,\"temperature\":27.5,\"oxygen\":6.8}'
```

**Expected response:**

```json
{
  "success": true,
  "message": "Data sensor berhasil disimpan",
  "data": {...}
}
```

✅ **HTTP 201** → Berhasil!  
❌ **HTTP 422** → `device_id` tidak ada di database  
❌ **HTTP 404** → URL salah

---

## 🔄 Upload Code ESP32 (Update)

Code **SUDAH DIUPDATE OTOMATIS** ke:

```cpp
const char* SERVER_URL = "http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store";
```

**Sekarang:**

1. **Save file** `ESP32_pH_XAMPP_Code.ino` (Ctrl+S)
2. **Buka Arduino IDE**
3. **Klik Upload** (→)
4. **Tunggu "Done uploading"**
5. **Buka Serial Monitor** (115200 baud)

---

## 🧪 Test dari ESP32

**Di Serial Monitor, ketik:**

```
sendnow
```

**Output yang BENAR:**

```
🌐 Mengirim data ke server...
   URL: http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store
   Payload: {"device_id":1,"ph":"7.23","temperature":27.5,"oxygen":6.8}
   Response Code: 201
   Response: {"success":true,"message":"Data sensor berhasil disimpan",...}
✅ Data berhasil dikirim!
```

✅ **Response Code 201** = SUCCESS!

---

## 🔍 Troubleshooting

### ❌ HTTP Error -1 (Connection Failed)

**Solusi:**

1. ✅ Pastikan **Apache XAMPP running**
2. ✅ Test browser: `http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/health`
3. ✅ Pastikan WiFi ESP32 dan laptop **di jaringan SAMA**
4. ✅ Ketik `showip` di Serial Monitor untuk cek network
5. ✅ Nonaktifkan **Windows Firewall** sementara

---

### ❌ HTTP 404 (Not Found)

**Solusi:**

1. ✅ Cek URL di code ESP32 sudah benar (sudah diupdate otomatis)
2. ✅ Test manual: `http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/health`
3. ✅ Pastikan file `.htaccess` ada di folder `public/`

---

### ❌ HTTP 422 (Validation Error)

**Solusi:**
Cek `device_id` ada di database:

```sql
SELECT * FROM devices WHERE id = 1;
```

Jika tidak ada, buat device:

```sql
INSERT INTO devices (user_id, name, status, created_at, updated_at)
VALUES (1, 'Kolam 1', 'active', NOW(), NOW());
```

---

## 📊 Perbandingan Port

| Port                     | Status          | Untuk IoT           |
| ------------------------ | --------------- | ------------------- |
| 8000 (php artisan serve) | ❌ Tidak stabil | ❌ Tidak disarankan |
| 80 (Apache XAMPP)        | ✅ Stabil       | ✅ **RECOMMENDED**  |

---

## ✅ Checklist

```
[ ] XAMPP Apache running (Port 80)
[ ] XAMPP MySQL running (Port 3306)
[ ] Code ESP32 sudah diupdate (tanpa :8000)
[ ] Code sudah diupload ke ESP32
[ ] Test sendnow → Response 201
[ ] Data masuk database
```

**Status**: ✅ **FIXED!**  
**Last Update**: 15 Oktober 2025

Selamat testing! 🚀
