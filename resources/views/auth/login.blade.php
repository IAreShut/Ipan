@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login-register.css') }}">
@endpush

@section('content')
<div class="auth-container">
    <div class="auth-card {{ old('name') || old('matrix_id') ? 'register-mode' : '' }}" id="authCard">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary-custom mb-1"><i class="fas fa-book-reader me-2"></i>LIMS</h3>
            <p class="text-muted small mb-0">Welcome back! Please login to continue.</p>
        </div>

        <!-- Tabs for Login / Register -->
        <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ old('name') || old('matrix_id') ? '' : 'active' }}" id="pills-login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab">Login</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ old('name') || old('matrix_id') ? 'active' : '' }}" id="pills-register-tab" data-bs-toggle="pill" data-bs-target="#pills-register" type="button" role="tab">Register</button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <!-- LOGIN FORM -->
            <div class="tab-pane fade {{ old('name') || old('matrix_id') ? '' : 'show active' }}" id="pills-login" role="tabpanel">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address / ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="Email or Student/Staff ID" value="{{ old('email') }}" required>
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="********" required id="loginPassword">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('loginPassword', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="remember" id="remember">
                        <label class="form-check-label text-muted small" for="remember">Remember me</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary-custom btn-lg">Login</button>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded text-center small border">
                        <strong class="text-muted">Test Credentials:</strong><br>
                        @if(app()->environment('local'))
                            <span class="text-muted">Student:</span> <code>student@test.com</code> | <code>password123</code><br>
                            <span class="text-muted">Supervisor:</span> <code>supervisor@test.com</code> | <code>password123</code>
                        @else
                            <span class="text-muted">Student:</span> <code>2024692246</code> | <code>11111111</code><br>
                            <span class="text-muted">Supervisor:</span> <code>sv1@sv.com</code> | <code>password</code>
                        @endif
                    </div>
                </form>
            </div>

            <!-- REGISTER FORM (Clean 2-Column Grid on Tablet/Desktop, 1-Column on Mobile) -->
            <div class="tab-pane fade {{ old('name') || old('matrix_id') ? 'show active' : '' }}" id="pills-register" role="tabpanel">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="reg-form-grid">
                        <div>
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="John Doe" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Student / Supervisor ID <span class="text-danger">*</span></label>
                            <input type="text" name="matrix_id" id="regMatrixId" class="form-control @error('matrix_id') is-invalid @enderror" 
                                   placeholder="e.g. 12345678" value="{{ old('matrix_id') }}" pattern="[0-9]+" title="Numbers only" required>
                            <div id="assignmentCheckResult" class="mt-1"></div>
                            @error('matrix_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="name@varsity.edu.my" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       placeholder="e.g. 0123456789" value="{{ old('phone') }}" pattern="01[0-9]{8,9}" title="Malaysian phone number (e.g., 0123456789)" required>
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" id="regRole">
                                <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                            </select>
                        </div>

                        <div id="companyField" style="{{ old('role') == 'supervisor' ? 'display: none;' : '' }}">
                            <label class="form-label">Company / Internship Place <span class="text-danger">*</span></label>
                            <input type="text" name="company" class="form-control @error('company') is-invalid @enderror" 
                                   placeholder="ABC Tech Solutions" value="{{ old('company') }}" {{ old('role') != 'supervisor' ? 'required' : '' }}>
                            @error('company')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="facultyField" style="{{ old('role') == 'supervisor' ? 'display: none;' : '' }}">
                            <label class="form-label">Faculty</label>
                            <input type="text" class="form-control @error('reg_faculty') is-invalid @enderror" name="reg_faculty" id="regFaculty" readonly placeholder="Auto-filled after ID verification" value="{{ old('reg_faculty') }}">
                            @error('reg_faculty')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="programmeField" style="{{ old('role') == 'supervisor' ? 'display: none;' : '' }}">
                            <label class="form-label">Programme Code</label>
                            <input type="text" class="form-control @error('reg_programme_code') is-invalid @enderror" name="reg_programme_code" id="regProgramme" readonly placeholder="Auto-filled after ID verification" value="{{ old('reg_programme_code') }}">
                            @error('reg_programme_code')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="classField" style="{{ old('role') == 'supervisor' ? 'display: none;' : '' }}">
                            <label class="form-label">Class</label>
                            <input type="text" class="form-control @error('reg_class') is-invalid @enderror" name="reg_class" id="regClass" readonly placeholder="Auto-filled after ID verification" value="{{ old('reg_class') }}">
                            @error('reg_class')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required id="regPassword" placeholder="********">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('regPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" class="form-control" required id="regConfirmPassword" placeholder="********">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('regConfirmPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="reg-grid-full mt-2">
                            <button type="submit" id="registerBtn" class="btn btn-success btn-lg w-100" disabled>Register Account</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.checkAssignmentUrl = '{{ route("check-assignment") }}';

// Dynamically expand auth card width when switching between Login & Register tabs on desktop/tablet
document.addEventListener('DOMContentLoaded', function() {
    var authCard = document.getElementById('authCard');
    var regTab = document.getElementById('pills-register-tab');
    var loginTab = document.getElementById('pills-login-tab');

    if (regTab && authCard) {
        regTab.addEventListener('shown.bs.tab', function() {
            authCard.classList.add('register-mode');
        });
    }

    if (loginTab && authCard) {
        loginTab.addEventListener('shown.bs.tab', function() {
            authCard.classList.remove('register-mode');
        });
    }
});
</script>
<script src="{{ asset('js/auth/login.js') }}"></script>
@endpush
