@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2>Admin Dashboard</h2>

        <!-- Konten Dashboard -->
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Reservations</h5>
                        <p class="card-text">150</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Customers</h5>
                        <p class="card-text">200</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Payments</h5>
                        <p class="card-text">30</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">New Messages</h5>
                        <p class="card-text">5</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Reservations -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h3>Recent Reservations</h3>
                <!-- Table displaying recent reservations -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>John Doe</td>
                            <td>Hair Cut</td>
                            <td>Pending</td>
                            <td>2025-10-01</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Jane Smith</td>
                            <td>Facial</td>
                            <td>Completed</td>
                            <td>2025-09-30</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
