@extends('layouts.app')

@section('title', 'Real-time Monitoring')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Real-time Monitoring</h1>
            <p class="text-gray-600 mt-1">Monitor kondisi kolam ikan secara real-time</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-download mr-2"></i>Export Data
            </button>
            <button id="refreshData" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
        </div>
    </div>
</div>

<!-- Real-time Status Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">pH Level</h3>
                <div class="flex items-center">
                    <span id="phValue" class="text-3xl font-bold text-blue-600">7.2</span>
                    <div class="ml-3">
                        <span id="phStatus" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Normal</span>
                        <p class="text-xs text-gray-500 mt-1">Range: 6.5-8.5</p>
                    </div>
                </div>
            </div>
            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                <i class="fas fa-vial text-blue-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="phProgress" class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: 45%"></div>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Temperature</h3>
                <div class="flex items-center">
                    <span id="tempValue" class="text-3xl font-bold text-red-600">26.5°C</span>
                    <div class="ml-3">
                        <span id="tempStatus" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Optimal</span>
                        <p class="text-xs text-gray-500 mt-1">Range: 24-28°C</p>
                    </div>
                </div>
            </div>
            <div class="p-3 rounded-full bg-red-500 bg-opacity-20">
                <i class="fas fa-thermometer-half text-red-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="tempProgress" class="bg-red-600 h-2 rounded-full transition-all duration-500" style="width: 65%"></div>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Oxygen Level</h3>
                <div class="flex items-center">
                    <span id="oxygenValue" class="text-3xl font-bold text-green-600">8.2 mg/L</span>
                    <div class="ml-3">
                        <span id="oxygenStatus" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                        <p class="text-xs text-gray-500 mt-1">Min: 5.0 mg/L</p>
                    </div>
                </div>
            </div>
            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                <i class="fas fa-wind text-green-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="oxygenProgress" class="bg-green-600 h-2 rounded-full transition-all duration-500" style="width: 82%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
    <!-- Real-time Chart -->
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Real-time Trends</h3>
            <select id="chartTimeRange" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="1h">Last 1 Hour</option>
                <option value="6h">Last 6 Hours</option>
                <option value="24h">Last 24 Hours</option>
            </select>
        </div>
        <div class="h-80">
            <canvas id="realtimeChart"></canvas>
        </div>
    </div>

    <!-- Device Status -->
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Device Status</h3>
        <div class="space-y-4">
            @foreach($devices as $device)
            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full {{ $device->status === 'online' ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $device->name }}</h4>
                        <p class="text-sm text-gray-500">{{ $device->location }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $device->status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($device->status) }}
                    </span>
                    <p class="text-xs text-gray-500 mt-1">{{ $device->updated_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Alerts and Notifications -->
<div class="card-gradient rounded-lg shadow-lg p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">System Alerts</h3>
        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            <i class="fas fa-cog mr-1"></i>Alert Settings
        </button>
    </div>
    
    <div class="space-y-4" id="alertContainer">
        <!-- Sample alerts will be populated here -->
        <div class="flex items-start p-4 border-l-4 border-yellow-400 bg-yellow-50 rounded-r-lg">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
            </div>
            <div class="ml-3 flex-1">
                <h4 class="font-medium text-yellow-800">Temperature Warning</h4>
                <p class="text-sm text-yellow-700 mt-1">Temperature in Pond A is approaching upper limit (27.8°C)</p>
                <p class="text-xs text-yellow-600 mt-2">2 minutes ago • Device: Sensor-001</p>
            </div>
            <button class="text-yellow-600 hover:text-yellow-800 ml-4">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex items-start p-4 border-l-4 border-blue-400 bg-blue-50 rounded-r-lg">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600 text-lg"></i>
            </div>
            <div class="ml-3 flex-1">
                <h4 class="font-medium text-blue-800">System Info</h4>
                <p class="text-sm text-blue-700 mt-1">Automatic feeding scheduled in 15 minutes</p>
                <p class="text-xs text-blue-600 mt-2">5 minutes ago • System</p>
            </div>
            <button class="text-blue-600 hover:text-blue-800 ml-4">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex items-start p-4 border-l-4 border-green-400 bg-green-50 rounded-r-lg">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-600 text-lg"></i>
            </div>
            <div class="ml-3 flex-1">
                <h4 class="font-medium text-green-800">System Normal</h4>
                <p class="text-sm text-green-700 mt-1">All parameters are within normal ranges</p>
                <p class="text-xs text-green-600 mt-2">10 minutes ago • Overall System</p>
            </div>
            <button class="text-green-600 hover:text-green-800 ml-4">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Real-time monitoring functionality
let realtimeChart;
let updateInterval;

document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    startRealTimeUpdates();
    
    // Refresh button
    document.getElementById('refreshData').addEventListener('click', function() {
        updateSensorData();
    });
    
    // Chart time range selector
    document.getElementById('chartTimeRange').addEventListener('change', function() {
        updateChartTimeRange(this.value);
    });
});

function initializeChart() {
    const ctx = document.getElementById('realtimeChart').getContext('2d');
    
    realtimeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: generateTimeLabels(12), // Last 12 data points
            datasets: [
                {
                    label: 'pH',
                    data: generateRandomData(12, 6.5, 8.5),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Temperature (°C)',
                    data: generateRandomData(12, 24, 28),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Oxygen (mg/L)',
                    data: generateRandomData(12, 5, 10),
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
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

function startRealTimeUpdates() {
    updateInterval = setInterval(() => {
        updateSensorData();
        updateChart();
    }, 5000); // Update every 5 seconds
}

function updateSensorData() {
    // Simulate real-time data updates
    const newPh = (6.5 + Math.random() * 2).toFixed(1);
    const newTemp = (24 + Math.random() * 4).toFixed(1);
    const newOxygen = (5 + Math.random() * 5).toFixed(1);
    
    // Update pH
    document.getElementById('phValue').textContent = newPh;
    updateStatus('ph', newPh, 6.5, 8.5);
    updateProgress('ph', newPh, 6.5, 8.5);
    
    // Update Temperature
    document.getElementById('tempValue').textContent = newTemp + '°C';
    updateStatus('temp', newTemp, 24, 28);
    updateProgress('temp', newTemp, 24, 28);
    
    // Update Oxygen
    document.getElementById('oxygenValue').textContent = newOxygen + ' mg/L';
    updateStatus('oxygen', newOxygen, 5, 10);
    updateProgress('oxygen', newOxygen, 5, 10);
}

function updateStatus(type, value, min, max) {
    const statusElement = document.getElementById(type + 'Status');
    value = parseFloat(value);
    
    if (value >= min && value <= max) {
        statusElement.textContent = type === 'ph' ? 'Normal' : (type === 'temp' ? 'Optimal' : 'Good');
        statusElement.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800';
    } else {
        statusElement.textContent = 'Warning';
        statusElement.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800';
    }
}

function updateProgress(type, value, min, max) {
    const progressElement = document.getElementById(type + 'Progress');
    value = parseFloat(value);
    const percentage = ((value - min) / (max - min)) * 100;
    progressElement.style.width = Math.max(0, Math.min(100, percentage)) + '%';
}

function updateChart() {
    const now = new Date();
    const newLabel = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    
    // Add new data point
    realtimeChart.data.labels.push(newLabel);
    realtimeChart.data.datasets[0].data.push((6.5 + Math.random() * 2).toFixed(1));
    realtimeChart.data.datasets[1].data.push((24 + Math.random() * 4).toFixed(1));
    realtimeChart.data.datasets[2].data.push((5 + Math.random() * 5).toFixed(1));
    
    // Keep only last 12 data points
    if (realtimeChart.data.labels.length > 12) {
        realtimeChart.data.labels.shift();
        realtimeChart.data.datasets.forEach(dataset => dataset.data.shift());
    }
    
    realtimeChart.update('none');
}

function generateTimeLabels(count) {
    const labels = [];
    const now = new Date();
    
    for (let i = count - 1; i >= 0; i--) {
        const time = new Date(now.getTime() - (i * 5 * 60 * 1000)); // 5 minutes intervals
        labels.push(time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
    }
    
    return labels;
}

function generateRandomData(count, min, max) {
    const data = [];
    for (let i = 0; i < count; i++) {
        data.push((min + Math.random() * (max - min)).toFixed(1));
    }
    return data;
}

function updateChartTimeRange(range) {
    // Update chart based on selected time range
    let dataPoints, intervalMinutes;
    
    switch(range) {
        case '1h':
            dataPoints = 12;
            intervalMinutes = 5;
            break;
        case '6h':
            dataPoints = 24;
            intervalMinutes = 15;
            break;
        case '24h':
            dataPoints = 24;
            intervalMinutes = 60;
            break;
        default:
            dataPoints = 12;
            intervalMinutes = 5;
    }
    
    realtimeChart.data.labels = generateTimeLabelsWithInterval(dataPoints, intervalMinutes);
    realtimeChart.data.datasets[0].data = generateRandomData(dataPoints, 6.5, 8.5);
    realtimeChart.data.datasets[1].data = generateRandomData(dataPoints, 24, 28);
    realtimeChart.data.datasets[2].data = generateRandomData(dataPoints, 5, 10);
    
    realtimeChart.update();
}

function generateTimeLabelsWithInterval(count, intervalMinutes) {
    const labels = [];
    const now = new Date();
    
    for (let i = count - 1; i >= 0; i--) {
        const time = new Date(now.getTime() - (i * intervalMinutes * 60 * 1000));
        labels.push(time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
    }
    
    return labels;
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>
@endpush
