@extends('layouts.app')

@section('title', 'Dashboard Analytics')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('library/chart.js/dist/Chart.min.css') }}">
    <style>
        .card-statistic-1 {
            transition: transform 0.3s ease;
        }
        .card-statistic-1:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        .card-header-action select {
            height: 35px;
            padding: 0 15px;
            border-radius: 4px;
            border: 1px solid #e4e6fc;
            font-size: 13px;
            color: #34395e;
        }
        .comparison-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }
        .badge-up {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .badge-down {
            background: #ffebee;
            color: #c62828;
        }
        .insight-card {
            border-left: 4px solid;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .insight-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .insight-success {
            border-left-color: #28a745;
            background: #f0f9f4;
        }
        .insight-warning {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        .insight-info {
            border-left-color: #17a2b8;
            background: #f0f8ff;
        }
        .prediction-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-confidence-high {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .badge-confidence-medium {
            background: #fff3e0;
            color: #e65100;
        }
        .table-prediction {
            font-size: 13px;
        }
        .table-prediction th {
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
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
                <!-- Key Metrics with Comparison -->
                <div class="row">
                    <!-- Total Visitors (6 months) -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Pengunjung</h4>
                                </div>
                                <div class="card-body">
                                    {{ number_format($total_visitors) }}
                                </div>
                                @if(isset($visitor_comparison))
                                <span class="comparison-badge badge-{{ $visitor_comparison['trend'] }}">
                                    <i class="fas fa-arrow-{{ $visitor_comparison['trend'] == 'up' ? 'up' : 'down' }}"></i>
                                    {{ abs($visitor_comparison['percentage']) }}% vs {{ now()->subMonth()->format('M') }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Pendapatan</h4>
                                </div>
                                <div class="card-body">
                                    Rp {{ number_format($total_revenue / 1000000, 2) }} Jt
                                </div>
                                @if(isset($revenue_comparison))
                                <span class="comparison-badge badge-{{ $revenue_comparison['trend'] }}">
                                    <i class="fas fa-arrow-{{ $revenue_comparison['trend'] == 'up' ? 'up' : 'down' }}"></i>
                                    {{ abs($revenue_comparison['percentage']) }}% vs kemarin
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Average Daily Visitors -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Rata-rata/Hari</h4>
                                </div>
                                <div class="card-body">
                                    {{ $avg_daily_visitors }}
                                </div>
                                <small class="text-muted">pengunjung</small>
                            </div>
                        </div>
                    </div>

                    <!-- Peak Ratio -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Peak Day Ratio</h4>
                                </div>
                                <div class="card-body">
                                    {{ $peak_ratio }}x
                                </div>
                                <small class="text-muted">hari libur vs hari biasa</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insights & Recommendations -->
                @if(isset($insights) && count($insights) > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4><i class="fas fa-lightbulb text-warning"></i> Insights & Rekomendasi</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($insights as $insight)
                                    <div class="col-lg-6 col-md-12">
                                        <div class="insight-card insight-{{ $insight['type'] }} p-3 rounded">
                                            <div class="d-flex align-items-start">
                                                <span style="font-size: 24px; margin-right: 12px;">{{ $insight['icon'] }}</span>
                                                <div>
                                                    <h6 class="mb-1 font-weight-bold">{{ $insight['title'] }}</h6>
                                                    <p class="mb-0 text-muted" style="font-size: 13px;">{{ $insight['message'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Trend Analysis (Dynamic Period) -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 id="trendTitle">Tren Pengunjung 6 Bulan Terakhir</h4>
                                <div class="card-header-action">
                                    <select id="periodSelector" class="form-control" style="width: auto; display: inline-block;">
                                        <option value="6_months">6 Bulan Terakhir</option>
                                        <option value="1_month_weekly">1 Bulan (Per Minggu)</option>
                                        <option value="daily">7 Hari Terakhir</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="trendChart"></canvas>
                                </div>
                                @if(isset($trend_insight))
                                <div class="alert alert-info mt-3 mb-0" id="trendInsight">
                                    <strong><i class="fas fa-chart-line"></i> Insight:</strong>
                                    Puncak pengunjung terjadi di <strong>{{ $trend_insight['peak_month'] ?? '-' }}</strong>.
                                    @if($trend_insight['last_month_change'] > 0)
                                        Bulan ini naik <strong>{{ $trend_insight['last_month_change'] }}%</strong> dibanding bulan lalu.
                                    @elseif($trend_insight['last_month_change'] < 0)
                                        Bulan ini turun <strong>{{ abs($trend_insight['last_month_change']) }}%</strong> dibanding bulan lalu.
                                    @else
                                        Hari libur konsisten <strong>1x lebih ramai</strong> dari hari biasa.
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Holiday Impact Analysis -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 id="holidayTitle">Dampak Hari Libur Nasional - <span id="holidayPeriodText">{{ now()->format('F Y') }}</span></h4>
                                <div class="card-header-action">
                                    <select id="holidayYearSelector" class="form-control mr-2" style="width: auto; display: inline-block;">
                                        <option value="2025">2025</option>
                                        <option value="2026" {{ now()->year == 2026 ? 'selected' : '' }}>2026</option>
                                    </select>
                                    <select id="holidayMonthSelector" class="form-control" style="width: auto; display: inline-block;">
                                        <option value="1" {{ now()->month == 1 ? 'selected' : '' }}>Januari</option>
                                        <option value="2" {{ now()->month == 2 ? 'selected' : '' }}>Februari</option>
                                        <option value="3" {{ now()->month == 3 ? 'selected' : '' }}>Maret</option>
                                        <option value="4" {{ now()->month == 4 ? 'selected' : '' }}>April</option>
                                        <option value="5" {{ now()->month == 5 ? 'selected' : '' }}>Mei</option>
                                        <option value="6" {{ now()->month == 6 ? 'selected' : '' }}>Juni</option>
                                        <option value="7" {{ now()->month == 7 ? 'selected' : '' }}>Juli</option>
                                        <option value="8" {{ now()->month == 8 ? 'selected' : '' }}>Agustus</option>
                                        <option value="9" {{ now()->month == 9 ? 'selected' : '' }}>September</option>
                                        <option value="10" {{ now()->month == 10 ? 'selected' : '' }}>Oktober</option>
                                        <option value="11" {{ now()->month == 11 ? 'selected' : '' }}>November</option>
                                        <option value="12" {{ now()->month == 12 ? 'selected' : '' }}>Desember</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body" id="holidayContent">
                                @if(isset($holiday_impact) && count($holiday_impact) > 0)
                                <div class="chart-container">
                                    <canvas id="holidayImpactChart"></canvas>
                                </div>
                                <div class="row mt-4" id="holidayStats">
                                    @foreach($holiday_impact as $holiday)
                                    <div class="col-lg-3 col-md-6 text-center mb-3">
                                        <h6 class="font-weight-bold">{{ $holiday['name'] }}</h6>
                                        <small class="text-muted d-block mb-1">{{ $holiday['date'] }}</small>
                                        <h3 class="text-{{ $holiday['impact'] >= 100 ? 'success' : ($holiday['impact'] >= 0 ? 'info' : 'danger') }}">
                                            {{ $holiday['impact'] >= 0 ? '+' : '' }}{{ $holiday['impact'] }}%
                                        </h3>
                                        <small class="text-muted">vs normal</small>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle"></i> Tidak ada libur nasional pada bulan ini
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Forecast (7 Days Ahead) -->
                @if(isset($forecast_data) && count($forecast_data) > 0)
                <div class="row">
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h4>Prediksi Minggu Depan ({{ now()->addDay()->format('d') }}-{{ now()->addDays(7)->format('d M Y') }})</h4>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="forecastChart"></canvas>
                                </div>
                                <div class="alert alert-info mt-3 mb-0">
                                    <strong><i class="fas fa-lightbulb"></i> Tips:</strong>
                                    Prediksi ini membantu Anda mempersiapkan stok tiket dan kebutuhan operasional harian. Hari libur umumnya lebih ramai dari hari biasa.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h4>Detail Prediksi</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-prediction mb-0">
                                        <thead>
                                            <tr>
                                                <th>Hari</th>
                                                <th>Prediksi</th>
                                                <th>Est. Pendapatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($forecast_details as $detail)
                                            <tr class="{{ $detail['is_weekend'] ? 'bg-light font-weight-bold' : '' }}">
                                                <td>
                                                    {{ $detail['day'] }}
                                                    @if($detail['is_weekend'])
                                                        <span class="badge badge-primary badge-sm">Hari Libur</span>
                                                    @endif
                                                    @if($detail['is_holiday'])
                                                        <span class="badge badge-danger badge-sm">Libur</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $detail['prediction'] > 40 ? 'success' : 'info' }}">
                                                        {{ $detail['prediction'] }} orang
                                                    </span>
                                                </td>
                                                <td>Rp {{ number_format($detail['revenue'] / 1000) }}K</td>
                                            </tr>
                                            @endforeach
                                            <tr class="bg-info text-white">
                                                <td colspan="2"><strong>Total Minggu</strong></td>
                                                <td colspan="2">
                                                    <strong>{{ array_sum(array_column($forecast_details, 'prediction')) }} orang</strong> |
                                                    <strong>Rp {{ number_format(array_sum(array_column($forecast_details, 'revenue')) / 1000000, 1) }} Jt</strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraries -->
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>

    <!-- Dashboard Analytics Charts -->
    <script>
        // Prepare data from PHP
        const ticketSalesTrend = @json($ticket_sales_trend ?? []);
        const revenueTrend = @json($revenue_trend ?? []);
        const paymentMethods = @json($payment_methods ?? []);
        const trendData = @json($trend_data ?? []);
        const holidayImpact = @json($holiday_impact ?? []);
        const forecastData = @json($forecast_data ?? []);

        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        console.log('Ticket Sales Trend:', ticketSalesTrend);
        console.log('Revenue Trend:', revenueTrend);
        console.log('Trend Data (6 months):', trendData);
        console.log('Holiday Impact:', holidayImpact);
        console.log('Forecast Data:', forecastData);
        console.log('Payment Methods:', paymentMethods);

        // 4. Trend Chart (6 Months) - Initial Chart
        let trendChartInstance = null;
        const trendCanvas = document.getElementById('trendChart');
        if (trendCanvas) {
            if (trendData.length === 0) {
                console.warn('Trend data is empty!');
            }
            trendChartInstance = new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: trendData.length > 0 ? trendData.map(item => item.month) : ['Aug 2025', 'Sep 2025', 'Oct 2025', 'Nov 2025', 'Dec 2025', 'Jan 2026'],
                    datasets: [
                        {
                            label: 'Total Pengunjung',
                            data: trendData.length > 0 ? trendData.map(item => item.total) : [0, 0, 0, 0, 0, 0],
                            borderColor: '#34395e',
                            backgroundColor: 'rgba(52, 57, 94, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: '#34395e'
                        },
                        {
                            label: 'Hari Libur',
                            data: trendData.length > 0 ? trendData.map(item => item.weekend) : [0, 0, 0, 0, 0, 0],
                            borderColor: '#6777ef',
                            backgroundColor: 'rgba(103, 119, 239, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4,
                            pointRadius: 4
                        },
                        {
                            label: 'Hari Biasa',
                            data: trendData.length > 0 ? trendData.map(item => item.weekday) : [0, 0, 0, 0, 0, 0],
                            borderColor: '#48c78e',
                            backgroundColor: 'rgba(72, 199, 142, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + ' pengunjung';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        } else {
            console.error('Canvas element trendChart not found!');
        }

        // 5. Holiday Impact Chart - Initial
        let holidayChartInstance = null;
        const holidayCanvas = document.getElementById('holidayImpactChart');
        if (holidayCanvas) {
            if (holidayImpact.length === 0) {
                console.warn('Holiday impact data is empty!');
            }
            holidayChartInstance = new Chart(holidayCanvas, {
                type: 'bar',
                data: {
                    labels: holidayImpact.length > 0 ? holidayImpact.map(h => h.name) : ['Tahun Baru', 'Natal', 'Idul Adha', 'Kemerdekaan'],
                    datasets: [
                        {
                            label: 'Rata-rata Normal',
                            data: holidayImpact.length > 0 ? holidayImpact.map(h => h.normal) : [0, 0, 0, 0],
                            backgroundColor: 'rgba(200, 200, 200, 0.5)',
                            borderColor: 'rgba(150, 150, 150, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Saat Libur',
                            data: holidayImpact.length > 0 ? holidayImpact.map(h => h.holiday) : [0, 0, 0, 0],
                            backgroundColor: 'rgba(72, 133, 237, 0.8)',
                            borderColor: 'rgba(72, 133, 237, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + ' pengunjung';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        } else {
            console.error('Canvas element holidayImpactChart not found!');
        }

        // Holiday Period Selector - Dynamic Update

        function updateHolidayChart() {
            const month = document.getElementById('holidayMonthSelector').value;
            const year = document.getElementById('holidayYearSelector').value;

            const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            document.getElementById('holidayPeriodText').textContent = monthNames[month] + ' ' + year;

            fetch(`/api/holiday-data?month=${month}&year=${year}`)
                .then(response => response.json())
                .then(result => {
                    console.log('Holiday data updated:', result);

                    const data = result.data;
                    const holidayContent = document.getElementById('holidayContent');

                    if (data.length === 0) {
                        holidayContent.innerHTML = `
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i> Tidak ada libur nasional pada ${monthNames[month]} ${year}
                            </div>
                        `;
                        if (holidayChartInstance) {
                            holidayChartInstance.destroy();
                            holidayChartInstance = null;
                        }
                        return;
                    }

                    // Update chart
                    const holidayCanvas = document.getElementById('holidayImpactChart');
                    if (!holidayCanvas) {
                        holidayContent.innerHTML = `
                            <div class="chart-container">
                                <canvas id="holidayImpactChart"></canvas>
                            </div>
                            <div class="row mt-4" id="holidayStats"></div>
                        `;
                    }

                    // Destroy existing chart
                    if (holidayChartInstance) {
                        holidayChartInstance.destroy();
                    }

                    // Create new chart
                    const canvas = document.getElementById('holidayImpactChart');
                    holidayChartInstance = new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: data.map(h => h.name),
                            datasets: [
                                {
                                    label: 'Rata-rata Normal (Hari Biasa)',
                                    data: data.map(h => h.normal),
                                    backgroundColor: 'rgba(200, 200, 200, 0.5)',
                                    borderColor: 'rgba(150, 150, 150, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Saat Libur',
                                    data: data.map(h => h.holiday),
                                    backgroundColor: 'rgba(72, 133, 237, 0.8)',
                                    borderColor: 'rgba(72, 133, 237, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: true, position: 'top' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y + ' pengunjung';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString();
                                        }
                                    }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });

                    // Update stats cards
                    let statsHtml = '';
                    data.forEach(holiday => {
                        const colorClass = holiday.impact >= 100 ? 'success' : (holiday.impact >= 0 ? 'info' : 'danger');
                        statsHtml += `
                            <div class="col-lg-3 col-md-6 text-center mb-3">
                                <h6 class="font-weight-bold">${holiday.name}</h6>
                                <small class="text-muted d-block mb-1">${holiday.date}</small>
                                <h3 class="text-${colorClass}">
                                    ${holiday.impact >= 0 ? '+' : ''}${holiday.impact}%
                                </h3>
                                <small class="text-muted">vs normal</small>
                            </div>
                        `;
                    });

                    document.getElementById('holidayStats').innerHTML = statsHtml;
                })
                .catch(error => {
                    console.error('Error fetching holiday data:', error);
                });
        }

        // Event listeners for holiday selectors
        document.getElementById('holidayYearSelector').addEventListener('change', updateHolidayChart);
        document.getElementById('holidayMonthSelector').addEventListener('change', updateHolidayChart);

        // 6. Forecast Chart with Confidence Interval
        const forecastCanvas = document.getElementById('forecastChart');
        if (forecastCanvas) {
            if (forecastData.length === 0) {
                console.warn('Forecast data is empty!');
            }
            new Chart(forecastCanvas, {
                type: 'line',
                data: {
                    labels: forecastData.length > 0 ? forecastData.map(f => f.day) : ['Hari 1', 'Hari 2', 'Hari 3', 'Hari 4', 'Hari 5', 'Hari 6', 'Hari 7'],
                    datasets: [
                        {
                            label: 'Prediksi Pengunjung',
                            data: forecastData.length > 0 ? forecastData.map(f => f.prediction) : [0, 0, 0, 0, 0, 0, 0],
                            borderColor: '#4885ed',
                            backgroundColor: 'rgba(72, 133, 237, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 6,
                            pointBackgroundColor: forecastData.length > 0 ? forecastData.map(f => f.is_weekend || f.is_holiday ? '#4885ed' : '#48c78e') : '#4885ed',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.datasetIndex === 1) {
                                        return 'Prediksi: ' + Math.round(context.parsed.y) + ' pengunjung';
                                    }
                                    return context.dataset.label + ': ' + Math.round(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Jumlah Pengunjung' },
                            ticks: {
                                callback: function(value) {
                                    return Math.round(value).toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            title: { display: true, text: 'Hari' }
                        }
                    }
                }
            });
        } else {
            console.error('Canvas element forecastChart not found!');
        }

        // Period Selector - Dynamic Trend Chart Update

        function updateTrendChart(period) {
            const titles = {
                '6_months': 'Tren Pengunjung 6 Bulan Terakhir',
                '1_month_weekly': 'Tren Pengunjung Bulan Ini (Per Minggu)',
                'daily': 'Tren Pengunjung 7 Hari Terakhir'
            };

            document.getElementById('trendTitle').textContent = titles[period] || titles['6_months'];

            fetch('/api/trend-data?period=' + period)
                .then(response => response.json())
                .then(result => {
                    console.log('Trend data updated:', result);

                    const data = result.data;

                    // Destroy existing chart
                    if (trendChartInstance) {
                        trendChartInstance.destroy();
                    }

                    // Create new chart
                    const trendCanvas = document.getElementById('trendChart');
                    trendChartInstance = new Chart(trendCanvas, {
                        type: 'line',
                        data: {
                            labels: data.map(item => item.label),
                            datasets: [
                                {
                                    label: 'Total Pengunjung',
                                    data: data.map(item => item.total),
                                    borderColor: '#34395e',
                                    backgroundColor: 'rgba(52, 57, 94, 0.1)',
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 5,
                                    pointBackgroundColor: '#34395e'
                                },
                                {
                                    label: 'Hari Libur',
                                    data: data.map(item => item.weekend),
                                    borderColor: '#6777ef',
                                    backgroundColor: 'rgba(103, 119, 239, 0.1)',
                                    borderWidth: 2,
                                    fill: false,
                                    tension: 0.4,
                                    pointRadius: 4
                                },
                                {
                                    label: 'Hari Biasa',
                                    data: data.map(item => item.weekday),
                                    borderColor: '#48c78e',
                                    backgroundColor: 'rgba(72, 199, 142, 0.1)',
                                    borderWidth: 2,
                                    fill: false,
                                    tension: 0.4,
                                    pointRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: true, position: 'top' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y + ' pengunjung';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return value.toLocaleString();
                                        }
                                    }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });

                    // Update insight
                    updateTrendInsight(data, period);
                })
                .catch(error => {
                    console.error('Error fetching trend data:', error);
                });
        }

        function updateTrendInsight(data, period) {
            const insightDiv = document.getElementById('trendInsight');
            if (!insightDiv) return;

            let insightHtml = '<strong><i class=\"fas fa-chart-line\"></i> Insight:</strong> ';

            if (data.length > 0) {
                const maxData = data.reduce((max, item) => {
                    const total = parseInt(item.total) || 0;
                    const maxTotal = parseInt(max.total) || 0;
                    return total > maxTotal ? item : max;
                }, data[0]);

                const totalVisitors = data.reduce((sum, item) => sum + (parseInt(item.total) || 0), 0);
                const avgVisitors = Math.round(totalVisitors / data.length);

                if (period === '6_months') {
                    insightHtml += `Puncak pengunjung terjadi di <strong>${maxData.label}</strong> dengan ${parseInt(maxData.total).toLocaleString('id-ID')} pengunjung. `;
                    insightHtml += `Rata-rata ${avgVisitors} pengunjung per bulan.`;
                } else if (period === '1_month_weekly') {
                    insightHtml += `Minggu tersibuk adalah <strong>${maxData.label}</strong> dengan ${parseInt(maxData.total).toLocaleString('id-ID')} pengunjung. `;
                    insightHtml += `Rata-rata ${avgVisitors} pengunjung per minggu.`;
                } else if (period === 'daily') {
                    insightHtml += `Hari tersibuk adalah <strong>${maxData.label}</strong> dengan ${parseInt(maxData.total).toLocaleString('id-ID')} pengunjung. `;
                    insightHtml += `Rata-rata ${avgVisitors} pengunjung per hari.`;
                }
            }

            insightDiv.innerHTML = insightHtml;
        }

        // Event listener for period selector
        document.getElementById('periodSelector').addEventListener('change', function() {
            updateTrendChart(this.value);
        });

        console.log('All charts initialized!');
    </script>
@endpush
