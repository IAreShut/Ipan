@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

@section('content')
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary-custom" href="#">
            <i class="fas fa-book-reader me-2"></i>LIMS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                @auth
                    @if(Auth::user()->role === 'supervisor')
                        <li class="nav-item">
                            <a class="btn btn-primary-custom" href="{{ route('supervisor.dashboard') }}">Dashboard</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-primary-custom" href="{{ route('student.dashboard') }}">Dashboard</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="btn btn-outline-primary me-2" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary-custom" href="{{ route('login') }}">Register</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="d-flex align-items-center justify-content-center text-center" style="min-height: 85vh; background: linear-gradient(135deg, #F3F4F6 0%, #E0E7FF 100%); padding-top: 80px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-4 text-primary-custom">Logbook Internship Management System</h1>
                <p class="lead text-muted mb-5">
                    Streamlining the industrial training experience for Students, Supervisors, and Administrators. 
                    Efficiently manage daily logs, track progress, and ensure a seamless internship journey.
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-primary-custom btn-lg px-5 shadow-sm">Get Started</a>
                    <a href="#features" class="btn btn-outline-secondary btn-lg px-5">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-white">
    <div class="container py-5">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 border rounded-3 h-100 shadow-sm border-0">
                    <div class="text-primary-custom mb-3"><i class="fas fa-edit fa-3x"></i></div>
                    <h4>Digital Logbook</h4>
                    <p class="text-muted">Easily record daily activities with AI-assisted summaries and image attachments.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded-3 h-100 shadow-sm border-0">
                    <div class="text-primary-custom mb-3"><i class="fas fa-chalkboard-teacher fa-3x"></i></div>
                    <h4>Supervisor Oversight</h4>
                    <p class="text-muted">Supervisors can review logs, approve/reject entries, and track student attendance effortlessly.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded-3 h-100 shadow-sm border-0">
                    <div class="text-primary-custom mb-3"><i class="fas fa-chart-line fa-3x"></i></div>
                    <h4>Progress Tracking</h4>
                    <p class="text-muted">Real-time analytics and detailed progress reports to keep students on track.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-12">
                <h5 class="fw-bold mb-2">LIMS - Faculty of Computing</h5>
                <p class="small text-white-50">Logbook Internship Management System © {{ date('Y') }}. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>
@endsection
