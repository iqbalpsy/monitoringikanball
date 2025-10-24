#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <EEPROM.h>

// WiFi Configuration
const char* ssid = "POCO";
const char* password = "12345678";

// Server Configuration 
const char* serverURL = "http://10.31.188.8/monitoringikanball/monitoringikanball/public/api/sensor-data/store"; // Change to your server IP
const char* firebaseURL = "https://container-kolam-default-rtdb.firebaseio.com/sensor_data/device_1.json";

// Device Configuration
const int DEVICE_ID = 1;
const unsigned long SEND_INTERVAL = 30000; // Send data every 30 seconds

// pH Sensor Configuration (based on your calibration code)
#define PH_PIN 4
#define VREF 3.3
#define ADC_RESOLUTION 4095.0

// EEPROM addresses for pH calibration
#define ADDR_SIGNATURE 0
#define ADDR_V7 4
#define ADDR_V4 8
#define ADDR_SLOPE 12
#define ADDR_INTER 16
#define EEPROM_SIZE 32

// Calibration variables
float voltage = 0.0;
float phValue = NAN;
float V_pH7 = NAN;
float V_pH4 = NAN;
float slope = NAN;
float intercept = NAN;
uint32_t signatureMagic = 0x5048434C; // "PHCL"

// Sensor Pins for additional sensors
const int TEMP_PIN = A1;
const int OXYGEN_PIN = A2;

// Variables
unsigned long lastSendTime = 0;
bool wifiConnected = false;

void setup() {
  Serial.begin(115200);
  Serial.println("\n🐟 Fish Pond Monitoring System - IoT Integration with Real pH Sensor");
  Serial.println("=" + String("=").repeat(60));
  
  // Initialize ADC resolution for ESP32
  analogReadResolution(12); // ESP32 ADC 12-bit
  
  // Initialize EEPROM
  EEPROM.begin(EEPROM_SIZE);
  
  // Initialize pins
  pinMode(LED_BUILTIN, OUTPUT);
  
  // Load pH calibration from EEPROM
  loadCalibrationFromEEPROM();
  
  // Connect to WiFi
  connectToWiFi();
  
  Serial.println("✅ Setup complete! Commands: sendnow, status, test, save7, save4, showcal, clearcal");
  Serial.println("📡 Auto-sending every " + String(SEND_INTERVAL/1000) + " seconds");
  Serial.println("🧪 pH sensor ready - calibrate with 'save7' in pH 7.0 and 'save4' in pH 4.0");
}

void loop() {
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    wifiConnected = false;
    digitalWrite(LED_BUILTIN, LOW);
    Serial.println("❌ WiFi disconnected, attempting reconnection...");
    connectToWiFi();
    return;
  }
  
  wifiConnected = true;
  digitalWrite(LED_BUILTIN, HIGH);
  
  // Handle serial commands
  if (Serial.available()) {
    String command = Serial.readString();
    command.trim();
    command.toLowerCase();
    
    if (command == "sendnow") {
      Serial.println("\n🚀 Manual data send triggered");
      sendSensorData();
    } else if (command == "status") {
      printSystemStatus();
    } else if (command == "test") {
      testConnections();
    } else if (command == "save7") {
      V_pH7 = readpHVoltage();
      Serial.println("💾 Saved V_pH7 = " + String(V_pH7, 4));
      computeLinearFromV7V4();
    } else if (command == "save4") {
      V_pH4 = readpHVoltage();
      Serial.println("💾 Saved V_pH4 = " + String(V_pH4, 4));
      computeLinearFromV7V4();
    } else if (command == "showcal") {
      Serial.println("📖 Calibration Data:");
      Serial.println("  V_pH7 = " + String(V_pH7, 4));
      Serial.println("  V_pH4 = " + String(V_pH4, 4));
      Serial.println("  slope = " + String(slope, 6));
      Serial.println("  intercept = " + String(intercept, 6));
    } else if (command == "clearcal") {
      clearCalibrationEEPROM();
    }
  }
  
  // Auto send data at intervals
  if (millis() - lastSendTime >= SEND_INTERVAL) {
    sendSensorData();
    lastSendTime = millis();
  }
  
  delay(1000);
}

void connectToWiFi() {
  Serial.println("🌐 Connecting to WiFi: " + String(ssid));
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi connected!");
    Serial.println("📍 IP Address: " + WiFi.localIP().toString());
    Serial.println("📶 Signal Strength: " + String(WiFi.RSSI()) + " dBm");
    wifiConnected = true;
  } else {
    Serial.println("\n❌ WiFi connection failed!");
    wifiConnected = false;
  }
}

void sendSensorData() {
  if (!wifiConnected) {
    Serial.println("❌ Cannot send data - WiFi not connected");
    return;
  }
  
  // Read sensor values
  SensorData data = readSensors();
  
  Serial.println("\n📊 Sensor Reading:");
  Serial.println("  🌡️  Temperature: " + String(data.temperature, 2) + " °C");
  Serial.println("  🧪 pH Level: " + String(data.ph, 2));
  Serial.println("  💨 Oxygen: " + String(data.oxygen, 2) + " mg/L");
  Serial.println("  ⚡ Voltage: " + String(data.voltage, 2) + " V");
  
  // Send to local database first (primary)
  bool localSuccess = sendToLocalDatabase(data);
  
  // Send to Firebase as backup
  bool firebaseSuccess = sendToFirebase(data);
  
  // Status summary
  Serial.println("\n📤 Data Transmission Summary:");
  Serial.println("  🏠 Local Database: " + String(localSuccess ? "✅ SUCCESS" : "❌ FAILED"));
  Serial.println("  🔥 Firebase: " + String(firebaseSuccess ? "✅ SUCCESS" : "❌ FAILED"));
  
  if (localSuccess || firebaseSuccess) {
    Serial.println("✅ Data sent successfully!");
    blinkLED(3, 200); // Success blink
  } else {
    Serial.println("❌ All transmissions failed!");
    blinkLED(5, 100); // Error blink
  }
}

struct SensorData {
  float temperature;
  float ph;
  float oxygen;
  float voltage;
  unsigned long timestamp;
};

SensorData readSensors() {
  SensorData data;
  
  // Read actual pH sensor value
  voltage = readpHVoltage();
  
  // Calculate pH using calibration
  if (!isnan(slope) && !isnan(intercept)) {
    phValue = slope * voltage + intercept;
    if (phValue < 0) phValue = 0;
    if (phValue > 14) phValue = 14;
  } else {
    phValue = NAN; // Mark as invalid if not calibrated
  }
  
  // Use actual pH reading
  data.ph = phValue;
  data.voltage = voltage;
  
  // Simulate temperature and oxygen (replace with actual sensors if available)
  data.temperature = 25.0 + (random(-30, 50) / 10.0); // 22-28°C range
  data.oxygen = 6.5 + (random(-15, 15) / 10.0); // 5.0-8.0 mg/L range
  data.timestamp = WiFi.getTime();
  
  return data;
}

float readpHVoltage() {
  // Average multiple readings for stability
  long sum = 0;
  const int samples = 10;
  for (int i = 0; i < samples; i++) {
    sum += analogRead(PH_PIN);
    delay(5);
  }
  float raw = (float)sum / samples;
  return raw / ADC_RESOLUTION * VREF;
}

// EEPROM functions for pH calibration
void saveCalibrationToEEPROM() {
  Serial.println("💾 Saving calibration to EEPROM...");
  EEPROM.put(ADDR_SIGNATURE, signatureMagic);
  EEPROM.put(ADDR_V7, V_pH7);
  EEPROM.put(ADDR_V4, V_pH4);
  EEPROM.put(ADDR_SLOPE, slope);
  EEPROM.put(ADDR_INTER, intercept);
  
  if (EEPROM.commit()) {
    Serial.println("💾 Successfully saved to EEPROM.");
  } else {
    Serial.println("❌ FAILED to save to EEPROM.");
  }
}

bool loadCalibrationFromEEPROM() {
  uint32_t sig;
  EEPROM.get(ADDR_SIGNATURE, sig);

  if (sig != signatureMagic) {
    Serial.println("⚠️  No calibration found in EEPROM. Use 'save7' and 'save4' commands.");
    return false;
  }

  EEPROM.get(ADDR_V7, V_pH7);
  EEPROM.get(ADDR_V4, V_pH4);
  EEPROM.get(ADDR_SLOPE, slope);
  EEPROM.get(ADDR_INTER, intercept);
  
  Serial.println("✅ Calibration loaded from EEPROM:");
  Serial.println("  V_pH7 = " + String(V_pH7, 4));
  Serial.println("  V_pH4 = " + String(V_pH4, 4));
  Serial.println("  slope = " + String(slope, 6));
  Serial.println("  intercept = " + String(intercept, 6));
  
  return true;
}

void clearCalibrationEEPROM() {
  for (int i = 0; i < EEPROM_SIZE; i++) {
    EEPROM.write(i, 0);
  }
  EEPROM.commit();
  V_pH7 = V_pH4 = slope = intercept = NAN;
  Serial.println("✅ Calibration cleared from EEPROM.");
}

void computeLinearFromV7V4() {
  if (!isnan(V_pH7) && !isnan(V_pH4) && fabs(V_pH7 - V_pH4) > 1e-6) {
    slope = (7.0 - 4.0) / (V_pH7 - V_pH4);
    intercept = 7.0 - slope * V_pH7;
    Serial.println("📊 Calibration computed:");
    Serial.println("  slope = " + String(slope, 6));
    Serial.println("  intercept = " + String(intercept, 6));
    saveCalibrationToEEPROM();
  } else {
    Serial.println("⚠️  Cannot compute slope: need different V7 and V4 values.");
  }
}

bool sendToLocalDatabase(SensorData data) {
  if (!wifiConnected) return false;
  
  Serial.println("📡 Sending to Local Database...");
  
  HTTPClient http;
  http.begin(serverURL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("User-Agent", "ESP32-SensorDevice");
  
  // Create JSON payload
  StaticJsonDocument<200> doc;
  doc["device_id"] = DEVICE_ID;
  doc["temperature"] = round(data.temperature * 100) / 100.0;
  doc["ph"] = round(data.ph * 100) / 100.0;
  doc["oxygen"] = round(data.oxygen * 100) / 100.0;
  doc["voltage"] = round(data.voltage * 100) / 100.0;
  doc["timestamp"] = data.timestamp;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.println("📦 Payload: " + jsonString);
  
  int httpResponseCode = http.POST(jsonString);
  String response = http.getString();
  
  Serial.println("📨 Response Code: " + String(httpResponseCode));
  Serial.println("📄 Response: " + response);
  
  http.end();
  
  return (httpResponseCode == 201); // 201 Created = success
}

bool sendToFirebase(SensorData data) {
  if (!wifiConnected) return false;
  
  Serial.println("🔥 Sending to Firebase...");
  
  HTTPClient http;
  http.begin(firebaseURL);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload for Firebase
  StaticJsonDocument<200> doc;
  doc["device_id"] = DEVICE_ID;
  doc["temperature"] = data.temperature;
  doc["ph"] = data.ph;
  doc["oxygen"] = data.oxygen;
  doc["voltage"] = data.voltage;
  doc["timestamp"] = data.timestamp;
  doc["created_at"] = data.timestamp;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpResponseCode = http.PUT(jsonString); // Firebase uses PUT
  String response = http.getString();
  
  Serial.println("🔥 Firebase Response: " + String(httpResponseCode));
  if (httpResponseCode != 200) {
    Serial.println("❌ Firebase Error: " + response);
  }
  
  http.end();
  
  return (httpResponseCode == 200);
}

void testConnections() {
  Serial.println("\n🧪 Testing Connections...");
  Serial.println("=" + String("=").repeat(30));
  
  // Test WiFi
  Serial.println("🌐 WiFi Status: " + String(wifiConnected ? "✅ Connected" : "❌ Disconnected"));
  if (wifiConnected) {
    Serial.println("📍 IP: " + WiFi.localIP().toString());
    Serial.println("📶 RSSI: " + String(WiFi.RSSI()) + " dBm");
  }
  
  // Test Local Server
  HTTPClient http;
  http.begin(String(serverURL).substring(0, String(serverURL).lastIndexOf('/')) + "/status");
  http.setTimeout(5000);
  
  int localCode = http.GET();
  Serial.println("🏠 Local Server: " + String(localCode == 200 ? "✅ Online" : "❌ Offline (" + String(localCode) + ")"));
  http.end();
  
  // Test Firebase
  http.begin("https://container-kolam-default-rtdb.firebaseio.com/.json");
  int firebaseCode = http.GET();
  Serial.println("🔥 Firebase: " + String(firebaseCode == 200 ? "✅ Online" : "❌ Offline (" + String(firebaseCode) + ")"));
  http.end();
}

void printSystemStatus() {
  Serial.println("\n📊 System Status");
  Serial.println("=" + String("=").repeat(40));
  Serial.println("💾 Device ID: " + String(DEVICE_ID));
  Serial.println("🌐 WiFi: " + String(wifiConnected ? "Connected" : "Disconnected"));
  if (wifiConnected) {
    Serial.println("� IP: " + WiFi.localIP().toString());
  }
  Serial.println("�📡 Server URL: " + String(serverURL));
  Serial.println("🔥 Firebase URL: " + String(firebaseURL));
  Serial.println("⏰ Send Interval: " + String(SEND_INTERVAL/1000) + "s");
  Serial.println("🔄 Uptime: " + String(millis()/1000) + "s");
  
  // Current sensor readings
  Serial.println("\n🧪 Current Sensor Values:");
  float currentVoltage = readpHVoltage();
  Serial.println("  ⚡ Voltage: " + String(currentVoltage, 3) + " V");
  
  if (!isnan(slope) && !isnan(intercept)) {
    float currentpH = slope * currentVoltage + intercept;
    if (currentpH < 0) currentpH = 0;
    if (currentpH > 14) currentpH = 14;
    Serial.println("  🧪 pH: " + String(currentpH, 3));
  } else {
    Serial.println("  🧪 pH: (not calibrated)");
  }
  
  Serial.println("\n📋 Commands: sendnow, status, test, save7, save4, showcal, clearcal");
}

void blinkLED(int times, int delayMs) {
  for (int i = 0; i < times; i++) {
    digitalWrite(LED_BUILTIN, LOW);
    delay(delayMs);
    digitalWrite(LED_BUILTIN, HIGH);
    delay(delayMs);
  }
}