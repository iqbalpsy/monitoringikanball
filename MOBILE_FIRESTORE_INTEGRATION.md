# 🔥 **FIRESTORE MOBILE API - FINAL INTEGRATION**

## Kolam Ikan Monitoring - Shared Firestore Database

### 🎯 **KONFIGURASI FIREBASE (SAMA UNTUK WEB & MOBILE)**

```javascript
const firebaseConfig = {
    apiKey: "AIzaSyCZsfM1CTPfIyx9mOun9O--Nbmk6bIgu5s",
    authDomain: "container-kolam.firebaseapp.com",
    projectId: "container-kolam",
    storageBucket: "container-kolam.firebasestorage.app",
    messagingSenderId: "980305612759",
    appId: "1:980305612759:web:a778ef7d0ad1f8cef7c592",
    measurementId: "G-Q6JFZMNBJK",
};
```

---

## 📱 **API ENDPOINTS FINAL UNTUK MOBILE APP**

### **Base URL:** `http://127.0.0.1:8000/api/mobile`

_(Ganti dengan domain server production)_

---

### 1️⃣ **GET Latest Data dari Firestore** ⭐ **PALING PENTING**

```
GET /api/mobile/firestore/latest/1
```

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
        "timestamp": "2025-10-24T14:30:00Z",
        "source": "firestore",
        "status": "online"
    },
    "message": "Latest sensor data from Firestore retrieved successfully",
    "timestamp": "2025-10-24T14:30:00Z"
}
```

---

### 2️⃣ **GET History dari Firestore** 📜

```
GET /api/mobile/firestore/history/1?limit=50
```

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
            "timestamp": "2025-10-24T14:30:00Z"
        },
        {
            "device_id": 1,
            "temperature": 26.2,
            "ph": 4.1,
            "oxygen": 6.9,
            "voltage": 3.2,
            "timestamp": "2025-10-24T14:25:00Z"
        }
    ],
    "count": 50,
    "limit": 50,
    "source": "firestore",
    "message": "Sensor history from Firestore retrieved successfully",
    "timestamp": "2025-10-24T14:30:00Z"
}
```

---

### 3️⃣ **POST Save Data ke Firestore** ✅

```
POST /api/mobile/firestore/save
Content-Type: application/json

{
  "device_id": 1,
  "temperature": 26.5,
  "ph": 4.0,
  "oxygen": 6.8,
  "voltage": 3.3
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "device_id": 1,
        "temperature": 26.5,
        "ph": 4.0,
        "oxygen": 6.8,
        "voltage": 3.3
    },
    "message": "Sensor data saved to Firestore successfully",
    "timestamp": "2025-10-24T14:30:00Z"
}
```

---

## 💻 **IMPLEMENTASI MOBILE APP**

### **Android (Java/Kotlin):**

```java
// 1. Get Latest Data
public void getLatestSensorData() {
    String url = "http://127.0.0.1:8000/api/mobile/firestore/latest/1";

    OkHttpClient client = new OkHttpClient();
    Request request = new Request.Builder()
        .url(url)
        .build();

    client.newCall(request).enqueue(new Callback() {
        @Override
        public void onResponse(Call call, Response response) throws IOException {
            String json = response.body().string();
            // Parse JSON dan update UI
            runOnUiThread(() -> updateUI(json));
        }
    });
}

// 2. Save Data to Firestore
public void saveSensorData(double temp, double ph, double oxygen, double voltage) {
    String url = "http://127.0.0.1:8000/api/mobile/firestore/save";

    JSONObject data = new JSONObject();
    try {
        data.put("device_id", 1);
        data.put("temperature", temp);
        data.put("ph", ph);
        data.put("oxygen", oxygen);
        data.put("voltage", voltage);
    } catch (JSONException e) {
        e.printStackTrace();
    }

    RequestBody body = RequestBody.create(
        data.toString(),
        MediaType.parse("application/json")
    );

    Request request = new Request.Builder()
        .url(url)
        .post(body)
        .build();

    // Execute request...
}
```

### **Flutter:**

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class FirestoreService {
  final String baseUrl = 'http://127.0.0.1:8000/api/mobile';

  // 1. Get Latest Data
  Future<Map<String, dynamic>> getLatestSensorData() async {
    final response = await http.get(
      Uri.parse('$baseUrl/firestore/latest/1'),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load sensor data');
    }
  }

  // 2. Get History
  Future<Map<String, dynamic>> getSensorHistory({int limit = 50}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/firestore/history/1?limit=$limit'),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load sensor history');
    }
  }

  // 3. Save Data
  Future<Map<String, dynamic>> saveSensorData({
    required double temperature,
    required double ph,
    required double oxygen,
    required double voltage,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/firestore/save'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({
        'device_id': 1,
        'temperature': temperature,
        'ph': ph,
        'oxygen': oxygen,
        'voltage': voltage,
      }),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to save sensor data');
    }
  }
}

// Usage in Widget
class SensorWidget extends StatefulWidget {
  @override
  _SensorWidgetState createState() => _SensorWidgetState();
}

class _SensorWidgetState extends State<SensorWidget> {
  final FirestoreService _firestoreService = FirestoreService();
  Map<String, dynamic>? sensorData;

  @override
  void initState() {
    super.initState();
    loadSensorData();

    // Auto refresh every 30 seconds
    Timer.periodic(Duration(seconds: 30), (timer) {
      loadSensorData();
    });
  }

  Future<void> loadSensorData() async {
    try {
      final data = await _firestoreService.getLatestSensorData();
      if (data['success']) {
        setState(() {
          sensorData = data['data'];
        });
      }
    } catch (e) {
      print('Error loading sensor data: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    if (sensorData == null) {
      return CircularProgressIndicator();
    }

    return Column(
      children: [
        Text('Temperature: ${sensorData!['temperature']}°C'),
        Text('pH: ${sensorData!['ph']}'),
        Text('Oxygen: ${sensorData!['oxygen']} mg/L'),
        Text('Voltage: ${sensorData!['voltage']}V'),
        Text('Status: ${sensorData!['status']}'),
      ],
    );
  }
}
```

### **React Native:**

```javascript
import React, { useState, useEffect } from "react";
import { View, Text } from "react-native";

const SensorScreen = () => {
    const [sensorData, setSensorData] = useState(null);
    const baseUrl = "http://127.0.0.1:8000/api/mobile";

    // 1. Get Latest Data
    const getLatestSensorData = async () => {
        try {
            const response = await fetch(`${baseUrl}/firestore/latest/1`);
            const data = await response.json();

            if (data.success) {
                setSensorData(data.data);
            }
        } catch (error) {
            console.error("Error:", error);
        }
    };

    // 2. Save Data
    const saveSensorData = async (temperature, ph, oxygen, voltage) => {
        try {
            const response = await fetch(`${baseUrl}/firestore/save`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    device_id: 1,
                    temperature,
                    ph,
                    oxygen,
                    voltage,
                }),
            });

            const data = await response.json();
            console.log("Save result:", data);
        } catch (error) {
            console.error("Error saving:", error);
        }
    };

    useEffect(() => {
        getLatestSensorData();

        // Auto refresh every 30 seconds
        const interval = setInterval(getLatestSensorData, 30000);
        return () => clearInterval(interval);
    }, []);

    if (!sensorData) {
        return <Text>Loading...</Text>;
    }

    return (
        <View>
            <Text>Temperature: {sensorData.temperature}°C</Text>
            <Text>pH: {sensorData.ph}</Text>
            <Text>Oxygen: {sensorData.oxygen} mg/L</Text>
            <Text>Voltage: {sensorData.voltage}V</Text>
            <Text>Status: {sensorData.status}</Text>
        </View>
    );
};

export default SensorScreen;
```

---

## 🔄 **DATA FLOW ARCHITECTURE**

```
ESP32 Sensor → Firestore Database ← Mobile App
                     ↕
              Web Laravel API
                     ↕
              Web Dashboard
```

**Keuntungan:**

-   ✅ **Satu Database:** Firestore untuk semua platform
-   ✅ **Real-time Sync:** Data otomatis sinkron
-   ✅ **Offline Support:** Firestore mendukung offline caching
-   ✅ **Scalable:** Google Cloud infrastructure

---

## 🧪 **TESTING ENDPOINTS**

```bash
# Test dengan curl
curl -X GET "http://127.0.0.1:8000/api/mobile/firestore/latest/1"

curl -X GET "http://127.0.0.1:8000/api/mobile/firestore/history/1?limit=10"

curl -X POST "http://127.0.0.1:8000/api/mobile/firestore/save" \
  -H "Content-Type: application/json" \
  -d '{"device_id":1,"temperature":26.5,"ph":4.0,"oxygen":6.8,"voltage":3.3}'
```

---

## 🔐 **PRODUCTION SETUP**

### **1. Update Base URL:**

```
Development: http://127.0.0.1:8000/api/mobile
Production:  https://yourdomain.com/api/mobile
```

### **2. Firebase Security Rules (Firestore):**

```javascript
// Allow read/write for sensor_data collection
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /sensor_data/{document} {
      allow read, write: if true; // Customize based on your security needs
    }
  }
}
```

### **3. API Rate Limiting (Optional):**

Add rate limiting middleware for production use.

---

## 📊 **MONITORING & ANALYTICS**

-   **Firebase Console:** Monitor Firestore usage
-   **Laravel Logs:** Check API performance
-   **Mobile Analytics:** Track API call frequency

---

## 🆘 **TROUBLESHOOTING**

### **Common Issues:**

1. **CORS Error:** Check if CORS middleware is enabled
2. **Firestore Connection:** Verify project ID and permissions
3. **API Timeout:** Increase timeout in mobile app
4. **Data Format:** Ensure JSON format is correct

### **Debug Endpoints:**

-   Health Check: `GET /api/health`
-   Firestore Test: `GET /api/mobile/firestore/latest/1`

---

## ✅ **FINAL CHECKLIST FOR MOBILE DEVELOPER**

-   [ ] Update Firebase config dengan projectId: "container-kolam"
-   [ ] Implement 3 main API calls: latest, history, save
-   [ ] Add auto-refresh every 30 seconds
-   [ ] Handle error responses gracefully
-   [ ] Test offline/online scenarios
-   [ ] Update base URL for production

**Mobile app sekarang terhubung dengan database Firestore yang sama!** 🚀
