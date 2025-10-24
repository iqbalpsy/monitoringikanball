# Reports & Analytics - Single Pond Configuration

## Overview

Halaman Reports & Analytics telah dimodifikasi untuk sistem monitoring 1 kolam saja (Kolam 1), dengan menghapus fitur multiple device selection dan menggunakan data real dari database.

## Perubahan yang Dilakukan

### 1. Controller Changes - `app/Http/Controllers/DashboardController.php`

#### Method: `reports(Request $request)`

**Fitur Baru:**

-   ✅ Filter berdasarkan date range (7 hari, 30 hari, 3 bulan)
-   ✅ Filter berdasarkan parameter (all, temperature, ph, oxygen)
-   ✅ Data real dari database `sensor_data`
-   ✅ Perhitungan statistik otomatis:
    -   Total Readings
    -   System Uptime (berdasarkan expected readings vs actual)
    -   Alert Count (deteksi otomatis nilai abnormal)
    -   Average Performance
-   ✅ Comparison dengan periode sebelumnya (% change)
-   ✅ Alert distribution per parameter
-   ✅ Average values untuk setiap parameter

**Parameter Normal Range:**

-   Temperature: 24°C - 30°C
-   pH Level: 6.5 - 8.5
-   Oxygen: 5.0 - 8.0 mg/L

**Data yang Dikirim ke View:**

```php
compact(
    'totalReadings',      // Total pembacaan sensor
    'systemUptime',       // Uptime sistem (%)
    'alertsCount',        // Jumlah total alerts
    'avgPerformance',     // Performa rata-rata (%)
    'readingsChange',     // Perubahan readings vs periode sebelumnya (%)
    'alertsChange',       // Perubahan alerts vs periode sebelumnya (%)
    'trendData',          // Data 30 hari terakhir untuk chart
    'tempAlerts',         // Jumlah alert temperature
    'phAlerts',           // Jumlah alert pH
    'oxygenAlerts',       // Jumlah alert oxygen
    'avgTemp',            // Rata-rata temperature
    'avgPh',              // Rata-rata pH
    'avgOxygen',          // Rata-rata oxygen
    'dateRange',          // Filter date range yang dipilih
    'parameter',          // Filter parameter yang dipilih
    'totalSensorData'     // Total data sensor di database
);
```

### 2. View Changes - `resources/views/admin/reports.blade.php`

#### A. Report Filters Section

**Sebelum:**

-   4 kolom filter: Date Range, Device, Parameter, Generate Button

**Sesudah:**

-   3 kolom filter: Date Range, Parameter, Generate Button
-   ❌ **DIHAPUS:** Device selector dropdown
-   ✅ **DITAMBAH:** Label "Kolam 1" di header

#### B. Analytics Overview Cards

**Sebelum:**

-   Menggunakan data static/random

**Sesudah:**

-   Total Readings: Data real dengan % change indicator
-   System Uptime: Calculated dari ratio actual vs expected readings
-   Total Alerts: Calculated dengan % change vs previous period
-   Avg Performance: Calculated dari normal readings ratio

#### C. Parameter Trends Chart

**Sebelum:**

-   Menggunakan dummy data dengan function `generateTrendData()`

**Sesudah:**

-   ✅ Data real dari database (30 hari terakhir)
-   ✅ 3 line charts: pH, Temperature, Oxygen
-   ✅ Labels menggunakan format tanggal Indonesia
-   ✅ Interactive tooltip dengan mode 'index'

#### D. Alert Distribution Chart

**Sebelum:**

-   4 kategori dengan persentase static
-   Termasuk "System Alerts"

**Sesudah:**

-   ✅ 3 kategori saja: Temperature, pH, Oxygen
-   ✅ Data real dari perhitungan alerts
-   ✅ Tooltip menampilkan count dan percentage
-   ❌ **DIHAPUS:** System Alerts category

#### E. Pond Performance Analysis

**Sebelum:**

-   Device Performance table dengan multiple devices
-   8 kolom data per device

**Sesudah:**

-   ✅ 3 kartu statistik untuk Kolam 1:

    1. **Temperature Card**

        - Average temperature
        - Alert count
        - Normal range indicator
        - Orange gradient design

    2. **pH Level Card**

        - Average pH
        - Alert count
        - Normal range indicator
        - Blue gradient design

    3. **Oxygen Level Card**
        - Average oxygen
        - Alert count
        - Normal range indicator
        - Green gradient design

### 3. JavaScript Updates

**Fungsi yang Dihapus:**

-   `generateDayLabels()` - Tidak diperlukan, data dari DB
-   `generateTrendData()` - Tidak diperlukan, data dari DB

**Fungsi yang Diupdate:**

-   `initializeTrendsChart()` - Menggunakan data dari PHP variable `trendData`
-   `initializeAlertsChart()` - Menggunakan actual alert counts

**PHP to JavaScript Data Transfer:**

```javascript
const trendData = @json($trendData);
const tempAlerts = {{ $tempAlerts }};
const phAlerts = {{ $phAlerts }};
const oxygenAlerts = {{ $oxygenAlerts }};
```

## Cara Penggunaan

### 1. Akses Reports Page

```
http://127.0.0.1:8000/admin/reports
```

### 2. Filter Report

**Date Range Options:**

-   Last 7 Days (default)
-   Last 30 Days
-   Last 3 Months

**Parameter Options:**

-   All Parameters (default)
-   Temperature
-   pH Level
-   Oxygen

### 3. Generate Report

Klik tombol "Generate Report" untuk melihat data dengan filter yang dipilih.

### 4. Export Report

Klik tombol "Export All" (fitur untuk export data)

## Alert Detection System

Sistem secara otomatis mendeteksi nilai abnormal berdasarkan range normal:

### Temperature Alerts

```php
if ($data->temperature < 24 || $data->temperature > 30) {
    // Temperature alert triggered
}
```

### pH Alerts

```php
if ($data->ph < 6.5 || $data->ph > 8.5) {
    // pH alert triggered
}
```

### Oxygen Alerts

```php
if ($data->oxygen < 5 || $data->oxygen > 8) {
    // Oxygen alert triggered
}
```

## Performance Calculation

### System Uptime

```php
$expectedReadings = $endDate->diffInHours($startDate); // Expected 1 per hour
$systemUptime = ($totalReadings / $expectedReadings) * 100;
$systemUptime = min($systemUptime, 100); // Cap at 100%
```

### Average Performance

```php
$normalReadings = $totalReadings - ($alertsCount / 3); // Divide by 3 (3 parameters)
$avgPerformance = ($normalReadings / $totalReadings) * 100;
```

### Change Percentage

```php
// Compare with previous period
$prevStartDate = $startDate->copy()->sub($endDate->diff($startDate));
$prevEndDate = $startDate->copy();

$readingsChange = (($totalReadings - $prevTotalReadings) / $prevTotalReadings) * 100;
$alertsChange = (($alertsCount - $prevAlerts) / $prevAlerts) * 100;
```

## Data Structure

### Sensor Data Model

```php
Schema: sensor_data
- id (bigint)
- temperature (decimal)
- ph (decimal)
- oxygen (decimal)
- recorded_at (timestamp)
- created_at (timestamp)
- updated_at (timestamp)
```

## Design Elements

### Color Scheme

-   **Blue (#3B82F6):** pH Level
-   **Red (#EF4444):** Temperature & Alerts
-   **Green (#22C55B):** Oxygen & Success
-   **Purple (#A855F7):** Performance
-   **Orange (#F97316):** Temperature alerts

### Icons (Font Awesome)

-   📊 Database: `fa-database`
-   ✅ Check: `fa-check-circle`
-   ⚠️ Alert: `fa-exclamation-triangle`
-   📈 Chart: `fa-chart-line`
-   🌡️ Temperature: `fa-thermometer-half`
-   🧪 pH: `fa-flask`
-   💨 Oxygen: `fa-wind`

## Improvements dari Versi Sebelumnya

### ✅ Single Pond Focus

-   Removed device selection complexity
-   Simplified UI untuk 1 kolam monitoring
-   Faster data loading

### ✅ Real Data Integration

-   No more dummy/random data
-   Accurate statistics and trends
-   Real-time alert detection

### ✅ Better Performance Metrics

-   Automatic uptime calculation
-   Smart alert counting
-   Period comparison

### ✅ Enhanced Visualizations

-   Interactive charts with real data
-   Better tooltip information
-   Responsive design

### ✅ User-Friendly Filters

-   Simple date range selection
-   Parameter-specific reports
-   Clean 3-column layout

## Testing Checklist

-   [ ] Filter by date range (7 days)
-   [ ] Filter by date range (30 days)
-   [ ] Filter by date range (3 months)
-   [ ] Filter by parameter (all)
-   [ ] Filter by parameter (temperature)
-   [ ] Filter by parameter (pH)
-   [ ] Filter by parameter (oxygen)
-   [ ] Analytics cards show correct data
-   [ ] Trends chart displays real data
-   [ ] Alert distribution chart shows correct percentages
-   [ ] Pond performance cards show averages
-   [ ] Alert counts are accurate
-   [ ] System uptime calculation is correct
-   [ ] Performance percentage is accurate
-   [ ] Page loads without errors
-   [ ] Charts render properly
-   [ ] Responsive on mobile devices

## Route Information

```php
Route: GET /admin/reports
Controller: DashboardController@reports
Middleware: auth, admin
Name: admin.reports
```

## Troubleshooting

### Issue: Chart tidak muncul

**Solution:**

-   Check browser console untuk error
-   Pastikan Chart.js CDN loaded
-   Verify data dari controller tidak kosong

### Issue: Data tidak update setelah filter

**Solution:**

-   Check form method adalah GET
-   Verify route menerima Request parameters
-   Clear browser cache

### Issue: Alert count tidak akurat

**Solution:**

-   Verify normal range values di controller
-   Check database values untuk outliers
-   Review alert detection logic

### Issue: System uptime > 100%

**Solution:**

-   Already capped at 100% with `min($systemUptime, 100)`
-   If still showing > 100%, check expectedReadings calculation

## Future Enhancements (Optional)

1. **Export Functionality**

    - PDF export dengan charts
    - CSV export untuk raw data
    - Excel export dengan formatting

2. **Advanced Filters**

    - Custom date range picker
    - Time of day filter
    - Multiple parameter comparison

3. **Alert Management**

    - Alert history table
    - Alert notifications
    - Custom alert thresholds

4. **Data Analysis**
    - Trend predictions
    - Anomaly detection
    - Correlation analysis

## Summary

Reports & Analytics page sekarang fully optimized untuk single pond monitoring system dengan data real dari database, alert detection otomatis, dan visualisasi yang lebih baik.
