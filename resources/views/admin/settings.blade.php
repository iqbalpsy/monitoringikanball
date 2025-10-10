@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
            <p class="text-gray-600 mt-1">Konfigurasi sistem dan preferensi</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-save mr-2"></i>Save All Changes
            </button>
            <button class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-undo mr-2"></i>Reset to Default
            </button>
        </div>
    </div>
</div>

<!-- Settings Navigation -->
<div class="mb-8">
    <nav class="flex space-x-8">
        <button onclick="showTab('general')" class="tab-button active px-4 py-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600">
            <i class="fas fa-cog mr-2"></i>General
        </button>
        <button onclick="showTab('alerts')" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
            <i class="fas fa-bell mr-2"></i>Alerts & Notifications
        </button>
        <button onclick="showTab('devices')" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
            <i class="fas fa-microchip mr-2"></i>Device Settings
        </button>
        <button onclick="showTab('users')" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
            <i class="fas fa-users mr-2"></i>User Management
        </button>
        <button onclick="showTab('backup')" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
            <i class="fas fa-database mr-2"></i>Backup & Restore
        </button>
    </nav>
</div>

<!-- General Settings Tab -->
<div id="general-tab" class="tab-content">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Information -->
        <div class="card-gradient rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">System Information</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">System Name:</span>
                    <input type="text" value="IoT Fish Monitoring System" class="border border-gray-300 rounded-lg px-3 py-1 text-sm w-64">
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Version:</span>
                    <span class="font-medium">v1.0.0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Database:</span>
                    <span class="font-medium text-green-600">Connected</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Firebase:</span>
                    <span class="font-medium text-green-600">Connected</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Backup:</span>
                    <span class="font-medium">Dec 31, 2024 08:00</span>
                </div>
            </div>
        </div>

        <!-- General Preferences -->
        <div class="card-gradient rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">General Preferences</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option>Asia/Jakarta (WIB)</option>
                        <option>Asia/Makassar (WITA)</option>
                        <option>Asia/Jayapura (WIT)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option>DD/MM/YYYY</option>
                        <option>MM/DD/YYYY</option>
                        <option>YYYY-MM-DD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Temperature Unit</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option>Celsius (°C)</option>
                        <option>Fahrenheit (°F)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Retention Period</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option>3 Months</option>
                        <option>6 Months</option>
                        <option>1 Year</option>
                        <option>2 Years</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts & Notifications Tab -->
<div id="alerts-tab" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Alert Thresholds -->
        <div class="card-gradient rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Alert Thresholds</h3>
            <div class="space-y-6">
                <!-- pH Thresholds -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">pH Level</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Minimum</label>
                            <input type="number" value="6.5" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Maximum</label>
                            <input type="number" value="8.5" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                </div>

                <!-- Temperature Thresholds -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Temperature (°C)</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Minimum</label>
                            <input type="number" value="24" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Maximum</label>
                            <input type="number" value="28" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                </div>

                <!-- Oxygen Thresholds -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Oxygen Level (mg/L)</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Minimum</label>
                            <input type="number" value="5.0" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Critical Low</label>
                            <input type="number" value="3.0" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card-gradient rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Notification Settings</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">Email Notifications</h4>
                        <p class="text-sm text-gray-600">Receive alerts via email</p>
                    </div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" checked>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">SMS Notifications</h4>
                        <p class="text-sm text-gray-600">Receive critical alerts via SMS</p>
                    </div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600">
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">Push Notifications</h4>
                        <p class="text-sm text-gray-600">Browser push notifications</p>
                    </div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" checked>
                    </label>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alert Email Recipients</label>
                    <textarea class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" rows="3" placeholder="admin@example.com&#10;operator@example.com"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alert Frequency</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option>Immediate</option>
                        <option>Every 5 minutes</option>
                        <option>Every 15 minutes</option>
                        <option>Every hour</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Device Settings Tab -->
<div id="devices-tab" class="tab-content hidden">
    <div class="space-y-6">
        <!-- Device Configuration -->
        <div class="card-gradient rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Device Configuration</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Collection Interval</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option>30 seconds</option>
                        <option>1 minute</option>
                        <option>5 minutes</option>
                        <option>15 minutes</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Connection Timeout</label>
                    <input type="number" value="30" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Seconds</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Retry Attempts</label>
                    <input type="number" value="3" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Calibration Reminder</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option>Weekly</option>
                        <option>Monthly</option>
                        <option>Quarterly</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Auto Controls -->
        <div class="card-gradient rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Automatic Controls</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Feeding System -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-4">Auto Feeding</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Enable Auto Feed</span>
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600" checked>
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Feed Times</label>
                            <input type="text" value="08:00, 14:00, 20:00" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Amount (grams)</label>
                            <input type="number" value="50" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Water Control -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-4">Water Control</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Auto Water Change</span>
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600">
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Change Interval</label>
                            <select class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                <option>Daily</option>
                                <option>Weekly</option>
                                <option>Bi-weekly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Percentage</label>
                            <input type="number" value="25" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Lighting -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-4">LED Lighting</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Auto Lighting</span>
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600" checked>
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">On Time</label>
                            <input type="time" value="06:00" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Off Time</label>
                            <input type="time" value="18:00" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Management Tab -->
<div id="users-tab" class="tab-content hidden">
    <div class="card-gradient rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">User Management Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-900 mb-4">Security Settings</h4>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Require Email Verification</span>
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" checked>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Two-Factor Authentication</span>
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600">
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Password Minimum Length</label>
                        <input type="number" value="8" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Session Timeout (minutes)</label>
                        <input type="number" value="120" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>
            <div>
                <h4 class="font-medium text-gray-900 mb-4">Registration Settings</h4>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Allow Self Registration</span>
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600">
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Admin Approval Required</span>
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" checked>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Default Role</label>
                        <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option>User</option>
                            <option>Viewer</option>
                            <option>Operator</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup & Restore Tab -->
<div id="backup-tab" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Backup Settings -->
        <div class="card-gradient rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Backup Settings</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">Automatic Backup</h4>
                        <p class="text-sm text-gray-600">Schedule regular system backups</p>
                    </div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" checked>
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Backup Frequency</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option>Daily</option>
                        <option>Weekly</option>
                        <option>Monthly</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Backup Time</label>
                    <input type="time" value="02:00" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Retention Period</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option>30 days</option>
                        <option>90 days</option>
                        <option>6 months</option>
                        <option>1 year</option>
                    </select>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Create Backup Now
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Backups -->
        <div class="card-gradient rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Recent Backups</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div>
                        <h4 class="font-medium text-gray-900">Full System Backup</h4>
                        <p class="text-sm text-gray-600">Dec 31, 2024 02:00 AM</p>
                        <p class="text-xs text-gray-500">Size: 245 MB</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition-all" title="Download">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-100 transition-all" title="Restore">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div>
                        <h4 class="font-medium text-gray-900">Database Backup</h4>
                        <p class="text-sm text-gray-600">Dec 30, 2024 02:00 AM</p>
                        <p class="text-xs text-gray-500">Size: 128 MB</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition-all" title="Download">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-100 transition-all" title="Restore">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div>
                        <h4 class="font-medium text-gray-900">Configuration Backup</h4>
                        <p class="text-sm text-gray-600">Dec 29, 2024 02:00 AM</p>
                        <p class="text-xs text-gray-500">Size: 2.5 MB</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition-all" title="Download">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-100 transition-all" title="Restore">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Restore from File</label>
                <div class="flex">
                    <input type="file" class="flex-1 border border-gray-300 rounded-l-lg px-3 py-2 text-sm">
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-r-lg transition-colors">
                        <i class="fas fa-upload"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-600', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Add active class to clicked tab button
    event.target.classList.add('active', 'border-blue-600', 'text-blue-600');
    event.target.classList.remove('border-transparent', 'text-gray-500');
}

// Initialize with general tab active
document.addEventListener('DOMContentLoaded', function() {
    showTab('general');
});
</script>
@endpush
