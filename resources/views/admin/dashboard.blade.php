@extends('adminnavlayout')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h2 class="mb-0">Admin Dashboard</h2>
        </div>
        <div class="card-body">
            <p class="lead">You are logged in as an admin.</p>
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-dark mb-3">
                        <div class="card-header">Total Registered Users</div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $totalUsers }}</h5>
                        </div>
                    </div>
                </div>
                <!-- Add more cards here for additional stats -->
            </div>
        </div>
    </div>
</div>
@endsection
