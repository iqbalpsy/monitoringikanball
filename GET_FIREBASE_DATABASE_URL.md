# 🔥 GET FIREBASE REALTIME DATABASE URL

## **Your Firebase Project Info:**

-   **Project ID:** `container-kolam`
-   **API Key:** `AIzaSyCZsfM1CTPfIyx9mOun9O--Nbmk6bIgu5s`
-   **Auth Domain:** `container-kolam.firebaseapp.com`
-   **Storage Bucket:** `container-kolam.firebasestorage.app`

## **⚠️ MISSING: Realtime Database URL**

Untuk menyelesaikan konfigurasi, Anda perlu **Realtime Database URL**.

---

## **📋 STEP-BY-STEP: Get Database URL**

### **Step 1: Buka Firebase Console**

1. Klik link ini: https://console.firebase.google.com/project/container-kolam/database
2. Atau:
    - Buka https://console.firebase.google.com/
    - Pilih project **"container-kolam"**
    - Di sidebar kiri, klik **"Build" → "Realtime Database"**

---

### **Step 2: Create Database (Jika Belum Ada)**

Jika Anda melihat halaman seperti ini:

```
┌─────────────────────────────────────┐
│  Get started with Realtime Database │
│                                     │
│     [Create Database]               │
└─────────────────────────────────────┘
```

**Klik "Create Database"**, lalu:

1. **Choose location:**

    - Pilih: **United States (us-central1)** [Recommended - default]
    - Atau: **Singapore (asia-southeast1)** [Lebih dekat ke Indonesia]

2. **Security rules:**

    - Pilih: **"Start in test mode"** (untuk development)
    - Klik **"Enable"**

3. Tunggu ~30 detik database dibuat

---

### **Step 3: Copy Database URL**

Setelah database dibuat, Anda akan melihat URL di bagian atas halaman:

```
https://container-kolam-default-rtdb.firebaseio.com/
```

atau (jika pakai region Singapore):

```
https://container-kolam-default-rtdb.asia-southeast1.firebasedatabase.app/
```

**COPY URL ini!** 📋

---

### **Step 4: Set Security Rules (Testing Mode)**

Di tab **"Rules"**, paste security rules berikut:

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

Klik **"Publish"** untuk apply rules.

---

## **✅ AFTER YOU GET THE URL:**

**Reply dengan format:**

```
Database URL: https://container-kolam-default-rtdb.firebaseio.com/
```

atau

```
Database URL: https://container-kolam-default-rtdb.asia-southeast1.firebasedatabase.app/
```

**Saya akan langsung update:**

1. ✅ `.env` file Laravel
2. ✅ `ESP32_pH_Firebase.ino` code
3. ✅ Test koneksi Firebase
4. ✅ Verify data flow ESP32 → Firebase → Laravel

---

## **🔍 ALTERNATIVE: Check If Database Already Exists**

Jika sudah pernah create database sebelumnya:

1. Buka: https://console.firebase.google.com/project/container-kolam/database
2. Lihat URL di bagian atas halaman (next to project name)
3. Copy URL tersebut

---

## **📱 QUICK ACCESS:**

**Direct Link:**
https://console.firebase.google.com/project/container-kolam/database

**Tunggu Database URL dari Anda!** 🕐
