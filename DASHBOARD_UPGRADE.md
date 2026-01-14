# 🎯 Dashboard Analitik - QuickTix

## ✅ Yang Sudah Diimplementasikan

### 1. **Controller Analytics** (`DashboardController.php`)

#### Fungsi Utama:
- ✅ `getVisitorAnalytics()` - Analisis pengunjung lengkap
- ✅ `getRevenueAnalytics()` - Analisis pendapatan lengkap  
- ✅ `generateInsights()` - Generate insight otomatis

#### Data Analitik yang Tersedia:

**Analitik Pengunjung:**
- Jumlah pengunjung hari ini
- Perbandingan dengan kemarin (angka & persentase)
- Hari dengan pengunjung tertinggi (7 hari terakhir)
- Rata-rata pengunjung per hari
- Total pengunjung 7 hari terakhir
- Trend (up/down/stable)

**Analitik Pendapatan:**
- Pendapatan hari ini vs kemarin
- Persentase perubahan
- Hari dengan pendapatan tertinggi
- Rata-rata pendapatan per hari
- Rata-rata nilai per transaksi
- Total pendapatan 7 hari terakhir

**Insights Otomatis:**
- Insight tren pengunjung (meningkat/menurun)
- Insight tren pendapatan (meningkat/menurun)
- Highlight hari puncak pengunjung
- Highlight hari puncak pendapatan
- Informasi rata-rata kunjungan

---

### 2. **Dashboard View** (`dashboard.blade.php`)

#### Komponen Baru:

**A. Insights & Rekomendasi Section**
- Menampilkan 5-6 insight otomatis
- Alert boxes dengan warna sesuai status (success/danger/info)
- Icon yang sesuai dengan konteks
- Title dan message yang informatif

**B. Analytics Summary Cards (4 Cards)**
1. **Analitik Pengunjung**
   - Puncak pengunjung 7 hari
   - Rata-rata per hari
   - Hari puncak

2. **Analitik Pendapatan**
   - Pendapatan tertinggi
   - Rata-rata pendapatan
   - Hari puncak

3. **Tren Hari Ini**
   - Persentase perubahan pengunjung
   - Persentase perubahan pendapatan
   - Dengan indicator warna (hijau/merah)

4. **Nilai Transaksi**
   - Rata-rata per transaksi
   - Format rupiah

**C. Enhanced Styling**
- Hover effect pada cards
- Color-coded alerts
- Responsive design
- Modern UI dengan icon FontAwesome

---

## 📊 Contoh Output Insights

```
✅ Pengunjung Meningkat
Jumlah pengunjung hari ini meningkat 25.5% dibanding kemarin (30 pengunjung lebih banyak).

🏆 Hari Puncak Pengunjung
Hari Sunday merupakan hari dengan kunjungan tertinggi minggu ini (200 pengunjung).

💰 Pendapatan Meningkat
Pendapatan hari ini meningkat 18.3% dibanding kemarin (Rp 1.500.000 lebih tinggi).

📈 Hari Puncak Pendapatan
Hari Friday merupakan hari dengan pendapatan tertinggi minggu ini (Rp 10.000.000).

📊 Rata-rata Kunjungan
Rata-rata kunjungan per hari dalam 7 hari terakhir adalah 156 pengunjung.
```

---

## 🎨 Tampilan Dashboard

### Layout:
```
┌─────────────────────────────────────────────────────┐
│  Dashboard Analitik                                  │
├─────────────────────────────────────────────────────┤
│  [Pengunjung] [Pendapatan] [Tiket Terjual] [Trans]  │ ← Metrics Cards
├─────────────────────────────────────────────────────┤
│  💡 Insights & Rekomendasi                           │
│  [Insight 1] [Insight 2]                            │
│  [Insight 3] [Insight 4]                            │ ← Auto Insights
│  [Insight 5] [Insight 6]                            │
├─────────────────────────────────────────────────────┤
│  [Analitik    [Analitik     [Tren      [Nilai      │
│   Pengunjung]  Pendapatan]   Hari Ini]  Transaksi] │ ← Summary Cards
├─────────────────────────────────────────────────────┤
│  [Chart: Tiket Terjual]  [Chart: Pendapatan]       │ ← Charts
├─────────────────────────────────────────────────────┤
│  [Chart: Metode Pembayaran]                         │
└─────────────────────────────────────────────────────┘
```

---

## 🚀 Cara Menggunakan

### 1. Akses Dashboard
```
http://192.168.0.130:8000/dashboard
```
(Pastikan sudah login sebagai admin/kasir)

### 2. Data yang Ditampilkan
- Dashboard akan otomatis load data hari ini
- Perbandingan dengan kemarin
- Analisis 7 hari terakhir
- Insight otomatis ter-generate

### 3. Interpretasi Insights

**Warna Alert:**
- 🟢 **Hijau (Success)**: Performa meningkat, pertahankan strategi
- 🔴 **Merah (Danger)**: Performa menurun, perlu evaluasi
- 🔵 **Biru (Info)**: Informasi umum, tidak ada tindakan mendesak

---

## 🔧 Perhitungan Statistik

### 1. Persentase Perubahan
```php
$percentage = (($today - $yesterday) / $yesterday) * 100
```

### 2. Trend Detection
```php
if ($percentage > 0) → 'up'
if ($percentage < 0) → 'down'
if ($percentage == 0) → 'stable'
```

### 3. Rata-rata
```php
$average = $total / $jumlah_hari
```

### 4. Hari Puncak
```php
$peak = $data->sortByDesc('value')->first()
```

---

## 📝 Catatan Penting

1. **Tidak Menggunakan ML/AI Prediction**
   - Semua insights menggunakan perhitungan statistik sederhana
   - Berdasarkan data historis nyata
   - Tidak ada prediksi masa depan

2. **Timezone**
   - Menggunakan Asia/Jakarta (WIB)
   - Data hari ini = hari ini pukul 00:00 - 23:59 WIB

3. **Data Source**
   - Table: `orders`, `order_items`, `transactions`
   - Field: `transaction_time` (bukan `created_at`)

4. **Performance**
   - Query sudah dioptimasi dengan groupBy
   - Cache bisa ditambahkan untuk performa lebih baik

---

## 🎯 Fitur Tambahan (Opsional)

Jika ingin menambahkan fitur lebih:

1. **Export Laporan PDF/Excel**
2. **Filter by Date Range**
3. **Notifikasi Real-time**
4. **Comparison dengan Bulan Lalu**
5. **Analitik per Produk/Kategori**
6. **Forecast Sederhana (trend line)**

---

## ✨ Keunggulan Dashboard Ini

✅ **Actionable Insights** - Bukan hanya angka, tapi ada konteks dan rekomendasi  
✅ **Real-time Data** - Data selalu update dari database  
✅ **User-friendly** - Visual menarik dengan warna dan icon  
✅ **Comprehensive** - Mencakup pengunjung, pendapatan, transaksi  
✅ **Comparative Analysis** - Selalu ada perbandingan dengan periode sebelumnya  
✅ **Trend Detection** - Otomatis deteksi naik/turun/stabil  

---

**Dibuat dengan ❤️ untuk QuickTix Dashboard Analytics**
