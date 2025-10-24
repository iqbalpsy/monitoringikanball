<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AquaMonitor</title>
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
        .range-slider {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 5px;
            background: #d3d3d3;
            outline: none;
        }
        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #667eea;
            cursor: pointer;
        }
        .range-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #667eea;
            cursor: pointer;
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
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
            <button onclick="closeNotification()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Error Notification -->
    @if($errors->any())
    <div id="error-notification" class="fixed top-4 right-4 z-50 notification">
        <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg">
            <div class="flex items-start space-x-3">
                <i class="fas fa-exclamation-circle text-2xl"></i>
                <div class="flex-1">
                    <p class="font-semibold mb-2">Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button onclick="document.getElementById('error-notification').remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
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
                    <a href="{{ route('user.dashboard') }}" class="menu-item flex items-center space-x-3 px-4 py-3 rounded-lg">
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
                    <a href="{{ route('user.settings') }}" class="menu-item active flex items-center space-x-3 px-4 py-3 rounded-lg">
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
                    <h2 class="text-2xl font-bold text-gray-800">Pengaturan Sistem</h2>
                    <p class="text-gray-600 text-sm">Atur batas threshold sensor</p>
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
                <form method="POST" action="{{ route('user.settings.update') }}">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Temperature Threshold -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="bg-gradient-to-br from-orange-400 to-orange-600 p-3 rounded-xl">
                                    <i class="fas fa-thermometer-half text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">Suhu Air</h3>
                                    <p class="text-sm text-gray-600">Batas suhu optimal (°C)</p>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-sm font-medium text-gray-700">Batas Bawah</label>
                                        <span class="text-sm font-bold text-gray-900"><span id="temp_min_display">{{ $settings->temp_min }}</span>°C</span>
                                    </div>
                                    <input type="range" name="temp_min" id="temp_min" min="0" max="50" step="0.5" 
                                           value="{{ old('temp_min', $settings->temp_min) }}"
                                           class="range-slider"
                                           oninput="document.getElementById('temp_min_display').textContent = this.value">
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>0°C</span>
                                        <span>50°C</span>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-sm font-medium text-gray-700">Batas Atas</label>
                                        <span class="text-sm font-bold text-gray-900"><span id="temp_max_display">{{ $settings->temp_max }}</span>°C</span>
                                    </div>
                                    <input type="range" name="temp_max" id="temp_max" min="0" max="50" step="0.5" 
                                           value="{{ old('temp_max', $settings->temp_max) }}"
                                           class="range-slider"
                                           oninput="document.getElementById('temp_max_display').textContent = this.value">
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>0°C</span>
                                        <span>50°C</span>
                                    </div>
                                </div>
                                
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-700">
                                        <i class="fas fa-info-circle text-orange-500 mr-2"></i>
                                        Suhu ideal untuk ikan: <strong>24-30°C</strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- pH Threshold -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="bg-gradient-to-br from-teal-400 to-teal-600 p-3 rounded-xl">
                                    <i class="fas fa-flask text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">pH Air</h3>
                                    <p class="text-sm text-gray-600">Batas keasaman optimal</p>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-sm font-medium text-gray-700">Batas Bawah</label>
                                        <span class="text-sm font-bold text-gray-900" id="ph_min_display">{{ $settings->ph_min }}</span>
                                    </div>
                                    <input type="range" name="ph_min" id="ph_min" min="0" max="14" step="0.1" 
                                           value="{{ old('ph_min', $settings->ph_min) }}"
                                           class="range-slider"
                                           oninput="document.getElementById('ph_min_display').textContent = this.value">
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>0 (Asam)</span>
                                        <span>14 (Basa)</span>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-sm font-medium text-gray-700">Batas Atas</label>
                                        <span class="text-sm font-bold text-gray-900" id="ph_max_display">{{ $settings->ph_max }}</span>
                                    </div>
                                    <input type="range" name="ph_max" id="ph_max" min="0" max="14" step="0.1" 
                                           value="{{ old('ph_max', $settings->ph_max) }}"
                                           class="range-slider"
                                           oninput="document.getElementById('ph_max_display').textContent = this.value">
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>0 (Asam)</span>
                                        <span>14 (Basa)</span>
                                    </div>
                                </div>
                                
                                <div class="bg-teal-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-700">
                                        <i class="fas fa-info-circle text-teal-500 mr-2"></i>
                                        pH ideal untuk ikan: <strong>6.5-8.5</strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Oxygen Threshold -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="bg-gradient-to-br from-green-400 to-green-600 p-3 rounded-xl">
                                    <i class="fas fa-wind text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">Oksigen Terlarut</h3>
                                    <p class="text-sm text-gray-600">Batas oksigen optimal (mg/L)</p>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-sm font-medium text-gray-700">Batas Bawah</label>
                                        <span class="text-sm font-bold text-gray-900"><span id="oxygen_min_display">{{ $settings->oxygen_min }}</span> mg/L</span>
                                    </div>
                                    <input type="range" name="oxygen_min" id="oxygen_min" min="0" max="20" step="0.1" 
                                           value="{{ old('oxygen_min', $settings->oxygen_min) }}"
                                           class="range-slider"
                                           oninput="document.getElementById('oxygen_min_display').textContent = this.value">
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>0 mg/L</span>
                                        <span>20 mg/L</span>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-sm font-medium text-gray-700">Batas Atas</label>
                                        <span class="text-sm font-bold text-gray-900"><span id="oxygen_max_display">{{ $settings->oxygen_max }}</span> mg/L</span>
                                    </div>
                                    <input type="range" name="oxygen_max" id="oxygen_max" min="0" max="20" step="0.1" 
                                           value="{{ old('oxygen_max', $settings->oxygen_max) }}"
                                           class="range-slider"
                                           oninput="document.getElementById('oxygen_max_display').textContent = this.value">
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>0 mg/L</span>
                                        <span>20 mg/L</span>
                                    </div>
                                </div>
                                
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-700">
                                        <i class="fas fa-info-circle text-green-500 mr-2"></i>
                                        Oksigen ideal untuk ikan: <strong>5-8 mg/L</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="reset" onclick="location.reload()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition flex items-center space-x-2">
                            <i class="fas fa-undo"></i>
                            <span>Reset</span>
                        </button>
                        <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>Simpan Pengaturan</span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
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

        // Close error notification after 10 seconds
        setTimeout(() => {
            const errorNotification = document.getElementById('error-notification');
            if (errorNotification) {
                errorNotification.style.opacity = '0';
                errorNotification.style.transition = 'opacity 0.5s';
                setTimeout(() => errorNotification.remove(), 500);
            }
        }, 10000);
    </script>
</body>
</html>
