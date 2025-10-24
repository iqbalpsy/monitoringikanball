# Laporan & Analitik - Konfigurasi 1 Kolam

## Ringkasan

Halaman Laporan & Analitik telah dimodifikasi untuk sistem monitoring 1 kolam saja (Kolam 1), dengan menghapus fitur pemilihan multiple device dan menggunakan data asli dari database.

## Perubahan yang Dilakukan

### 1. Controller - `app/Http/Controllers/DashboardController.php`

#### Method: `reports(Request $request)`

**Fitur:**

-   ✅ Filter berdasarkan rentang waktu (7 hari, 30 hari, 3 bulan)
-   ✅ Filter berdasarkan parameter (semua, suhu, pH, oksigen)
-   ✅ Data asli dari database `sensor_data`
-   ✅ Perhitungan statistik otomatis
-   ✅ Perbandingan dengan periode sebelumnya
-   ✅ Distribusi alert per parameter
-   ✅ Nilai rata-rata untuk setiap parameter

**Range Normal:**

-   Suhu: 24°C - 30°C
-   pH: 6.5 - 8.5
-   Oksigen: 5.0 - 8.0 mg/L

### 2. Tampilan - `resources/views/admin/reports.blade.php`

#### A. Filter Laporan

**Perubahan:**

-   ❌ **DIHAPUS:** Dropdown pemilihan perangkat
-   ✅ **DITAMBAH:** Label "Kolam 1"
-   Filter: Rentang Waktu, Parameter, Tombol Generate

#### B. Kartu Analitik (4 kartu)

1. **Total Pembacaan**

    - Jumlah pembacaan sensor
    - Indikator perubahan (%)

2. **Uptime Sistem**

    - Persentase uptime
    - Dihitung dari pembacaan aktual vs ekspektasi

3. **Total Alert**

    - Jumlah alert yang terdeteksi
    - Perubahan dibanding periode sebelumnya

4. **Performa Rata-rata**
    - Persentase pembacaan normal
    - Rating (Excellent, Good, etc)

#### C. Grafik Tren Parameter

-   Data 30 hari terakhir
-   3 line chart: pH, Suhu, Oksigen
-   Label tanggal format Indonesia
-   Tooltip interaktif

#### D. Grafik Distribusi Alert

-   3 kategori: Suhu, pH, Oksigen
-   Data asli dari perhitungan
-   Tooltip menampilkan jumlah dan persentase

#### E. Analisis Performa Kolam

3 kartu statistik untuk Kolam 1:

1. **Kartu Suhu** (Orange)

    - Rata-rata suhu
    - Jumlah alert
    - Range normal: 24°C - 30°C

2. **Kartu pH** (Blue)

    - Rata-rata pH
    - Jumlah alert
    - Range normal: 6.5 - 8.5

3. **Kartu Oksigen** (Green)
    - Rata-rata oksigen
    - Jumlah alert
    - Range normal: 5.0 - 8.0 mg/L

## Cara Menggunakan

### 1. Akses Halaman Laporan

```
http://127.0.0.1:8000/admin/reports
```

### 2. Filter Laporan

**Pilihan Rentang Waktu:**

-   7 Hari Terakhir (default)
-   30 Hari Terakhir
-   3 Bulan Terakhir

**Pilihan Parameter:**

-   Semua Parameter (default)
-   Suhu
-   pH
-   Oksigen

### 3. Generate Laporan

Klik tombol **"Generate Report"** untuk melihat data dengan filter yang dipilih.

### 4. Export Laporan

Klik tombol **"Export All"** untuk export data (fitur untuk implementasi export).

## Sistem Deteksi Alert

Sistem otomatis mendeteksi nilai abnormal:

### Alert Suhu

-   Alert jika suhu < 24°C atau > 30°C

### Alert pH

-   Alert jika pH < 6.5 atau > 8.5

### Alert Oksigen

-   Alert jika oksigen < 5 mg/L atau > 8 mg/L

## Perhitungan Performa

### Uptime Sistem

```
Expected Readings = Jumlah jam dalam periode (1 pembacaan per jam)
System Uptime = (Total Readings / Expected Readings) × 100%
Maximum = 100%
```

### Performa Rata-rata

```
Normal Readings = Total Readings - (Alert Count / 3)
Average Performance = (Normal Readings / Total Readings) × 100%
```

### Persentase Perubahan

Membandingkan dengan periode sebelumnya yang sama panjangnya.

## Struktur Data

### Tabel sensor_data

-   `id` - ID unik
-   `temperature` - Suhu air (°C)
-   `ph` - Tingkat pH
-   `oxygen` - Kadar oksigen (mg/L)
-   `recorded_at` - Waktu pencatatan
-   `created_at` - Waktu dibuat
-   `updated_at` - Waktu diupdate

## Skema Warna

-   **Biru (#3B82F6):** pH Level
-   **Merah (#EF4444):** Suhu & Alert
-   **Hijau (#22C55B):** Oksigen & Sukses
-   **Ungu (#A855F7):** Performa
-   **Oranye (#F97316):** Alert suhu

## Icon (Font Awesome)

-   📊 Database: `fa-database`
-   ✅ Check: `fa-check-circle`
-   ⚠️ Alert: `fa-exclamation-triangle`
-   📈 Chart: `fa-chart-line`
-   🌡️ Suhu: `fa-thermometer-half`
-   🧪 pH: `fa-flask`
-   💨 Oksigen: `fa-wind`

## Peningkatan dari Versi Sebelumnya

### ✅ Fokus 1 Kolam

-   Tidak ada kompleksitas pemilihan perangkat
-   UI lebih sederhana
-   Loading data lebih cepat

### ✅ Integrasi Data Asli

-   Tidak ada data dummy
-   Statistik akurat
-   Deteksi alert real-time

### ✅ Metrik Performa Lebih Baik

-   Perhitungan uptime otomatis
-   Penghitungan alert yang smart
-   Perbandingan periode

### ✅ Visualisasi Ditingkatkan

-   Chart interaktif dengan data asli
-   Informasi tooltip lebih baik
-   Desain responsif

### ✅ Filter User-Friendly

-   Pemilihan rentang tanggal sederhana
-   Laporan per parameter
-   Layout 3 kolom yang bersih

## Checklist Testing

-   [ ] Filter rentang waktu 7 hari
-   [ ] Filter rentang waktu 30 hari
-   [ ] Filter rentang waktu 3 bulan
-   [ ] Filter parameter: semua
-   [ ] Filter parameter: suhu
-   [ ] Filter parameter: pH
-   [ ] Filter parameter: oksigen
-   [ ] Kartu analitik menampilkan data benar
-   [ ] Grafik tren menampilkan data asli
-   [ ] Grafik distribusi alert akurat
-   [ ] Kartu performa kolam menampilkan rata-rata
-   [ ] Jumlah alert akurat
-   [ ] Perhitungan uptime sistem benar
-   [ ] Persentase performa akurat
-   [ ] Halaman load tanpa error
-   [ ] Chart render dengan baik
-   [ ] Responsif di perangkat mobile

## Route Information

```php
Route: GET /admin/reports
Controller: DashboardController@reports
Middleware: auth, admin
Name: admin.reports
```

## Troubleshooting

### Masalah: Chart tidak muncul

**Solusi:**

-   Cek browser console untuk error
-   Pastikan Chart.js CDN loaded
-   Verifikasi data dari controller tidak kosong

### Masalah: Data tidak update setelah filter

**Solusi:**

-   Pastikan form method adalah GET
-   Verifikasi route menerima Request parameters
-   Clear browser cache

### Masalah: Jumlah alert tidak akurat

**Solusi:**

-   Verifikasi nilai normal range di controller
-   Cek nilai database untuk outliers
-   Review logika deteksi alert

### Masalah: Uptime sistem > 100%

**Solusi:**

-   Sudah dibatasi 100% dengan `min($systemUptime, 100)`
-   Jika masih > 100%, cek perhitungan expectedReadings

## Fitur yang Bisa Ditambahkan (Opsional)

1. **Fungsi Export**

    - Export PDF dengan charts
    - Export CSV untuk raw data
    - Export Excel dengan formatting

2. **Filter Lanjutan**

    - Custom date range picker
    - Filter berdasarkan jam
    - Perbandingan multiple parameter

3. **Manajemen Alert**

    - Tabel riwayat alert
    - Notifikasi alert
    - Custom alert threshold

4. **Analisis Data**
    - Prediksi tren
    - Deteksi anomali
    - Analisis korelasi

## Kesimpulan

Halaman Laporan & Analitik sekarang sudah dioptimasi penuh untuk sistem monitoring 1 kolam dengan data asli dari database, deteksi alert otomatis, dan visualisasi yang lebih baik.

---

## Cara Test

1. Buka browser dan akses: `http://127.0.0.1:8000/admin/reports`
2. Login sebagai admin
3. Pilih filter rentang waktu
4. Pilih parameter yang ingin dilihat
5. Klik "Generate Report"
6. Lihat hasil:
    - 4 kartu analitik di atas
    - 2 chart (Tren Parameter & Distribusi Alert)
    - 3 kartu performa kolam

## Screenshot Reference

### Filter Section

```
┌─────────────────────────────────────────────────┐
│  Report Filters - Kolam 1                       │
├─────────────────────────────────────────────────┤
│  [Date Range ▼] [Parameter ▼] [Generate Report]│
└─────────────────────────────────────────────────┘
```

### Analytics Cards

```
┌────────────┬────────────┬────────────┬────────────┐
│ 50 Readings│ 98.5% Up   │ 15 Alerts  │ 95.2% Perf │
│ +12.5%     │ Excellent  │ -15.8%     │ Excellent  │
└────────────┴────────────┴────────────┴────────────┘
```

### Charts

```
┌────────────────────────┬────────────────────────┐
│ Parameter Trends       │ Alert Distribution     │
│ (Line Chart)           │ (Doughnut Chart)       │
└────────────────────────┴────────────────────────┘
```

### Performance Cards

```
┌────────────┬────────────┬────────────┐
│ 🌡️ 26.5°C │ 🧪 pH 7.2  │ 💨 6.8 mg/L│
│ 3 Alerts   │ 5 Alerts   │ 7 Alerts   │
│ 24-30°C    │ 6.5-8.5    │ 5.0-8.0    │
└────────────┴────────────┴────────────┘
```
