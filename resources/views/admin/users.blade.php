@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<!-- Page Header -->
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
@endsection
