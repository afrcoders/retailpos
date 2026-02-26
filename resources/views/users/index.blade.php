@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-people"></i> User Management</h1>
        <button class="btn btn-primary" onclick="createUser()" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="bi bi-plus-circle"></i> Add New User
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by name or email...">
        </div>
        <div class="col-md-4">
            <select id="roleFilter" class="form-select">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="subadmin">Sub-Administrator</option>
                <option value="sales_staff">Sales Staff</option>
            </select>
        </div>
        <div class="col-md-4">
            <select id="statusFilter" class="form-select">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i> All Users
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTable">
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $user->role->display_name }}</span>
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge badge-success"><i class="bi bi-check-circle"></i> Active</span>
                                    @else
                                        <span class="badge badge-danger"><i class="bi bi-x-circle"></i> Inactive</span>
                                    @endif
                                </td>
                                <td><small>{{ $user->created_at->format('M d, Y') }}</small></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editUser({{ $user->id }})" data-bs-toggle="modal" data-bs-target="#userModal">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    @if($user->id !== Auth::id())
                                        <button class="btn btn-sm btn-danger" onclick="deleteUser({{ $user->id }})">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mt-2">No users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                {{ $users->links() }}
            </ul>
        </nav>
    @endif
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> <span id="modalTitle">Add New User</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm" method="POST" action="/users">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="userId" name="id">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" id="userName" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="userEmail" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" id="userPhone" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select id="userRole" name="role_id" class="form-select" required>
                            <option value="">Select a role...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">
                            <strong>Admin:</strong> Full system access<br>
                            <strong>Sub-Admin:</strong> Full access to operations<br>
                            <strong>Sales Staff:</strong> POS operations only
                        </small>
                    </div>

                    <div class="mb-3" id="passwordDiv">
                        <label class="form-label">Password</label>
                        <input type="password" id="userPassword" name="password" class="form-control" required>
                        <small class="text-muted">Min 8 characters</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="userStatus" name="is_active" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global functions for onclick handlers
function createUser() {
    // Reset form
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordDiv').style.display = 'block';
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userPassword').setAttribute('required', 'required');
}

function editUser(userId) {
    fetch(`/users/${userId}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const user = data.data;
            document.getElementById('userId').value = user.id;
            document.getElementById('userName').value = user.name;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userPhone').value = user.phone || '';
            document.getElementById('userRole').value = user.role_id;
            document.getElementById('userStatus').value = user.is_active ? 1 : 0;
            document.getElementById('passwordDiv').style.display = 'none';
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('userPassword').removeAttribute('required');
        }
    });
}

function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) return;

    fetch(`/users/${userId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('User deleted successfully!');
            location.reload();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));

    document.getElementById('userForm').addEventListener('submit', function(e) {
        console.log('Form submit triggered'); // Debug

        // Let the form submit normally to /users
        // Our enhanced store method will handle both create and update
        const userId = document.getElementById('userId').value;
        console.log('Submitting form with userId:', userId);

        // The form will submit to POST /users and our store method will handle it
    });

    // Search and filter
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('#usersTable tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>
@endsection
