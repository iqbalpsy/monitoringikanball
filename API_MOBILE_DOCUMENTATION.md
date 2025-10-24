# 📱 **API Documentation - Mobile Android**

## **Base URL**

```
http://192.168.1.100/monitoringikanball/monitoringikanball/public/api
```

atau

```
http://10.240.181.8/monitoringikanball/monitoringikanball/public/api
```

> **⚠️ Catatan:** Ganti IP address sesuai dengan IP server XAMPP Anda

---

## **Authentication**

API ini menggunakan **Laravel Sanctum** dengan token-based authentication.

### **Headers yang Diperlukan:**

**Untuk endpoint yang memerlukan autentikasi:**

```
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
Content-Type: application/json
```

---

## **📡 API Endpoints**

### **1. AUTHENTICATION**

#### **1.1 Register** ✅

Mendaftarkan user baru

**Endpoint:** `POST /mobile/auth/register`

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201 Created):**

```json
{
    "success": true,
    "message": "Registrasi berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "user",
            "is_active": true
        },
        "token": "1|abcdefghijklmnopqrstuvwxyz123456789"
    }
}
```

**Error Response (422 Validation Failed):**

```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password confirmation does not match."]
    }
}
```

---

#### **1.2 Login** ✅

Login user dan mendapatkan token

**Endpoint:** `POST /mobile/auth/login`

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "user",
            "is_active": true,
            "last_login_at": "2025-10-22T10:30:00.000000Z"
        },
        "token": "2|xyz987654321abcdefghijklmnopqrstu"
    }
}
```

**Error Response (401 Unauthorized):**

```json
{
    "success": false,
    "message": "Email atau password salah"
}
```

**Error Response (403 Forbidden):**

```json
{
    "success": false,
    "message": "Akun Anda tidak aktif. Hubungi administrator."
}
```

---

#### **1.3 Logout** 🔒

Logout user (revoke token)

**Endpoint:** `POST /mobile/logout`

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Logout berhasil"
}
```

---

### **2. USER PROFILE**

#### **2.1 Get Profile** 🔒

Mendapatkan informasi profile user yang sedang login

**Endpoint:** `GET /mobile/profile`

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "user",
            "is_active": true,
            "last_login_at": "2025-10-22T10:30:00.000000Z",
            "created_at": "2025-10-01T08:00:00.000000Z"
        },
        "settings": {
            "temp_min": 24.0,
            "temp_max": 30.0,
            "ph_min": 6.5,
            "ph_max": 8.5,
            "oxygen_min": 5.0,
            "oxygen_max": 8.0
        }
    }
}
```

---

#### **2.2 Update Profile** 🔒

Update informasi profile user

**Endpoint:** `PUT /mobile/profile`

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Request Body (semua field optional):**

```json
{
    "name": "John Doe Updated",
    "email": "newemail@example.com",
    "current_password": "oldpassword123",
    "new_password": "newpassword456",
    "new_password_confirmation": "newpassword456"
}
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Profile berhasil diupdate",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe Updated",
            "email": "newemail@example.com",
            "role": "user"
        }
    }
}
```

---

### **3. DASHBOARD**

#### **3.1 Get Dashboard Data** 🔒

Mendapatkan semua data untuk dashboard (latest reading, chart data, statistics, alerts)

**Endpoint:** `GET /mobile/dashboard`

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "latest": {
            "temperature": 27.5,
            "ph": 7.2,
            "oxygen": 6.8,
            "recorded_at": "2025-10-22T15:30:00.000000Z"
        },
        "chart_data": [
            {
                "temperature": 27.3,
                "ph": 7.1,
                "oxygen": 6.7,
                "time": "08:00",
                "recorded_at": "2025-10-22T08:15:00.000000Z"
            },
            {
                "temperature": 27.5,
                "ph": 7.2,
                "oxygen": 6.8,
                "time": "09:00",
                "recorded_at": "2025-10-22T09:20:00.000000Z"
            }
        ],
        "statistics": {
            "avg_temperature": 27.4,
            "avg_ph": 7.15,
            "avg_oxygen": 6.75,
            "max_temperature": 27.8,
            "min_temperature": 27.0,
            "max_ph": 7.3,
            "min_ph": 7.0,
            "max_oxygen": 7.0,
            "min_oxygen": 6.5
        },
        "status": {
            "temperature": "normal",
            "ph": "normal",
            "oxygen": "normal"
        },
        "alerts": [],
        "time_range": {
            "start": "2025-10-22 08:00:00",
            "end": "2025-10-22 17:00:00",
            "label": "Jam Kerja (08:00 - 17:00)"
        }
    }
}
```

**Example with Alerts:**

```json
{
    "success": true,
    "data": {
        "latest": {
            "temperature": 32.5,
            "ph": 9.2,
            "oxygen": 4.2
        },
        "status": {
            "temperature": "warning",
            "ph": "warning",
            "oxygen": "warning"
        },
        "alerts": [
            {
                "type": "temperature",
                "message": "Temperature di luar batas normal: 32.5°C",
                "level": "warning"
            },
            {
                "type": "ph",
                "message": "pH di luar batas normal: 9.2",
                "level": "warning"
            },
            {
                "type": "oxygen",
                "message": "Oksigen di luar batas normal: 4.2 mg/L",
                "level": "warning"
            }
        ]
    }
}
```

---

#### **3.2 Get Latest Reading** 🔒

Mendapatkan data sensor terbaru saja (untuk real-time monitoring)

**Endpoint:** `GET /mobile/latest`

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "temperature": 27.5,
        "ph": 7.2,
        "oxygen": 6.8,
        "recorded_at": "2025-10-22T15:30:00.000000Z",
        "status": {
            "temperature": "normal",
            "ph": "normal",
            "oxygen": "normal"
        }
    }
}
```

---

### **4. HISTORY**

#### **4.1 Get History Data** 🔒

Mendapatkan data history dengan pagination dan filter

**Endpoint:** `GET /mobile/history`

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Query Parameters (semua optional):**

-   `per_page` (int): Jumlah data per page (default: 20)
-   `page` (int): Nomor halaman (default: 1)
-   `date_from` (datetime): Filter dari tanggal (format: Y-m-d H:i:s)
-   `date_to` (datetime): Filter sampai tanggal
-   `temp_min` (float): Filter temperature minimum
-   `temp_max` (float): Filter temperature maximum
-   `ph_min` (float): Filter pH minimum
-   `ph_max` (float): Filter pH maximum
-   `oxygen_min` (float): Filter oxygen minimum
-   `oxygen_max` (float): Filter oxygen maximum

**Example Request:**

```
GET /mobile/history?per_page=10&page=1&date_from=2025-10-20 00:00:00&temp_min=25&temp_max=30
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": [
        {
            "id": 51,
            "device_id": 1,
            "temperature": "27.50",
            "ph": "7.23",
            "oxygen": "6.80",
            "recorded_at": "2025-10-22T15:30:00.000000Z",
            "created_at": "2025-10-22T15:30:00.000000Z",
            "updated_at": "2025-10-22T15:30:00.000000Z"
        },
        {
            "id": 50,
            "device_id": 1,
            "temperature": "27.30",
            "ph": "7.15",
            "oxygen": "6.75",
            "recorded_at": "2025-10-22T15:00:00.000000Z",
            "created_at": "2025-10-22T15:00:00.000000Z",
            "updated_at": "2025-10-22T15:00:00.000000Z"
        }
    ],
    "pagination": {
        "total": 51,
        "per_page": 10,
        "current_page": 1,
        "last_page": 6,
        "from": 1,
        "to": 10
    }
}
```

---

### **5. SETTINGS**

#### **5.1 Get Settings** 🔒

Mendapatkan threshold settings user

**Endpoint:** `GET /mobile/settings`

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "temp_min": 24.0,
        "temp_max": 30.0,
        "ph_min": 6.5,
        "ph_max": 8.5,
        "oxygen_min": 5.0,
        "oxygen_max": 8.0
    }
}
```

---

#### **5.2 Update Settings** 🔒

Update threshold settings user

**Endpoint:** `PUT /mobile/settings`

**Headers:**

```
Authorization: Bearer YOUR_TOKEN
```

**Request Body (semua field optional):**

```json
{
    "temp_min": 25.0,
    "temp_max": 29.0,
    "ph_min": 6.8,
    "ph_max": 8.2,
    "oxygen_min": 5.5,
    "oxygen_max": 7.5
}
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Settings berhasil diupdate",
    "data": {
        "temp_min": 25.0,
        "temp_max": 29.0,
        "ph_min": 6.8,
        "ph_max": 8.2,
        "oxygen_min": 5.5,
        "oxygen_max": 7.5
    }
}
```

---

## **🔧 Error Responses**

### **Unauthorized (401)**

```json
{
    "message": "Unauthenticated."
}
```

**Solusi:** Token tidak valid atau sudah expired. User harus login ulang.

---

### **Validation Error (422)**

```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 6 characters."]
    }
}
```

---

### **Server Error (500)**

```json
{
    "success": false,
    "message": "Gagal mengambil data",
    "error": "Error message here"
}
```

---

## **📲 Example Usage (Android - Retrofit)**

### **1. Setup Retrofit Interface**

```kotlin
interface ApiService {
    // Authentication
    @POST("mobile/auth/register")
    suspend fun register(@Body request: RegisterRequest): Response<AuthResponse>

    @POST("mobile/auth/login")
    suspend fun login(@Body request: LoginRequest): Response<AuthResponse>

    @POST("mobile/logout")
    suspend fun logout(): Response<BaseResponse>

    // Dashboard
    @GET("mobile/dashboard")
    suspend fun getDashboard(): Response<DashboardResponse>

    @GET("mobile/latest")
    suspend fun getLatest(): Response<LatestResponse>

    // History
    @GET("mobile/history")
    suspend fun getHistory(
        @Query("per_page") perPage: Int = 20,
        @Query("page") page: Int = 1,
        @Query("date_from") dateFrom: String? = null,
        @Query("date_to") dateTo: String? = null
    ): Response<HistoryResponse>

    // Profile
    @GET("mobile/profile")
    suspend fun getProfile(): Response<ProfileResponse>

    @PUT("mobile/profile")
    suspend fun updateProfile(@Body request: UpdateProfileRequest): Response<ProfileUpdateResponse>

    // Settings
    @GET("mobile/settings")
    suspend fun getSettings(): Response<SettingsResponse>

    @PUT("mobile/settings")
    suspend fun updateSettings(@Body request: UpdateSettingsRequest): Response<SettingsUpdateResponse>
}
```

---

### **2. Setup Retrofit Client with Interceptor**

```kotlin
class AuthInterceptor : Interceptor {
    override fun intercept(chain: Interceptor.Chain): okhttp3.Response {
        val token = SharedPreferencesHelper.getToken() // Get token from storage
        val request = chain.request().newBuilder()
            .addHeader("Accept", "application/json")
            .addHeader("Content-Type", "application/json")
            .apply {
                if (token != null) {
                    addHeader("Authorization", "Bearer $token")
                }
            }
            .build()
        return chain.proceed(request)
    }
}

object RetrofitClient {
    private const val BASE_URL = "http://192.168.1.100/monitoringikanball/monitoringikanball/public/api/"

    private val okHttpClient = OkHttpClient.Builder()
        .addInterceptor(AuthInterceptor())
        .connectTimeout(30, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .build()

    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}
```

---

### **3. Example: Login**

```kotlin
class LoginViewModel : ViewModel() {
    fun login(email: String, password: String) {
        viewModelScope.launch {
            try {
                val response = RetrofitClient.apiService.login(
                    LoginRequest(email, password)
                )

                if (response.isSuccessful && response.body()?.success == true) {
                    val data = response.body()!!.data

                    // Save token
                    SharedPreferencesHelper.saveToken(data.token)

                    // Save user info
                    SharedPreferencesHelper.saveUser(data.user)

                    // Navigate to dashboard
                    _loginState.value = LoginState.Success(data.user)
                } else {
                    _loginState.value = LoginState.Error("Login gagal")
                }
            } catch (e: Exception) {
                _loginState.value = LoginState.Error(e.message ?: "Network error")
            }
        }
    }
}
```

---

### **4. Example: Get Dashboard**

```kotlin
class DashboardViewModel : ViewModel() {
    private val _dashboardData = MutableLiveData<DashboardData>()
    val dashboardData: LiveData<DashboardData> = _dashboardData

    fun loadDashboard() {
        viewModelScope.launch {
            try {
                val response = RetrofitClient.apiService.getDashboard()

                if (response.isSuccessful && response.body()?.success == true) {
                    _dashboardData.value = response.body()!!.data
                } else {
                    // Handle error
                }
            } catch (e: Exception) {
                // Handle exception
            }
        }
    }
}
```

---

## **🔐 Security Best Practices**

1. **HTTPS di Production**: Gunakan HTTPS untuk mengamankan komunikasi
2. **Token Storage**: Simpan token di SharedPreferences (encrypted) atau DataStore
3. **Token Expiration**: Implementasikan refresh token mechanism
4. **Input Validation**: Validasi input di client side sebelum kirim ke server
5. **Error Handling**: Handle semua error response dengan proper

---

## **📝 Testing dengan Postman**

### **1. Register User**

```
POST http://192.168.1.100/monitoringikanball/monitoringikanball/public/api/mobile/auth/register

Body (raw JSON):
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### **2. Login**

```
POST http://192.168.1.100/monitoringikanball/monitoringikanball/public/api/mobile/auth/login

Body (raw JSON):
{
  "email": "test@example.com",
  "password": "password123"
}

Response: Save the token!
```

### **3. Get Dashboard (with token)**

```
GET http://192.168.1.100/monitoringikanball/monitoringikanball/public/api/mobile/dashboard

Headers:
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
```

---

## **✅ Checklist Integrasi Android**

-   [ ] Setup Retrofit dengan base URL server XAMPP
-   [ ] Implementasi AuthInterceptor untuk token
-   [ ] Buat data classes untuk semua response
-   [ ] Implementasi Login & Register screen
-   [ ] Simpan token setelah login berhasil
-   [ ] Implementasi Dashboard screen dengan chart library (MPAndroidChart)
-   [ ] Implementasi History screen dengan RecyclerView pagination
-   [ ] Implementasi Settings screen untuk threshold
-   [ ] Implementasi Profile screen
-   [ ] Implementasi Logout functionality
-   [ ] Handle error responses & network exceptions
-   [ ] Testing semua endpoint dengan Postman
-   [ ] Testing integrasi dengan aplikasi Android

---

## **📞 Support**

Jika ada pertanyaan atau error, cek:

1. IP address server XAMPP sudah benar
2. XAMPP Apache & MySQL sudah running
3. Token masih valid (belum expired)
4. Internet/WiFi connection stabil
5. Firewall tidak memblokir port 80

**Good luck! 🚀**
