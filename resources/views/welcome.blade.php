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
<section class="hero-section dotted-bg">
    
    <!-- Floating Elements (Desktop Only) -->
    <!-- Card 1: Sticky Note (Left Top) -->
    <div class="floating-wrapper fw-1 d-none d-xl-block">
        <div class="ui-card sticky-note" style="width: 220px; transform: rotate(-4deg); padding: 1.5rem 1.25rem;">
            <div class="text-dark" style="font-family: 'Comic Sans MS', 'Chalkboard SE', sans-serif; font-size: 0.95rem; line-height: 1.4;">
                Take notes to keep track of crucial internship details, and accomplish tasks with ease.
            </div>
            <!-- Pin visual -->
            <div style="position:absolute; top:-8px; left:50%; transform:translateX(-50%); width:14px; height:14px; background:#ef4444; border-radius:50%; box-shadow:inset -2px -2px 4px rgba(0,0,0,0.3), 0 2px 4px rgba(0,0,0,0.2);"></div>
            <!-- Checkbox floating on sticky note -->
            <div class="position-absolute bg-white rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; bottom: -20px; left: -15px; border: 1px solid #e2e8f0;">
                <div class="bg-primary-custom rounded d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                    <i class="fas fa-check text-white" style="font-size: 12px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Reminders (Right Top) -->
    <div class="floating-wrapper fw-2 d-none d-xl-block">
        <div class="ui-card" style="width: 250px; padding: 0;">
            <div class="bg-light px-3 py-2 border-bottom d-flex justify-content-between align-items-center" style="border-radius: 1.25rem 1.25rem 0 0;">
                <span class="fw-bold ms-4 ps-2" style="font-size: 0.85rem;">Reminders</span>
                <span class="text-muted" style="font-size: 0.7rem;">Today</span>
            </div>
            <div class="p-3">
                <div class="fw-bold text-dark" style="font-size: 0.85rem;">Logbook Submission</div>
                <div class="text-muted mb-2" style="font-size: 0.75rem;">Submit your weekly logbook to supervisor.</div>
                <div class="d-inline-flex align-items-center bg-primary-custom bg-opacity-10 text-primary-custom px-2 py-1 rounded" style="font-size: 0.75rem; font-weight: 600;">
                    <i class="far fa-clock me-1"></i> 23:59 PM
                </div>
            </div>
            <!-- Clock Floating icon -->
            <div class="position-absolute bg-white rounded-circle shadow d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; top: -15px; left: -25px; border: 1px solid #e2e8f0;">
                <i class="far fa-clock text-dark" style="font-size: 24px;"></i>
            </div>
        </div>
    </div>

    <!-- Card 3: AI Assistant (Bottom Left) -->
    <div class="floating-wrapper fw-3 d-none d-xl-block">
        <div class="ui-card" style="width: 250px;">
            <div class="fw-bold mb-3 text-dark" style="font-size: 0.9rem;">AI Logbook Assist</div>
            
            <div class="d-flex mb-3">
                <div class="bg-primary-custom text-white rounded d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px;">
                    <i class="fas fa-sparkles" style="font-size: 12px;"></i>
                </div>
                <div>
                    <div class="bg-light rounded mb-1" style="height: 8px; width: 140px;"></div>
                    <div class="bg-light rounded" style="height: 8px; width: 100px;"></div>
                </div>
            </div>

            <div class="progress mb-2" style="height: 6px; border-radius: 3px;">
                <div class="progress-bar bg-success" style="width: 100%;"></div>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size: 0.7rem;">
                <span>Generation Complete</span>
                <span class="text-success fw-bold"><i class="fas fa-check"></i></span>
            </div>
        </div>
    </div>

    <!-- Card 4: Approvals/Tracking (Bottom Right) -->
    <div class="floating-wrapper fw-4 d-none d-xl-block">
        <div class="ui-card" style="width: 230px;">
            <div class="fw-bold mb-2 text-dark" style="font-size: 0.9rem;">Supervisor Approvals</div>
            <div class="d-flex align-items-center p-2 border rounded mb-2 bg-white">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 24px; height: 24px;">
                    <i class="fas fa-check" style="font-size: 10px;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 0.75rem;">Week 10 Log</div>
                    <div class="text-muted" style="font-size: 0.65rem;">Approved by Dr. Ahmad</div>
                </div>
            </div>
            <div class="d-flex align-items-center p-2 border rounded bg-white">
                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 24px; height: 24px;">
                    <i class="fas fa-hourglass-half" style="font-size: 10px;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 0.75rem;">Week 11 Log</div>
                    <div class="text-muted" style="font-size: 0.65rem;">Pending review</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container hero-content text-center pb-5">
        <!-- Center Icon -->
        <div class="mx-auto mb-4 d-flex align-items-center justify-content-center bg-white shadow" style="width: 72px; height: 72px; border-radius: 1.25rem; border: 1px solid #e2e8f0;">
            <div class="row g-1" style="width: 32px;">
                <div class="col-6"><div class="bg-primary-custom rounded-circle" style="width:12px; height:12px;"></div></div>
                <div class="col-6"><div class="bg-dark rounded-circle" style="width:12px; height:12px;"></div></div>
                <div class="col-6"><div class="bg-dark rounded-circle" style="width:12px; height:12px;"></div></div>
                <div class="col-6"><div class="bg-dark rounded-circle" style="width:12px; height:12px;"></div></div>
            </div>
        </div>

        <h1 class="hero-title mb-3">
            Logbook Internship <br>
            <span style="color: #1E40AF;">Management System</span>
        </h1>
        
        <p class="hero-subtitle mb-4">
            Streamlining the industrial training experience for Students, Supervisors, and Administrators. <br>
            Efficiently manage daily logs, track progress, and ensure a seamless internship journey.
        </p>
        
        <div class="d-flex justify-content-center">
            <a href="{{ route('login') }}" class="btn btn-hero-primary">Get Started</a>
        </div>
    </div>
</section>

<!-- Mobile Features Section (Hidden on xl since floating cards exist) -->
<section class="py-5 bg-white d-xl-none border-top">
    <div class="container py-4">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 border rounded-4 h-100 bg-light border-0">
                    <div class="text-primary-custom mb-3"><i class="fas fa-magic fa-2x"></i></div>
                    <h5 class="fw-bold">AI Logbook</h5>
                    <p class="text-muted small">Auto-generate summaries for your daily activities efficiently.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded-4 h-100 bg-light border-0">
                    <div class="text-primary-custom mb-3"><i class="fas fa-check-circle fa-2x"></i></div>
                    <h5 class="fw-bold">Approvals</h5>
                    <p class="text-muted small">Seamless review and approval process for supervisors.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded-4 h-100 bg-light border-0">
                    <div class="text-primary-custom mb-3"><i class="far fa-clock fa-2x"></i></div>
                    <h5 class="fw-bold">Reminders</h5>
                    <p class="text-muted small">Never miss a submission deadline with automated tracking.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="py-4 bg-white">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-12">
                <h6 class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-center">
                    <i class="fas fa-book-reader me-2 text-primary-custom"></i> LIMS
                </h6>
                <p class="small text-muted mb-0">Logbook Internship Management System © {{ date('Y') }}. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>
@endsection
