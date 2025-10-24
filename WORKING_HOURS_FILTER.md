# Update Filter Jam Kerja (Working Hours)

## ✅ Perubahan yang Dilakukan

### 🎯 Objective

Mengubah filter "8 Jam" menjadi "Jam Kerja" yang menampilkan data dari **jam 08:00 pagi hingga 16:00 sore** (8 jam kerja).

---

## 📊 Detail Perubahan

### 1. **Backend - DashboardController.php**

#### Fungsi `getSensorData()` Updated

**Fitur Baru**:

-   ✅ Parameter `type` ditambahkan (`hours` atau `working_hours`)
-   ✅ Filter khusus untuk jam kerja (08:00 - 16:00)
-   ✅ Query data hanya untuk hari ini dalam range jam kerja

**Logic**:

```php
if ($filterType === 'working_hours') {
    // Get today's data between 08:00 and 16:00
    $startTime = Carbon::today()->setHour(8)->setMinute(0);
    $endTime = Carbon::today()->setHour(16)->setMinute(0);

    $sensorData = SensorData::whereBetween('recorded_at', [$startTime, $endTime])
        ->orderBy('recorded_at', 'asc')
        ->get()
        // ... group by hour
}
```

**API Response**:

```json
{
    "success": true,
    "data": [
        {"temperature": 27.5, "ph": 7.2, "oxygen": 6.8, "time": "08:00"},
        {"temperature": 27.8, "ph": 7.3, "oxygen": 6.9, "time": "09:00"},
        {"temperature": 28.1, "ph": 7.1, "oxygen": 6.7, "time": "10:00"},
        // ... hingga 16:00
    ],
    "latest": {...},
    "count": 9,
    "type": "working_hours"
}
```

---

### 2. **Frontend - user.blade.php**

#### Button Filter Updated

**Before**:

```html
<button onclick="loadSensorData(8)">8 Jam</button>
```

**After**:

```html
<button onclick="loadWorkingHours()" title="Jam 08:00 - 16:00">
    Jam Kerja
</button>
```

**Tooltip**: Hover pada button menampilkan "Jam 08:00 - 16:00"

---

#### JavaScript Functions Added

##### `loadWorkingHours()`

**Purpose**: Load data khusus jam kerja (08:00 - 16:00)

**Features**:

-   ✅ Set `currentFilterType = 'working_hours'`
-   ✅ Update active button styling
-   ✅ Fetch API dengan parameter `?type=working_hours`
-   ✅ Update chart dengan 9 data points (08:00 - 16:00)
-   ✅ Update info text: "Jam Kerja (08:00 - 16:00)"

**Code**:

```javascript
function loadWorkingHours() {
    currentFilterType = "working_hours";

    // Update active button
    // ... styling code

    fetch(`/api/sensor-data?type=working_hours`)
        .then((response) => response.json())
        .then((result) => {
            // Update chart
            // Update cards
            // Update info: "Jam Kerja (08:00 - 16:00)"
        });
}
```

##### `loadSensorData()` Updated

**Changes**:

-   ✅ Support dual mode: `hours` atau `working_hours`
-   ✅ Dynamic URL based on `currentFilterType`
-   ✅ Maintain current filter on refresh

**Code**:

```javascript
const url =
    currentFilterType === "working_hours"
        ? "/api/sensor-data?type=working_hours"
        : "/api/sensor-data?hours=" + currentHours;
```

---

## 🕐 Working Hours Specification

### Time Range

-   **Start**: 08:00 (8 AM)
-   **End**: 16:00 (4 PM)
-   **Duration**: 8 hours
-   **Data Points**: 9 points (08:00, 09:00, 10:00, ..., 16:00)

### Chart Display

```
X-axis (Time):
08:00, 09:00, 10:00, 11:00, 12:00, 13:00, 14:00, 15:00, 16:00

Y-axis (Values):
- Suhu (°C): 24-30
- pH: 6.5-8.5
- Oksigen (mg/L): 5-8
```

### Visual Example

```
Chart: Jam Kerja (08:00 - 16:00)

 30°C ┤     ╭──╮
      ┤   ╭─╯  ╰─╮
 27°C ┤ ╭─╯      ╰──╮
      ┤─╯           ╰─
 24°C ┤
      └─────────────────────────
      08 09 10 11 12 13 14 15 16
```

---

## 🎯 Filter Options Summary

| Filter        | Time Range             | Data Points | Use Case             |
| ------------- | ---------------------- | ----------- | -------------------- |
| **Jam Kerja** | 08:00 - 16:00 hari ini | ~9          | Monitoring jam kerja |
| 24 Jam        | Last 24 hours          | ~24         | Daily overview       |
| 3 Hari        | Last 72 hours          | ~72         | Weekly trend         |
| 7 Hari        | Last 168 hours         | ~168        | Long-term analysis   |

---

## 🔄 Data Flow

```
User Click "Jam Kerja"
    ↓
loadWorkingHours() called
    ↓
Fetch: /api/sensor-data?type=working_hours
    ↓
Controller: getSensorData()
    ↓
Query: WHERE recorded_at BETWEEN '08:00' AND '16:00' TODAY
    ↓
Group by hour: 08:00, 09:00, ..., 16:00
    ↓
Calculate averages per hour
    ↓
JSON Response (9 data points)
    ↓
JavaScript: Update Chart
    ↓
Display: 08:00, 09:00, 10:00, 11:00, 12:00, 13:00, 14:00, 15:00, 16:00
```

---

## 📋 API Endpoint Update

### GET `/api/sensor-data`

**Query Parameters**:

| Parameter | Type    | Values                   | Default | Description                       |
| --------- | ------- | ------------------------ | ------- | --------------------------------- |
| type      | string  | `hours`, `working_hours` | `hours` | Filter type                       |
| hours     | integer | 24, 72, 168              | 24      | Number of hours (jika type=hours) |

**Example Requests**:

```bash
# Jam Kerja (08:00 - 16:00)
GET /api/sensor-data?type=working_hours

# Last 24 hours
GET /api/sensor-data?hours=24

# Last 3 days
GET /api/sensor-data?hours=72

# Last 7 days
GET /api/sensor-data?hours=168
```

**Response for Working Hours**:

```json
{
    "success": true,
    "data": [
        { "temperature": 27.5, "ph": 7.2, "oxygen": 6.8, "time": "08:00" },
        { "temperature": 27.8, "ph": 7.3, "oxygen": 6.9, "time": "09:00" },
        { "temperature": 28.1, "ph": 7.1, "oxygen": 6.7, "time": "10:00" },
        { "temperature": 28.3, "ph": 7.2, "oxygen": 6.8, "time": "11:00" },
        { "temperature": 28.5, "ph": 7.0, "oxygen": 6.6, "time": "12:00" },
        { "temperature": 28.4, "ph": 7.1, "oxygen": 6.7, "time": "13:00" },
        { "temperature": 28.2, "ph": 7.2, "oxygen": 6.9, "time": "14:00" },
        { "temperature": 28.0, "ph": 7.3, "oxygen": 7.0, "time": "15:00" },
        { "temperature": 27.7, "ph": 7.2, "oxygen": 6.8, "time": "16:00" }
    ],
    "latest": {
        "temperature": 27.7,
        "ph": 7.2,
        "oxygen": 6.8
    },
    "count": 9,
    "hours": 0,
    "type": "working_hours"
}
```

---

## 🧪 Testing Instructions

### 1. Test Filter "Jam Kerja"

**Steps**:

1. Login ke dashboard
2. Click button "Jam Kerja"

**Expected Result**:

-   ✅ Button "Jam Kerja" berubah biru (active)
-   ✅ Chart update dengan 9 data points
-   ✅ X-axis labels: 08:00, 09:00, 10:00, ..., 16:00
-   ✅ Info text: "Jam Kerja (08:00 - 16:00)"
-   ✅ Data count: "9 titik data (9 pembacaan)"

---

### 2. Test Refresh dengan Filter Jam Kerja

**Steps**:

1. Set filter ke "Jam Kerja"
2. Click button "Refresh"

**Expected Result**:

-   ✅ Data diperbarui
-   ✅ Filter tetap "Jam Kerja" (tidak reset ke 24 jam)
-   ✅ Chart masih menampilkan 08:00 - 16:00
-   ✅ Timestamp "Update terakhir" berubah

---

### 3. Test Switch Between Filters

**Steps**:

1. Click "Jam Kerja" → See 9 points (08:00-16:00)
2. Click "24 Jam" → See 24 points (last 24 hours)
3. Click "Jam Kerja" → Back to 9 points (08:00-16:00)

**Expected Result**:

-   ✅ Each filter shows correct data
-   ✅ Active button changes color
-   ✅ Chart updates smoothly
-   ✅ Info text updates correctly

---

### 4. Test Auto Refresh

**Steps**:

1. Set filter ke "Jam Kerja"
2. Wait 30 seconds (auto refresh)

**Expected Result**:

-   ✅ Data refreshes automatically
-   ✅ Filter remains "Jam Kerja"
-   ✅ No page reload
-   ✅ Timestamp updates

---

### 5. Test Edge Cases

#### Case 1: Before 08:00 AM

**Time**: 07:30 AM
**Expected**: Empty data atau data dari hari sebelumnya

#### Case 2: During Working Hours

**Time**: 12:00 PM
**Expected**: Data dari 08:00 - 12:00 (5 points so far)

#### Case 3: After 16:00 PM

**Time**: 18:00 PM
**Expected**: Full data 08:00 - 16:00 (9 points)

---

## 🎨 UI/UX Improvements

### Before (8 Jam):

```
[  8 Jam  ] [ 24 Jam ] [ 3 Hari ] [ 7 Hari ]
```

-   Ambiguitas: "8 jam terakhir" atau "8 jam spesifik"?
-   Tidak jelas time range

### After (Jam Kerja):

```
[ Jam Kerja ] [ 24 Jam ] [ 3 Hari ] [ 7 Hari ]
     ↓
  (tooltip)
08:00 - 16:00
```

-   ✅ Clear purpose: "Jam Kerja"
-   ✅ Tooltip shows exact time range
-   ✅ Consistent with business hours
-   ✅ Better for work monitoring

---

## 📊 Use Cases

### 1. **Daily Work Monitoring**

```
Manager wants to see sensor readings during office hours
→ Click "Jam Kerja"
→ See 08:00 - 16:00 data
→ Check if all values normal during work time
```

### 2. **Shift Handover**

```
Morning shift (08:00) checks evening data (16:00)
→ Filter "Jam Kerja"
→ See full day trend
→ Document any anomalies
```

### 3. **Compliance Reporting**

```
Generate report for working hours only
→ "Jam Kerja" filter active
→ Export data 08:00 - 16:00
→ Submit to management
```

---

## 🔧 Technical Details

### Carbon Date Handling

```php
// Get today's date
$today = \Carbon\Carbon::today(); // 2025-10-12 00:00:00

// Set start time (08:00)
$startTime = $today->copy()->setHour(8)->setMinute(0)->setSecond(0);
// Result: 2025-10-12 08:00:00

// Set end time (16:00)
$endTime = $today->copy()->setHour(16)->setMinute(0)->setSecond(0);
// Result: 2025-10-12 16:00:00

// Query
whereBetween('recorded_at', [$startTime, $endTime])
```

### Data Grouping

```php
->groupBy(function($date) {
    return \Carbon\Carbon::parse($date->recorded_at)->format('Y-m-d H:00:00');
})
```

Result:

```
2025-10-12 08:00:00 → [data1, data2, data3]
2025-10-12 09:00:00 → [data4, data5]
2025-10-12 10:00:00 → [data6, data7, data8]
...
```

### Average Calculation

```php
->map(function($group) {
    return [
        'temperature' => round((float)$group->avg('temperature'), 2),
        'ph' => round((float)$group->avg('ph_level'), 2),
        'oxygen' => round((float)$group->avg('oxygen_level'), 2),
        'time' => Carbon::parse($group->first()->recorded_at)->format('H:i')
    ];
})
```

---

## 📁 Files Modified

1. ✅ `app/Http/Controllers/DashboardController.php`

    - Added `$filterType` parameter
    - Added working hours logic
    - Updated response format

2. ✅ `resources/views/dashboard/user.blade.php`

    - Changed button: "8 Jam" → "Jam Kerja"
    - Added tooltip
    - Added `loadWorkingHours()` function
    - Updated `loadSensorData()` for dual mode
    - Added `currentFilterType` variable

3. ✅ `WORKING_HOURS_FILTER.md` (This file)
    - Complete documentation

---

## ✅ Testing Checklist

### Backend:

-   [ ] API responds to `?type=working_hours`
-   [ ] Returns 9 data points (08:00 - 16:00)
-   [ ] Data grouped by hour
-   [ ] Averages calculated correctly
-   [ ] Empty gracefully if no data

### Frontend:

-   [ ] Button "Jam Kerja" visible
-   [ ] Tooltip shows "Jam 08:00 - 16:00"
-   [ ] Click activates blue highlighting
-   [ ] Chart updates to 9 points
-   [ ] X-axis shows: 08:00, 09:00, ..., 16:00
-   [ ] Info text: "Jam Kerja (08:00 - 16:00)"
-   [ ] Data count shows correct number

### Integration:

-   [ ] Switch between filters works
-   [ ] Refresh maintains current filter
-   [ ] Auto refresh works with Jam Kerja
-   [ ] Loading animation shows
-   [ ] Error handling graceful

---

## 🎉 Benefits

### For Users:

1. ✅ **Clear Time Range**: Knows exactly what period is shown
2. ✅ **Business Hours Focus**: Monitor during operational hours
3. ✅ **Consistent Data**: Same hours every day for comparison
4. ✅ **Easy to Understand**: "Jam Kerja" is self-explanatory

### For Business:

1. ✅ **Compliance**: Track during required hours
2. ✅ **Shift Management**: Clear handover data
3. ✅ **Reporting**: Standardized time frame
4. ✅ **Efficiency**: Focus on relevant hours only

### For Development:

1. ✅ **Flexible**: Easy to change time range if needed
2. ✅ **Maintainable**: Clean code separation
3. ✅ **Scalable**: Can add more custom ranges
4. ✅ **Testable**: Clear input/output

---

## 🚀 Future Enhancements

### Possible Additions:

1. ⬜ **Shift Filters**:

    - Shift 1: 07:00 - 15:00
    - Shift 2: 15:00 - 23:00
    - Shift 3: 23:00 - 07:00

2. ⬜ **Custom Time Range**:

    - Date picker
    - Start time selector
    - End time selector

3. ⬜ **Preset Ranges**:

    - Morning (06:00 - 12:00)
    - Afternoon (12:00 - 18:00)
    - Evening (18:00 - 24:00)

4. ⬜ **Compare Periods**:
    - Today vs Yesterday (same hours)
    - This week vs Last week (working hours)

---

**Status**: ✅ **COMPLETED!**

Filter "Jam Kerja" (08:00 - 16:00) berhasil diimplementasikan!

**Updated**: October 12, 2025, 21:30
