# 🐟 Database Design - IoT Fish Monitoring System

## 📋 Overview
Sistem monitoring IoT untuk budidaya ikan dengan cross-platform support (mobile & web). Database dirancang untuk mengelola data sensor real-time, kontrol device, dan management user dengan role-based access.

## 🗃️ Database Schema

### 1. **users** - User Management & Authentication
```sql
- id (Primary Key)
- name (varchar)
- email (varchar, unique)
- email_verified_at (timestamp, nullable)
- password (varchar, hashed)
- role (enum: 'admin', 'user') - DEFAULT 'user'
- is_active (boolean) - DEFAULT true
- last_login_at (timestamp, nullable)
- remember_token (varchar, nullable)
- created_at, updated_at
```

**Roles:**
- **Admin**: Dapat mengontrol IoT devices, mengelola users, melihat semua data
- **User**: Hanya dapat melihat data sensor dari device yang diberi akses

### 2. **devices** - IoT Device Management
```sql
- id (Primary Key)
- name (varchar) - Nama device (ex: "Kolam A", "Tank 1")
- device_id (varchar, unique) - ID unik hardware device
- location (varchar, nullable) - Lokasi fisik device
- description (text, nullable) - Deskripsi device
- status (enum: 'online', 'offline', 'maintenance') - DEFAULT 'offline'
- settings (json, nullable) - Konfigurasi device (threshold, dll)
- created_by (Foreign Key -> users.id) - Admin yang menambahkan
- is_active (boolean) - DEFAULT true
- last_seen_at (timestamp, nullable) - Terakhir device kirim data
- created_at, updated_at
```

### 3. **sensor_data** - Real-time Sensor Data
```sql
- id (Primary Key)
- device_id (Foreign Key -> devices.id)
- ph_level (decimal 4,2, nullable) - pH air (0.00 - 14.00)
- temperature (decimal 5,2, nullable) - Suhu air (°C)
- oxygen_level (decimal 5,2, nullable) - Level oksigen (mg/L)
- turbidity (decimal 5,2, nullable) - Kekeruhan air
- raw_data (json, nullable) - Data mentah sensor
- recorded_at (timestamp) - Waktu data direkam di device
- created_at, updated_at
```

**Indexes:**
- `(device_id, recorded_at)` - Query data per device + time range
- `recorded_at` - Query data berdasarkan waktu

### 4. **device_controls** - Device Control History
```sql
- id (Primary Key)
- device_id (Foreign Key -> devices.id)
- user_id (Foreign Key -> users.id) - Admin yang kontrol
- action (varchar) - Jenis aksi (ex: 'turn_on_pump', 'adjust_ph')
- parameters (json, nullable) - Parameter kontrol
- previous_state (json, nullable) - State sebelum kontrol
- new_state (json, nullable) - State setelah kontrol
- status (enum: 'pending', 'executed', 'failed', 'cancelled')
- notes (text, nullable) - Catatan admin
- executed_at (timestamp, nullable) - Waktu eksekusi
- created_at, updated_at
```

### 5. **user_device_access** - User Access Control
```sql
- id (Primary Key)
- user_id (Foreign Key -> users.id)
- device_id (Foreign Key -> devices.id)
- granted_by (Foreign Key -> users.id) - Admin yang memberikan akses
- can_view_data (boolean) - DEFAULT true
- can_control (boolean) - DEFAULT false
- granted_at (timestamp) - Waktu akses diberikan
- expires_at (timestamp, nullable) - Waktu akses berakhir
- created_at, updated_at
```

**Unique Constraint:** `(user_id, device_id)` - Satu user, satu record per device

## 🔗 Entity Relationships

### User Relationships
- `User hasMany Device` (created_by)
- `User hasMany DeviceControl`
- `User belongsToMany Device` through `user_device_access`
- `User hasMany UserDeviceAccess` (granted_by)

### Device Relationships
- `Device belongsTo User` (creator)
- `Device hasMany SensorData`
- `Device hasOne SensorData` (latest)
- `Device hasMany DeviceControl`
- `Device belongsToMany User` through `user_device_access`

### SensorData Relationships
- `SensorData belongsTo Device`

### DeviceControl Relationships
- `DeviceControl belongsTo Device`
- `DeviceControl belongsTo User`

### UserDeviceAccess Relationships
- `UserDeviceAccess belongsTo User`
- `UserDeviceAccess belongsTo Device`
- `UserDeviceAccess belongsTo User` (granted_by)

## 📊 Sample Data Structure

### Normal Sensor Values
- **pH Level**: 6.5 - 8.5 (optimal untuk sebagian besar ikan)
- **Temperature**: 24°C - 30°C (ikan tropis)
- **Oxygen Level**: >5 mg/L (minimum untuk ikan sehat)
- **Turbidity**: <5 NTU (air jernih)

### Control Actions Examples
```json
{
  "action": "turn_on_pump",
  "parameters": {
    "pump_id": "PUMP_001",
    "duration": 30,
    "intensity": "medium"
  }
}

{
  "action": "adjust_ph",
  "parameters": {
    "target_ph": 7.2,
    "method": "buffer_addition"
  }
}
```

## 🚀 Usage Examples

### Admin Operations
```php
// Create new device
$device = Device::create([
    'name' => 'Kolam C - Gurame',
    'device_id' => 'IOT_FISH_003',
    'location' => 'Sektor Timur',
    'created_by' => $admin->id
]);

// Grant access to user
UserDeviceAccess::grantViewAccess($user->id, $device->id, $admin->id);

// Control device
DeviceControl::create([
    'device_id' => $device->id,
    'user_id' => $admin->id,
    'action' => 'emergency_oxygen',
    'status' => 'pending'
]);
```

### User Operations
```php
// Get accessible devices
$devices = auth()->user()->accessibleDevices()
    ->where('can_view_data', true)
    ->get();

// Get latest sensor data
$latestData = $device->latestSensorData;

// Get sensor data in time range
$todayData = SensorData::where('device_id', $deviceId)
    ->inTimeRange(today(), now())
    ->latest()
    ->get();
```

## 🔧 API Endpoints Suggestion

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user

### Devices (Admin Only)
- `GET /api/devices` - List all devices
- `POST /api/devices` - Create device
- `PUT /api/devices/{id}` - Update device
- `DELETE /api/devices/{id}` - Delete device

### Sensor Data
- `GET /api/devices/{id}/sensor-data` - Get device sensor data
- `POST /api/devices/{id}/sensor-data` - Insert sensor data (from IoT)
- `GET /api/devices/{id}/latest-data` - Get latest sensor reading

### Device Control (Admin Only)
- `POST /api/devices/{id}/control` - Send control command
- `GET /api/devices/{id}/controls` - Get control history

### User Management (Admin Only)
- `GET /api/users` - List users
- `POST /api/users/{userId}/grant-access` - Grant device access

## 📱 Mobile/Web Features

### User Dashboard
- List of accessible devices
- Real-time sensor readings
- Historical data charts
- Alert notifications

### Admin Dashboard
- All devices overview
- User management
- Device control interface
- System logs
- Analytics dashboard

## 🔔 Alert System (Future Enhancement)
```sql
-- alerts table
- id
- device_id
- alert_type (ph_high, ph_low, temp_high, temp_low, oxygen_low)
- threshold_value
- current_value
- severity (low, medium, high, critical)
- is_acknowledged
- acknowledged_by
- created_at
```

Struktur database ini dirancang untuk skalabilitas, performa, dan kemudahan maintenance. Dengan role-based access control yang fleksibel dan logging yang comprehensive untuk audit trail.
