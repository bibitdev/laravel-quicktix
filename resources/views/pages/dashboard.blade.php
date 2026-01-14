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
            height: 300px;
        }
        .card-header-action select {
            height: 35px;
            padding: 0 15px;
            border-radius: 4px;
            border: 1px solid #e4e6fc;
            font-size: 13px;
            color: #34395e;
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
                <!-- Key Metrics -->
                <div class="row">
                    <!-- Daily Visitors -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Pengunjung Hari Ini</h4>
                                </div>
                                <div class="card-body">
                                    {{ $daily_visitors }}
                                </div>
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
                                    <h4>Pendapatan Hari Ini</h4>
                                </div>
                                <div class="card-body">
                                    Rp {{ number_format($daily_revenue) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets Sold -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Tiket Terjual</h4>
                                </div>
                                <div class="card-body">
                                    {{ $tickets_sold }}
                                </div>
                            </div>
                        </div>
                    </div>
                        <!-- Card: Active Sessions -->
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Transaksi</h4>
                                </div>
                                <div class="card-body">
                                    {{ $total_transactions ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                </div>

                <!-- Charts -->
                <div class="row">
                    <!-- Visitor Trends (Tiket Terjual) -->
                    <div class="col-lg-6 col-md-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Tren Penjualan Tiket (7 Hari Terakhir)</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="visitorTrend" height="180"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Revenue -->
                    <div class="col-lg-6 col-md-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Tren Pendapatan (7 Hari Terakhir)</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="salesTrend" height="180"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Payment Methods -->
                    <div class="col-lg-12 col-md-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Transaksi per Metode Pembayaran</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="paymentMethods" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraries -->
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>

    <!-- Dashboard Analytics Charts -->
    <script>
        // Prepare data from PHP (array format from controller)
        const ticketSalesTrend = @json($ticket_sales_trend ?? []);
        const revenueTrend = @json($revenue_trend ?? []);
        const paymentMethods = @json($payment_methods ?? []);

        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        console.log('Ticket Sales Trend:', ticketSalesTrend);
        console.log('Revenue Trend:', revenueTrend);
        console.log('Payment Methods:', paymentMethods);

            // Extract dates and totals for charts
            const visitorData = {
                dates: ticketSalesTrend.map(item => item.date),
                counts: ticketSalesTrend.map(item => item.total)
            };

            const salesData = {
                dates: revenueTrend.map(item => item.date),
                totals: revenueTrend.map(item => item.total)
            };

            const paymentData = {
                methods: paymentMethods.map(item => item.payment_method),
                counts: paymentMethods.map(item => item.count),
                totals: paymentMethods.map(item => item.total)
            };

        // Visitor Trend Chart (Tiket Terjual)
        const visitorCanvas = document.getElementById('visitorTrend');
        if (!visitorCanvas) {
            console.error('Canvas element visitorTrend not found!');
        } else {
            console.log('Creating visitorTrend chart...');
            const visitorTrend = new Chart(visitorCanvas, {
            type: 'line',
            data: {
                labels: visitorData.dates.length > 0 ? visitorData.dates : ['Belum ada data'],
                datasets: [{
                    label: 'Jumlah Tiket Terjual',
                    data: visitorData.counts.length > 0 ? visitorData.counts : [0],
                    borderColor: '#6777ef',
                    backgroundColor: 'rgba(103, 119, 239, 0.15)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#6777ef',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'start',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Tiket: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
        }

        // Sales Trend Chart (Pendapatan)
        const salesCanvas = document.getElementById('salesTrend');
        if (!salesCanvas) {
            console.error('Canvas element salesTrend not found!');
        } else {
            console.log('Creating salesTrend chart...');
            const salesTrend = new Chart(salesCanvas, {
            type: 'line',
            data: {
                labels: salesData.dates.length > 0 ? salesData.dates : ['Belum ada data'],
                datasets: [{
                    label: 'Total Pendapatan',
                    data: salesData.totals.length > 0 ? salesData.totals : [0],
                    borderColor: '#48c78e',
                    backgroundColor: 'rgba(72, 199, 142, 0.15)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#48c78e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'start',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + 'M';
                                } else if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + 'k';
                                }
                                return value;
                            },
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
        }

        // Payment Methods Chart (Horizontal Bar Chart)
        const paymentCanvas = document.getElementById('paymentMethods');
        if (!paymentCanvas) {
            console.error('Canvas element paymentMethods not found!');
        } else {
            console.log('Creating paymentMethods chart...');
            const paymentMethodsChart = new Chart(paymentCanvas, {
            type: 'bar',
            data: {
                labels: paymentData.methods.length > 0 ? paymentData.methods : ['Belum ada data'],
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: paymentData.counts.length > 0 ? paymentData.counts : [0],
                    backgroundColor: [
                        'rgba(103, 119, 239, 0.8)',
                        'rgba(72, 199, 142, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    borderColor: [
                        'rgba(103, 119, 239, 1)',
                        'rgba(72, 199, 142, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'start',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' transaksi';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
        }

        console.log('All charts initialized!');
    </script>
@endpush
