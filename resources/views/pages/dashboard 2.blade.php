@extends('layouts.app')

@section('title', 'Dashboard Analitik')

@push('style')
    <link rel="stylesheet" href="{{ asset('library/chart.js/dist/Chart.min.css') }}">
    <style>
        .metric-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }
        .metric-value {
            font-size: 36px;
            font-weight: 700;
            color: #2d3748;
            margin: 10px 0;
        }
        .metric-label {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }
        .metric-change {
            font-size: 12px;
            margin-top: 8px;
        }
        .insight-box {
            background: #ebf8ff;
            border-left: 4px solid #3182ce;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .insight-warning {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard Analitik</h1>
            </div>

            <div class="section-body">
                <!-- Summary Metrics -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="metric-card">
                            <div class="metric-label">TOTAL PENGUNJUNG (JAN)</div>
                            <div class="metric-value">{{ number_format($total_visitors) }}</div>
                            <div class="metric-change text-muted">
                                ↓ {{ abs($trend_insight['last_month_change']) }}% vs {{ \Carbon\Carbon::now()->subMonth()->format('M') }} 2025
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="metric-card">
                            <div class="metric-label">PENDAPATAN (JAN)</div>
                            <div class="metric-value">Rp {{ number_format($total_revenue / 1000000, 1) }} Jt</div>
                            <div class="metric-change text-muted">
                                ↓ {{ abs($trend_insight['last_month_change']) }}% vs {{ \Carbon\Carbon::now()->subMonth()->format('M') }} 2025
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="metric-card">
                            <div class="metric-label">RATA-RATA/HARI</div>
                            <div class="metric-value">{{ $avg_daily_visitors }}</div>
                            <div class="metric-change text-muted">pengunjung</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="metric-card">
                            <div class="metric-label">PEAK DAY RATIO</div>
                            <div class="metric-value">{{ $peak_ratio }}x</div>
                            <div class="metric-change text-muted">weekend vs weekday</div>
                        </div>
                    </div>
                </div>

                <!-- Trend Analysis -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Tren Pengunjung 6 Bulan Terakhir</h4>
                                <div class="card-header-action">
                                    <span class="badge badge-primary">Analisis time series untuk identifikasi pola musiman</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="trendChart" height="80"></canvas>
                                <div class="insight-box mt-3">
                                    <strong>📊 Insight:</strong>
                                    Puncak pengunjung terjadi di <strong>{{ $trend_insight['peak_month'] }}</strong> (liburan sekolah).
                                    Weekend konsisten <strong>{{ $peak_ratio }}x</strong> lebih ramai dari weekday.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Holiday Impact -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Dampak Hari Libur Nasional</h4>
                                <div class="card-header-action">
                                    <span class="badge badge-info">Perbandingan pengunjung saat libur vs hari normal</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="holidayChart" height="80"></canvas>
                                <div class="row mt-4">
                                    @foreach($holiday_impact as $holiday)
                                    <div class="col-3 text-center">
                                        <h6 class="text-muted">{{ $holiday['name'] }}</h6>
                                        <h3 class="text-success">+{{ $holiday['impact'] }}%</h3>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Forecast -->
                <div class="row mt-4">
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h4>Prediksi Minggu Depan (18-24 Jan 2026)</h4>
                                <div class="card-header-action">
                                    <span class="badge badge-warning">Dengan confidence interval 95%</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="forecastChart" height="80"></canvas>
                                <div class="insight-box insight-warning mt-3">
                                    <strong>⚠️ Interpretasi:</strong>
                                    Area abu-gray menunjukkan range kemungkinan (CI 95%). Weekend diprediksi: {{ collect($forecast_data)->where('is_weekend', true)->first()['prediction'] ?? 0 }}-{{ collect($forecast_data)->where('is_weekend', true)->last()['prediction'] ?? 0 }} pengunjung.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h4>Detail Prediksi & Rekomendasi Operasional</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Hari</th>
                                                <th>Prediksi</th>
                                                <th>Range (95% CI)</th>
                                                <th>Kasir</th>
                                                <th>Est. Pendapatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($forecast_details as $detail)
                                            <tr class="{{ $detail['is_weekend'] ? 'table-warning' : '' }} {{ $detail['is_holiday'] ? 'table-danger' : '' }}">
                                                <td><strong>{{ $detail['day'] }}</strong></td>
                                                <td>
                                                    <span class="badge {{ $detail['prediction'] > 60 ? 'badge-danger' : ($detail['prediction'] > 40 ? 'badge-warning' : 'badge-success') }}">
                                                        {{ $detail['prediction'] }} orang
                                                    </span>
                                                </td>
                                                <td><small>{{ $detail['range'] }}</small></td>
                                                <td>{{ $detail['kasir'] }}</td>
                                                <td>Rp {{ number_format($detail['revenue'] / 1000, 0) }}K</td>
                                            </tr>
                                            @endforeach
                                            <tr class="table-active">
                                                <td><strong>Total Minggu</strong></td>
                                                <td><strong>{{ collect($forecast_details)->sum('prediction') }} orang</strong></td>
                                                <td colspan="2">-</td>
                                                <td><strong>Rp {{ number_format(collect($forecast_details)->sum('revenue') / 1000000, 1) }} Jt</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3">
                                    <small class="text-muted">* Kebutuhan kasir: 1 kasir per 25 pengunjung per shift</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12 text-center">
                        <small class="text-muted">Dashboard Analitik Wisata v1.0 | Data: Juli 2025 - Januari 2026</small>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>
    <script>
        // Trend Chart
        const trendData = @json($trend_data);
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendData.map(d => d.month),
                datasets: [{
                    label: 'Total Pengunjung',
                    data: trendData.map(d => d.total),
                    borderColor: '#2d3748',
                    backgroundColor: 'rgba(45, 55, 72, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Weekend',
                    data: trendData.map(d => d.weekend),
                    borderColor: '#3182ce',
                    backgroundColor: 'rgba(49, 130, 206, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Weekday',
                    data: trendData.map(d => d.weekday),
                    borderColor: '#48bb78',
                    backgroundColor: 'rgba(72, 187, 120, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ctx.dataset.label + ': ' + ctx.parsed.y + ' pengunjung'
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Jumlah Pengunjung' } }
                }
            }
        });

        // Holiday Impact Chart
        const holidayData = @json($holiday_impact);
        new Chart(document.getElementById('holidayChart'), {
            type: 'bar',
            data: {
                labels: holidayData.map(h => h.name),
                datasets: [{
                    label: 'Rata-rata Normal',
                    data: holidayData.map(h => h.normal),
                    backgroundColor: 'rgba(203, 213, 224, 0.8)'
                }, {
                    label: 'Saat Libur',
                    data: holidayData.map(h => h.holiday),
                    backgroundColor: 'rgba(49, 130, 206, 0.8)'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    x: { beginAtZero: true, title: { display: true, text: 'Rata-rata Pengunjung' } }
                }
            }
        });

        // Forecast Chart
        const forecastData = @json($forecast_data);
        new Chart(document.getElementById('forecastChart'), {
            type: 'line',
            data: {
                labels: forecastData.map(f => f.day),
                datasets: [{
                    label: 'Upper Bound (97.5%)',
                    data: forecastData.map(f => f.upper),
                    borderColor: 'rgba(203, 213, 224, 0.5)',
                    backgroundColor: 'rgba(203, 213, 224, 0.2)',
                    borderWidth: 1,
                    fill: '+1',
                    pointRadius: 0
                }, {
                    label: 'Prediksi',
                    data: forecastData.map(f => f.prediction),
                    borderColor: '#3182ce',
                    backgroundColor: 'rgba(49, 130, 206, 0.3)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#3182ce'
                }, {
                    label: 'Lower Bound (2.5%)',
                    data: forecastData.map(f => f.lower),
                    borderColor: 'rgba(203, 213, 224, 0.5)',
                    backgroundColor: 'rgba(255, 255, 255, 0.8)',
                    borderWidth: 1,
                    fill: true,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                if (ctx.datasetIndex === 1) {
                                    return 'Prediksi: ' + ctx.parsed.y + ' pengunjung';
                                }
                                return ctx.dataset.label + ': ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Prediksi Pengunjung' } }
                }
            }
        });
    </script>
@endpush
