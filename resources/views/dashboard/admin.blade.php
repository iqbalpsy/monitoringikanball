<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Kolam Ikan Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .bg-gradient-main {
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #581c87 100%);
        }
        
        .bg-gradient-sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
        }
        
        .card-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }
        
        .sidebar-link {
            transition: all 0.3s ease;
        }
        
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #3b82f6;
        }
        
        .sidebar-link.active {
            background: rgba(59, 130, 246, 0.2);
            border-left: 4px solid #3b82f6;
        }
        
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    
    <!-- Navigation Bar -->
    <nav class="bg-gradient-main shadow-lg fixed w-full z-30 top-0">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button onclick="toggleSidebar()" class="lg:hidden text-white hover:text-gray-200 mr-3">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-8 w-8 rounded-lg">
                    <h1 class="ml-3 text-white text-xl font-bold">Admin Dashboard</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Search Bar -->
                    <div class="hidden md:block relative">
                        <input type="text" placeholder="Search..." class="bg-white bg-opacity-20 text-white placeholder-white placeholder-opacity-75 px-4 py-2 rounded-lg w-64 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50">
                        <i class="fas fa-search absolute right-3 top-3 text-white text-opacity-75"></i>
                    </div>
                    
                    <!-- Notifications -->
                    <div class="relative">
                        <button onclick="toggleNotifications()" class="text-white hover:text-gray-200 relative p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-all">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">{{ $devices->where('status', 'offline')->count() }}</span>
                        </button>
                        
                        <!-- Notifications Dropdown -->
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg py-2 z-50 max-h-96 overflow-y-auto">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                            </div>
                            @if($devices->where('status', 'offline')->count() > 0)
                                @foreach($devices->where('status', 'offline') as $device)
                                <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-gray-900">Device Offline</p>
                                            <p class="text-sm text-gray-600">{{ $device->name }} is currently offline</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $device->updated_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="px-4 py-8 text-center">
                                    <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                                    <p class="text-gray-600">All systems are running normally</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    <div class="relative">
                        <button class="text-white hover:text-gray-200 p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-all">
                            <i class="fas fa-envelope text-lg"></i>
                            <span class="absolute -top-1 -right-1 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                        </button>
                    </div>
                    
                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button onclick="toggleDropdown()" class="flex items-center text-white hover:text-gray-200 space-x-3 p-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition-all">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center ring-2 ring-white ring-opacity-30">
                                <span class="text-white font-semibold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-300">{{ ucfirst(auth()->user()->role) }}</p>
                            </div>
                            <i class="fas fa-chevron-down text-sm"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg py-2 z-50">
                            <!-- Profile Header -->
                            <div class="px-4 py-3 border-b border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                        <p class="text-sm text-gray-600">{{ auth()->user()->email }}</p>
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded-full {{ auth()->user()->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst(auth()->user()->role) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Menu Items -->
                            <div class="py-2">
                                <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-cog mr-3 text-gray-400"></i>
                                    <span>Account Settings</span>
                                </a>
                                <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-key mr-3 text-gray-400"></i>
                                    <span>Change Password</span>
                                </a>
                                <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-bell mr-3 text-gray-400"></i>
                                    <span>Notifications</span>
                                </a>
                                <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-question-circle mr-3 text-gray-400"></i>
                                    <span>Help & Support</span>
                                </a>
                            </div>
                            
                            <hr class="my-2">
                            
                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-20 w-64 bg-gradient-sidebar transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="flex flex-col h-full pt-16">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-center py-6 border-b border-gray-600">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-fish text-white text-2xl"></i>
                    </div>
                    <h3 class="text-white text-sm font-medium">IoT Fish Monitor</h3>
                    <p class="text-gray-400 text-xs">Administrator</p>
                </div>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="#" onclick="showContent('dashboard')" class="sidebar-link active flex items-center px-4 py-3 text-white rounded-lg group">
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                        <i class="fas fa-tachometer-alt text-sm"></i>
                    </div>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                <a href="#" onclick="showContent('history')" class="sidebar-link flex items-center px-4 py-3 text-white rounded-lg group">
                    <div class="flex items-center justify-center w-8 h-8 bg-green-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                        <i class="fas fa-history text-sm"></i>
                    </div>
                    <span class="font-medium">History</span>
                    <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">{{ $totalSensorData > 999 ? '999+' : $totalSensorData }}</span>
                </a>
                
                <a href="#" onclick="showContent('user')" class="sidebar-link flex items-center px-4 py-3 text-white rounded-lg group">
                    <div class="flex items-center justify-center w-8 h-8 bg-purple-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                        <i class="fas fa-users text-sm"></i>
                    </div>
                    <span class="font-medium">User</span>
                    <span class="ml-auto bg-purple-500 text-white text-xs px-2 py-1 rounded-full">{{ $users->count() }}</span>
                </a>
                
                <!-- Divider -->
                <div class="py-2">
                    <hr class="border-gray-600">
                </div>
                
                <!-- Additional Menu Items -->
                <div class="space-y-1">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Management</p>
                    
                    <a href="#" onclick="showContent('devices')" class="sidebar-link flex items-center px-4 py-3 text-white rounded-lg group">
                        <div class="flex items-center justify-center w-8 h-8 bg-indigo-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                            <i class="fas fa-microchip text-sm"></i>
                        </div>
                        <span class="font-medium">Devices</span>
                        <span class="ml-auto bg-indigo-500 text-white text-xs px-2 py-1 rounded-full">{{ $devices->count() }}</span>
                    </a>
                    
                    <a href="#" onclick="showContent('monitoring')" class="sidebar-link flex items-center px-4 py-3 text-white rounded-lg group">
                        <div class="flex items-center justify-center w-8 h-8 bg-yellow-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                            <i class="fas fa-chart-line text-sm"></i>
                        </div>
                        <span class="font-medium">Monitoring</span>
                        <span class="ml-auto">
                            <span class="w-2 h-2 bg-green-400 rounded-full inline-block animate-pulse"></span>
                        </span>
                    </a>
                    
                    <a href="#" onclick="showContent('reports')" class="sidebar-link flex items-center px-4 py-3 text-white rounded-lg group">
                        <div class="flex items-center justify-center w-8 h-8 bg-red-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                            <i class="fas fa-file-alt text-sm"></i>
                        </div>
                        <span class="font-medium">Reports</span>
                    </a>
                </div>
                
                <!-- Divider -->
                <div class="py-2">
                    <hr class="border-gray-600">
                </div>
                
                <!-- System Menu -->
                <div class="space-y-1">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">System</p>
                    
                    <a href="#" onclick="showContent('settings')" class="sidebar-link flex items-center px-4 py-3 text-white rounded-lg group">
                        <div class="flex items-center justify-center w-8 h-8 bg-gray-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                            <i class="fas fa-cog text-sm"></i>
                        </div>
                        <span class="font-medium">Settings</span>
                    </a>
                </div>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="px-4 py-4 border-t border-gray-600">
                <div class="text-center text-gray-400 text-xs">
                    <p>Version 1.0.0</p>
                    <p>© 2025 IoT Fish Monitor</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-10 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="lg:ml-64 pt-16 min-h-screen">
        <div class="p-6">
            
            <!-- Dashboard Content -->
            <div id="dashboard-content" class="content-section active">
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Dashboard Overview</h1>
                            <p class="text-gray-600 mt-1">Monitor semua device dan sistem IoT fish monitoring</p>
                        </div>
                        <div class="flex space-x-3">
                            <!-- Firebase/Database Toggle -->
                            <div class="flex bg-gray-100 rounded-lg p-1 mr-4">
                                <button id="btn-firebase" onclick="switchToFirebase()" class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 text-gray-600 hover:text-blue-600">
                                    <i class="fas fa-cloud mr-2"></i>Firebase
                                </button>
                                <button id="btn-database" onclick="switchToDatabase()" class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 bg-blue-600 text-white shadow-sm">
                                    <i class="fas fa-database mr-2"></i>Database Local
                                </button>
                            </div>
                            
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>Add Device
                            </button>
                            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-download mr-2"></i>Export Data
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Data Source Status -->
                <div id="data-source-status" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg hidden">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <span id="source-status-text" class="text-blue-800 font-medium">Connected to Database Local</span>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ $users->count() }}</p>
                                <p class="text-gray-600">Total Users</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                                <i class="fas fa-microchip text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ $devices->count() }}</p>
                                <p class="text-gray-600">Total Devices</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                                <i class="fas fa-wifi text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ $devices->where('status', 'online')->count() }}</p>
                                <p class="text-gray-600">Online Devices</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                                <i class="fas fa-database text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSensorData) }}</p>
                                <p class="text-gray-600">Sensor Records</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Real-time Sensor Data Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Temperature</h3>
                            <i class="fas fa-thermometer-half text-red-500 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-gray-900 mb-2">
                            <span id="admin-temp-value">{{ $latestData ? number_format($latestData->temperature, 1) : '0.0' }}</span>°C
                        </div>
                        <p class="text-sm text-gray-600" id="admin-temp-status">
                            {{ $latestData && $latestData->isTemperatureNormal() ? 'Normal Range' : 'Alert Range' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2" id="admin-temp-time">
                            {{ $latestData ? $latestData->recorded_at->format('d/m/Y H:i:s') : 'No data' }}
                        </p>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">pH Level</h3>
                            <i class="fas fa-flask text-blue-500 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-gray-900 mb-2">
                            <span id="admin-ph-value">{{ $latestData ? number_format($latestData->ph, 1) : '0.0' }}</span>
                        </div>
                        <p class="text-sm text-gray-600" id="admin-ph-status">
                            {{ $latestData && $latestData->isPhNormal() ? 'Normal Range' : 'Alert Range' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2" id="admin-ph-time">
                            {{ $latestData ? $latestData->recorded_at->format('d/m/Y H:i:s') : 'No data' }}
                        </p>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Oxygen Level</h3>
                            <i class="fas fa-wind text-green-500 text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-gray-900 mb-2">
                            <span id="admin-oxygen-value">{{ $latestData ? number_format($latestData->oxygen, 1) : '0.0' }}</span> mg/L
                        </div>
                        <p class="text-sm text-gray-600" id="admin-oxygen-status">
                            {{ $latestData && $latestData->isOxygenAdequate() ? 'Adequate Level' : 'Alert Range' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2" id="admin-oxygen-time">
                            {{ $latestData ? $latestData->recorded_at->format('d/m/Y H:i:s') : 'No data' }}
                        </p>
                    </div>
                </div>

                <!-- Device Status Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                    @foreach($devices as $device)
                    <div class="card-gradient rounded-lg shadow-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $device->name }}</h3>
                                @if($device->status === 'online')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-circle mr-1"></i>Online
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-circle mr-1"></i>Offline
                                    </span>
                                @endif
                            </div>
                            
                            <div class="text-sm text-gray-600 mb-4">
                                <p><i class="fas fa-map-marker-alt mr-2"></i>{{ $device->location }}</p>
                                <p><i class="fas fa-barcode mr-2"></i>{{ $device->device_id }}</p>
                            </div>

                            @if($device->latestSensorData)
                            <div class="space-y-3">
                                <!-- pH Level -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium text-gray-700">pH Level</span>
                                        <span class="text-sm font-bold {{ $device->latestSensorData->isPhNormal() ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $device->latestSensorData->ph }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($device->latestSensorData->ph / 14) * 100 }}%"></div>
                                    </div>
                                </div>

                                <!-- Temperature -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium text-gray-700">Temperature</span>
                                        <span class="text-sm font-bold {{ $device->latestSensorData->isTemperatureNormal() ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $device->latestSensorData->temperature }}°C
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($device->latestSensorData->temperature / 40) * 100 }}%"></div>
                                    </div>
                                </div>

                                <!-- Oxygen Level -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium text-gray-700">Oxygen</span>
                                        <span class="text-sm font-bold {{ $device->latestSensorData->isOxygenAdequate() ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $device->latestSensorData->oxygen }} mg/L
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(($device->latestSensorData->oxygen / 10) * 100, 100) }}%"></div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-gray-200 text-center">
                                    <p class="text-xs text-gray-500">
                                        Last update: {{ $device->latestSensorData->recorded_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-8">
                                <i class="fas fa-exclamation-triangle text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500 text-sm">No sensor data available</p>
                            </div>
                            @endif

                            <div class="mt-6 flex space-x-2">
                                <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-sm">
                                    <i class="fas fa-chart-line mr-1"></i>View Details
                                </button>
                                <button class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-3 rounded-lg transition-colors">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- History Content -->
            <div id="history-content" class="content-section">
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">History & Logs</h1>
                            <p class="text-gray-600 mt-1">Riwayat aktivitas sistem dan data sensor</p>
                        </div>
                        <div class="flex space-x-3">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-filter mr-2"></i>Filter Data
                            </button>
                            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-download mr-2"></i>Export History
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                                <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\SensorData::whereDate('recorded_at', today())->count() }}</p>
                                <p class="text-gray-600">Today's Records</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                                <i class="fas fa-calendar-week text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\SensorData::where('recorded_at', '>=', now()->subWeek())->count() }}</p>
                                <p class="text-gray-600">This Week</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                                <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\SensorData::where('recorded_at', '>=', now()->subMonth())->count() }}</p>
                                <p class="text-gray-600">This Month</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-500 bg-opacity-20">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\SensorData::whereHas('device', function($q) { $q->where('status', 'offline'); })->count() }}</p>
                                <p class="text-gray-600">Error Records</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Table -->
                <div class="card-gradient rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Sensor Data</h3>
                            <div class="flex space-x-2">
                                <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    <option>All Devices</option>
                                    @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }}</option>
                                    @endforeach
                                </select>
                                <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    <option>Last 24 Hours</option>
                                    <option>Last 7 Days</option>
                                    <option>Last 30 Days</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Timestamp</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Device</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">pH Level</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Temperature</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Oxygen</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\SensorData::with('device')->latest('recorded_at')->take(20)->get() as $sensorData)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-6 text-sm">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $sensorData->recorded_at->format('d/m/Y H:i:s') }}</p>
                                            <p class="text-xs text-gray-500">{{ $sensorData->recorded_at->diffForHumans() }}</p>
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-microchip text-blue-600 text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $sensorData->device->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $sensorData->device->device_id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sensorData->isPhNormal() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $sensorData->ph }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sensorData->isTemperatureNormal() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $sensorData->temperature }}°C
                                        </span>
                                    </td>
                                    <td class="py-3 px-6">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sensorData->isOxygenAdequate() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $sensorData->oxygen }} mg/L
                                        </span>
                                    </td>
                                    <td class="py-3 px-6">
                                        @if($sensorData->isPhNormal() && $sensorData->isTemperatureNormal() && $sensorData->isOxygenAdequate())
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i>Normal
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Warning
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Showing 20 of {{ number_format($totalSensorData) }} records</p>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-100">Previous</button>
                                <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-100">Next</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Content -->
            <div id="user-content" class="content-section">
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
                            <p class="text-gray-600 mt-1">Kelola users dan permission access</p>
                        </div>
                        <div class="flex space-x-3">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>Add User
                            </button>
                            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-download mr-2"></i>Export Users
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ $users->count() }}</p>
                                <p class="text-gray-600">Total Users</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-20">
                                <i class="fas fa-user-shield text-purple-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ $users->where('role', 'admin')->count() }}</p>
                                <p class="text-gray-600">Administrators</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-20">
                                <i class="fas fa-user text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ $users->where('role', 'user')->count() }}</p>
                                <p class="text-gray-600">Regular Users</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-20">
                                <i class="fas fa-user-check text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-900">{{ $users->where('is_active', true)->count() }}</p>
                                <p class="text-gray-600">Active Users</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- User Table -->
                <div class="card-gradient rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">All Users</h3>
                            <div class="flex space-x-2">
                                <input type="text" placeholder="Search users..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64">
                                <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    <option>All Roles</option>
                                    <option>Administrator</option>
                                    <option>User</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">User</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Email</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Role</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Status</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Last Login</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-sm mr-4">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                                <p class="text-sm text-gray-500">ID: #{{ $user->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div>
                                            <p class="text-gray-900">{{ $user->email }}</p>
                                            @if($user->email_verified_at)
                                                <p class="text-xs text-green-600">
                                                    <i class="fas fa-check-circle mr-1"></i>Verified
                                                </p>
                                            @else
                                                <p class="text-xs text-red-600">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>Unverified
                                                </p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            <i class="fas fa-{{ $user->role === 'admin' ? 'user-shield' : 'user' }} mr-1"></i>
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($user->is_active)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-circle mr-1"></i>Active
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-circle mr-1"></i>Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-600">
                                        @if($user->last_login_at)
                                            <div>
                                                <p>{{ $user->last_login_at->format('d/m/Y H:i') }}</p>
                                                <p class="text-xs text-gray-500">{{ $user->last_login_at->diffForHumans() }}</p>
                                            </div>
                                        @else
                                            <span class="text-gray-400">Never</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <button class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition-all" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-100 transition-all" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-yellow-600 hover:text-yellow-800 p-2 rounded-full hover:bg-yellow-100 transition-all" title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition-all" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Other sections placeholder -->
            <div id="devices-content" class="content-section">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Device Management</h1>
                    <p class="text-gray-600 mt-1">Kelola semua IoT devices dan konfigurasinya</p>
                </div>
                <div class="card-gradient rounded-lg shadow-lg p-6">
                    <p class="text-gray-600">Device management interface coming soon...</p>
                </div>
            </div>

            <div id="monitoring-content" class="content-section">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Real-time Monitoring</h1>
                    <p class="text-gray-600 mt-1">Monitor data sensor secara real-time</p>
                </div>
                <div class="card-gradient rounded-lg shadow-lg p-6">
                    <p class="text-gray-600">Real-time monitoring charts will be implemented here...</p>
                </div>
            </div>

            <div id="reports-content" class="content-section">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
                    <p class="text-gray-600 mt-1">Generate reports dan analisis data</p>
                </div>
                <div class="card-gradient rounded-lg shadow-lg p-6">
                    <p class="text-gray-600">Reports and analytics features coming soon...</p>
                </div>
            </div>

            <div id="settings-content" class="content-section">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
                    <p class="text-gray-600 mt-1">Konfigurasi sistem dan preferensi</p>
                </div>
                <div class="card-gradient rounded-lg shadow-lg p-6">
                    <p class="text-gray-600">System settings will be available here...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Notifications toggle
        function toggleNotifications() {
            const notificationDropdown = document.getElementById('notificationDropdown');
            notificationDropdown.classList.toggle('hidden');
        }

        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // User dropdown toggle
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const userDropdown = document.getElementById('userDropdown');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            // Close user dropdown
            if (!event.target.closest('#userDropdown') && !event.target.closest('button[onclick="toggleDropdown()"]')) {
                userDropdown.classList.add('hidden');
            }
            
            // Close notification dropdown
            if (!event.target.closest('#notificationDropdown') && !event.target.closest('button[onclick="toggleNotifications()"]')) {
                notificationDropdown.classList.add('hidden');
            }
        });

        // Content switching
        function showContent(contentId) {
            // Hide all content sections
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => {
                section.classList.remove('active');
            });

            // Remove active class from all sidebar links
            const links = document.querySelectorAll('.sidebar-link');
            links.forEach(link => {
                link.classList.remove('active');
            });

            // Show selected content
            document.getElementById(contentId + '-content').classList.add('active');
            
            // Add active class to clicked link
            event.target.closest('.sidebar-link').classList.add('active');
        }

        // Firebase Data Source Management
        let currentDataSource = 'database';
        let refreshInterval;

        function switchToFirebase() {
            currentDataSource = 'firebase';
            updateButtonStates('firebase');
            loadFirebaseData();
            showStatusMessage('Switching to Firebase...', 'info');
        }

        function switchToDatabase() {
            currentDataSource = 'database';
            updateButtonStates('database');
            loadDatabaseData();
            showStatusMessage('Switching to Database Local...', 'info');
        }

        function updateButtonStates(activeSource) {
            const firebaseBtn = document.getElementById('btn-firebase');
            const databaseBtn = document.getElementById('btn-database');
            
            if (activeSource === 'firebase') {
                firebaseBtn.className = 'px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 bg-orange-500 text-white shadow-sm';
                databaseBtn.className = 'px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 text-gray-600 hover:text-blue-600';
            } else {
                firebaseBtn.className = 'px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 text-gray-600 hover:text-orange-600';
                databaseBtn.className = 'px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 bg-blue-600 text-white shadow-sm';
            }
        }

        function loadFirebaseData() {
            fetch('/admin/api/firebase-data', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.latest) {
                    updateSensorCards(data.latest, 'firebase');
                    showStatusMessage('✅ Connected to Firebase - Real-time data loaded', 'success');
                } else {
                    showStatusMessage('❌ Firebase connection failed: ' + (data.message || 'No data available'), 'error');
                    // Fall back to database
                    switchToDatabase();
                }
            })
            .catch(error => {
                console.error('Firebase load error:', error);
                showStatusMessage('❌ Firebase error: ' + error.message, 'error');
                // Fall back to database
                switchToDatabase();
            });
        }

        function loadDatabaseData() {
            // Reload page to get fresh database data
            // In a real implementation, you might want to use AJAX here too
            showStatusMessage('✅ Connected to Database Local', 'success');
        }

        function updateSensorCards(data, source) {
            // Update temperature card
            document.getElementById('admin-temp-value').textContent = data.temperature || '0.0';
            document.getElementById('admin-temp-status').textContent = getTemperatureStatus(data.temperature);
            document.getElementById('admin-temp-time').textContent = data.timestamp || 'No data';

            // Update pH card
            document.getElementById('admin-ph-value').textContent = data.ph || '0.0';
            document.getElementById('admin-ph-status').textContent = getPhStatus(data.ph);
            document.getElementById('admin-ph-time').textContent = data.timestamp || 'No data';

            // Update oxygen card
            document.getElementById('admin-oxygen-value').textContent = data.oxygen || '0.0';
            document.getElementById('admin-oxygen-status').textContent = getOxygenStatus(data.oxygen);
            document.getElementById('admin-oxygen-time').textContent = data.timestamp || 'No data';
        }

        function getTemperatureStatus(temp) {
            if (!temp) return 'No Data';
            return (temp >= 24 && temp <= 30) ? 'Normal Range' : 'Alert Range';
        }

        function getPhStatus(ph) {
            if (!ph) return 'No Data';
            return (ph >= 6.5 && ph <= 8.5) ? 'Normal Range' : 'Alert Range';
        }

        function getOxygenStatus(oxygen) {
            if (!oxygen) return 'No Data';
            return (oxygen >= 5 && oxygen <= 8) ? 'Adequate Level' : 'Alert Range';
        }

        function showStatusMessage(message, type) {
            const statusDiv = document.getElementById('data-source-status');
            const statusText = document.getElementById('source-status-text');
            
            statusText.textContent = message;
            statusDiv.className = 'mb-6 p-4 border rounded-lg';
            
            if (type === 'success') {
                statusDiv.className += ' bg-green-50 border-green-200';
                statusText.className = 'text-green-800 font-medium';
            } else if (type === 'error') {
                statusDiv.className += ' bg-red-50 border-red-200';  
                statusText.className = 'text-red-800 font-medium';
            } else {
                statusDiv.className += ' bg-blue-50 border-blue-200';
                statusText.className = 'text-blue-800 font-medium';
            }
            
            statusDiv.classList.remove('hidden');
            
            // Hide status message after 3 seconds for success/info
            if (type !== 'error') {
                setTimeout(() => {
                    statusDiv.classList.add('hidden');
                }, 3000);
            }
        }

        // Auto refresh current data source every 30 seconds
        setInterval(function() {
            // Only refresh if dashboard is active
            if (document.getElementById('dashboard-content').classList.contains('active')) {
                if (currentDataSource === 'firebase') {
                    loadFirebaseData();
                } else {
                    console.log('Auto-refreshing database data...');
                    // For database, you could implement AJAX refresh here too
                }
            }
        }, 30000);

        // Initialize data source status on page load
        document.addEventListener('DOMContentLoaded', function() {
            showStatusMessage('Connected to Database Local', 'success');
        });

        console.log('Admin Dashboard with Firebase Integration loaded successfully');
    </script>
</body>
</html>
