# Testing Dashboard dengan Sample Data

## 🎯 Langkah-Langkah Testing

### 1. Generate Sample Data

Sebelum testing dashboard, kita perlu generate data sensor sample:

```powershell
php artisan db:seed --class=SensorDataSeeder
```

**Output yang diharapkan**:

```
Sensor data seeded successfully! Total: XXX records
```

Seeder akan generate:

-   ✅ Data untuk 7 hari terakhir
-   ✅ Data per jam (168 data points)
-   ✅ Nilai realistic untuk:
    -   Suhu: 24-30°C
    -   pH: 6.5-8.5
    -   Oksigen: 5-8 mg/L
    -   Turbidity: 0-10 NTU

---

### 2. Jalankan Development Server

```powershell
php artisan serve
```

Buka browser: `http://127.0.0.1:8000`

---

### 3. Login ke System

**Credentials**:

-   Email: (gunakan email yang sudah terdaftar)
-   Password: (password anda)

Atau register user baru di: `http://127.0.0.1:8000/register`

---

### 4. Test Dashboard Features

#### A. Initial Load

1. Setelah login, anda akan diarahkan ke dashboard
2. **Verify**:
    - ✅ Cards menampilkan nilai sensor (bukan 0)
    - ✅ Chart terisi dengan garis data
    - ✅ Filter "24 Jam" aktif (biru)
    - ✅ Info menampilkan jumlah data

**Expected View**:

```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│ Suhu Air        │  │ pH Air          │  │ Oksigen         │
│ 27.5°C          │  │ 7.2             │  │ 6.8 mg/L        │
└─────────────────┘  └─────────────────┘  └─────────────────┘

Chart dengan 3 garis (orange, teal, green)
```

---

#### B. Test Filter "8 Jam"

1. Click button "8 Jam"
2. **Expected**:
    - ✅ Button "8 Jam" jadi biru
    - ✅ Chart update dengan 8 data points
    - ✅ Info text: "8 jam terakhir"
    - ✅ Data count: "8 titik data"

---

#### C. Test Filter "24 Jam"

1. Click button "24 Jam"
2. **Expected**:
    - ✅ Button "24 Jam" jadi biru
    - ✅ Chart update dengan 24 data points
    - ✅ Info text: "24 jam terakhir"
    - ✅ Data count: "24 titik data"

---

#### D. Test Filter "3 Hari"

1. Click button "3 Hari"
2. **Expected**:
    - ✅ Button "3 Hari" jadi biru
    - ✅ Chart update dengan ~72 data points
    - ✅ Info text: "3 hari terakhir"
    - ✅ Data count: "72 titik data"

---

#### E. Test Filter "7 Hari"

1. Click button "7 Hari"
2. **Expected**:
    - ✅ Button "7 Hari" jadi biru
    - ✅ Chart update dengan ~168 data points
    - ✅ Info text: "7 hari terakhir"
    - ✅ Data count: "168 titik data"

---

#### F. Test Refresh Button

1. Click button "Refresh" (icon sync)
2. **Expected**:
    - ✅ Icon berputar (loading animation)
    - ✅ Data diperbarui
    - ✅ Timestamp "Update terakhir" berubah
    - ✅ Filter yang aktif tetap sama

---

#### G. Test Auto Refresh

1. Biarkan dashboard terbuka
2. Tunggu 30 detik
3. **Expected**:
    - ✅ Data refresh otomatis
    - ✅ Timestamp update
    - ✅ No page reload
    - ✅ Chart smooth update

---

#### H. Test Chart Interaction

1. **Hover pada chart**

    - ✅ Tooltip muncul dengan nilai detail
    - ✅ Menampilkan waktu dan nilai semua sensor

2. **Click pada legend**
    - ✅ Line bisa di-toggle on/off
    - ✅ Suhu (orange), pH (teal), Oksigen (green)

---

### 5. Test API Endpoint (Optional)

Untuk developer, test API secara langsung:

```powershell
# Test 8 jam
curl http://127.0.0.1:8000/api/sensor-data?hours=8

# Test 24 jam
curl http://127.0.0.1:8000/api/sensor-data?hours=24

# Test 3 hari (72 jam)
curl http://127.0.0.1:8000/api/sensor-data?hours=72

# Test 7 hari (168 jam)
curl http://127.0.0.1:8000/api/sensor-data?hours=168
```

**Expected Response**:

```json
{
    "success": true,
    "data": [
        {
            "temperature": 27.5,
            "ph": 7.2,
            "oxygen": 6.8,
            "time": "14:00"
        }
    ],
    "latest": {
        "temperature": 27.5,
        "ph": 7.2,
        "oxygen": 6.8
    },
    "count": 8,
    "hours": 8
}
```

---

## 🐛 Troubleshooting

### Problem 1: Cards menampilkan "0"

**Cause**: Tidak ada data di database

**Solution**:

```powershell
# Run seeder
php artisan db:seed --class=SensorDataSeeder

# Verify data
php artisan tinker
>>> \App\Models\SensorData::count()
# Should return > 0
```

---

### Problem 2: Chart kosong

**Cause**: JavaScript error atau API tidak response

**Solution**:

1. Open browser console (F12)
2. Check for errors
3. Verify API endpoint:
    ```
    Network tab -> Check /api/sensor-data request
    ```

---

### Problem 3: "Device not found" error

**Cause**: Device ID 1 tidak ada di database

**Solution**:

```powershell
php artisan tinker
>>> \App\Models\Device::create([
...     'name' => 'Sensor Kolam 1',
...     'location' => 'Kolam Utama',
...     'status' => 'active'
... ]);
```

Atau edit seeder untuk gunakan device yang ada.

---

### Problem 4: Filter button tidak bekerja

**Cause**: JavaScript error

**Solution**:

1. Check browser console
2. Verify `loadSensorData()` function exists
3. Clear browser cache: `Ctrl + Shift + R`

---

### Problem 5: 403 Forbidden di API

**Cause**: User tidak authenticated

**Solution**:

1. Pastikan sudah login
2. Check session: `php artisan session:table`
3. Clear cache: `php artisan cache:clear`

---

## 📊 Sample Data Overview

Setelah run seeder, database akan berisi:

| Time Range    | Data Points | Description       |
| ------------- | ----------- | ----------------- |
| Last 8 hours  | 8           | Recent monitoring |
| Last 24 hours | 24          | Daily view        |
| Last 3 days   | 72          | Weekly trend      |
| Last 7 days   | 168         | Full dataset      |

**Sensor Ranges**:

-   **Suhu**: 24-30°C (Normal range untuk ikan tropis)
-   **pH**: 6.5-8.5 (Optimal water quality)
-   **Oksigen**: 5-8 mg/L (Adequate oxygen level)
-   **Turbidity**: 0-10 NTU (Water clarity)

---

## ✅ Testing Checklist

### Initial Setup

-   [ ] Run seeder successfully
-   [ ] Verify data in database (>0 records)
-   [ ] Start development server
-   [ ] Login to system

### Dashboard Features

-   [ ] Cards show real values (not 0)
-   [ ] Chart displays data lines
-   [ ] Default filter is "24 Jam" (blue)
-   [ ] Info shows correct data count

### Filter Buttons

-   [ ] 8 Jam filter works
-   [ ] 24 Jam filter works
-   [ ] 3 Hari filter works
-   [ ] 7 Hari filter works
-   [ ] Active button highlighted

### Interactive Features

-   [ ] Refresh button works
-   [ ] Loading animation shows
-   [ ] Auto refresh (30s)
-   [ ] Timestamp updates
-   [ ] Chart hover tooltip
-   [ ] Legend toggle

### API Endpoint

-   [ ] /api/sensor-data responds
-   [ ] Returns correct JSON
-   [ ] Filters work (hours param)
-   [ ] Latest values correct

---

## 🎉 Success Criteria

Dashboard testing berhasil jika:

1. ✅ **Visual**: All cards show numeric values
2. ✅ **Chart**: Lines visible with data points
3. ✅ **Filters**: All 4 buttons functional
4. ✅ **Refresh**: Manual and auto refresh works
5. ✅ **Performance**: Smooth transitions, no lag
6. ✅ **Data**: Values realistic and varied
7. ✅ **UI/UX**: Professional appearance, no errors

---

## 📝 Notes

-   Seeder generate data untuk device_id = 1
-   Jika device tidak ada, create dulu sebelum run seeder
-   Data generated secara random dalam range realistic
-   Untuk production, replace dengan real sensor data
-   Auto refresh dapat di-adjust (default 30 detik)

---

**Ready to Test**: ✅

Jalankan seeder dan mulai testing dashboard!

**Created**: October 12, 2025, 21:05
