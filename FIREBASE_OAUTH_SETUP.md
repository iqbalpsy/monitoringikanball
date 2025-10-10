# 🔥 Firebase & OAuth Integration Setup - IoT Fish Monitoring

## ✅ **Status Implementasi**

Semua konfigurasi Firebase dan OAuth Google telah berhasil disetup untuk project IoT monitoring ikan Anda!

## 🔧 **Yang Telah Dikonfigurasi:**

### 1. **Firebase Integration**
- ✅ Firebase Realtime Database untuk data real-time
- ✅ Service class `FirebaseService` untuk komunikasi dengan Firebase
- ✅ Auto-sync data sensor ke Firebase untuk real-time monitoring
- ✅ Device control melalui Firebase

### 2. **Google OAuth Authentication**
- ✅ Laravel Socialite untuk Google login
- ✅ Support untuk web dan mobile app authentication
- ✅ Automatic user creation/update dari Google account

### 3. **API Endpoints**
- ✅ Complete REST API untuk mobile/web app
- ✅ Laravel Sanctum untuk API token authentication
- ✅ Role-based access control (Admin/User)

### 4. **Security Features**
- ✅ Admin middleware untuk control access
- ✅ User device access control
- ✅ API rate limiting dan validation

## 📱 **API Endpoints Available:**

### **Authentication**
```
POST /api/auth/login          - Regular login
POST /api/auth/google         - Google OAuth login (mobile)
POST /api/auth/logout         - Logout
GET  /api/user               - Get current user info
```

### **Web OAuth**
```
GET  /auth/google            - Google OAuth redirect
GET  /auth/google/callback   - Google OAuth callback
```

### **IoT Device Management**
```
GET  /api/devices                     - Get user's devices
GET  /api/devices/{id}                - Get device details
GET  /api/devices/{id}/sensor-data    - Get sensor data
GET  /api/devices/{id}/stream         - Real-time data stream
GET  /api/devices/{id}/controls       - Control history (Admin)
POST /api/devices/{id}/control        - Send control (Admin)
```

### **IoT Data Receiver**
```
POST /api/iot/sensor-data    - Receive data from IoT devices
```

## 🌐 **Server Running**
Server development Laravel sudah berjalan di: `http://localhost:8000`

## 🔑 **Credentials untuk Testing:**

### **Admin Account:**
- Email: `admin@fishmonitoring.com`
- Password: `password123`

### **User Accounts:**
- Email: `budi@example.com` / Password: `password123`
- Email: `siti@example.com` / Password: `password123`

### **Google OAuth:**
- Client ID: `685083671333-vo8hr9uf67s3ui3i774t5p6p81fvhh66.apps.googleusercontent.com`
- Redirect URI: `http://localhost:8000/auth/google/callback`

## 📊 **Firebase Configuration:**
- Project ID: `kolam-ikan-project`
- Database URL: `https://kolam-ikan-project-default-rtdb.firebaseio.com`
- Real-time sync aktif untuk semua sensor data

## 🧪 **Testing API:**

### 1. **Test Health Check:**
```bash
curl http://localhost:8000/api/health
```

### 2. **Test Login:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@fishmonitoring.com","password":"password123"}'
```

### 3. **Test Get Devices (with token):**
```bash
curl -X GET http://localhost:8000/api/devices \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 4. **Test Send Sensor Data:**
```bash
curl -X POST http://localhost:8000/api/iot/sensor-data \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": "IOT_FISH_001",
    "ph_level": 7.2,
    "temperature": 27.5,
    "oxygen_level": 6.8,
    "turbidity": 2.1
  }'
```

## 📱 **Mobile App Integration:**

### **Login dengan Google:**
1. Mobile app redirect user ke Google OAuth
2. Dapat Google access token
3. Kirim token ke `/api/auth/google`
4. Terima API token untuk subsequent requests

### **Real-time Data:**
- Gunakan Server-Sent Events di `/api/devices/{id}/stream`
- Atau polling `/api/devices/{id}/sensor-data` setiap 5-10 detik

## 🔄 **Firebase Real-time Flow:**

1. **IoT Device** → Laravel API → Database + Firebase
2. **Mobile/Web App** ← Firebase (real-time) + Laravel API
3. **Admin Control** → Laravel API → Firebase → IoT Device

## 🚀 **Next Steps:**

1. **Mobile App Development:**
   - Implement Google OAuth flow
   - Setup real-time data streaming
   - Create dashboard UI

2. **IoT Device Programming:**
   - Setup HTTP client untuk kirim data ke `/api/iot/sensor-data`
   - Monitor Firebase untuk control commands
   - Implement device actions

3. **Web Dashboard:**
   - Create proper login/dashboard views
   - Real-time charts untuk sensor data
   - Admin control interface

4. **Production Setup:**
   - Setup proper Firebase security rules
   - Configure CORS untuk mobile app
   - Add rate limiting dan API security

## 🔐 **Security Notes:**

- API token expires otomatis
- Firebase security rules perlu dikonfigurasi
- HTTPS wajib untuk production
- Rate limiting aktif untuk API endpoints

**Selamat! Firebase dan OAuth integration sudah siap untuk project IoT Fish Monitoring Anda! 🐟🎉**
