# 🔥 Firebase Setup Instructions - PENTING!

## ❌ **MASALAH DITEMUKAN:**
Firebase Realtime Database memberikan **404 Not Found** error, yang berarti database belum diaktifkan atau dikonfigurasi dengan benar.

## 🛠️ **SOLUSI - Setup Firebase Realtime Database:**

### 1. **Buka Firebase Console**
- Buka: https://console.firebase.google.com/
- Login dengan akun Google Anda
- Pilih project: `kolam-ikan-project`

### 2. **Aktifkan Realtime Database**
- Di sidebar kiri, klik **"Realtime Database"**
- Klik **"Create Database"** (jika belum ada)
- Pilih lokasi database: **"us-central1"** (atau Asia jika tersedia)

### 3. **Set Database Rules (Sementara untuk Development)**
Masuk ke tab **"Rules"** dan ganti rules dengan:

```json
{
  "rules": {
    ".read": true,
    ".write": true
  }
}
```

**⚠️ PERINGATAN:** Rules ini terbuka untuk semua orang. Hanya untuk development!

### 4. **Rules untuk Production (Setelah testing selesai):**
```json
{
  "rules": {
    "devices": {
      "$deviceId": {
        ".read": true,
        ".write": true
      }
    },
    "connection_test": {
      ".read": true,
      ".write": true
    },
    "test": {
      ".read": true,
      ".write": true
    }
  }
}
```

### 5. **Verifikasi Database URL**
Pastikan URL database di Firebase Console sama dengan yang ada di `.env`:
```
FIREBASE_DATABASE_URL=https://kolam-ikan-project-default-rtdb.firebaseio.com
```

Jika berbeda, update file `.env` dengan URL yang benar.

### 6. **Test Data Manual**
Di Firebase Console, tab **"Data"**, coba tambah data manual:
- Klik **"+"** 
- Name: `test`
- Value: `"Hello Firebase"`
- Klik **"Add"**

## 🔧 **Alternative - Import Data Structure**

Jika ingin setup struktur database IoT langsung, import JSON ini di Firebase Console:

```json
{
  "devices": {
    "IOT_FISH_001": {
      "info": {
        "name": "Kolam A - Lele",
        "location": "Sektor Utara",
        "device_id": "IOT_FISH_001"
      },
      "status": {
        "online": true,
        "last_seen": "2025-10-10T12:00:00Z"
      },
      "sensor_data": {
        "latest": {
          "ph_level": 7.2,
          "temperature": 27.5,
          "oxygen_level": 6.8,
          "turbidity": 2.1,
          "timestamp": "2025-10-10T12:00:00Z"
        }
      }
    },
    "IOT_FISH_002": {
      "info": {
        "name": "Kolam B - Nila",
        "location": "Sektor Selatan", 
        "device_id": "IOT_FISH_002"
      },
      "status": {
        "online": false,
        "last_seen": "2025-10-10T11:30:00Z"
      }
    }
  },
  "connection_test": {
    "status": "ready"
  }
}
```

## 🚀 **Setelah Setup Firebase:**

Jalankan ulang test:
```bash
php test_firebase_direct.php
```

Jika berhasil, Anda akan melihat:
- ✅ Status: 200
- ✅ Success: YES 
- ✅ Data berhasil ditulis dan dibaca

## 📱 **Untuk Mobile App Development:**

Setelah Firebase aktif, mobile app bisa:
1. Real-time listen ke path: `/devices/{deviceId}/sensor_data/latest`
2. Kirim control commands ke: `/devices/{deviceId}/controls`
3. Monitor device status di: `/devices/{deviceId}/status`

## 🔐 **Security Rules Production:**

Setelah testing selesai, gunakan rules yang lebih aman:

```json
{
  "rules": {
    ".read": "auth != null",
    ".write": "auth != null",
    "devices": {
      "$deviceId": {
        "sensor_data": {
          ".write": "auth.uid == 'iot-device' || auth.token.admin == true"
        },
        "controls": {
          ".write": "auth.token.admin == true"
        }
      }
    }
  }
}
```

---

## ✅ **NEXT STEPS SETELAH FIREBASE AKTIF:**

1. **Test koneksi:** `php test_firebase_direct.php`
2. **Setup authentication** untuk production
3. **Configure mobile app** dengan Firebase SDK
4. **Program IoT devices** untuk connect ke Firebase
5. **Build real-time dashboard**

**🎯 Setelah Firebase diaktifkan, semua fitur real-time monitoring akan berfungsi!**
