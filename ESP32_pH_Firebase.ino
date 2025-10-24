/* ESP32 pH Sensor with Firebase Realtime Database
   Untuk ESP32-S3 dengan sensor pH analog
   Data dikirim ke Firebase Realtime Database, bukan ke Laravel direct

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

// === WIFI CONFIGURATION ===
const char* WIFI_SSID = "POCO";           // Ganti dengan SSID WiFi Anda
const char* WIFI_PASSWORD = "12345678";   // Ganti dengan password WiFi Anda

// === FIREBASE CONFIGURATION ===
// ✅ Updated dengan Firebase project: container-kolam
const char* FIREBASE_HOST = "container-kolam-default-rtdb.firebaseio.com"; // Tanpa https://
const char* FIREBASE_AUTH = "";  // Kosongkan untuk testing mode (public write rules)
const int DEVICE_ID = 1;                  // ID device Anda

// Construct Firebase URL - Updated untuk struktur sensor_data/device_X
String firebaseUrl;  // Will be constructed in setup()

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

unsigned long lastSendTime = 0;
unsigned long lastReminderTime = 0;
bool wifiConnected = false;

// Signature untuk validasi EEPROM
const uint32_t EEPROM_SIGNATURE = 0x5048434C; // "PHCL" in hex

// === FUNCTION PROTOTYPES ===
void connectWiFi();
void loadCalibration();
void saveCalibration();
void clearCalibration();
float readVoltage();
float calculatePh(float v);
void sendToFirebase();
void handleSerialCommands();

void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n\n===============================================");
  Serial.println("      ESP32 pH Sensor - Firebase Ready");
  Serial.println("===============================================");
  
  // Initialize EEPROM
  EEPROM.begin(EEPROM_SIZE);
  
  // Load calibration from EEPROM
  loadCalibration();
  
  // Connect to WiFi
  connectWiFi();
  
  // Construct Firebase URL berdasarkan auth setting
  if (strlen(FIREBASE_AUTH) > 0) {
    // With authentication
    firebaseUrl = "https://" + String(FIREBASE_HOST) + "/sensor_data/device_" + String(DEVICE_ID) + ".json?auth=" + String(FIREBASE_AUTH);
  } else {
    // Testing mode without auth
    firebaseUrl = "https://" + String(FIREBASE_HOST) + "/sensor_data/device_" + String(DEVICE_ID) + ".json";
  }
  
  Serial.println("\n[FIREBASE] Configuration:");
  Serial.print("  Host: "); Serial.println(FIREBASE_HOST);
  Serial.print("  Auth: "); Serial.println(strlen(FIREBASE_AUTH) > 0 ? "Enabled" : "Testing Mode");
  Serial.print("  Device ID: "); Serial.println(DEVICE_ID);
  Serial.print("  URL: "); Serial.println(firebaseUrl);
  
  Serial.println("\n===============================================");
  Serial.println("          Monitoring Started");
  Serial.println("===============================================");
  Serial.println("Commands: save7, save4, showcal, clearcal, sendnow, showip");
  Serial.println("===============================================\n");
}

void loop() {
  // Read voltage from pH sensor
  voltage = readVoltage();
  
  // Calculate pH value if calibrated
  if (!isnan(slope) && !isnan(intercept)) {
    phValue = calculatePh(voltage);
    
    Serial.print("Voltage: ");
    Serial.print(voltage, 3);
    Serial.print(" V | pH: ");
    Serial.println(phValue, 2);
  } else {
    Serial.print("Voltage: ");
    Serial.print(voltage, 3);
    Serial.println(" V | pH: NOT CALIBRATED");
    
    // Reminder to calibrate
    if (millis() - lastReminderTime > CALIBRATION_REMINDER) {
      Serial.println("\n⚠️  REMINDER: Please calibrate the sensor!");
      Serial.println("   1. Put sensor in pH 7 buffer → type 'save7'");
      Serial.println("   2. Put sensor in pH 4 buffer → type 'save4'\n");
      lastReminderTime = millis();
    }
  }
  
  // Auto-send to Firebase every SEND_INTERVAL
  if (wifiConnected && millis() - lastSendTime > SEND_INTERVAL) {
    if (!isnan(phValue)) {
      sendToFirebase();
      lastSendTime = millis();
    } else {
      Serial.println("❌ Cannot send: Sensor not calibrated yet");
      lastSendTime = millis();
    }
  }
  
  // Handle serial commands
  handleSerialCommands();
  
  delay(2000); // Read every 2 seconds
}

// ==========================================
// WiFi Connection
// ==========================================
void connectWiFi() {
  Serial.println("\n🌐 Connecting to WiFi...");
  Serial.print("   SSID: ");
  Serial.println(WIFI_SSID);
  
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    wifiConnected = true;
    Serial.println("\n✅ WiFi Connected!");
    Serial.print("   IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("   Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    wifiConnected = false;
    Serial.println("\n❌ WiFi Connection Failed!");
    Serial.println("   Please check your SSID and password");
  }
}

// ==========================================
// EEPROM Calibration Management
// ==========================================
void loadCalibration() {
  Serial.println("\n📂 Loading calibration from EEPROM...");
  
  uint32_t signature;
  EEPROM.get(ADDR_SIGNATURE, signature);
  
  if (signature == EEPROM_SIGNATURE) {
    EEPROM.get(ADDR_V7, V_pH7);
    EEPROM.get(ADDR_V4, V_pH4);
    EEPROM.get(ADDR_SLOPE, slope);
    EEPROM.get(ADDR_INTER, intercept);
    
    Serial.println("✅ Calibration data found:");
    Serial.print("   V(pH7) = ");
    Serial.print(V_pH7, 3);
    Serial.println(" V");
    Serial.print("   V(pH4) = ");
    Serial.print(V_pH4, 3);
    Serial.println(" V");
    Serial.print("   Slope = ");
    Serial.println(slope, 4);
    Serial.print("   Intercept = ");
    Serial.println(intercept, 4);
  } else {
    Serial.println("⚠️  No calibration data found");
    Serial.println("   Please calibrate the sensor first");
  }
}

void saveCalibration() {
  if (!isnan(V_pH7) && !isnan(V_pH4)) {
    // Calculate slope and intercept: pH = slope * voltage + intercept
    // Two points: (V_pH7, 7.0) and (V_pH4, 4.0)
    slope = (4.0 - 7.0) / (V_pH4 - V_pH7);
    intercept = 7.0 - slope * V_pH7;
    
    // Save to EEPROM
    EEPROM.put(ADDR_SIGNATURE, EEPROM_SIGNATURE);
    EEPROM.put(ADDR_V7, V_pH7);
    EEPROM.put(ADDR_V4, V_pH4);
    EEPROM.put(ADDR_SLOPE, slope);
    EEPROM.put(ADDR_INTER, intercept);
    EEPROM.commit();
    
    Serial.println("\n✅ Calibration saved to EEPROM!");
    Serial.print("   Slope = ");
    Serial.println(slope, 4);
    Serial.print("   Intercept = ");
    Serial.println(intercept, 4);
    Serial.println("   Sensor is now calibrated and ready to use");
  } else {
    Serial.println("\n❌ Cannot save: Need both pH 7 and pH 4 calibration points");
  }
}

void clearCalibration() {
  EEPROM.put(ADDR_SIGNATURE, 0);
  EEPROM.commit();
  
  V_pH7 = NAN;
  V_pH4 = NAN;
  slope = NAN;
  intercept = NAN;
  
  Serial.println("\n✅ Calibration cleared from EEPROM");
}

// ==========================================
// Sensor Reading
// ==========================================
float readVoltage() {
  // Read analog value and convert to voltage
  int rawValue = analogRead(PH_PIN);
  float v = (rawValue / ADC_RESOLUTION) * VREF;
  return v;
}

float calculatePh(float v) {
  if (isnan(slope) || isnan(intercept)) {
    return NAN;
  }
  return slope * v + intercept;
}

// ==========================================
// Send Data to Firebase
// ==========================================
void sendToFirebase() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("❌ WiFi not connected. Reconnecting...");
    connectWiFi();
    return;
  }
  
  Serial.println("\n📤 Sending data to Firebase...");
  
  HTTPClient http;
  
  // Start HTTP connection to Firebase
  http.begin(firebaseUrl);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload dengan Unix timestamp
  StaticJsonDocument<256> doc;
  doc["device_id"] = DEVICE_ID;
  doc["ph"] = round(phValue * 100) / 100.0;  // Round to 2 decimal places
  doc["temperature"] = 27.5;  // Dummy value (bisa ditambah sensor DS18B20)
  doc["oxygen"] = 6.8;        // Dummy value (bisa ditambah sensor DO)
  doc["voltage"] = round(voltage * 1000) / 1000.0;  // Round to 3 decimal places
  
  // Generate Unix timestamp in milliseconds (simulasi)
  // Dalam implementasi real, bisa menggunakan NTP client untuk get real timestamp
  unsigned long currentTimestamp = millis() + 1729587234000UL;  // Base timestamp + millis
  doc["timestamp"] = currentTimestamp;
  
  String jsonData;
  serializeJson(doc, jsonData);
  
  Serial.println("   Data:");
  Serial.println("   " + jsonData);
  
  // Send PUT request to Firebase (required for Realtime Database)
  int httpResponseCode = http.PUT(jsonData);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("   ✅ HTTP Response code: ");
    Serial.println(httpResponseCode);
    Serial.print("   Response: ");
    Serial.println(response);
    
    if (httpResponseCode == 200) {
      Serial.println("   ✅ Data successfully sent to Firebase!");
    }
  } else {
    Serial.print("   ❌ Error sending data: ");
    Serial.println(http.errorToString(httpResponseCode));
  }
  
  http.end();
}

// ==========================================
// Serial Command Handler
// ==========================================
void handleSerialCommands() {
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    command.trim();
    command.toLowerCase();
    
    if (command == "save7") {
      V_pH7 = readVoltage();
      Serial.println("\n✅ pH 7 calibration point saved");
      Serial.print("   Voltage: ");
      Serial.print(V_pH7, 3);
      Serial.println(" V");
      
      if (!isnan(V_pH4)) {
        saveCalibration();
      } else {
        Serial.println("   ⚠️  Now calibrate pH 4 point (type 'save4')");
      }
    }
    else if (command == "save4") {
      V_pH4 = readVoltage();
      Serial.println("\n✅ pH 4 calibration point saved");
      Serial.print("   Voltage: ");
      Serial.print(V_pH4, 3);
      Serial.println(" V");
      
      if (!isnan(V_pH7)) {
        saveCalibration();
      } else {
        Serial.println("   ⚠️  Now calibrate pH 7 point (type 'save7')");
      }
    }
    else if (command == "showcal") {
      Serial.println("\n📊 Current Calibration:");
      if (!isnan(V_pH7)) {
        Serial.print("   V(pH7) = ");
        Serial.print(V_pH7, 3);
        Serial.println(" V");
      } else {
        Serial.println("   V(pH7) = NOT SET");
      }
      
      if (!isnan(V_pH4)) {
        Serial.print("   V(pH4) = ");
        Serial.print(V_pH4, 3);
        Serial.println(" V");
      } else {
        Serial.println("   V(pH4) = NOT SET");
      }
      
      if (!isnan(slope)) {
        Serial.print("   Slope = ");
        Serial.println(slope, 4);
        Serial.print("   Intercept = ");
        Serial.println(intercept, 4);
      } else {
        Serial.println("   Slope/Intercept = NOT CALCULATED");
      }
    }
    else if (command == "clearcal") {
      clearCalibration();
    }
    else if (command == "sendnow") {
      if (!isnan(phValue)) {
        sendToFirebase();
      } else {
        Serial.println("\n❌ Cannot send: Sensor not calibrated yet");
      }
    }
    else if (command == "showip") {
      if (wifiConnected) {
        Serial.println("\n📡 Network Information:");
        Serial.print("   IP Address: ");
        Serial.println(WiFi.localIP());
        Serial.print("   Signal Strength: ");
        Serial.print(WiFi.RSSI());
        Serial.println(" dBm");
        Serial.print("   Firebase Host: ");
        Serial.println(FIREBASE_HOST);
      } else {
        Serial.println("\n❌ WiFi not connected");
      }
    }
    else {
      Serial.println("\n❌ Unknown command: " + command);
      Serial.println("   Available commands:");
      Serial.println("   - save7    : Save pH 7 calibration point");
      Serial.println("   - save4    : Save pH 4 calibration point");
      Serial.println("   - showcal  : Show current calibration");
      Serial.println("   - clearcal : Clear calibration");
      Serial.println("   - sendnow  : Send data to Firebase immediately");
      Serial.println("   - showip   : Show network information");
    }
  }
}
