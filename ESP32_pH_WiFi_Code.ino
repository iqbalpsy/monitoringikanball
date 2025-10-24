/* pH sensor with WiFi - Send data to Laravel Server
   Untuk ESP32-S3 dengan kalibrasi manual + EEPROM + HTTP POST
   
   CARA SETUP:
   1. Ganti WIFI_SSID dan WIFI_PASSWORD dengan WiFi Anda
   2. Ganti SERVER_URL dengan IP komputer Anda (contoh: "http://192.168.1.100:8000")
   3. Upload code ke ESP32-S3
   4. Lakukan kalibrasi dengan "save7" dan "save4"
   5. Data otomatis terkirim setiap 30 detik

   Perintah via Serial Monitor:
     - "save7"   → simpan tegangan saat pH 7
     - "save4"   → simpan tegangan saat pH 4
     - "showcal" → tampilkan data kalibrasi
     - "clearcal"→ hapus kalibrasi dari EEPROM
     - "sendnow" → kirim data sekarang juga
     - "showip"  → tampilkan IP ESP32
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <EEPROM.h>

// ========== KONFIGURASI WIFI & SERVER ==========
#define WIFI_SSID "NAMA_WIFI_ANDA"        // 👈 GANTI INI!
#define WIFI_PASSWORD "PASSWORD_WIFI_ANDA" // 👈 GANTI INI!
#define SERVER_URL "http://192.168.1.100:8000/api/sensor-data/store" // 👈 GANTI IP INI!
#define DEVICE_ID 1  // ID device di database (sesuaikan dengan tabel devices)

// ========== KONFIGURASI pH SENSOR ==========
#define PH_PIN 4
#define VREF 3.3
#define ADC_RESOLUTION 4095.0

// ========== KONFIGURASI EEPROM ==========
#define ADDR_SIGNATURE 0
#define ADDR_V7 4
#define ADDR_V4 8
#define ADDR_SLOPE 12
#define ADDR_INTER 16
#define EEPROM_SIZE 32

// ========== KONFIGURASI TIMING ==========
#define SEND_INTERVAL 30000  // Kirim data setiap 30 detik
#define CALIBRATION_REMINDER 300000  // Reminder kalibrasi setiap 5 menit

// ========== VARIABEL GLOBAL ==========
float voltage = 0.0;
float phValue = NAN;
float V_pH7 = NAN;
float V_pH4 = NAN;
float slope = NAN;
float intercept = NAN;

unsigned long lastSendTime = 0;
unsigned long lastCalibrationReminder = 0;
uint32_t signatureMagic = 0x5048434C; // "PHCL"

bool wifiConnected = false;
int sendCount = 0;
int sendFailCount = 0;

// ========== FUNGSI EEPROM ==========
void writeFloatToEEPROM(int addr, float val){
  byte *p = (byte*)(void*)&val;
  for (int i=0; i<4; i++) EEPROM.write(addr + i, p[i]);
}

float readFloatFromEEPROM(int addr){
  byte p[4];
  for (int i=0; i<4; i++) p[i] = EEPROM.read(addr + i);
  float val;
  memcpy(&val, p, 4);
  return val;
}

void saveCalibrationToEEPROM(){
  EEPROM.write(ADDR_SIGNATURE + 0, (byte)(signatureMagic & 0xFF));
  EEPROM.write(ADDR_SIGNATURE + 1, (byte)((signatureMagic >> 8) & 0xFF));
  EEPROM.write(ADDR_SIGNATURE + 2, (byte)((signatureMagic >> 16) & 0xFF));
  EEPROM.write(ADDR_SIGNATURE + 3, (byte)((signatureMagic >> 24) & 0xFF));
  writeFloatToEEPROM(ADDR_V7, V_pH7);
  writeFloatToEEPROM(ADDR_V4, V_pH4);
  writeFloatToEEPROM(ADDR_SLOPE, slope);
  writeFloatToEEPROM(ADDR_INTER, intercept);
  EEPROM.commit();
}

bool loadCalibrationFromEEPROM(){
  uint32_t sig = 0;
  sig |= ((uint32_t)EEPROM.read(ADDR_SIGNATURE + 0));
  sig |= ((uint32_t)EEPROM.read(ADDR_SIGNATURE + 1)) << 8;
  sig |= ((uint32_t)EEPROM.read(ADDR_SIGNATURE + 2)) << 16;
  sig |= ((uint32_t)EEPROM.read(ADDR_SIGNATURE + 3)) << 24;
  if (sig != signatureMagic) return false;

  V_pH7 = readFloatFromEEPROM(ADDR_V7);
  V_pH4 = readFloatFromEEPROM(ADDR_V4);
  slope = readFloatFromEEPROM(ADDR_SLOPE);
  intercept = readFloatFromEEPROM(ADDR_INTER);
  return true;
}

void clearCalibrationEEPROM(){
  for (int i=0; i<EEPROM_SIZE; i++) EEPROM.write(i, 0);
  EEPROM.commit();
  V_pH7 = V_pH4 = slope = intercept = NAN;
  Serial.println("✅ Kalibrasi dihapus dari EEPROM.");
}

void computeLinearFromV7V4(){
  if (!isnan(V_pH7) && !isnan(V_pH4) && fabs(V_pH7 - V_pH4) > 1e-6){
    slope = (7.0 - 4.0) / (V_pH7 - V_pH4);
    intercept = 7.0 - slope * V_pH7;
    Serial.println("📊 Perhitungan kalibrasi:");
    Serial.print("   slope = "); Serial.println(slope, 6);
    Serial.print("   intercept = "); Serial.println(intercept, 6);
    saveCalibrationToEEPROM();
    Serial.println("💾 Kalibrasi disimpan ke EEPROM.");
  } else {
    Serial.println("⚠️  Tidak bisa menghitung slope: butuh nilai V7 dan V4 yang berbeda.");
  }
}

// ========== FUNGSI WIFI ==========
void connectWiFi(){
  Serial.println("\n📡 Menghubungkan ke WiFi...");
  Serial.print("   SSID: ");
  Serial.println(WIFI_SSID);
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    wifiConnected = true;
    Serial.println("\n✅ WiFi Terhubung!");
    Serial.print("   IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("   Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    wifiConnected = false;
    Serial.println("\n❌ WiFi Gagal Terhubung!");
    Serial.println("   Periksa SSID dan Password!");
  }
}

void checkWiFiConnection(){
  if (WiFi.status() != WL_CONNECTED) {
    if (wifiConnected) {
      Serial.println("⚠️  WiFi terputus! Mencoba reconnect...");
      wifiConnected = false;
    }
    connectWiFi();
  }
}

// ========== FUNGSI HTTP POST ==========
bool sendDataToServer(float pH, float volt){
  if (!wifiConnected) {
    Serial.println("❌ Tidak bisa kirim data: WiFi tidak terhubung");
    return false;
  }

  HTTPClient http;
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  
  // Buat JSON payload
  String jsonPayload = "{";
  jsonPayload += "\"device_id\":" + String(DEVICE_ID) + ",";
  jsonPayload += "\"ph\":" + String(pH, 2) + ",";
  jsonPayload += "\"temperature\":27.5,"; // Default temperature (bisa ditambah sensor suhu)
  jsonPayload += "\"oxygen\":6.8";        // Default oxygen (bisa ditambah sensor oksigen)
  jsonPayload += "}";
  
  Serial.println("\n📤 Mengirim data ke server...");
  Serial.println("   URL: " + String(SERVER_URL));
  Serial.println("   Payload: " + jsonPayload);
  
  int httpResponseCode = http.POST(jsonPayload);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.println("✅ Data terkirim!");
    Serial.print("   Response Code: ");
    Serial.println(httpResponseCode);
    Serial.print("   Response: ");
    Serial.println(response);
    
    sendCount++;
    sendFailCount = 0; // Reset fail counter
    return true;
  } else {
    Serial.println("❌ Gagal mengirim data!");
    Serial.print("   Error Code: ");
    Serial.println(httpResponseCode);
    Serial.print("   Error: ");
    Serial.println(http.errorToString(httpResponseCode));
    
    sendFailCount++;
    return false;
  }
  
  http.end();
}

// ========== FUNGSI pH SENSOR ==========
void readpHSensor(){
  long sum = 0;
  const int N = 10;
  for (int i=0; i<N; i++){
    sum += analogRead(PH_PIN);
    delay(5);
  }
  float raw = (float)sum / N;
  voltage = raw / ADC_RESOLUTION * VREF;

  if (!isnan(slope) && !isnan(intercept)){
    phValue = slope * voltage + intercept;
    if (phValue < 0) phValue = 0;
    if (phValue > 14) phValue = 14;
  } else {
    phValue = NAN;
  }
}

void displaySensorData(){
  Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("📊 DATA SENSOR");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.print("   Voltage: ");
  Serial.print(voltage, 3);
  Serial.println(" V");
  
  if (!isnan(phValue)){
    Serial.print("   pH: ");
    Serial.println(phValue, 2);
    
    // Status pH
    if (phValue >= 6.5 && phValue <= 8.5){
      Serial.println("   Status: ✅ NORMAL");
    } else if (phValue < 6.5){
      Serial.println("   Status: ⚠️  ASAM (pH terlalu rendah)");
    } else {
      Serial.println("   Status: ⚠️  BASA (pH terlalu tinggi)");
    }
  } else {
    Serial.println("   pH: ❌ (Belum dikalibrasi)");
    Serial.println("   Status: ⚠️  Harap lakukan kalibrasi!");
  }
  
  Serial.print("   WiFi: ");
  Serial.println(wifiConnected ? "✅ Terhubung" : "❌ Terputus");
  Serial.print("   Data Terkirim: ");
  Serial.print(sendCount);
  Serial.println(" kali");
  Serial.print("   Gagal Kirim: ");
  Serial.print(sendFailCount);
  Serial.println(" kali");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
}

// ========== FUNGSI PERINTAH SERIAL ==========
void handleSerialCommand(){
  if (Serial.available()){
    String cmd = Serial.readStringUntil('\n');
    cmd.trim();
    cmd.toLowerCase();

    Serial.println("\n📝 Perintah diterima: " + cmd);

    if (cmd == "save7"){
      V_pH7 = voltage;
      Serial.print("💾 Disimpan: V_pH7 = ");
      Serial.println(V_pH7, 4);
      computeLinearFromV7V4();
    }
    else if (cmd == "save4"){
      V_pH4 = voltage;
      Serial.print("💾 Disimpan: V_pH4 = ");
      Serial.println(V_pH4, 4);
      computeLinearFromV7V4();
    }
    else if (cmd == "showcal"){
      Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
      Serial.println("📖 DATA KALIBRASI");
      Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
      Serial.print("   V_pH7 = ");
      Serial.println(isnan(V_pH7) ? "Belum diset" : String(V_pH7, 4));
      Serial.print("   V_pH4 = ");
      Serial.println(isnan(V_pH4) ? "Belum diset" : String(V_pH4, 4));
      Serial.print("   slope = ");
      Serial.println(isnan(slope) ? "Belum dihitung" : String(slope, 6));
      Serial.print("   intercept = ");
      Serial.println(isnan(intercept) ? "Belum dihitung" : String(intercept, 6));
      Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
    }
    else if (cmd == "clearcal"){
      clearCalibrationEEPROM();
    }
    else if (cmd == "sendnow"){
      if (!isnan(phValue)){
        Serial.println("📤 Mengirim data sekarang...");
        sendDataToServer(phValue, voltage);
      } else {
        Serial.println("❌ Tidak bisa kirim: pH belum dikalibrasi!");
      }
    }
    else if (cmd == "showip"){
      Serial.println("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
      Serial.println("📡 INFORMASI JARINGAN");
      Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
      Serial.print("   SSID: ");
      Serial.println(WIFI_SSID);
      Serial.print("   Status: ");
      Serial.println(wifiConnected ? "✅ Terhubung" : "❌ Terputus");
      if (wifiConnected){
        Serial.print("   IP Address: ");
        Serial.println(WiFi.localIP());
        Serial.print("   Signal: ");
        Serial.print(WiFi.RSSI());
        Serial.println(" dBm");
      }
      Serial.print("   Server URL: ");
      Serial.println(SERVER_URL);
      Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
    }
    else {
      Serial.println("❓ Perintah tidak dikenal!");
      Serial.println("\n📋 Perintah yang tersedia:");
      Serial.println("   save7    → Simpan kalibrasi pH 7");
      Serial.println("   save4    → Simpan kalibrasi pH 4");
      Serial.println("   showcal  → Tampilkan data kalibrasi");
      Serial.println("   clearcal → Hapus kalibrasi");
      Serial.println("   sendnow  → Kirim data sekarang");
      Serial.println("   showip   → Tampilkan info jaringan");
    }
  }
}

// ========== SETUP ==========
void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n\n");
  Serial.println("╔════════════════════════════════════════╗");
  Serial.println("║   SENSOR pH IoT - ESP32-S3             ║");
  Serial.println("║   Monitoring Kualitas Air Kolam Ikan   ║");
  Serial.println("╚════════════════════════════════════════╝\n");
  
  // Setup ADC
  analogReadResolution(12);
  Serial.println("✅ ADC Resolution: 12-bit");
  
  // Setup EEPROM
  EEPROM.begin(EEPROM_SIZE);
  Serial.println("✅ EEPROM Initialized");
  
  // Load kalibrasi dari EEPROM
  if (loadCalibrationFromEEPROM()){
    Serial.println("\n✅ Kalibrasi ditemukan di EEPROM:");
    Serial.print("   V_pH7 = "); Serial.println(V_pH7, 4);
    Serial.print("   V_pH4 = "); Serial.println(V_pH4, 4);
    Serial.print("   slope = "); Serial.println(slope, 6);
    Serial.print("   intercept = "); Serial.println(intercept, 6);
  } else {
    Serial.println("\n⚠️  Belum ada data kalibrasi!");
    Serial.println("   Langkah kalibrasi:");
    Serial.println("   1. Celupkan sensor ke buffer pH 7");
    Serial.println("   2. Tunggu stabil, ketik: save7");
    Serial.println("   3. Celupkan sensor ke buffer pH 4");
    Serial.println("   4. Tunggu stabil, ketik: save4");
  }
  
  // Connect WiFi
  connectWiFi();
  
  Serial.println("\n📋 Perintah tersedia:");
  Serial.println("   save7, save4, showcal, clearcal, sendnow, showip");
  Serial.println("\n🚀 System Ready!\n");
}

// ========== LOOP ==========
void loop() {
  unsigned long currentTime = millis();
  
  // Check WiFi connection
  checkWiFiConnection();
  
  // Read pH sensor
  readpHSensor();
  
  // Display sensor data
  displaySensorData();
  
  // Auto send data every SEND_INTERVAL
  if (currentTime - lastSendTime >= SEND_INTERVAL) {
    lastSendTime = currentTime;
    
    if (!isnan(phValue)) {
      sendDataToServer(phValue, voltage);
    } else {
      Serial.println("⚠️  Skipping send: pH belum dikalibrasi");
    }
  }
  
  // Calibration reminder
  if (isnan(slope) || isnan(intercept)) {
    if (currentTime - lastCalibrationReminder >= CALIBRATION_REMINDER) {
      lastCalibrationReminder = currentTime;
      Serial.println("\n⏰ REMINDER: Harap lakukan kalibrasi sensor!");
      Serial.println("   Gunakan perintah: save7 dan save4\n");
    }
  }
  
  // Handle serial commands
  handleSerialCommand();
  
  // Delay before next reading
  delay(1000);
}
