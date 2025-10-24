# 🐟 Quick Start Guide - AquaMonitor User Features

## 🚀 Cara Memulai

### 1. Registrasi & Login

```
1. Buka: http://127.0.0.1:8000/register
2. Isi form registrasi (Nama, Email, Password)
3. Login dengan akun yang dibuat
4. Anda akan diarahkan ke Dashboard
```

### 2. Atur Threshold (PENTING!)

```
1. Klik menu "Settings" di sidebar
2. Atur batas suhu kolam Anda:
   • Batas Bawah: Geser slider ke kiri/kanan
   • Batas Atas: Geser slider ke kiri/kanan
3. Atur batas pH dan Oksigen
4. Klik "Simpan Pengaturan"
```

**Contoh Setting untuk Ikan Nila:**

-   Suhu: 25°C - 32°C
-   pH: 6.5 - 8.5
-   Oksigen: 5.0 - 8.0 mg/L

**Contoh Setting untuk Ikan Lele:**

-   Suhu: 24°C - 30°C
-   pH: 6.0 - 8.0
-   Oksigen: 4.0 - 7.0 mg/L

**Contoh Setting untuk Ikan Koi:**

-   Suhu: 15°C - 25°C
-   pH: 7.0 - 8.0
-   Oksigen: 6.0 - 9.0 mg/L

## 📊 Menu & Fitur

### 🏠 Dashboard

**Fungsi:** Monitoring real-time sensor kolam

**Yang Ditampilkan:**

-   📊 3 Kartu Sensor: Suhu, pH, Oksigen
-   📈 Grafik 8 Jam / 24 Jam
-   🔄 Auto-refresh setiap 30 detik
-   ✅ Status: Normal (hijau) / ⚠️ Perhatian (oranye)

**Cara Baca Status:**

-   ✅ **Hijau = Normal**: Nilai dalam batas aman
-   ⚠️ **Oranye = Perhatian**: Nilai melewati batas, perlu tindakan!

**Tips:**

-   Jika muncul status ⚠️ Perhatian, segera cek kolam
-   Klik "8 Jam" atau "24 Jam" untuk lihat tren data

---

### 📜 History

**Fungsi:** Lihat riwayat pembacaan sensor

**Fitur:**

-   📅 Filter berdasarkan tanggal (mulai - akhir)
-   🔍 Filter berdasarkan tipe (Suhu/pH/Oksigen)
-   📥 Export data ke CSV (Excel)
-   ⚠️ Icon warning pada nilai abnormal

**Cara Pakai:**

1. Pilih tanggal mulai & akhir
2. Pilih tipe data (atau "Semua")
3. Klik "Filter"
4. Untuk download data: klik "Export"

**Tips:**

-   Gunakan untuk analisis mingguan/bulanan
-   Export ke Excel untuk buat grafik sendiri

---

### 👤 Profile

**Fungsi:** Kelola informasi profil Anda

**Yang Bisa Diubah:**

-   ✏️ Nama lengkap
-   📧 Email
-   📱 No. Telepon
-   🔐 Password

**Cara Ubah Profil:**

1. Edit Nama/Email/Telepon
2. Klik "Simpan Perubahan"
3. Profil ter-update

**Cara Ubah Password:**

1. Masukkan password lama
2. Masukkan password baru (minimal 8 karakter)
3. Konfirmasi password baru
4. Klik "Update Password"
5. Logout & login kembali

---

### ⚙️ Settings

**Fungsi:** Atur batas aman sensor

**Pengaturan Threshold:**

1. **Suhu Air**

    - Geser slider "Batas Bawah" dan "Batas Atas"
    - Lihat nilai real-time di sebelah kanan slider

2. **pH Air**

    - Atur pH minimum dan maximum
    - Ideal: 6.5 - 8.5 (kebanyakan ikan)

3. **Oksigen**

    - Atur oksigen minimum dan maksimum
    - Ideal: 5.0 - 8.0 mg/L

4. **Notifikasi** (Coming Soon)
    - Email Notifications: ON/OFF
    - Push Notifications: ON/OFF

**Cara Simpan:**

-   Klik "Simpan Pengaturan" di bawah
-   Sistem akan langsung pakai threshold baru
-   Dashboard akan update status otomatis

---

## ⚠️ Cara Merespon Alert

### Jika Suhu Terlalu Tinggi (> Batas Atas)

```
✅ Solusi:
1. Beri naungan/atap di atas kolam
2. Tambah aerator untuk sirkulasi air
3. Tambahkan air dingin secara bertahap
4. Kurangi padat tebar ikan
```

### Jika Suhu Terlalu Rendah (< Batas Bawah)

```
✅ Solusi:
1. Pakai heater kolam (jika ada)
2. Tutup sebagian permukaan kolam
3. Kurangi pertukaran air
```

### Jika pH Terlalu Tinggi/Rendah

```
✅ Solusi pH Tinggi (Basa):
1. Tambahkan asam sitrat/cuka
2. Ganti 20-30% air kolam
3. Kurangi aerasi berlebih

✅ Solusi pH Rendah (Asam):
1. Tambahkan kapur/baking soda
2. Kurangi pakan berlebih
3. Bersihkan kotoran di dasar kolam
```

### Jika Oksigen Rendah

```
✅ Solusi:
1. SEGERA hidupkan aerator FULL
2. Kurangi pakan sementara
3. Ganti 30% air dengan air baru
4. Kurangi padat tebar jika berlebihan
5. Bersihkan filter
```

---

## 📱 Workflow Harian Pembudidaya

### Pagi Hari (07:00 - 08:00)

```
1. Buka Dashboard
2. Cek status ketiga sensor
3. Jika NORMAL ✅:
   - Beri pakan pagi
   - Lanjutkan aktivitas biasa
4. Jika PERHATIAN ⚠️:
   - Buka History
   - Lihat tren 24 jam
   - Ambil tindakan koreksi
```

### Siang Hari (12:00 - 13:00)

```
1. Cek Dashboard sekali lagi
2. Perhatikan suhu (biasanya naik siang hari)
3. Pastikan aerator jalan normal
```

### Sore Hari (17:00 - 18:00)

```
1. Cek Dashboard
2. Beri pakan sore
3. Lihat grafik 8 Jam
4. Prediksi kondisi malam hari
```

### Malam/Sebelum Tidur (21:00)

```
1. Cek Dashboard terakhir kali
2. Pastikan oksigen mencukupi (aerator ON)
3. Cek ada notifikasi atau tidak
```

---

## 📊 Cara Export & Analisis Data

### Export ke CSV

```
1. Buka menu History
2. Pilih periode: misal 7 hari terakhir
3. Klik tombol "Export"
4. File CSV ter-download
5. Buka dengan Excel/Google Sheets
```

### Analisis di Excel

```
1. Buka file CSV
2. Buat grafik garis (Line Chart):
   - X-axis: Waktu
   - Y-axis: Suhu, pH, Oksigen
3. Identifikasi pola:
   - Jam berapa suhu tertinggi?
   - Kapan oksigen drop?
   - Apakah pH stabil?
```

---

## 🆘 Troubleshooting

### Dashboard tidak update?

```
✅ Solusi:
1. Refresh browser (F5)
2. Cek koneksi internet
3. Clear cache browser
4. Logout dan login kembali
```

### Settings tidak tersimpan?

```
✅ Solusi:
1. Pastikan batas max > batas min
2. Cek nilai dalam range yang valid
3. Refresh halaman
4. Coba simpan lagi
```

### Export CSV tidak jalan?

```
✅ Solusi:
1. Pastikan ada data di periode yang dipilih
2. Cek browser tidak block download
3. Coba periode waktu berbeda
```

### Status selalu "Perhatian" padahal normal?

```
✅ Solusi:
1. Buka Settings
2. Cek threshold yang di-set
3. Sesuaikan dengan jenis ikan Anda
4. Simpan pengaturan baru
```

---

## 💡 Tips & Trik

### 1. Monitoring Efektif

-   ✅ Cek dashboard minimal 3x sehari (pagi, siang, malam)
-   ✅ Export data setiap minggu untuk analisis
-   ✅ Catat treatment/tindakan yang dilakukan

### 2. Setting Threshold

-   ✅ Jangan set terlalu ketat (beri toleransi ±0.5)
-   ✅ Sesuaikan dengan musim/cuaca
-   ✅ Referensi dari buku panduan jenis ikan

### 3. Interpretasi Data

-   ✅ Lihat tren, bukan nilai sesaat
-   ✅ Bandingkan dengan hari sebelumnya
-   ✅ Perhatikan pola harian (siang vs malam)

### 4. Respon Cepat

-   ✅ Siapkan peralatan darurat (aerator cadangan, obat)
-   ✅ Simpan kontak toko ikan/peternak
-   ✅ Punya stok pakan & suplemen

---

## 📞 Bantuan

### Pertanyaan Umum

**Q: Berapa sering data sensor update?**
A: Setiap 1 jam, dan dashboard refresh otomatis setiap 30 detik

**Q: Apakah bisa monitoring dari HP?**
A: Ya, buka browser HP dan akses URL yang sama

**Q: Data history berapa lama tersimpan?**
A: Semua data tersimpan permanent di database

**Q: Bisa pakai untuk beberapa kolam?**
A: Saat ini 1 akun = 1 kolam (multi-kolam: coming soon)

---

## 🎯 Goals Pembudidaya

### Jangka Pendek (1-2 Minggu)

-   [ ] Pahami pola harian sensor
-   [ ] Set threshold optimal
-   [ ] Kurangi fluktuasi parameter

### Jangka Menengah (1-3 Bulan)

-   [ ] Analisis tren mingguan
-   [ ] Optimalkan feeding schedule
-   [ ] Tingkatkan survival rate

### Jangka Panjang (6-12 Bulan)

-   [ ] Data-driven decision making
-   [ ] Prediksi masalah sebelum terjadi
-   [ ] Maksimalkan produksi

---

**Selamat Menggunakan AquaMonitor! 🐟💧**

> "Data yang akurat adalah kunci sukses budidaya ikan modern"

---

_Panduan ini dibuat dengan ❤️ untuk membantu pembudidaya ikan Indonesia_

**Update Terakhir:** 12 Oktober 2025
