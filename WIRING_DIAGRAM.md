# 🔌 Diagram Wiring pH Sensor ke ESP32-S3

## Tampilan Fisik

```
┌─────────────────────────────────────────────────────────────┐
│                                                               │
│                    pH Sensor Module                           │
│                                                               │
│  ┌─────────┐     ┌──────────────────┐                       │
│  │  BNC    │─────│  Circuit Board   │                       │
│  │ Connector│    │  (pH to Voltage) │                       │
│  └─────────┘     └──────────────────┘                       │
│                           │                                   │
│                           │ 3 Pin Header                      │
│                    ┌──────┴──────┐                           │
│                    │ VCC GND OUT │                           │
│                    │  │   │   │  │                           │
└────────────────────┼───┼───┼───┼──────────────────────────────┘
                     │   │   │   │
                     │   │   │   │  Jumper Wires
                     │   │   │   │
                  Merah Hitam  Biru/Kuning
                     │   │   │   │
                     │   │   │   │
┌────────────────────┼───┼───┼───┼──────────────────────────────┐
│                    │   │   │   │                               │
│                ESP32-S3 Development Board                      │
│                                                                │
│  ┌──────────────────────────────────────────────────────┐    │
│  │                                                       │    │
│  │  ┌─────┐  ┌─────┐  ┌─────┐  ┌─────┐  ┌─────┐      │    │
│  │  │ 3V3 │  │ GND │  │ GP4 │  │ GP5 │  │ GP6 │      │    │
│  │  └──┬──┘  └──┬──┘  └──┬──┘  └─────┘  └─────┘      │    │
│  │     │        │        │                              │    │
│  │     ▼        ▼        ▼                              │    │
│  │    VCC      GND      OUT                             │    │
│  │   (Merah) (Hitam)  (Biru)                           │    │
│  │                                                       │    │
│  │                USB-C Port                            │    │
│  │                   [====]                             │    │
│  └──────────────────────────────────────────────────────┘    │
│                        │                                      │
│                        │ USB-C Cable                          │
│                        │                                      │
└────────────────────────┼──────────────────────────────────────┘
                         │
                         │ To Computer / Power Supply
                         ▼
                  ┌──────────────┐
                  │  Computer    │
                  │  atau        │
                  │  Power Bank  │
                  └──────────────┘
```

---

## Koneksi Detail

### Pin Mapping

| pH Sensor Pin | Warna Kabel | ESP32-S3 Pin | Fungsi        |
| ------------- | ----------- | ------------ | ------------- |
| VCC           | 🔴 Merah    | 3.3V         | Power Supply  |
| GND           | ⚫ Hitam    | GND          | Ground        |
| OUT           | 🔵 Biru     | GPIO 4       | Analog Signal |

---

## Gambar Step-by-Step

### Step 1: Identifikasi Pin pH Sensor

```
┌────────────────────┐
│  pH Sensor Module  │
│                    │
│  ┌──────────────┐  │
│  │   VCC  (V+)  │◄─── Tegangan masuk (3.3V)
│  │   GND  (G)   │◄─── Ground (0V)
│  │   OUT  (S)   │◄─── Signal output (analog voltage)
│  └──────────────┘  │
└────────────────────┘
```

### Step 2: Identifikasi Pin ESP32-S3

```
ESP32-S3 Layout (Top View):
┌─────────────────────────────────────┐
│                                     │
│  3V3  GND  GP4  GP5  GP6  ...      │ ◄─ Pin Header Kiri
│   ●    ●    ●    ●    ●            │
│                                     │
│            [ USB-C ]                │
│                                     │
│   ●    ●    ●    ●    ●            │
│  5V   GND  GP0  GP1  GP2  ...      │ ◄─ Pin Header Kanan
│                                     │
└─────────────────────────────────────┘

⚠️ GUNAKAN 3V3, JANGAN 5V!
```

### Step 3: Sambungkan Kabel

**1. Kabel Merah (VCC):**

```
pH Sensor VCC  ──[Merah]──►  ESP32 3.3V Pin
```

**2. Kabel Hitam (GND):**

```
pH Sensor GND  ──[Hitam]──►  ESP32 GND Pin
```

**3. Kabel Biru (OUT):**

```
pH Sensor OUT  ──[Biru]───►  ESP32 GPIO 4
```

### Step 4: Verifikasi Koneksi

```
Setelah tersambung:

pH Sensor Module          ESP32-S3
     ┌────┐               ┌────┐
     │VCC │───Merah───────│3V3 │
     │GND │───Hitam───────│GND │
     │OUT │───Biru────────│GP4 │
     └────┘               └────┘
```

---

## ⚠️ Peringatan Penting!

### ❌ JANGAN LAKUKAN:

```
pH Sensor VCC  ──X──►  ESP32 5V Pin
                 ❌
          INI SALAH! Akan merusak ESP32!
```

### ✅ YANG BENAR:

```
pH Sensor VCC  ──✓──►  ESP32 3.3V Pin
                 ✅
          INI BENAR!
```

### Alasan:

-   ESP32-S3 menggunakan logika **3.3V**
-   Pin ADC (GPIO 4) **tidak tahan 5V**
-   Jika kasih 5V → Pin rusak permanent
-   pH Sensor bisa kerja di 3.3V atau 5V
-   Pilih 3.3V untuk safety

---

## 🔍 Cara Verifikasi Koneksi

### 1. Visual Check

```
✅ Kabel terpasang kencang (tidak goyang)
✅ Tidak ada short circuit (kabel tidak menyentuh)
✅ Warna kabel sesuai dengan diagram
✅ Pin ESP32 benar (3.3V, GND, GPIO 4)
```

### 2. Multimeter Test (Opsional)

```
1. Set multimeter ke DC Voltage
2. Ukur antara VCC dan GND sensor
3. Harusnya dapat: 3.3V ± 0.1V
4. Jika dapat 0V → koneksi bermasalah
5. Jika dapat 5V → STOP! Salah pin!
```

### 3. Serial Monitor Test

```
Upload code → Buka Serial Monitor

Harusnya lihat:
   Raw ADC: 1500-2500 (bukan 0 atau 4095)
   V: 1.5-2.5V (bukan 0.0V atau 3.3V)

Jika Raw ADC = 0 atau 4095 → koneksi OUT bermasalah
```

---

## 🎨 Foto Contoh (Deskripsi)

### Setup Lengkap:

```
┌────────────────────────────────────────┐
│                                        │
│  [Laptop/PC]                           │
│       │                                │
│       │ USB-C                          │
│       ▼                                │
│  ┌─────────────┐                       │
│  │  ESP32-S3   │                       │
│  │   Board     │                       │
│  └──────┬──────┘                       │
│         │ 3 Jumper Wires               │
│         ▼                              │
│  ┌─────────────┐                       │
│  │ pH Sensor   │                       │
│  │   Module    │                       │
│  └──────┬──────┘                       │
│         │ BNC Cable                    │
│         ▼                              │
│    ┌────────┐                          │
│    │ pH     │    Dicelup ke:           │
│    │ Probe  │    - Air kolam           │
│    │        │    - Buffer pH           │
│    └────────┘    - Air sample          │
│                                        │
└────────────────────────────────────────┘
```

---

## 📏 Ukuran Kabel yang Disarankan

### Untuk Testing (di meja):

-   **Panjang**: 15-30 cm
-   **Type**: Jumper wire male-to-female
-   **Gauge**: 22-26 AWG

### Untuk Deployment (di kolam):

-   **Panjang**: Sesuai kebutuhan (max 5 meter)
-   **Type**: Kabel tembaga terisolasi
-   **Gauge**: 20-22 AWG (lebih tebal)
-   **Protection**: Pakai heat shrink tube

---

## 🔧 Alternative Pin (Jika GPIO 4 Tidak Jalan)

ESP32-S3 ADC1 Pins yang bisa digunakan:

| GPIO | ADC Channel | Status      | Note                      |
| ---- | ----------- | ----------- | ------------------------- |
| 1    | ADC1_CH0    | ⚠️ Reserved | Untuk USB, hindari        |
| 2    | ADC1_CH1    | ⚠️ Reserved | Untuk USB, hindari        |
| 3    | ADC1_CH2    | ✅ OK       | Alternative OK            |
| 4    | ADC1_CH3    | ✅ OK       | **Recommended (default)** |
| 5    | ADC1_CH4    | ✅ OK       | Alternative OK            |
| 6    | ADC1_CH5    | ✅ OK       | Alternative OK            |
| 7    | ADC1_CH6    | ✅ OK       | Alternative OK            |
| 8    | ADC1_CH7    | ✅ OK       | Alternative OK            |
| 9    | ADC1_CH8    | ✅ OK       | Alternative OK            |
| 10   | ADC1_CH9    | ✅ OK       | Alternative OK            |

**Cara ganti pin di code:**

```cpp
#define PH_PIN 4  // Ganti dengan nomor GPIO lain jika perlu
```

---

## 📦 Shopping List

Jika belum punya, beli:

1. **ESP32-S3 DevKit** - Rp 80.000
2. **pH Sensor Analog Module** - Rp 150.000
3. **pH Probe (Elektroda)** - Rp 100.000
4. **Buffer pH 4.01** - Rp 30.000
5. **Buffer pH 7.00** - Rp 30.000
6. **Jumper Wires (M-F)** - Rp 10.000
7. **USB-C Cable** - Rp 20.000

**Total**: ~Rp 420.000

---

## ✅ Final Check

Sebelum power on, check:

```
[ ] Kabel VCC ke 3.3V (BUKAN 5V!)
[ ] Kabel GND ke GND
[ ] Kabel OUT ke GPIO 4
[ ] Semua kabel terpasang kencang
[ ] Tidak ada short circuit
[ ] pH probe terhubung ke sensor module
[ ] USB-C cable ready
```

Jika semua OK → Hubungkan ke USB → Upload code → Test!

---

**Status**: ✅ **READY TO CONNECT!**
**Last Update**: 15 Oktober 2025

Happy Wiring! 🔌⚡
