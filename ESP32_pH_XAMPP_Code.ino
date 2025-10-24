/* pH sensor only - manual linear caconst char* SERVER_URL = "http://192.168.56.1/monitoringikanball/monitoringikanball/public/api/sensor-data/store"; // Apache XAMPP (Port 80)ibration + EEPROM storage
   + Kirim data ke Laravel XAMPP API
   Untuk ESP32-S3 (gunakan pin ADC yang sudah kamu tes berfungsi)
   Gunakan 3.3V untuk VCC sensor.

   Perintah via Serial Monitor:
     - "save7"    → simpan tegangan saat pH 7
     - "save4"    → simpan tegangan saat pH 4
     - "showcal"  → tampilkan data kalibrasi
     - "clearcal" → hapus kalibrasi dari EEPROM
     - "sendnow"  → kirim data sekarang juga
     - "showip"   → tampilkan IP ESP32
*/

#include <EEPROM.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// === PIN CONFIGURATION ===
#define PH_PIN 4                // Pin ADC untuk sensor pH
#define VREF 3.3                // Tegangan referensi (3.3V)
#define ADC_RESOLUTION 4095.0   // 12-bit ADC

// === EEPROM CONFIGURATION ===
#define ADDR_SIGNATURE 0
#define ADDR_V7 4
#define ADDR_V4 8
#define ADDR_SLOPE 12
#define ADDR_INTER 16
#define EEPROM_SIZE 32

// === WIFI & SERVER CONFIGURATION ===
// ⚠️ PENTING: Ganti dengan konfigurasi Anda!
const char* WIFI_SSID = "POCO";           // Ganti dengan SSID WiFi Anda
const char* WIFI_PASSWORD = "12345678";       // Ganti dengan password WiFi Anda
const char* SERVER_URL = "http://10.31.188.8/monitoringikanball/monitoringikanball/public/api/sensor-data/store"; // Apache XAMPP (Port 80)
const int DEVICE_ID = 1;                      // ID device di database (sesuaikan dengan database Anda)

// === TIMING CONFIGURATION ===
const unsigned long SEND_INTERVAL = 30000;    // Kirim data tiap 30 detik
const unsigned long CALIBRATION_REMINDER = 300000; // Reminder kalibrasi tiap 5 menit

// === GLOBAL VARIABLES ===
float voltage = 0.0;
float phValue = NAN;
float V_pH7 = NAN;
float V_pH4 = NAN;
float slope = NAN;
float intercept = NAN;

uint32_t signatureMagic = 0x5048434C; // "PHCL"
unsigned long lastSendTime = 0;
unsigned long lastCalibrationReminder = 0;
int sendCount = 0;
int sendFailCount = 0;

// === EEPROM FUNCTIONS ===
void writeFloatToEEPROM(int addr, float val) {
  byte* p = (byte*)(void*)&val;
  for (int i = 0; i < 4; i++) {
    EEPROM.write(addr + i, p[i]);
  }
}

float readFloatFromEEPROM(int addr) {
  byte p[4];
  for (int i = 0; i < 4; i++) {
    p[i] = EEPROM.read(addr + i);
  }
  float val;
  memcpy(&val, p, 4);
  return val;
}

void saveCalibrationToEEPROM() {
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

bool loadCalibrationFromEEPROM() {
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

void clearCalibrationEEPROM() {
  for (int i = 0; i < EEPROM_SIZE; i++) {
    EEPROM.write(i, 0);
  }
  EEPROM.commit();
  V_pH7 = V_pH4 = slope = intercept = NAN;
  Serial.println("✅ Kalibrasi dihapus dari EEPROM.");
}

void computeLinearFromV7V4() {
  if (!isnan(V_pH7) && !isnan(V_pH4) && fabs(V_pH7 - V_pH4) > 1e-6) {
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

// === WIFI FUNCTIONS ===
void connectToWiFi() {
  Serial.println("\n🔌 Menghubungkan ke WiFi...");
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
    Serial.println("\n✅ WiFi tersambung!");
    Serial.print("   IP ESP32: ");
    Serial.println(WiFi.localIP());
    Serial.print("   Signal: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    Serial.println("\n❌ WiFi gagal tersambung!");
    Serial.println("   Periksa SSID dan password.");
  }
}

// === SEND DATA TO LARAVEL ===
bool sendDataToLaravel(float ph, float voltage) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("❌ WiFi tidak tersambung. Reconnecting...");
    connectToWiFi();
    if (WiFi.status() != WL_CONNECTED) {
      return false;
    }
  }

  HTTPClient http;
  
  // Buat JSON payload
  StaticJsonDocument<200> doc;
  doc["device_id"] = DEVICE_ID;
  doc["ph"] = String(ph, 2);
  doc["temperature"] = 26.5; // Real temperature (sesuai sensor)
  doc["oxygen"] = 6.8;       // Dummy oxygen (bisa tambah sensor DO nanti)  
  doc["voltage"] = String(voltage, 2); // ✅ VOLTAGE DITAMBAHKAN!
  
  String jsonPayload;
  serializeJson(doc, jsonPayload);
  
  // Kirim HTTP POST request
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  
  Serial.println("\n🌐 Mengirim data ke server...");
  Serial.print("   URL: ");
  Serial.println(SERVER_URL);
  Serial.print("   Payload: ");
  Serial.println(jsonPayload);
  
  int httpResponseCode = http.POST(jsonPayload);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("   Response Code: ");
    Serial.println(httpResponseCode);
    Serial.print("   Response: ");
    Serial.println(response);
    
    if (httpResponseCode == 201 || httpResponseCode == 200) {
      Serial.println("✅ Data berhasil dikirim!");
      sendCount++;
      http.end();
      return true;
    } else {
      Serial.println("⚠️  Server menolak data.");
      sendFailCount++;
      http.end();
      return false;
    }
  } else {
    Serial.print("❌ Gagal kirim data. Error: ");
    Serial.println(http.errorToString(httpResponseCode).c_str());
    sendFailCount++;
    http.end();
    return false;
  }
}

// === SETUP ===
void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n");
  Serial.println("╔════════════════════════════════════════════════╗");
  Serial.println("║     ESP32 pH Sensor - Laravel XAMPP v2.0      ║");
  Serial.println("╚════════════════════════════════════════════════╝");
  Serial.println();
  
  // Initialize ADC
  analogReadResolution(12);
  Serial.println("✅ ADC initialized (12-bit)");
  
  // Initialize EEPROM
  EEPROM.begin(EEPROM_SIZE);
  Serial.println("✅ EEPROM initialized");
  
  // Connect to WiFi
  connectToWiFi();
  
  // Load calibration
  Serial.println("\n📖 Memuat data kalibrasi...");
  if (loadCalibrationFromEEPROM()) {
    Serial.println("✅ Kalibrasi ditemukan di EEPROM:");
    Serial.print("   V_pH7 = "); Serial.print(V_pH7, 4); Serial.println(" V");
    Serial.print("   V_pH4 = "); Serial.print(V_pH4, 4); Serial.println(" V");
    Serial.print("   slope = "); Serial.println(slope, 6);
    Serial.print("   intercept = "); Serial.println(intercept, 6);
  } else {
    Serial.println("⚠️  Belum ada data kalibrasi.");
    Serial.println("   Celupkan sensor ke buffer pH 7, lalu ketik: save7");
    Serial.println("   Celupkan sensor ke buffer pH 4, lalu ketik: save4");
  }
  
  Serial.println("\n📋 Perintah tersedia:");
  Serial.println("   save7    - Simpan kalibrasi pH 7");
  Serial.println("   save4    - Simpan kalibrasi pH 4");
  Serial.println("   showcal  - Tampilkan data kalibrasi");
  Serial.println("   clearcal - Hapus kalibrasi");
  Serial.println("   sendnow  - Kirim data sekarang");
  Serial.println("   showip   - Tampilkan IP ESP32");
  Serial.println("\n" + String('=').repeat(50) + "\n");
  
  lastSendTime = millis();
  lastCalibrationReminder = millis();
}

// === MAIN LOOP ===
void loop() {
  // Read pH sensor (average 10 samples)
  long sum = 0;
  const int N = 10;
  for (int i = 0; i < N; i++) {
    sum += analogRead(PH_PIN);
    delay(5);
  }
  float raw = (float)sum / N;
  voltage = raw / ADC_RESOLUTION * VREF;

  // Calculate pH
  if (!isnan(slope) && !isnan(intercept)) {
    phValue = slope * voltage + intercept;
    if (phValue < 0) phValue = 0;
    if (phValue > 14) phValue = 14;
  } else {
    phValue = NAN;
  }

  // Display readings
  Serial.print("📊 Raw ADC: "); Serial.print((int)raw);
  Serial.print(" | V: "); Serial.print(voltage, 3); Serial.print("V");
  
  if (!isnan(phValue)) {
    Serial.print(" | pH: "); Serial.print(phValue, 2);
    
    // pH status indicator
    if (phValue >= 6.5 && phValue <= 8.5) {
      Serial.print(" ✅ Normal");
    } else if (phValue < 6.5) {
      Serial.print(" ⚠️  Asam");
    } else {
      Serial.print(" ⚠️  Basa");
    }
  } else {
    Serial.print(" | pH: (belum kalibrasi ⚠️)");
  }
  
  Serial.print(" | WiFi: ");
  Serial.print((WiFi.status() == WL_CONNECTED) ? "✅" : "❌");
  Serial.print(" | Send: "); Serial.print(sendCount);
  Serial.print(" | Fail: "); Serial.println(sendFailCount);

  // Auto send to server every SEND_INTERVAL
  if (!isnan(phValue) && (millis() - lastSendTime >= SEND_INTERVAL)) {
    sendDataToLaravel(phValue, voltage);
    lastSendTime = millis();
  }

  // Calibration reminder
  if (isnan(slope) && (millis() - lastCalibrationReminder >= CALIBRATION_REMINDER)) {
    Serial.println("\n⏰ REMINDER: Sensor belum dikalibrasi!");
    Serial.println("   Gunakan perintah: save7, save4");
    lastCalibrationReminder = millis();
  }

  // Handle serial commands
  if (Serial.available()) {
    String cmd = Serial.readStringUntil('\n');
    cmd.trim();
    cmd.toLowerCase();

    Serial.println("\n🔧 Perintah diterima: " + cmd);

    if (cmd == "save7") {
      V_pH7 = voltage;
      Serial.print("💾 Disimpan: V_pH7 = "); Serial.print(V_pH7, 4); Serial.println(" V");
      computeLinearFromV7V4();
    }
    else if (cmd == "save4") {
      V_pH4 = voltage;
      Serial.print("💾 Disimpan: V_pH4 = "); Serial.print(V_pH4, 4); Serial.println(" V");
      computeLinearFromV7V4();
    }
    else if (cmd == "showcal") {
      Serial.println("📖 Data Kalibrasi:");
      Serial.print("   V_pH7 = "); Serial.print(V_pH7, 4); Serial.println(" V");
      Serial.print("   V_pH4 = "); Serial.print(V_pH4, 4); Serial.println(" V");
      Serial.print("   slope = "); Serial.println(slope, 6);
      Serial.print("   intercept = "); Serial.println(intercept, 6);
      if (!isnan(slope)) {
        Serial.println("   Status: ✅ Terkalibrasi");
      } else {
        Serial.println("   Status: ⚠️  Belum terkalibrasi");
      }
    }
    else if (cmd == "clearcal") {
      clearCalibrationEEPROM();
    }
    else if (cmd == "sendnow") {
      if (!isnan(phValue)) {
        Serial.println("📤 Mengirim data manual...");
        if (sendDataToLaravel(phValue, voltage)) {
          Serial.println("✅ Berhasil!");
        } else {
          Serial.println("❌ Gagal!");
        }
      } else {
        Serial.println("⚠️  Tidak bisa kirim: sensor belum dikalibrasi");
      }
    }
    else if (cmd == "showip") {
      Serial.println("📡 Network Info:");
      Serial.print("   SSID: "); Serial.println(WiFi.SSID());
      Serial.print("   IP Address: "); Serial.println(WiFi.localIP());
      Serial.print("   Signal: "); Serial.print(WiFi.RSSI()); Serial.println(" dBm");
      Serial.print("   Server URL: "); Serial.println(SERVER_URL);
      Serial.print("   Device ID: "); Serial.println(DEVICE_ID);
    }
    else {
      Serial.println("❓ Perintah tidak dikenal.");
      Serial.println("   Gunakan: save7, save4, showcal, clearcal, sendnow, showip");
    }
    Serial.println();
  }

  delay(2000); // Update tiap 2 detik
}
