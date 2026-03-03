@extends('layouts.app')

@section('title', 'Profile - LIMS')

@section('sidebar-menu')
<a class="nav-link" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.notifications') }}"><i class="fas fa-bell"></i> Notifications</a>
<a class="nav-link active" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'My Profile')

@section('main-content')
<div class="row">
    <div class="col-lg-4">
        <div class="card card-custom p-4 text-center">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&size=150" 
                 class="rounded-circle mx-auto mb-3" width="150" alt="Avatar">
            <h4 class="fw-bold">{{ $user->name }}</h4>
            <p class="text-muted text-capitalize">{{ $user->role }}</p>
            <span class="badge bg-primary-custom">{{ $user->company ?? 'No Company' }}</span>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <h5 class="fw-bold mb-4">Profile Information</h5>
            <form>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Company / Internship Place</label>
                        <input type="text" class="form-control" value="{{ $user->company ?? 'Not specified' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Supervisor</label>
                        <input type="text" class="form-control" value="{{ $user->supervisor->name ?? 'Not assigned' }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" value="{{ $user->phone ?? 'Not specified' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Student / Supervisor ID</label>
                        <input type="text" class="form-control" value="{{ $user->matrix_id ?? 'Not specified' }}" readonly>
                    </div>
                </div>
                @if($internship)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Internship Start Date</label>
                        <input type="text" class="form-control" value="{{ $internship->start_date->format('d M Y') }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Internship End Date</label>
                        <input type="text" class="form-control" value="{{ $internship->end_date->format('d M Y') }}" readonly>
                    </div>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
