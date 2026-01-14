# Dashboard Analitik - Laravel QuickTix

## 📊 Fitur Analitik

Sistem analitik dashboard ini menyediakan analisis mendalam dari data transaksi dan pengunjung, mencakup:

### 1. Analitik Pengunjung
- ✅ Jumlah pengunjung hari ini
- ✅ Perbandingan dengan hari kemarin
- ✅ Persentase kenaikan/penurunan
- ✅ Hari dengan pengunjung tertinggi (7 hari terakhir)
- ✅ Rata-rata pengunjung per hari
- ✅ Total pengunjung 7 hari terakhir

### 2. Analitik Pendapatan
- ✅ Pendapatan hari ini vs kemarin
- ✅ Persentase perubahan pendapatan
- ✅ Hari dengan pendapatan tertinggi
- ✅ Rata-rata pendapatan per hari
- ✅ Rata-rata nilai per transaksi
- ✅ Total pendapatan 7 hari terakhir

### 3. Insights Otomatis
- ✅ Insight tentang tren pengunjung
- ✅ Insight tentang tren pendapatan
- ✅ Rekomendasi berdasarkan data historis
- ✅ Highlight hari-hari puncak
- ✅ Performa keseluruhan minggu ini

### 4. Visualisasi Data
- ✅ Grafik pengunjung 7 hari terakhir
- ✅ Grafik pendapatan 7 hari terakhir
- ✅ Grafik jumlah transaksi per hari

---

## 🔌 API Endpoints

### 1. Dashboard Analytics (Lengkap)
```
GET /api/analytics/dashboard
```

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:** Lihat contoh lengkap di bawah.

---

### 2. Analytics by Date Range
```
GET /api/analytics/date-range?start_date=2026-01-01&end_date=2026-01-10
```

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Parameters:**
- `start_date` (required): Format YYYY-MM-DD
- `end_date` (required): Format YYYY-MM-DD

**Response:**
```json
{
  "status": "success",
  "data": {
    "date_range": {
      "start": "2026-01-01",
      "end": "2026-01-10",
      "days": 10
    },
    "summary": {
      "total_revenue": 15000000,
      "total_revenue_formatted": "Rp 15.000.000",
      "total_visitors": 1250,
      "total_transactions": 320,
      "average_revenue_per_day": 1500000.00,
      "average_visitors_per_day": 125.00
    },
    "daily_data": [
      {
        "date": "2026-01-01",
        "revenue": 1200000,
        "visitors": 95,
        "transactions": 28
      }
    ]
  }
}
```

---

## 📱 Contoh Response JSON Dashboard Analytics

```json
{
  "status": "success",
  "data": {
    "visitors": {
      "today": {
        "count": 145,
        "date": "2026-01-12"
      },
      "yesterday": {
        "count": 120,
        "date": "2026-01-11"
      },
      "comparison": {
        "difference": 25,
        "percentage": 20.83,
        "trend": "up",
        "status": "increase"
      },
      "peak_day": {
        "date": "2026-01-10",
        "day_name": "Sunday",
        "count": 200
      },
      "average_per_day": 156.43,
      "total_last_7_days": 1095
    },
    "revenue": {
      "today": {
        "amount": 7250000,
        "formatted": "Rp 7.250.000",
        "date": "2026-01-12"
      },
      "yesterday": {
        "amount": 6000000,
        "formatted": "Rp 6.000.000",
        "date": "2026-01-11"
      },
      "comparison": {
        "difference": 1250000,
        "difference_formatted": "Rp 1.250.000",
        "percentage": 20.83,
        "trend": "up",
        "status": "increase"
      },
      "peak_day": {
        "date": "2026-01-10",
        "day_name": "Sunday",
        "amount": 10000000,
        "formatted": "Rp 10.000.000"
      },
      "average_per_day": 7142857.14,
      "average_per_day_formatted": "Rp 7.142.857",
      "average_per_transaction": 50000.00,
      "average_per_transaction_formatted": "Rp 50.000",
      "total_last_7_days": 50000000,
      "total_last_7_days_formatted": "Rp 50.000.000"
    },
    "insights": [
      {
        "type": "positive",
        "category": "visitors",
        "message": "Jumlah pengunjung hari ini meningkat 20.83% dibanding hari sebelumnya (25 pengunjung lebih banyak).",
        "icon": "📈"
      },
      {
        "type": "info",
        "category": "visitors",
        "message": "Hari Sunday (2026-01-10) merupakan hari dengan kunjungan tertinggi minggu ini (200 pengunjung).",
        "icon": "🏆"
      },
      {
        "type": "info",
        "category": "visitors",
        "message": "Rata-rata kunjungan per hari dalam 7 hari terakhir adalah 156 pengunjung.",
        "icon": "📊"
      },
      {
        "type": "positive",
        "category": "revenue",
        "message": "Pendapatan hari ini meningkat 20.83% dibanding hari sebelumnya (Rp 1.250.000 lebih tinggi).",
        "icon": "💰"
      },
      {
        "type": "info",
        "category": "revenue",
        "message": "Hari Sunday (2026-01-10) merupakan hari dengan pendapatan tertinggi minggu ini (Rp 10.000.000).",
        "icon": "💎"
      },
      {
        "type": "info",
        "category": "revenue",
        "message": "Rata-rata nilai per transaksi hari ini adalah Rp 50.000.",
        "icon": "🎫"
      },
      {
        "type": "summary",
        "category": "overall",
        "message": "Dalam 7 hari terakhir, total 1095 pengunjung telah berkunjung dengan total pendapatan Rp 50.000.000.",
        "icon": "📅"
      }
    ],
    "charts": {
      "visitors_7_days": [
        {
          "date": "2026-01-06",
          "day": "Monday",
          "value": 145
        },
        {
          "date": "2026-01-07",
          "day": "Tuesday",
          "value": 132
        },
        {
          "date": "2026-01-08",
          "day": "Wednesday",
          "value": 158
        },
        {
          "date": "2026-01-09",
          "day": "Thursday",
          "value": 140
        },
        {
          "date": "2026-01-10",
          "day": "Friday",
          "value": 200
        },
        {
          "date": "2026-01-11",
          "day": "Saturday",
          "value": 175
        },
        {
          "date": "2026-01-12",
          "day": "Sunday",
          "value": 145
        }
      ],
      "revenue_7_days": [
        {
          "date": "2026-01-06",
          "day": "Monday",
          "value": 7250000
        },
        {
          "date": "2026-01-07",
          "day": "Tuesday",
          "value": 6600000
        },
        {
          "date": "2026-01-08",
          "day": "Wednesday",
          "value": 7900000
        },
        {
          "date": "2026-01-09",
          "day": "Thursday",
          "value": 7000000
        },
        {
          "date": "2026-01-10",
          "day": "Friday",
          "value": 10000000
        },
        {
          "date": "2026-01-11",
          "day": "Saturday",
          "value": 8750000
        },
        {
          "date": "2026-01-12",
          "day": "Sunday",
          "value": 7250000
        }
      ],
      "transactions_7_days": [
        {
          "date": "2026-01-06",
          "day": "Monday",
          "value": 45
        },
        {
          "date": "2026-01-07",
          "day": "Tuesday",
          "value": 38
        },
        {
          "date": "2026-01-08",
          "day": "Wednesday",
          "value": 52
        },
        {
          "date": "2026-01-09",
          "day": "Thursday",
          "value": 42
        },
        {
          "date": "2026-01-10",
          "day": "Friday",
          "value": 65
        },
        {
          "date": "2026-01-11",
          "day": "Saturday",
          "value": 58
        },
        {
          "date": "2026-01-12",
          "day": "Sunday",
          "value": 47
        }
      ]
    },
    "summary": {
      "all_time": {
        "total_revenue": 250000000,
        "total_revenue_formatted": "Rp 250.000.000",
        "total_visitors": 5420,
        "total_transactions": 1250
      },
      "this_month": {
        "revenue": 85000000,
        "revenue_formatted": "Rp 85.000.000",
        "visitors": 1850,
        "month": "January 2026"
      }
    }
  },
  "timestamp": "2026-01-12 14:30:25"
}
```

---

## 🎯 Cara Menggunakan di Flutter

### 1. Buat Service untuk API Analytics

```dart
// services/analytics_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class AnalyticsService {
  static const String baseUrl = 'https://your-api-url.com/api';
  
  Future<Map<String, dynamic>> getDashboardAnalytics() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    
    final response = await http.get(
      Uri.parse('$baseUrl/analytics/dashboard'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load analytics');
    }
  }
  
  Future<Map<String, dynamic>> getAnalyticsByDateRange(
    String startDate, 
    String endDate
  ) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    
    final response = await http.get(
      Uri.parse('$baseUrl/analytics/date-range?start_date=$startDate&end_date=$endDate'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load analytics');
    }
  }
}
```

### 2. Buat Model untuk Analytics Data

```dart
// models/analytics_model.dart
class DashboardAnalytics {
  final VisitorAnalytics visitors;
  final RevenueAnalytics revenue;
  final List<Insight> insights;
  final ChartData charts;
  final SummaryData summary;
  
  DashboardAnalytics({
    required this.visitors,
    required this.revenue,
    required this.insights,
    required this.charts,
    required this.summary,
  });
  
  factory DashboardAnalytics.fromJson(Map<String, dynamic> json) {
    return DashboardAnalytics(
      visitors: VisitorAnalytics.fromJson(json['visitors']),
      revenue: RevenueAnalytics.fromJson(json['revenue']),
      insights: (json['insights'] as List)
          .map((i) => Insight.fromJson(i))
          .toList(),
      charts: ChartData.fromJson(json['charts']),
      summary: SummaryData.fromJson(json['summary']),
    );
  }
}

class VisitorAnalytics {
  final int todayCount;
  final int yesterdayCount;
  final double percentage;
  final String trend;
  final int peakDayCount;
  final String peakDayName;
  final double averagePerDay;
  
  VisitorAnalytics({
    required this.todayCount,
    required this.yesterdayCount,
    required this.percentage,
    required this.trend,
    required this.peakDayCount,
    required this.peakDayName,
    required this.averagePerDay,
  });
  
  factory VisitorAnalytics.fromJson(Map<String, dynamic> json) {
    return VisitorAnalytics(
      todayCount: json['today']['count'],
      yesterdayCount: json['yesterday']['count'],
      percentage: json['comparison']['percentage'].toDouble(),
      trend: json['comparison']['trend'],
      peakDayCount: json['peak_day']['count'],
      peakDayName: json['peak_day']['day_name'] ?? '',
      averagePerDay: json['average_per_day'].toDouble(),
    );
  }
}

class Insight {
  final String type;
  final String category;
  final String message;
  final String icon;
  
  Insight({
    required this.type,
    required this.category,
    required this.message,
    required this.icon,
  });
  
  factory Insight.fromJson(Map<String, dynamic> json) {
    return Insight(
      type: json['type'],
      category: json['category'],
      message: json['message'],
      icon: json['icon'],
    );
  }
}
```

### 3. Tampilkan di Dashboard Flutter

```dart
// screens/analytics_dashboard_screen.dart
import 'package:flutter/material.dart';
import '../services/analytics_service.dart';
import '../models/analytics_model.dart';

class AnalyticsDashboardScreen extends StatefulWidget {
  @override
  _AnalyticsDashboardScreenState createState() => _AnalyticsDashboardScreenState();
}

class _AnalyticsDashboardScreenState extends State<AnalyticsDashboardScreen> {
  final AnalyticsService _analyticsService = AnalyticsService();
  bool _isLoading = true;
  DashboardAnalytics? _analytics;
  
  @override
  void initState() {
    super.initState();
    _loadAnalytics();
  }
  
  Future<void> _loadAnalytics() async {
    try {
      final response = await _analyticsService.getDashboardAnalytics();
      setState(() {
        _analytics = DashboardAnalytics.fromJson(response['data']);
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memuat data analitik')),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }
    
    return Scaffold(
      appBar: AppBar(
        title: Text('Dashboard Analitik'),
      ),
      body: RefreshIndicator(
        onRefresh: _loadAnalytics,
        child: SingleChildScrollView(
          padding: EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Visitor Card
              _buildStatCard(
                'Pengunjung Hari Ini',
                _analytics!.visitors.todayCount.toString(),
                _analytics!.visitors.percentage,
                _analytics!.visitors.trend,
                Icons.people,
                Colors.blue,
              ),
              SizedBox(height: 16),
              
              // Revenue Card
              _buildStatCard(
                'Pendapatan Hari Ini',
                _analytics!.revenue.todayFormatted,
                _analytics!.revenue.percentage,
                _analytics!.revenue.trend,
                Icons.attach_money,
                Colors.green,
              ),
              SizedBox(height: 24),
              
              // Insights Section
              Text(
                'Insights',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
              SizedBox(height: 12),
              ...(_analytics!.insights.map((insight) => _buildInsightCard(insight))),
              
              // Add charts here using fl_chart or charts_flutter package
            ],
          ),
        ),
      ),
    );
  }
  
  Widget _buildStatCard(
    String title,
    String value,
    double percentage,
    String trend,
    IconData icon,
    Color color,
  ) {
    return Card(
      elevation: 4,
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, color: color, size: 32),
                SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey[600],
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        value,
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            SizedBox(height: 12),
            Row(
              children: [
                Icon(
                  trend == 'up' ? Icons.trending_up : Icons.trending_down,
                  color: trend == 'up' ? Colors.green : Colors.red,
                  size: 20,
                ),
                SizedBox(width: 4),
                Text(
                  '${percentage.abs()}%',
                  style: TextStyle(
                    color: trend == 'up' ? Colors.green : Colors.red,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  ' vs kemarin',
                  style: TextStyle(
                    color: Colors.grey[600],
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
  
  Widget _buildInsightCard(Insight insight) {
    Color backgroundColor;
    switch (insight.type) {
      case 'positive':
        backgroundColor = Colors.green[50]!;
        break;
      case 'negative':
        backgroundColor = Colors.red[50]!;
        break;
      default:
        backgroundColor = Colors.blue[50]!;
    }
    
    return Container(
      margin: EdgeInsets.only(bottom: 12),
      padding: EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Text(
            insight.icon,
            style: TextStyle(fontSize: 24),
          ),
          SizedBox(width: 12),
          Expanded(
            child: Text(
              insight.message,
              style: TextStyle(fontSize: 13),
            ),
          ),
        ],
      ),
    );
  }
}
```

---

## ⚡ Best Practices & Optimizations

1. **Caching**: Pertimbangkan untuk cache data analytics selama 5-10 menit
2. **Indexing**: Pastikan kolom `transaction_time` di tabel orders memiliki index
3. **Pagination**: Untuk date range yang besar, gunakan pagination
4. **Background Jobs**: Untuk laporan bulanan/tahunan, gunakan queue jobs

---

## 🔧 Testing API

Anda bisa test menggunakan Postman atau cURL:

```bash
# Test Dashboard Analytics
curl -X GET http://localhost:8000/api/analytics/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Test Date Range Analytics
curl -X GET "http://localhost:8000/api/analytics/date-range?start_date=2026-01-01&end_date=2026-01-10" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 📝 Notes

- Semua perhitungan menggunakan statistik sederhana (persentase, rata-rata, sum)
- Tidak menggunakan Machine Learning atau AI prediction
- Data diambil dari tabel `orders` yang sudah ada
- Insight text di-generate secara otomatis berdasarkan kondisi data
- Response sudah dalam format yang siap digunakan Flutter
