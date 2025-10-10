@extends('layouts.app')

@section('title', 'Device Management')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Device Management</h1>
            <p class="text-gray-600 mt-1">Monitor dan kelola semua IoT devices</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Device
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>Sync Devices
            </button>
        </div>
    </div>
</div>

<!-- Device Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500 bg-opacity-20">
                <i class="fas fa-microchip text-blue-600 text-xl"></i>
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
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ $devices->where('status', 'online')->count() }}</p>
                <p class="text-gray-600">Online</p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-500 bg-opacity-20">
                <i class="fas fa-times-circle text-red-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ $devices->where('status', 'offline')->count() }}</p>
                <p class="text-gray-600">Offline</p>
            </div>
        </div>
    </div>

    <div class="card-gradient rounded-lg shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-20">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">{{ $devices->where('status', 'warning')->count() }}</p>
                <p class="text-gray-600">Warnings</p>
            </div>
        </div>
    </div>
</div>

<!-- Device List -->
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
    @foreach($devices as $device)
    <div class="card-gradient rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full {{ $device->status === 'online' ? 'bg-green-500 bg-opacity-20' : ($device->status === 'offline' ? 'bg-red-500 bg-opacity-20' : 'bg-yellow-500 bg-opacity-20') }}">
                    <i class="fas fa-microchip {{ $device->status === 'online' ? 'text-green-600' : ($device->status === 'offline' ? 'text-red-600' : 'text-yellow-600') }} text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="font-semibold text-gray-900">{{ $device->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $device->device_id }}</p>
                </div>
            </div>
            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $device->status === 'online' ? 'bg-green-100 text-green-800' : ($device->status === 'offline' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                <i class="fas fa-circle mr-1"></i>{{ ucfirst($device->status) }}
            </span>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Location:</span>
                <span class="font-medium">{{ $device->location }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Type:</span>
                <span class="font-medium">{{ $device->type }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Last Update:</span>
                <span class="font-medium text-sm">{{ $device->updated_at->diffForHumans() }}</span>
            </div>
        </div>

        <!-- Latest Sensor Data -->
        @if($device->latestSensorData)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h4 class="font-medium text-gray-900 mb-3">Latest Readings</h4>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($device->latestSensorData->ph, 1) }}</div>
                    <div class="text-xs text-gray-500">pH</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ number_format($device->latestSensorData->temperature, 1) }}°</div>
                    <div class="text-xs text-gray-500">Temp</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($device->latestSensorData->oxygen, 1) }}%</div>
                    <div class="text-xs text-gray-500">O2</div>
                </div>
            </div>
        </div>
        @endif

        <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between">
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <i class="fas fa-chart-line mr-1"></i>View Details
            </button>
            <div class="flex space-x-2">
                <button class="text-gray-600 hover:text-gray-800 p-1 rounded-full hover:bg-gray-100 transition-all" title="Edit">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <button class="text-green-600 hover:text-green-800 p-1 rounded-full hover:bg-green-100 transition-all" title="Control">
                    <i class="fas fa-sliders-h text-sm"></i>
                </button>
                <button class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100 transition-all" title="Delete">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Device Controls -->
<div class="card-gradient rounded-lg shadow-lg overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Device Controls</h3>
        <p class="text-gray-600 mt-1">Kontrol dan automation settings</p>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Feeding System -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-900">Auto Feeding</h4>
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Enable</span>
                    </label>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Next Feed:</span>
                        <span class="text-sm font-medium">14:00 WIB</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Amount:</span>
                        <span class="text-sm font-medium">50g</span>
                    </div>
                </div>
                <button class="w-full mt-3 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm transition-colors">
                    <i class="fas fa-utensils mr-2"></i>Feed Now
                </button>
            </div>

            <!-- Water System -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-900">Water Control</h4>
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" checked>
                        <span class="ml-2 text-sm text-gray-700">Auto</span>
                    </label>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Flow Rate:</span>
                        <span class="text-sm font-medium">2.5 L/min</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Pump Status:</span>
                        <span class="text-sm font-medium text-green-600">Running</span>
                    </div>
                </div>
                <button class="w-full mt-3 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg text-sm transition-colors">
                    <i class="fas fa-tint mr-2"></i>Control Pump
                </button>
            </div>

            <!-- Lighting System -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-900">LED Lighting</h4>
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Schedule</span>
                    </label>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Brightness:</span>
                        <span class="text-sm font-medium">75%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Mode:</span>
                        <span class="text-sm font-medium">Day Light</span>
                    </div>
                </div>
                <button class="w-full mt-3 bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-lg text-sm transition-colors">
                    <i class="fas fa-lightbulb mr-2"></i>Toggle Light
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
