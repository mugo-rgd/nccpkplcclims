@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>My Calibrations</h1>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Equipment</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($calibrations as $calibration)
                    <tr>
                        <td>{{ $calibration->id }}</td>
                        <td>{{ $calibration->equipment_id }}</td>
                        <td>{{ $calibration->status }}</td>
                        <td>{{ $calibration->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $calibrations->links() }}
        </div>
    </div>
</div>
@endsection
