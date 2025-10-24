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
            <button id="addUserBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>Add User
            </button>
            <button id="exportBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
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
                <input type="text" id="searchInput" placeholder="Search users..." value="{{ request('search') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64">
                <select id="roleFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
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
                            <a href="{{ route('admin.users.dashboard', $user->id) }}" class="text-indigo-600 hover:text-indigo-800 p-2 rounded-full hover:bg-indigo-100 transition-all" title="View User Dashboard">
                                <i class="fas fa-chart-line"></i>
                            </a>
                            <button onclick="editUser({{ $user->id }})" class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-100 transition-all" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="viewUser({{ $user->id }})" class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-100 transition-all" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-100 transition-all" title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-user-plus mr-2 text-blue-600"></i>Add New User
            </h3>
            <button onclick="closeModal('addUserModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addUserForm" onsubmit="handleAddUser(event)">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked class="mr-2 rounded">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('addUserModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i>Save User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-user-edit mr-2 text-blue-600"></i>Edit User
            </h3>
            <button onclick="closeModal('editUserModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editUserForm" onsubmit="handleEditUser(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_user_id" name="user_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="edit_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select id="edit_role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Password (leave blank to keep current)</label>
                <input type="password" id="edit_password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('editUserModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i>Update User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-user mr-2 text-blue-600"></i>User Details
            </h3>
            <button onclick="closeModal('viewUserModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="viewUserContent" class="space-y-4">
            <!-- User details will be loaded here -->
        </div>
        <div class="flex justify-end mt-4">
            <button onclick="closeModal('viewUserModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete User</h3>
            <p class="text-sm text-gray-500 mb-4">
                Are you sure you want to delete user <strong id="delete_user_name"></strong>? This action cannot be undone.
            </p>
            <div class="flex justify-center space-x-2">
                <button onclick="closeModal('deleteUserModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button onclick="confirmDeleteUser()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Global variables for modals
let currentDeleteUserId = null;

// Open Modal
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Add User - Open Modal
document.getElementById('addUserBtn').addEventListener('click', function() {
    document.getElementById('addUserForm').reset();
    openModal('addUserModal');
});

// Add User - Submit
function handleAddUser(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('{{ route("admin.users.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeModal('addUserModal');
            location.reload();
        } else {
            alert(data.message || 'Error creating user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the user');
    });
}

// Edit User - Open Modal
function editUser(userId) {
    fetch(`/admin/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_user_id').value = data.user.id;
                document.getElementById('edit_name').value = data.user.name;
                document.getElementById('edit_email').value = data.user.email;
                document.getElementById('edit_role').value = data.user.role;
                document.getElementById('edit_password').value = '';
                openModal('editUserModal');
            }
        })
        .catch(error => console.error('Error:', error));
}

// Edit User - Submit
function handleEditUser(event) {
    event.preventDefault();
    const form = event.target;
    const userId = document.getElementById('edit_user_id').value;
    const formData = new FormData(form);
    
    fetch(`/admin/users/${userId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeModal('editUserModal');
            location.reload();
        } else {
            alert(data.message || 'Error updating user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the user');
    });
}

// View User - Open Modal
function viewUser(userId) {
    fetch(`/admin/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                const content = `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-2xl mr-4">
                                ${user.name.charAt(0)}
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg">${user.name}</h4>
                                <p class="text-sm text-gray-500">ID: #${user.id}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Email</label>
                                <p class="text-gray-900">${user.email}</p>
                                ${user.email_verified_at ? '<span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i>Verified</span>' : '<span class="text-xs text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>Unverified</span>'}
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Role</label>
                                <p><span class="px-3 py-1 text-xs font-semibold rounded-full ${user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}">
                                    <i class="fas fa-${user.role === 'admin' ? 'user-shield' : 'user'} mr-1"></i>${user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                                </span></p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Status</label>
                                <p><span class="px-3 py-1 text-xs font-semibold rounded-full ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    <i class="fas fa-circle mr-1"></i>${user.is_active ? 'Active' : 'Inactive'}
                                </span></p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Created At</label>
                                <p class="text-gray-900">${new Date(user.created_at).toLocaleString()}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Last Login</label>
                                <p class="text-gray-900">${user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never'}</p>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('viewUserContent').innerHTML = content;
                openModal('viewUserModal');
            }
        })
        .catch(error => console.error('Error:', error));
}

// Delete User - Open Modal
function deleteUser(userId, userName) {
    currentDeleteUserId = userId;
    document.getElementById('delete_user_name').textContent = userName;
    openModal('deleteUserModal');
}

// Delete User - Confirm
function confirmDeleteUser() {
    if (!currentDeleteUserId) return;
    
    fetch(`/admin/users/${currentDeleteUserId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeModal('deleteUserModal');
            location.reload();
        } else {
            alert(data.message || 'Error deleting user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the user');
    });
}

// Export Users
document.getElementById('exportBtn').addEventListener('click', function() {
    window.location.href = '{{ route("admin.users.export") }}';
});

// Search functionality
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const search = e.target.value;
        const currentUrl = new URL(window.location.href);
        if (search) {
            currentUrl.searchParams.set('search', search);
        } else {
            currentUrl.searchParams.delete('search');
        }
        window.location.href = currentUrl.toString();
    }, 500);
});

// Role filter
document.getElementById('roleFilter').addEventListener('change', function(e) {
    const role = e.target.value;
    const currentUrl = new URL(window.location.href);
    if (role) {
        currentUrl.searchParams.set('role', role);
    } else {
        currentUrl.searchParams.delete('role');
    }
    window.location.href = currentUrl.toString();
});
</script>
@endsection
