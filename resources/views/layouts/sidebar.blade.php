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
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg group">
                <div class="flex items-center justify-center w-8 h-8 bg-blue-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                    <i class="fas fa-tachometer-alt text-sm"></i>
                </div>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="{{ route('admin.history') }}" class="sidebar-link {{ request()->routeIs('admin.history') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg group">
                <div class="flex items-center justify-center w-8 h-8 bg-green-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                    <i class="fas fa-history text-sm"></i>
                </div>
                <span class="font-medium">History</span>
                <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                    {{ isset($totalSensorData) ? ($totalSensorData > 999 ? '999+' : $totalSensorData) : '0' }}
                </span>
            </a>
            
            <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg group">
                <div class="flex items-center justify-center w-8 h-8 bg-purple-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                    <i class="fas fa-users text-sm"></i>
                </div>
                <span class="font-medium">Pengguna</span>
                <span class="ml-auto bg-purple-500 text-white text-xs px-2 py-1 rounded-full">
                    {{ isset($users) ? $users->count() : '0' }}
                </span>
            </a>
            
            <a href="{{ route('admin.reports') }}" class="sidebar-link {{ request()->routeIs('admin.reports') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg group">
                <div class="flex items-center justify-center w-8 h-8 bg-indigo-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                    <i class="fas fa-file-alt text-sm"></i>
                </div>
                <span class="font-medium">Laporan</span>
            </a>
            
            <!-- Divider -->
            <div class="py-2">
                <hr class="border-gray-600">
            </div>
            
            <!-- Sensor Parameters Section -->
            <div class="space-y-1">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Parameter Sensor</p>
                
                <a href="#" onclick="showParameterDetails('temperature')" class="sidebar-link flex items-center px-4 py-3 text-white rounded-lg group hover:bg-white hover:bg-opacity-10 transition-all">
                    <div class="flex items-center justify-center w-8 h-8 bg-orange-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                        <i class="fas fa-thermometer-half text-sm"></i>
                    </div>
                    <span class="font-medium">Suhu Air</span>
                    <span class="ml-auto">
                        <span id="tempSidebarValue" class="text-xs text-gray-300">26.3°C</span>
                    </span>
                </a>
                
                <a href="#" onclick="showParameterDetails('oxygen')" class="sidebar-link flex items-center px-4 py-3 text-white rounded-lg group hover:bg-white hover:bg-opacity-10 transition-all">
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                        <i class="fas fa-wind text-sm"></i>
                    </div>
                    <span class="font-medium">Oksigen</span>
                    <span class="ml-auto">
                        <span id="oxygenSidebarValue" class="text-xs text-gray-300">8.5 ppm</span>
                    </span>
                </a>
                
                <a href="#" onclick="showParameterDetails('ph')" class="sidebar-link flex items-center px-4 py-3 text-white rounded-lg group hover:bg-white hover:bg-opacity-10 transition-all">
                    <div class="flex items-center justify-center w-8 h-8 bg-green-500 bg-opacity-20 rounded-lg mr-3 group-hover:bg-opacity-30 transition-all">
                        <i class="fas fa-vial text-sm"></i>
                    </div>
                    <span class="font-medium">pH</span>
                    <span class="ml-auto">
                        <span id="phSidebarValue" class="text-xs text-gray-300">6.8 pH</span>
                    </span>
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
