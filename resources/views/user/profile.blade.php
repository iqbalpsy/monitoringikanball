<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - AquaMonitor</title>
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
                    <a href="{{ route('user.profile') }}" class="menu-item active flex items-center space-x-3 px-4 py-3 rounded-lg">
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
                    <h2 class="text-2xl font-bold text-gray-800">Profile Pengguna</h2>
                    <p class="text-gray-600 text-sm">Kelola informasi profil Anda</p>
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
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <div class="text-center">
                                <div class="w-32 h-32 mx-auto mb-4 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-5xl font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $user->name }}</h3>
                                <p class="text-gray-600 mb-4">{{ $user->email }}</p>
                                <span class="inline-block px-4 py-2 bg-purple-100 text-purple-600 rounded-full text-sm font-semibold capitalize">
                                    <i class="fas fa-user-tag mr-2"></i>{{ $user->role }}
                                </span>
                            </div>
                            <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-calendar w-6"></i>
                                    <span>Bergabung {{ $user->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-clock w-6"></i>
                                    <span>Login terakhir: {{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Belum pernah' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Profile Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Personal Information -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <i class="fas fa-user-edit text-purple-600 text-2xl"></i>
                                <h3 class="text-xl font-bold text-gray-800">Informasi Personal</h3>
                            </div>
                            <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition flex items-center space-x-2">
                                            <i class="fas fa-save"></i>
                                            <span>Simpan Perubahan</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <i class="fas fa-lock text-purple-600 text-2xl"></i>
                                <h3 class="text-xl font-bold text-gray-800">Ubah Password</h3>
                            </div>
                            <form method="POST" action="{{ route('user.password.update') }}">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Lama</label>
                                        <input type="password" name="current_password" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                                        <input type="password" name="new_password" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                               minlength="8">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                                        <input type="password" name="new_password_confirmation" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                               minlength="8">
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="bg-orange-600 text-white px-6 py-3 rounded-lg hover:bg-orange-700 transition flex items-center space-x-2">
                                            <i class="fas fa-key"></i>
                                            <span>Update Password</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
