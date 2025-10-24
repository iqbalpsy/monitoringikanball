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
                
                <!-- Notifications -->
                <div class="relative">
                    <button onclick="toggleNotifications()" class="text-white hover:text-gray-200 relative p-2 rounded-full hover:bg-white hover:bg-opacity-10 transition-all">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ isset($devices) ? $devices->where('status', 'offline')->count() : 0 }}
                        </span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg py-2 z-50 max-h-96 overflow-y-auto">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                        </div>
                        @if(isset($devices) && $devices->where('status', 'offline')->count() > 0)
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
                        <form method="POST" action="{{ route('logout') }}" class="block" id="logoutFormNavbar">
                            @csrf
                            <button type="button" onclick="confirmLogout('logoutFormNavbar')" class="flex items-center w-full px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
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
