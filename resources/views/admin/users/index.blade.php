@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">User Management</h3>
        <div class="card-tools">
            @can('create_users')
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add User
                </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Staff No.</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->staff_number ?? '-' }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @can('view_users')
                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endcan
                            @can('edit_users')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @can('edit_users')
                                <button onclick="toggleStatus({{ $user->id }})" class="btn btn-sm btn-warning">
                                    <i class="fas fa-power-off"></i>
                                </button>
                            @endcan
                            @can('delete_users')
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
              {{-- Pagination --}}
    {{ $users->appends(request()->query())->links() }}

    </div>
</div>

<form id="toggleForm" method="POST" style="display:none;">
    @csrf
</form>

<script>
function toggleStatus(userId) {
    const form = document.getElementById('toggleForm');
    form.action = `/admin/users/${userId}/toggle-status`;
    form.submit();
}
</script>
@endsection
