# 🗄️ DATABASE UNTUK IOT pH SENSOR - READY!

**Database**: `monitoringikan`  
**Status**: ✅ **SUDAH SIAP DIGUNAKAN!**

---

## 📊 STRUKTUR DATABASE YANG SUDAH ADA

### Tabel: `sensor_data`

| Kolom             | Type                               | Fungsi                                             |
| ----------------- | ---------------------------------- | -------------------------------------------------- |
| `id`              | bigint(20) unsigned AUTO_INCREMENT | ID unik                                            |
| `device_id`       | bigint(20) unsigned                | ID kolam/device (FK)                               |
| **`ph`**          | **decimal(4,2)**                   | **Nilai pH dari sensor (0-14)** ← DATA DARI ESP32! |
| `temperature`     | decimal(5,2)                       | Suhu air (°C)                                      |
| `oxygen`          | decimal(4,2)                       | Oksigen (mg/L)                                     |
| **`recorded_at`** | **timestamp**                      | **Waktu pembacaan sensor**                         |
| `created_at`      | timestamp                          | Auto                                               |
| `updated_at`      | timestamp                          | Auto                                               |

**Status**: ✅ **SUDAH ADA dan READY!**  
**Total Records**: 51 data  
**Last Record**: ID 51 (pH: 7.23, 15 Okt 2025)

---

## 📥 DATA DARI ESP32 → DATABASE

### Alur Data:

```
┌─────────────────┐
│  ESP32 pH       │  Baca sensor pH
│  Sensor         │  pH = 7.23
└────────┬────────┘
         │
         │ HTTP POST (WiFi)
         │
         ▼
┌─────────────────────────────────┐
│  Laravel API                    │
│  /api/sensor-data/store         │
│  Validasi data                  │
└────────┬────────────────────────┘
         │
         │ INSERT INTO sensor_data
         │
         ▼
┌─────────────────────────────────┐
│  MySQL Database                 │
│  monitoringikan.sensor_data     │
│                                 │
│  id: 52                         │
│  device_id: 1                   │
│  ph: 7.23        ← DARI SENSOR! │
│  temperature: 27.5              │
│  oxygen: 6.8                    │
│  recorded_at: 2025-10-15 14:30  │
└─────────────────────────────────┘
```

---

## 💾 SQL CREATE TABLE (untuk referensi)

```sql
CREATE TABLE `sensor_data` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `device_id` bigint(20) UNSIGNED NOT NULL,
  `ph` decimal(4,2) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `oxygen` decimal(4,2) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sensor_data_device_id_foreign` (`device_id`),
  KEY `sensor_data_recorded_at_index` (`recorded_at`),
  CONSTRAINT `sensor_data_device_id_foreign`
    FOREIGN KEY (`device_id`)
    REFERENCES `devices` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🔍 QUERY CONTOH

### Lihat Data Terakhir

```sql
SELECT * FROM sensor_data
ORDER BY recorded_at DESC
LIMIT 10;
```

### Data Device 1 Hari Ini

```sql
SELECT * FROM sensor_data
WHERE device_id = 1
  AND DATE(recorded_at) = CURDATE()
ORDER BY recorded_at ASC;
```

### Rata-rata pH per Jam

```sql
SELECT
  HOUR(recorded_at) as jam,
  AVG(ph) as avg_ph,
  COUNT(*) as jumlah_data
FROM sensor_data
WHERE device_id = 1
  AND DATE(recorded_at) = CURDATE()
GROUP BY HOUR(recorded_at)
ORDER BY jam ASC;
```

---

## ✅ CHECKLIST DATABASE

```
[✅] Database monitoringikan EXISTS
[✅] Tabel sensor_data EXISTS
[✅] Kolom ph (decimal 4,2) EXISTS
[✅] Kolom temperature EXISTS
[✅] Kolom oxygen EXISTS
[✅] Kolom recorded_at dengan INDEX EXISTS
[✅] Foreign key ke devices EXISTS
[✅] API endpoint tested (HTTP 201)
[✅] Test data berhasil insert (ID: 51)
```

---

## 🎯 KESIMPULAN

**DATABASE SUDAH 100% READY!**

Tidak perlu membuat database baru. Setelah ESP32 diupload:

1. ESP32 baca sensor → pH = 7.23
2. ESP32 kirim via WiFi → POST /api/sensor-data/store
3. Laravel simpan ke database → INSERT sensor_data
4. Dashboard tampilkan → Grafik update otomatis

**Tinggal:** Upload code ESP32 → Data otomatis masuk database ini! 🚀

---

**Status**: ✅ READY FOR IoT DATA!  
**Last Update**: 15 Oktober 2025
