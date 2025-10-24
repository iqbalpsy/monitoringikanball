<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - AquaMonitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-normal {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body class="bg-gray-100">
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
                    <a href="{{ route('user.dashboard') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                        <i class="fas fa-th-large text-lg"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('user.history') }}" class="menu-item active flex items-center space-x-3 px-4 py-3 rounded-lg">
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
                    <h2 class="text-2xl font-bold text-gray-800">History Data Sensor</h2>
                    <p class="text-gray-600 text-sm">Riwayat pembacaan sensor kolam ikan</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <p class="font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                        <p class="text-sm text-gray-500 capitalize">{{ Auth::user()->role }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="p-8">
                <!-- Filter Section -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <form method="GET" action="{{ route('user.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Data</label>
                            <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>Semua</option>
                                <option value="temperature" {{ request('type') == 'temperature' ? 'selected' : '' }}>Suhu</option>
                                <option value="ph" {{ request('type') == 'ph' ? 'selected' : '' }}>pH</option>
                                <option value="oxygen" {{ request('type') == 'oxygen' ? 'selected' : '' }}>Oksigen</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition flex items-center justify-center space-x-2">
                                <i class="fas fa-filter"></i>
                                <span>Filter</span>
                            </button>
                            <a href="{{ route('user.history.export', request()->all()) }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition flex items-center space-x-2">
                                <i class="fas fa-download"></i>
                                <span>Export</span>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Data Table -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suhu (°C)</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">pH</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oksigen (mg/L)</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($history as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($data->recorded_at)->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($data->temperature)
                                            <span class="font-medium text-gray-900">{{ number_format($data->temperature, 2) }}</span>
                                            @php
                                                $tempNormal = $settings ? 
                                                    ($data->temperature >= $settings->temp_min && $data->temperature <= $settings->temp_max) : 
                                                    ($data->temperature >= 25 && $data->temperature <= 30);
                                            @endphp
                                            @if(!$tempNormal)
                                                <i class="fas fa-exclamation-triangle text-orange-500 ml-2"></i>
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($data->ph)
                                            <span class="font-medium text-gray-900">{{ number_format($data->ph, 2) }}</span>
                                            @php
                                                $phNormal = $settings ? 
                                                    ($data->ph >= $settings->ph_min && $data->ph <= $settings->ph_max) : 
                                                    ($data->ph >= 6.5 && $data->ph <= 8.5);
                                            @endphp
                                            @if(!$phNormal)
                                                <i class="fas fa-exclamation-triangle text-orange-500 ml-2"></i>
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($data->oxygen)
                                            <span class="font-medium text-gray-900">{{ number_format($data->oxygen, 2) }}</span>
                                            @php
                                                $oxygenNormal = $settings ? 
                                                    ($data->oxygen >= $settings->oxygen_min) : 
                                                    ($data->oxygen >= 5.0);
                                            @endphp
                                            @if(!$oxygenNormal)
                                                <i class="fas fa-exclamation-triangle text-orange-500 ml-2"></i>
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            // Check if all parameters are normal
                                            $tempOk = !$data->temperature || ($settings ? 
                                                ($data->temperature >= $settings->temp_min && $data->temperature <= $settings->temp_max) : 
                                                ($data->temperature >= 25 && $data->temperature <= 30));
                                            $phOk = !$data->ph || ($settings ? 
                                                ($data->ph >= $settings->ph_min && $data->ph <= $settings->ph_max) : 
                                                ($data->ph >= 6.5 && $data->ph <= 8.5));
                                            $oxygenOk = !$data->oxygen || ($settings ? 
                                                ($data->oxygen >= $settings->oxygen_min) : 
                                                ($data->oxygen >= 5.0));
                                            $isNormal = $tempOk && $phOk && $oxygenOk;
                                        @endphp
                                        @if($isNormal)
                                            <span class="status-badge status-normal">
                                                <i class="fas fa-check-circle mr-1"></i>Normal
                                            </span>
                                        @else
                                            <span class="status-badge status-warning">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Perhatian
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-4"></i>
                                        <p class="text-lg">Tidak ada data history</p>
                                        <p class="text-sm">Silakan ubah filter atau tunggu data sensor masuk</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $history->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
