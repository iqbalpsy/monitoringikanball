# 📱 Mobile API Documentation - Monitoring Ikan Ball

## Base URL
- **Development**: `http://127.0.0.1:8000` (Laravel dev server)
- **XAMPP**: `http://localhost/monitoringikanball/monitoringikanball/public`
- **Android Emulator**: `http://10.0.2.2:8000` (untuk emulator Android)
- **Production**: Ganti dengan IP server Anda

## 🔗 Available Endpoints

### 1. Latest Sensor Data
**GET** `/api/mobile/sensor/latest/{device_id}`

**Parameters:**
- `device_id` (optional): Device ID (default: 1)

**Response Example:**
```json
{
  "success": true,
  "data": {
    "device_id": 1,
    "temperature": 27.35,
    "ph": 4.12,
    "oxygen": 6.80,
    "voltage": 3.30,
    "timestamp": "2025-10-24T01:19:04Z",
    "source": "firebase",
    "status": "online"
  },
  "message": "Latest sensor data retrieved successfully",
  "timestamp": "2025-10-24T01:19:04Z"
}
```

### 2. Sensor Data History
**GET** `/api/mobile/sensor/history/{device_id}`

**Parameters:**
- `device_id` (optional): Device ID (default: 1)
- `limit` (optional): Number of records (default: 50)

**Example:** `/api/mobile/sensor/history/1?limit=20`

### 3. Chart Data (Hourly)
**GET** `/api/mobile/sensor/chart/{device_id}`

**Parameters:**
- `device_id` (optional): Device ID (default: 1)
- `type` (optional): "working_hours", "24_hours", "daily" (default: "working_hours")

### 4. Sensor Statistics
**GET** `/api/mobile/sensor/stats/{device_id}`

### 5. Device Status
**GET** `/api/mobile/device/status/{device_id}`

### 6. Devices List
**GET** `/api/mobile/devices`

## 🛠️ Android Integration (Kotlin + Retrofit)

### Dependencies (build.gradle.kts)
```kotlin
implementation("com.squareup.retrofit2:retrofit:2.9.0")
implementation("com.squareup.retrofit2:converter-gson:2.9.0")
implementation("com.squareup.okhttp3:logging-interceptor:4.11.0")
```

### Data Models
```kotlin
data class SensorLatestResponse(
    val success: Boolean,
    val data: SensorData?,
    val message: String,
    val timestamp: String
)

data class SensorData(
    val device_id: Int,
    val temperature: Double,
    val ph: Double,
    val oxygen: Double,
    val voltage: Double?,
    val timestamp: String
)
```

### Retrofit Interface
```kotlin
interface ApiService {
    @GET("api/mobile/sensor/latest/{device_id}")
    suspend fun getLatestSensorData(
        @Path("device_id") deviceId: Int = 1
    ): Response<SensorLatestResponse>
}
```

### Quick Setup
```kotlin
val retrofit = Retrofit.Builder()
    .baseUrl("http://10.0.2.2:8000/") // Android Emulator
    .addConverterFactory(GsonConverterFactory.create())
    .build()

val apiService = retrofit.create(ApiService::class.java)
```

## 🧪 Testing dengan cURL

```bash
# Test Latest Data
curl http://127.0.0.1:8000/api/mobile/sensor/latest/1

# Test dari Android Emulator
curl http://10.0.2.2:8000/api/mobile/sensor/latest/1
```