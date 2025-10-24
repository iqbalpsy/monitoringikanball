# 📱 Mobile App API Documentation

## Kolam Ikan Monitoring System - Firebase Integration

### 🌐 Base URL

```
http://127.0.0.1:8000/api/mobile
```

### 📋 API Overview

API ini menyediakan akses data sensor kolam ikan dari Firebase untuk mobile app tanpa memerlukan authentication. Semua endpoint mendukung CORS untuk cross-origin requests.

---

## 🔥 Firebase Data Endpoints

### 1. **Get Latest Sensor Data**

Mengambil data sensor terbaru dari Firebase

**Endpoint:** `GET /mobile/sensor/latest/{device_id?}`

**Parameters:**

-   `device_id` (optional): ID device (default: 1)

**Response:**

```json
{
    "success": true,
    "data": {
        "device_id": 1,
        "temperature": 26.5,
        "ph": 4.0,
        "oxygen": 6.8,
        "voltage": 3.3,
        "timestamp": "2025-10-24T14:30:00.000000Z",
        "source": "firebase",
        "status": "online"
    },
    "message": "Latest sensor data retrieved successfully",
    "timestamp": "2025-10-24T14:30:00.000000Z"
}
```

**Status Values:**

-   `online`: Device aktif (< 5 menit)
-   `no_data`: Tidak ada data tersedia

---

### 2. **Get Sensor History**

Mengambil riwayat data sensor dari Firebase

**Endpoint:** `GET /mobile/sensor/history/{device_id?}`

**Parameters:**

-   `device_id` (optional): ID device (default: 1)
-   `limit` (optional): Jumlah data maksimal (default: 50)

**Example:** `GET /mobile/sensor/history/1?limit=100`

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "device_id": 1,
            "temperature": 26.5,
            "ph": 4.0,
            "oxygen": 6.8,
            "voltage": 3.3,
            "timestamp": "2025-10-24T14:30:00.000000Z"
        },
        {
            "device_id": 1,
            "temperature": 26.2,
            "ph": 4.1,
            "oxygen": 6.9,
            "voltage": 3.2,
            "timestamp": "2025-10-24T14:25:00.000000Z"
        }
    ],
    "count": 50,
    "limit": 50,
    "message": "Sensor history retrieved successfully",
    "timestamp": "2025-10-24T14:30:00.000000Z"
}
```

---

### 3. **Get Chart Data**

Mengambil data agregat per jam untuk grafik

**Endpoint:** `GET /mobile/sensor/chart/{device_id?}`

**Parameters:**

-   `device_id` (optional): ID device (default: 1)
-   `type` (optional): Tipe data (default: working_hours)

**Example:** `GET /mobile/sensor/chart/1?type=working_hours`

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "time": "08:00",
            "hour": 8,
            "temperature": 25.2,
            "ph": 6.8,
            "oxygen": 7.5,
            "readings_count": 3
        },
        {
            "time": "09:00",
            "hour": 9,
            "temperature": 25.8,
            "ph": 6.9,
            "oxygen": 7.2,
            "readings_count": 5
        }
    ],
    "count": 10,
    "type": "working_hours",
    "device_id": 1,
    "message": "Chart data retrieved successfully",
    "timestamp": "2025-10-24T14:30:00.000000Z"
}
```

---

### 4. **Get Sensor Statistics**

Mengambil statistik sensor (rata-rata, min, max)

**Endpoint:** `GET /mobile/sensor/stats/{device_id?}`

**Response:**

```json
{
    "success": true,
    "data": {
        "device_id": 1,
        "total_readings": 124,
        "temperature": {
            "current": 26.5,
            "average": 25.8,
            "min": 24.2,
            "max": 27.1,
            "status": "normal"
        },
        "ph": {
            "current": 4.0,
            "average": 4.2,
            "min": 3.8,
            "max": 4.5,
            "status": "low"
        },
        "oxygen": {
            "current": 6.8,
            "average": 7.1,
            "min": 6.2,
            "max": 8.0,
            "status": "normal"
        },
        "last_updated": "2025-10-24T14:30:00.000000Z"
    },
    "message": "Sensor statistics retrieved successfully",
    "timestamp": "2025-10-24T14:30:00.000000Z"
}
```

**Status Values:**

-   Temperature: `normal`, `too_cold`, `too_hot`
-   pH: `normal`, `low`, `high`
-   Oxygen: `normal`, `low`, `high`

---

### 5. **Get Device Status**

Mengambil status perangkat dan koneksi

**Endpoint:** `GET /mobile/device/status/{device_id?}`

**Response:**

```json
{
    "success": true,
    "data": {
        "device_id": 1,
        "status": "online",
        "status_text": "Device Online",
        "last_seen": "2025-10-24T14:30:00.000000Z",
        "minutes_ago": 2,
        "voltage": 3.3,
        "connection": "firebase",
        "readings_today": 124
    },
    "message": "Device status retrieved successfully",
    "timestamp": "2025-10-24T14:30:00.000000Z"
}
```

**Status Values:**

-   `online`: Device aktif (< 5 menit)
-   `warning`: Respon lambat (5-30 menit)
-   `offline`: Tidak ada koneksi (> 30 menit)
-   `no_data`: Tidak ada data sama sekali

---

### 6. **Get Devices List**

Mengambil daftar semua device yang tersedia

**Endpoint:** `GET /mobile/devices`

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "device_id": 1,
            "name": "Kolam Ikan Utama",
            "location": "Pool 1",
            "type": "ESP32 pH Sensor",
            "status": "active",
            "last_seen": "2025-10-24T14:30:00.000000Z"
        }
    ],
    "count": 1,
    "message": "Devices list retrieved successfully",
    "timestamp": "2025-10-24T14:30:00.000000Z"
}
```

---

### 7. **Get Real-time Firebase Data**

Mengambil data real-time langsung dari Firebase (optimized untuk mobile)

**Endpoint:** `GET /mobile/firebase/realtime/{device_id?}`

**Response:** Same as web dashboard Firebase endpoint but with mobile optimization

---

## 🔧 Technical Implementation

### **HTTP Headers**

All endpoints automatically include CORS headers:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-API-Key, Authorization
```

### **Error Handling**

Semua endpoint menggunakan format error yang konsisten:

```json
{
    "success": false,
    "error": "Detailed error message",
    "message": "User-friendly error description",
    "timestamp": "2025-10-24T14:30:00.000000Z"
}
```

**HTTP Status Codes:**

-   `200`: Success
-   `500`: Server Error
-   `404`: Endpoint Not Found

### **Data Sources**

-   **Primary**: Firebase Realtime Database
-   **Fallback**: Sample data for demonstration when Firebase has no data
-   **Format**: All timestamps in ISO 8601 format (UTC)

---

## 📱 Mobile App Integration Examples

### **Android (Java/Kotlin)**

```java
// Example HTTP request
String url = "http://127.0.0.1:8000/api/mobile/sensor/latest/1";
// Use OkHttp, Retrofit, or Volley for HTTP requests
```

### **iOS (Swift)**

```swift
// Example HTTP request
let url = URL(string: "http://127.0.0.1:8000/api/mobile/sensor/latest/1")
// Use URLSession or Alamofire for HTTP requests
```

### **React Native**

```javascript
// Example fetch request
fetch("http://127.0.0.1:8000/api/mobile/sensor/latest/1")
    .then((response) => response.json())
    .then((data) => console.log(data));
```

### **Flutter**

```dart
// Example HTTP request
import 'package:http/http.dart' as http;

final response = await http.get(
  Uri.parse('http://127.0.0.1:8000/api/mobile/sensor/latest/1'),
);
```

---

## 🚀 Testing Endpoints

Use these curl commands to test the API:

```bash
# Test latest sensor data
curl -X GET "http://127.0.0.1:8000/api/mobile/sensor/latest/1"

# Test sensor history
curl -X GET "http://127.0.0.1:8000/api/mobile/sensor/history/1?limit=10"

# Test chart data
curl -X GET "http://127.0.0.1:8000/api/mobile/sensor/chart/1"

# Test sensor statistics
curl -X GET "http://127.0.0.1:8000/api/mobile/sensor/stats/1"

# Test device status
curl -X GET "http://127.0.0.1:8000/api/mobile/device/status/1"

# Test devices list
curl -X GET "http://127.0.0.1:8000/api/mobile/devices"
```

---

## 🔐 Security Notes

-   **No Authentication**: Currently no API key required for development
-   **CORS Enabled**: All origins allowed (should be restricted in production)
-   **Rate Limiting**: Not implemented (recommended for production)
-   **HTTPS**: Use HTTPS in production environment

---

## 📊 Data Flow

1. **ESP32** → **Firebase Realtime Database** → **API** → **Mobile App**
2. **Real-time Updates**: Mobile app can poll endpoints every 30-60 seconds
3. **Offline Support**: API provides sample data when Firebase is unavailable

---

## 🆘 Support & Troubleshooting

### Common Issues:

1. **CORS Error**: Check if CORS middleware is enabled
2. **No Data**: Firebase might be empty, API will return sample data
3. **Connection Error**: Ensure Laravel server is running on port 8000

### Debug Endpoints:

-   Health Check: `GET /api/health`
-   Raw Firebase: `GET /api/firebase-data?device_id=1`
