## 📊 PHPMYADMIN DATABASE GUIDE

### Langkah melihat data ESP32 di phpMyAdmin:

#### 1. Buka phpMyAdmin

```
http://localhost/phpmyadmin
```

#### 2. Pilih Database

-   Di sidebar kiri, klik: `monitoringikan`
-   Database harus tampil dengan warna highlight

#### 3. Pilih Tabel

-   Klik tabel: `sensor_data`
-   Akan muncul struktur tabel

#### 4. Lihat Data

-   Klik tab: `Browse` atau `Jelajah`
-   Data ESP32 terbaru akan tampil

### 🔍 Data yang harus terlihat:

```
ID: 63
device_id: 1
ph: 4.00
temperature: 26.50
oxygen: 6.80
voltage: 3.30 (jika kolom ada)
recorded_at: 2025-10-23 16:12:47
created_at: 2025-10-23 16:12:47
```

### 🚨 Troubleshooting phpMyAdmin:

#### Jika database `monitoringikan` tidak muncul:

1. Refresh halaman phpMyAdmin (F5)
2. Check XAMPP MySQL service running
3. Restart Apache & MySQL di XAMPP

#### Jika database kosong:

1. Pastikan di sidebar kiri terpilih `monitoringikan`
2. Bukan `information_schema` atau database lain
3. Klik `Browse` pada tabel `sensor_data`

#### Jika tabel tidak ada:

```bash
php artisan migrate
```

### ✅ Konfirmasi Data Tersedia:

-   **Total records: 10**
-   **Latest ID: 63**
-   **ESP32 pH: 4.00** ✅
-   **Status: REAL-TIME** ✅

Data ESP32 sudah berhasil masuk database! 🎉
Tinggal navigate di phpMyAdmin untuk melihatnya.
