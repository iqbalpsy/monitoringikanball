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
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-calendar-alt mr-2"></i>Custom Report
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-download mr-2"></i>Export All
            </button>
        </div>
    </div>
</div>

<!-- Report Filters -->
<div class="card-gradient rounded-lg shadow-lg p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Filters</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
                <option>Last 3 Months</option>
                <option>Custom Range</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Device</label>
            <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option>All Devices</option>
                @foreach($devices as $device)
                <option value="{{ $device->id }}">{{ $device->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Parameter</label>
            <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option>All Parameters</option>
                <option>pH Level</option>
                <option>Temperature</option>
                <option>Oxygen Level</option>
            </select>
        </div>
        <div class="flex items-end">
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-search mr-2"></i>Generate Report
            </button>
        </div>
    </div>
</div>

<!-- Analytics Summary -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalReadings) }}</p>
                <p class="text-gray-600">Total Readings</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>12% vs last month
                </p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">98.5%</p>
                <p class="text-gray-600">System Uptime</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>2% vs last month
                </p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-20">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ $alertsCount }}</p>
                <p class="text-gray-600">Alerts Generated</p>
                <p class="text-sm text-red-600 mt-1">
                    <i class="fas fa-arrow-down mr-1"></i>8% vs last month
                </p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                <i class="fas fa-tachometer-alt text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">95.2%</p>
                <p class="text-gray-600">Avg. Performance</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>5% vs last month
                </p>
            </div>
        </div>
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

<!-- Device Performance -->
<div class="card-gradient rounded-lg shadow-lg p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Device Performance Analysis</h3>
    <div class="overflow-x-auto">
        <table class="w-full table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Device</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Uptime</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Data Points</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Avg pH</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Avg Temp</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Avg O2</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Alerts</th>
                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Performance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devices as $device)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-4 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-xs mr-3">
                                {{ substr($device->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $device->name }}</p>
                                <p class="text-sm text-gray-500">{{ $device->location }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center">
                            <div class="w-full bg-gray-200 rounded-full h-2 mr-3" style="width: 60px;">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ rand(85, 99) }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ rand(85, 99) }}%</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-gray-900">{{ number_format(rand(1000, 5000)) }}</td>
                    <td class="py-4 px-6">
                        <span class="text-gray-900">{{ number_format(rand(65, 85)/10, 1) }}</span>
                        <span class="text-xs text-gray-500 ml-1">pH</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="text-gray-900">{{ number_format(rand(240, 280)/10, 1) }}</span>
                        <span class="text-xs text-gray-500 ml-1">°C</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="text-gray-900">{{ number_format(rand(60, 100)/10, 1) }}</span>
                        <span class="text-xs text-gray-500 ml-1">mg/L</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ rand(0, 10) < 3 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ rand(0, 15) }}
                        </span>
                    </td>
                    <td class="py-4 px-6">
                        @php $performance = rand(85, 98) @endphp
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $performance >= 95 ? 'bg-green-100 text-green-800' : ($performance >= 90 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $performance }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
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
document.addEventListener('DOMContentLoaded', function() {
    initializeTrendsChart();
    initializeAlertsChart();
});

function initializeTrendsChart() {
    const ctx = document.getElementById('trendsChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: generateDayLabels(30),
            datasets: [
                {
                    label: 'pH Level',
                    data: generateTrendData(30, 6.5, 8.5),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Temperature (°C)',
                    data: generateTrendData(30, 24, 28),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Oxygen (mg/L)',
                    data: generateTrendData(30, 5, 10),
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
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Temperature Alerts', 'pH Alerts', 'Oxygen Alerts', 'System Alerts'],
            datasets: [{
                data: [35, 25, 20, 20],
                backgroundColor: [
                    'rgb(239, 68, 68)',
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(168, 85, 247)'
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
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        }
    });
}

function generateDayLabels(days) {
    const labels = [];
    const today = new Date();
    
    for (let i = days - 1; i >= 0; i--) {
        const date = new Date(today.getTime() - (i * 24 * 60 * 60 * 1000));
        labels.push(date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
    }
    
    return labels;
}

function generateTrendData(days, min, max) {
    const data = [];
    let lastValue = (min + max) / 2;
    
    for (let i = 0; i < days; i++) {
        // Generate trending data with some continuity
        const change = (Math.random() - 0.5) * (max - min) * 0.1;
        lastValue = Math.max(min, Math.min(max, lastValue + change));
        data.push(Number(lastValue.toFixed(1)));
    }
    
    return data;
}
</script>
@endpush
