<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AquaMonitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .sidebar {
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        }
        .menu-item {
            transition: all 0.3s ease;
        }
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            padding-left: 2rem;
        }
        .menu-item.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid white;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .notification {
            animation: slideIn 0.5s ease;
        }
        
        /* Chart specific styles */
        #sensorChart {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            height: 350px !important;
            max-height: 350px !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Success Notification -->
    @if(session('success'))
    <div id="notification" class="fixed top-4 right-4 z-50 notification">
        <div class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3">
            <i class="fas fa-check-circle text-2xl"></i>
            <div>
                <p class="font-semibold">Data 24 jam terakhir berhasil diperbarui!</p>
            </div>
            <button onclick="closeNotification()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 sidebar text-white flex-shrink-0">
            <div class="p-6">
                <!-- Logo -->
                <div class="flex items-center space-x-3 mb-8">
                    <div class="bg-white rounded-full p-2">
                        <i class="fas fa-fish text-purple-600 text-2xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold">AquaMonitor</h1>
                </div>

                <!-- Menu -->
                <nav class="space-y-2">
                    <a href="{{ route('user.dashboard') }}" class="menu-item active flex items-center space-x-3 px-4 py-3 rounded-lg">
                        <i class="fas fa-th-large text-lg"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('user.history') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                        <i class="fas fa-history text-lg"></i>
                        <span>History</span>
                    </a>
                    <a href="{{ route('user.profile') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                        <i class="fas fa-user text-lg"></i>
                        <span>Profile</span>
                    </a>
                    <a href="{{ route('user.settings') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                        <i class="fas fa-cog text-lg"></i>
                        <span>Settings</span>
                    </a>
                </nav>
            </div>

            <!-- Logout -->
            <div class="absolute bottom-0 w-64 p-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg w-full text-left hover:bg-red-500">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50">
            <!-- Header -->
            <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
                    <p class="text-gray-600 text-sm">Monitoring Real-time Kolam Ikan</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="relative">
                        <i class="fas fa-bell text-gray-600 text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                    </button>
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-sm text-gray-500 capitalize">{{ Auth::user()->role }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-8">
                <!-- Sensor Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Suhu Air Card -->
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover border-t-4 border-orange-500">
                        <div class="flex justify-between items-start mb-4">
                            <div class="bg-gradient-to-br from-orange-400 to-orange-600 p-4 rounded-xl">
                                <i class="fas fa-thermometer-half text-white text-2xl"></i>
                            </div>
                            @if($latestData && $settings)
                                @php
                                    $isTempNormal = $latestData->temperature >= $settings->temp_min && $latestData->temperature <= $settings->temp_max;
                                @endphp
                                @if($isTempNormal)
                                    <span class="bg-green-100 text-green-600 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                                        <i class="fas fa-check mr-1"></i> Normal
                                    </span>
                                @else
                                    <span class="bg-orange-100 text-orange-600 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Perhatian
                                    </span>
                                @endif
                            @else
                                <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                                    <i class="fas fa-minus mr-1"></i> N/A
                                </span>
                            @endif
                        </div>
                        <h3 class="text-gray-600 text-sm mb-2">Suhu Air</h3>
                        <div class="flex items-end space-x-2">
                            <span id="temp-value" class="text-4xl font-bold text-gray-800">{{ $latestData ? number_format($latestData->temperature, 1) : '0' }}</span>
                            <span class="text-2xl text-gray-600">°C</span>
                        </div>
                        <p class="text-gray-500 text-xs mt-2">
                            @if($settings)
                                Range: {{ $settings->temp_min }}°C - {{ $settings->temp_max }}°C
                            @else
                                Suhu optimal untuk pertumbuhan ikan
                            @endif
                        </p>
                    </div>

                    <!-- pH Air Card -->
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover border-t-4 border-teal-500">
                        <div class="flex justify-between items-start mb-4">
                            <div class="bg-gradient-to-br from-teal-400 to-teal-600 p-4 rounded-xl">
                                <i class="fas fa-flask text-white text-2xl"></i>
                            </div>
                            @if($latestData && $settings)
                                @php
                                    $isPhNormal = $latestData->ph >= $settings->ph_min && $latestData->ph <= $settings->ph_max;
                                @endphp
                                @if($isPhNormal)
                                    <span class="bg-green-100 text-green-600 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                                        <i class="fas fa-check mr-1"></i> Baik
                                    </span>
                                @else
                                    <span class="bg-orange-100 text-orange-600 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Perhatian
                                    </span>
                                @endif
                            @else
                                <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                                    <i class="fas fa-minus mr-1"></i> N/A
                                </span>
                            @endif
                        </div>
                        <h3 class="text-gray-600 text-sm mb-2">pH Air</h3>
                        <div class="flex items-end space-x-2">
                            <span id="ph-value" class="text-4xl font-bold text-gray-800">{{ $latestData ? number_format($latestData->ph, 1) : '0' }}</span>
                        </div>
                        <p class="text-gray-500 text-xs mt-2">
                            @if($settings)
                                Range: {{ $settings->ph_min }} - {{ $settings->ph_max }}
                            @else
                                Tingkat keasaman air kolam
                            @endif
                        </p>
                    </div>

                    <!-- Oksigen Card -->
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover border-t-4 border-green-500">
                        <div class="flex justify-between items-start mb-4">
                            <div class="bg-gradient-to-br from-green-400 to-green-600 p-4 rounded-xl">
                                <i class="fas fa-wind text-white text-2xl"></i>
                            </div>
                            @if($latestData && $settings)
                                @php
                                    $isOxygenNormal = $latestData->oxygen >= $settings->oxygen_min && $latestData->oxygen <= $settings->oxygen_max;
                                @endphp
                                @if($isOxygenNormal)
                                    <span class="bg-green-100 text-green-600 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                                        <i class="fas fa-check mr-1"></i> Optimal
                                    </span>
                                @else
                                    <span class="bg-orange-100 text-orange-600 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Perhatian
                                    </span>
                                @endif
                            @else
                                <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full flex items-center">
                                    <i class="fas fa-minus mr-1"></i> N/A
                                </span>
                            @endif
                        </div>
                        <h3 class="text-gray-600 text-sm mb-2">Oksigen (mg/L)</h3>
                        <div class="flex items-end space-x-2">
                            <span id="oxygen-value" class="text-4xl font-bold text-gray-800">{{ $latestData ? number_format($latestData->oxygen, 1) : '0' }}</span>
                        </div>
                        <p class="text-gray-500 text-xs mt-2">
                            @if($settings)
                                Range: {{ $settings->oxygen_min }} - {{ $settings->oxygen_max }} mg/L
                            @else
                                Kadar oksigen terlarut dalam air
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                            <h3 class="text-xl font-bold text-gray-800">Monitoring Per Jam - Sensor Data</h3>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Firebase Data Source Only -->
                            <div class="flex space-x-2">
                                <div class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg flex items-center space-x-2">
                                    <i class="fas fa-fire"></i>
                                    <span>Firebase Real-time</span>
                                </div>
                            </div>
                            <button onclick="refreshFirebaseData()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center space-x-2">
                                <i class="fas fa-sync-alt"></i>
                                <span>Refresh</span>
                            </button>
                            <button id="live-indicator" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg flex items-center space-x-2">
                                <i class="fas fa-circle text-xs animate-pulse"></i>
                                <span>Live</span>
                            </button>
                        </div>
                    </div>

                    <!-- Chart Info -->
                    <div class="flex items-center justify-between mb-4 text-sm text-gray-600">
                        <div class="flex items-center space-x-4">
                            <span class="flex items-center space-x-2">
                                <i class="far fa-clock"></i>
                                <span id="time-range">Jam Kerja (08:00 - 17:00)</span>
                            </span>
                            <span class="flex items-center space-x-2">
                                <i class="fas fa-database"></i>
                                <span id="data-count">0 titik data (0 pembacaan)</span>
                            </span>
                            <span class="flex items-center space-x-2" id="connection-status">
                                <i class="fas fa-circle text-green-500 text-xs animate-pulse"></i>
                                <span>Terhubung</span>
                            </span>
                        </div>
                        <span class="text-gray-500" id="last-update">Update terakhir: {{ now()->format('H:i:s') }}</span>
                    </div>

                    <!-- Chart Canvas -->
                    <div style="width: 100%; height: 400px; background: white; border: 2px solid #ddd; border-radius: 8px; padding: 10px; margin: 10px 0;">
                        <h3 style="margin: 0 0 10px 0; color: #333;">Monitoring Per Jam - Sensor Data</h3>
                        <div style="position: relative; height: 350px; width: 100%;">
                            <canvas id="sensorChart" style="position: absolute; top: 0; left: 0; width: 100% !important; height: 100% !important;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        console.log('🚀 Dashboard loading...');
        
        // Global variables
        let sensorChart = null;
        let currentFilterType = 'working_hours';
        
        // Close notification after 5 seconds
        setTimeout(() => {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.5s';
                setTimeout(() => notification.remove(), 500);
            }
        }, 5000);

        function closeNotification() {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s';
                setTimeout(() => notification.remove(), 300);
            }
        }

        // SIMPLIFIED CHART INITIALIZATION
        function initChart() {
            console.log('� Initializing chart...');
            
            // Check Chart.js availability
            if (typeof Chart === 'undefined') {
                console.error('❌ Chart.js not loaded');
                return false;
            }
            
            // Get canvas
            const canvas = document.getElementById('sensorChart');
            if (!canvas) {
                console.error('❌ Canvas not found');
                return false;
            }
            
            // Create chart with sample data
            const ctx = canvas.getContext('2d');
            
            try {
                sensorChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
                        datasets: [{
                            label: 'Suhu (°C)',
                            data: [25.5, 26.0, 26.5, 27.0, 27.2, 26.8, 26.5, 26.0, 25.8, 25.5],
                            borderColor: '#fb923c',
                            backgroundColor: 'rgba(251, 146, 60, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'pH',
                            data: [6.8, 6.9, 7.0, 4.0, 4.1, 4.2, 4.0, 3.9, 3.8, 4.0],
                            borderColor: '#14b8a6',
                            backgroundColor: 'rgba(20, 184, 166, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'Oksigen (mg/L)',
                            data: [7.5, 7.8, 7.2, 6.8, 6.5, 6.9, 7.1, 7.4, 7.6, 7.3],
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
                
                // Store globally
                window.sensorChart = sensorChart;
                
                console.log('✅ Chart created successfully!');
                return true;
                
            } catch (error) {
                console.error('❌ Chart creation failed:', error);
                return false;
            }
        }

        // DOM ready handler
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM loaded, initializing chart in 1 second...');
            setTimeout(() => {
                const success = initChart();
                if (success) {
                    // Load Firebase data after chart is ready
                    setTimeout(loadFirebaseData, 1000);
                }
            }, 1000);
        });

        // Load Firebase data
        function loadFirebaseData() {
            console.log('🔥 Loading Firebase data...');
            
            if (!sensorChart) {
                console.warn('⚠️ Chart not ready for Firebase data');
                return;
            }
            
            // Show loading
            const refreshBtn = document.querySelector('.fa-sync-alt');
            if (refreshBtn) refreshBtn.classList.add('fa-spin');

            fetch('/api/sensor-data?type=working_hours&source=firebase')
                .then(response => response.json())
                .then(result => {
                    console.log('� Firebase response:', result);
                    
                    if (result.success && result.data && result.data.length > 0) {
                        // Update chart with real Firebase data
                        const labels = result.data.map(d => d.time || d.hour);
                        const temps = result.data.map(d => parseFloat(d.temperature || 0));
                        const phs = result.data.map(d => parseFloat(d.ph || 0));
                        const oxygens = result.data.map(d => parseFloat(d.oxygen || 0));

                        sensorChart.data.labels = labels;
                        sensorChart.data.datasets[0].data = temps;
                        sensorChart.data.datasets[1].data = phs;
                        sensorChart.data.datasets[2].data = oxygens;
                        sensorChart.update();
                        
                        console.log('✅ Chart updated with Firebase data');
                    }

                    // Update sensor cards
                    if (result.latest) {
                        document.getElementById('temp-value').textContent = parseFloat(result.latest.temperature).toFixed(1);
                        document.getElementById('ph-value').textContent = parseFloat(result.latest.ph).toFixed(1);
                        document.getElementById('oxygen-value').textContent = parseFloat(result.latest.oxygen).toFixed(1);
                    }

                    // Update info display
                    document.getElementById('data-count').textContent = `${result.count || 0} titik data Firebase`;
                    const now = new Date();
                    document.getElementById('last-update').textContent = 
                        `Update: ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
                })
                .catch(error => {
                    console.error('❌ Firebase load error:', error);
                })
                .finally(() => {
                    if (refreshBtn) refreshBtn.classList.remove('fa-spin');
                });
        }

        // Refresh function
        function refreshFirebaseData() {
            loadFirebaseData();
        }
        
        // Auto-refresh every 30 seconds
        setInterval(loadFirebaseData, 30000);
    </script>
    
        <!-- DIAGNOSTIC: log canvas/chart metrics to browser console and force resize -->
        <script>
            (function(){
                let attempts = 0;
                function diag(){
                    const canvas = document.getElementById('sensorChart');
                    console.log('🔎 DIAG attempt', attempts, 'canvas:', canvas);
                    if (!canvas) {
                        console.warn('🔎 DIAG: canvas not found');
                    } else {
                        console.log('🔎 canvas.clientWidth:', canvas.clientWidth, 'clientHeight:', canvas.clientHeight, 'width attr:', canvas.width, 'height attr:', canvas.height);
                        // try to force canvas pixel size to match css size
                        try {
                            canvas.width = canvas.clientWidth;
                            canvas.height = canvas.clientHeight;
                            console.log('🔎 DIAG: forced canvas width/height to client values');
                        } catch(e){
                            console.warn('🔎 DIAG: set size error', e);
                        }
                    }

                    console.log('🔎 sensorChart exists:', !!window.sensorChart, window.sensorChart);
                    if (window.sensorChart && typeof window.sensorChart.resize === 'function') {
                        try {
                            window.sensorChart.resize();
                            console.log('🔎 sensorChart.resize() called');
                        } catch(e){
                            console.warn('🔎 resize error', e);
                        }
                    }

                    attempts++;
                    if (attempts < 5) setTimeout(diag, 1000);
                }

                // start shortly after load so the page can settle
                setTimeout(diag, 1500);
            })();
        </script>
    </body>
    </html>
    

</body>
</html>
