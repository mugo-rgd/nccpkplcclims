@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>My Certificates</h1>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Certificate Number</th>
                        <th>Issue Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($certificates as $certificate)
                    <tr>
                        <td>{{ $certificate->certificate_number }}</td>
                        <td>{{ $certificate->issued_date }}</td>
                        <td>{{ $certificate->is_signed ? 'Signed' : 'Pending' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $certificates->links() }}
        </div>
    </div>
</div>
@endsection
