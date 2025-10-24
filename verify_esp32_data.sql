-- SQL Query untuk memverifikasi data ESP32 di phpMyAdmin
-- Copy-paste query ini di phpMyAdmin → SQL tab

-- 1. Check database yang sedang aktif
SELECT DATABASE() as current_database;

-- 2. Check total records di sensor_data
SELECT COUNT(*) as total_records FROM sensor_data;

-- 3. Check ID tertinggi (seharusnya 63)
SELECT MAX(id) as highest_id FROM sensor_data;

-- 4. Show 10 data terbaru (ESP32 data dengan pH 4.00)
SELECT 
    id,
    device_id,
    ph,
    temperature,
    oxygen,
    voltage,
    recorded_at,
    created_at
FROM sensor_data 
ORDER BY id DESC 
LIMIT 10;

-- 5. Check specifically ESP32 data (pH = 4.00)
SELECT COUNT(*) as esp32_records FROM sensor_data WHERE ph = 4.00;

-- 6. Show latest ESP32 data
SELECT 
    id,
    ph,
    temperature,
    voltage,
    recorded_at
FROM sensor_data 
WHERE ph = 4.00 
ORDER BY id DESC 
LIMIT 5;

-- 7. Check if voltage column exists
SHOW COLUMNS FROM sensor_data LIKE 'voltage';