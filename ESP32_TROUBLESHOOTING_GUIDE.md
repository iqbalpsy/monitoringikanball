## 🔧 ESP32 TROUBLESHOOTING GUIDE - DATA TIDAK MASUK

### 📊 CURRENT STATUS:

-   **Latest data:** ID 67, 8 minutes ago ❌
-   **ESP32 Serial:** Menampilkan pH readings tapi tidak HTTP ❌
-   **Database:** No new data in 8+ minutes ❌

### 🔍 STEP-BY-STEP DIAGNOSIS:

#### 1. CHECK ESP32 SERIAL MONITOR OUTPUT

**Yang HARUS muncul setiap 30 detik:**

```
Raw ADC: 4095 | V: 3.300 | pH: 4.000 | WiFi: ✅ | Send: X | Fail: Y

🌐 Mengirim data ke server...
   URL: http://10.31.188.8/monitoringikanball/monitoringikanball/public/api/sensor-data/store
   Payload: {"device_id":1,"ph":"4.00","temperature":"26.50","oxygen":"6.80","voltage":"3.30"}
   Response Code: 201
   Response: {"success":true,"message":"Data sensor berhasil disimpan"...}
✅ Data berhasil dikirim!
```

**Jika TIDAK ADA output HTTP:**

#### 2. CHECK WiFi CONNECTION

```
Raw ADC: 4095 | V: 3.300 | pH: 4.000 | WiFi: ❌ | Send: 0 | Fail: X
```

**MASALAH:** ESP32 tidak connect ke WiFi
**SOLUSI:**

-   Check SSID: "POCO"
-   Check Password: "12345678"
-   Restart ESP32 (tekan reset button)
-   Check WiFi router working

#### 3. FORCE TEST TRANSMISSION

Di ESP32 Serial Monitor, ketik:

```
sendnow
```

**Expected response:**

```
🔧 Perintah diterima: sendnow
🌐 Mengirim data ke server...
   Response Code: 201
✅ Data berhasil dikirim!
```

**Jika masih error:**

#### 4. CHECK NETWORK CONNECTIVITY

**Problem:** ESP32 connect WiFi tapi tidak bisa reach server
**Diagnosis:**

-   ESP32 dapat IP address berbeda dengan PC
-   Firewall Windows blocking
-   Network isolation

**Test dari ESP32 Serial Monitor:**

```
showip
```

**Should show ESP32 IP address**

#### 5. ALTERNATIVE IP SOLUTIONS

**Option A: Use localhost (if same network)**

```cpp
const char* SERVER_URL = "http://localhost/monitoringikanball/monitoringikanball/public/api/sensor-data/store";
```

**Option B: Use router gateway**

```cpp
// Check dengan: ipconfig | findstr "Default Gateway"
const char* SERVER_URL = "http://192.168.1.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store";
```

**Option C: Disable Windows Firewall (temporary)**

-   Windows Security → Firewall → Turn off (for testing only)

### 🚨 MOST COMMON ISSUES:

1. **WiFi not connected** (90% of cases)

    - Wrong SSID/password
    - ESP32 not in range
    - Router blocking device

2. **Network isolation** (8% of cases)

    - ESP32 gets IP but can't reach PC
    - Firewall blocking
    - Different subnet

3. **Code not uploaded** (2% of cases)
    - Old code still running
    - Upload failed silently

### ✅ QUICK FIX CHECKLIST:

1. **Restart ESP32** (reset button)
2. **Check Serial Monitor** for WiFi: ✅
3. **Type "sendnow"** and check response
4. **If fails:** Check Windows Firewall
5. **If still fails:** Try different IP/localhost

### 🎯 NEXT ACTION:

**Check ESP32 Serial Monitor RIGHT NOW for WiFi status and HTTP output!**
