@extends('adminnavlayout')

@section('content')
<div class="container mt-5">
    <h2>Users Management</h2>
    <p>Here you can view and manage users.</p>

    <!-- Example table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <a href="/admin/users/{{ $user->id }}/edit" class="btn btn-sm btn-primary">Edit</a>
                    <form action="/admin/users/{{ $user->id }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
