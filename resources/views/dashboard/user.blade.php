<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Kolam Ikan Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .bg-gradient-main {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <!-- Navigation -->
    <nav class="bg-gradient-main shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-8 w-8 rounded-lg">
                    <h1 class="ml-3 text-white text-xl font-bold">Dashboard Monitoring</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white hover:text-gray-200 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <!-- Welcome Message -->
        <div class="card-gradient rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Selamat Datang, {{ auth()->user()->name }}!</h2>
            <p class="text-gray-600">Monitor kondisi kolam ikan Anda secara real-time</p>
        </div>

        <!-- Device Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($devices as $device)
            <div class="card-gradient rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $device->name }}</h3>
                        @if($device->status === 'online')
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                ● Online
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                ● Offline
                            </span>
                        @endif
                    </div>
                    
                    <div class="text-sm text-gray-600 mb-4">
                        <p>📍 {{ $device->location }}</p>
                        <p>🔧 {{ $device->device_id }}</p>
                    </div>

                    @if($device->latestSensorData)
                    <div class="space-y-3">
                        <!-- pH Level -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">pH Level</span>
                            <span class="text-sm font-bold {{ $device->latestSensorData->isPhNormal() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $device->latestSensorData->ph_level }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($device->latestSensorData->ph_level / 14) * 100 }}%"></div>
                        </div>

                        <!-- Temperature -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Temperature</span>
                            <span class="text-sm font-bold {{ $device->latestSensorData->isTemperatureNormal() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $device->latestSensorData->temperature }}°C
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($device->latestSensorData->temperature / 40) * 100 }}%"></div>
                        </div>

                        <!-- Oxygen Level -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Oxygen</span>
                            <span class="text-sm font-bold {{ $device->latestSensorData->isOxygenAdequate() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $device->latestSensorData->oxygen_level }} mg/L
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(($device->latestSensorData->oxygen_level / 10) * 100, 100) }}%"></div>
                        </div>

                        <div class="pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                Terakhir diperbarui: {{ $device->latestSensorData->recorded_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m-2 0a1 1 0 00-1 1v1a1 1 0 001 1h2m0-4v4h2m2-4h2"></path>
                        </svg>
                        <p class="text-gray-500 text-sm">Belum ada data sensor</p>
                    </div>
                    @endif

                    <!-- Action Button -->
                    <div class="mt-6">
                        <button onclick="viewDetails({{ $device->id }})" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($devices->isEmpty())
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2-2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m-2 0a1 1 0 00-1 1v1a1 1 0 001 1h2m0-4v4h2m2-4h2"></path>
            </svg>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Belum Ada Device Tersedia</h3>
            <p class="text-gray-600 mb-6">Hubungi admin untuk mendapatkan akses ke device monitoring.</p>
        </div>
        @endif

        <!-- Quick Stats -->
        @if(!$devices->isEmpty())
        <div class="card-gradient rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Status</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $devices->count() }}</div>
                    <div class="text-sm text-gray-600">Total Device</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $devices->where('status', 'online')->count() }}</div>
                    <div class="text-sm text-gray-600">Online</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $devices->where('status', 'offline')->count() }}</div>
                    <div class="text-sm text-gray-600">Offline</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $devices->filter(function($d) { return $d->latestSensorData; })->count() }}</div>
                    <div class="text-sm text-gray-600">Dengan Data</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        function viewDetails(deviceId) {
            alert('Menampilkan detail device ID: ' + deviceId + '\n\nFitur ini akan menampilkan:\n- Grafik data historis\n- Trend analisis\n- Alert & notifikasi');
        }

        // Auto refresh setiap 30 detik
        setInterval(function() {
            location.reload();
        }, 30000);

        // Show loading message
        console.log('Dashboard loaded. Auto-refresh setiap 30 detik.');
    </script>
</body>
</html>
