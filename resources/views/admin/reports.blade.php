@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
            <p class="text-gray-600 mt-1">Analisis data dan laporan sistem monitoring</p>
        </div>
        <div class="flex space-x-3">
            <div class="relative">
                <button id="exportMenuBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-download mr-2"></i>Export
                    <i class="fas fa-chevron-down ml-2 text-sm"></i>
                </button>
                <!-- Dropdown Menu -->
                <div id="exportMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl z-50 border border-gray-200">
                    <a href="{{ route('admin.reports.export', ['format' => 'pdf', 'date_range' => $dateRange, 'parameter' => $parameter]) }}" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-100">
                        <i class="fas fa-file-pdf text-red-600 mr-2"></i>Export PDF
                    </a>
                    <a href="{{ route('admin.reports.export', ['format' => 'excel', 'date_range' => $dateRange, 'parameter' => $parameter]) }}" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-100">
                        <i class="fas fa-file-excel text-green-600 mr-2"></i>Export Excel
                    </a>
                    <a href="{{ route('admin.reports.export', ['format' => 'csv', 'date_range' => $dateRange, 'parameter' => $parameter]) }}" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-file-csv text-blue-600 mr-2"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Filters -->
<div class="card-gradient rounded-lg shadow-lg p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Filters - Kolam 1</h3>
    <form action="{{ route('admin.reports') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Date Range -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <select name="date_range" class="form-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="last_7_days" {{ $dateRange == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="last_30_days" {{ $dateRange == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                <option value="last_3_months" {{ $dateRange == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
            </select>
        </div>

        <!-- Parameter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Parameter</label>
            <select name="parameter" class="form-select w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="all" {{ $parameter == 'all' ? 'selected' : '' }}>All Parameters</option>
                <option value="temperature" {{ $parameter == 'temperature' ? 'selected' : '' }}>Temperature</option>
                <option value="ph" {{ $parameter == 'ph' ? 'selected' : '' }}>pH Level</option>
                <option value="oxygen" {{ $parameter == 'oxygen' ? 'selected' : '' }}>Oxygen</option>
            </select>
        </div>

        <!-- Generate Button -->
        <div class="flex items-end">
            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-2 rounded-lg hover:shadow-lg transition-all duration-300">
                <i class="fas fa-chart-line mr-2"></i>Generate Report
            </button>
        </div>
    </form>
</div>

<!-- Analytics Overview -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Readings -->
    <div class="card-gradient rounded-lg shadow-lg p-6 border-l-4 border-blue-600">
        <div class="flex items-center justify-between mb-2">
            <div class="p-2 rounded-full bg-blue-100">
                <i class="fas fa-database text-blue-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium {{ $readingsChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                <i class="fas fa-arrow-{{ $readingsChange >= 0 ? 'up' : 'down' }}"></i>
                {{ number_format(abs($readingsChange), 1) }}%
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($totalReadings) }}</p>
        <p class="text-sm text-gray-600">Total Readings</p>
    </div>

    <!-- System Uptime -->
    <div class="card-gradient rounded-lg shadow-lg p-6 border-l-4 border-green-600">
        <div class="flex items-center justify-between mb-2">
            <div class="p-2 rounded-full bg-green-100">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-green-600">
                <i class="fas fa-arrow-up"></i>
                {{ number_format($systemUptime, 1) }}%
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($systemUptime, 1) }}%</p>
        <p class="text-sm text-gray-600">System Uptime</p>
    </div>

    <!-- Alerts -->
    <div class="card-gradient rounded-lg shadow-lg p-6 border-l-4 border-red-600">
        <div class="flex items-center justify-between mb-2">
            <div class="p-2 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium {{ $alertsChange >= 0 ? 'text-red-600' : 'text-green-600' }}">
                <i class="fas fa-arrow-{{ $alertsChange >= 0 ? 'up' : 'down' }}"></i>
                {{ number_format(abs($alertsChange), 1) }}%
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900 mb-1">{{ $alertsCount }}</p>
        <p class="text-sm text-gray-600">Total Alerts</p>
    </div>

    <!-- Performance -->
    <div class="card-gradient rounded-lg shadow-lg p-6 border-l-4 border-purple-600">
        <div class="flex items-center justify-between mb-2">
            <div class="p-2 rounded-full bg-purple-100">
                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-purple-600">
                <i class="fas fa-star"></i>
                Excellent
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($avgPerformance, 1) }}%</p>
        <p class="text-sm text-gray-600">Avg Performance</p>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
    <!-- Parameter Trends -->
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Parameter Trends (Last 30 Days)</h3>
            <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option>All Parameters</option>
                <option>pH Level</option>
                <option>Temperature</option>
                <option>Oxygen Level</option>
            </select>
        </div>
        <div class="h-80">
            <canvas id="trendsChart"></canvas>
        </div>
    </div>

    <!-- Alert Distribution -->
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Alert Distribution</h3>
        <div class="h-80">
            <canvas id="alertsChart"></canvas>
        </div>
    </div>
</div>

<!-- Pond Performance -->
<div class="card-gradient rounded-lg shadow-lg p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Pond Performance Analysis - Kolam 1</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Temperature Stats -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-6 border border-orange-200">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-full bg-orange-500 bg-opacity-20">
                    <i class="fas fa-thermometer-half text-orange-600 text-2xl"></i>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-200 text-orange-800">
                    {{ $tempAlerts }} Alerts
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mb-1">{{ number_format($avgTemp, 1) }}°C</p>
            <p class="text-sm text-gray-600 mb-3">Average Temperature</p>
            <div class="flex items-center text-sm">
                <span class="text-gray-500">Normal Range:</span>
                <span class="ml-2 font-medium text-gray-700">24°C - 30°C</span>
            </div>
        </div>

        <!-- pH Stats -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                    <i class="fas fa-flask text-blue-600 text-2xl"></i>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-200 text-blue-800">
                    {{ $phAlerts }} Alerts
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mb-1">{{ number_format($avgPh, 1) }}</p>
            <p class="text-sm text-gray-600 mb-3">Average pH Level</p>
            <div class="flex items-center text-sm">
                <span class="text-gray-500">Normal Range:</span>
                <span class="ml-2 font-medium text-gray-700">6.5 - 8.5</span>
            </div>
        </div>

        <!-- Oxygen Stats -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                    <i class="fas fa-wind text-green-600 text-2xl"></i>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-200 text-green-800">
                    {{ $oxygenAlerts }} Alerts
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mb-1">{{ number_format($avgOxygen, 1) }} mg/L</p>
            <p class="text-sm text-gray-600 mb-3">Average Oxygen Level</p>
            <div class="flex items-center text-sm">
                <span class="text-gray-500">Normal Range:</span>
                <span class="ml-2 font-medium text-gray-700">5.0 - 8.0 mg/L</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Reports -->
<div class="card-gradient rounded-lg shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Recent Reports</h3>
        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            <i class="fas fa-plus mr-1"></i>New Report
        </button>
    </div>
    
    <div class="space-y-4">
        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-blue-100 mr-4">
                    <i class="fas fa-file-pdf text-blue-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Monthly System Report - December 2024</h4>
                    <p class="text-sm text-gray-500">Generated on Dec 31, 2024 • 2.4 MB</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition-all">
                    <i class="fas fa-download"></i>
                </button>
                <button class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-gray-100 transition-all">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-green-100 mr-4">
                    <i class="fas fa-file-excel text-green-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Sensor Data Export - Week 52</h4>
                    <p class="text-sm text-gray-500">Generated on Dec 29, 2024 • 1.8 MB</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition-all">
                    <i class="fas fa-download"></i>
                </button>
                <button class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-gray-100 transition-all">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-purple-100 mr-4">
                    <i class="fas fa-chart-bar text-purple-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Performance Analysis Report</h4>
                    <p class="text-sm text-gray-500">Generated on Dec 25, 2024 • 3.1 MB</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition-all">
                    <i class="fas fa-download"></i>
                </button>
                <button class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-gray-100 transition-all">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Pass PHP data to JavaScript
const trendData = @json($trendData);
const tempAlerts = {{ $tempAlerts }};
const phAlerts = {{ $phAlerts }};
const oxygenAlerts = {{ $oxygenAlerts }};

document.addEventListener('DOMContentLoaded', function() {
    initializeTrendsChart();
    initializeAlertsChart();
});

function initializeTrendsChart() {
    const ctx = document.getElementById('trendsChart').getContext('2d');
    
    // Prepare data from database
    const labels = [];
    const temperatureData = [];
    const phData = [];
    const oxygenData = [];
    
    trendData.forEach(item => {
        const date = new Date(item.recorded_at);
        labels.push(date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit' }));
        temperatureData.push(parseFloat(item.temperature));
        phData.push(parseFloat(item.ph));
        oxygenData.push(parseFloat(item.oxygen));
    });
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'pH Level',
                    data: phData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Temperature (°C)',
                    data: temperatureData,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Oxygen (mg/L)',
                    data: oxygenData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            }
        }
    });
}

function initializeAlertsChart() {
    const ctx = document.getElementById('alertsChart').getContext('2d');
    
    // Calculate total alerts
    const totalAlerts = tempAlerts + phAlerts + oxygenAlerts;
    
    // Calculate percentages
    const tempPercent = totalAlerts > 0 ? ((tempAlerts / totalAlerts) * 100).toFixed(1) : 0;
    const phPercent = totalAlerts > 0 ? ((phAlerts / totalAlerts) * 100).toFixed(1) : 0;
    const oxygenPercent = totalAlerts > 0 ? ((oxygenAlerts / totalAlerts) * 100).toFixed(1) : 0;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Temperature Alerts', 'pH Alerts', 'Oxygen Alerts'],
            datasets: [{
                data: [tempAlerts, phAlerts, oxygenAlerts],
                backgroundColor: [
                    'rgb(239, 68, 68)',
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const percent = totalAlerts > 0 ? ((value / totalAlerts) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' (' + percent + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Export menu dropdown
const exportMenuBtn = document.getElementById('exportMenuBtn');
const exportMenu = document.getElementById('exportMenu');

if (exportMenuBtn && exportMenu) {
    exportMenuBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        exportMenu.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!exportMenuBtn.contains(e.target) && !exportMenu.contains(e.target)) {
            exportMenu.classList.add('hidden');
        }
    });
}
</script>
@endpush
