@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login-register.css') }}">
<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #F3F4F6 0%, #E0E7FF 100%);
}
.auth-card {
    background: white;
    border-radius: 1rem;
    padding: 2.5rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 450px;
}
</style>
@endpush

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary-custom"><i class="fas fa-book-reader me-2"></i>LIMS</h3>
            <p class="text-muted">Welcome back! Please login to continue.</p>
        </div>

        <!-- Tabs for Login / Register -->
        <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ old('name') || old('matrix_id') ? '' : 'active' }} rounded-pill px-4" id="pills-login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab">Login</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ old('name') || old('matrix_id') ? 'active' : '' }} rounded-pill px-4" id="pills-register-tab" data-bs-toggle="pill" data-bs-target="#pills-register" type="button" role="tab">Register</button>
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
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary-custom btn-lg">Login</button>
                    </div>
                </form>
            </div>

            <!-- REGISTER FORM -->
            <div class="tab-pane fade {{ old('name') || old('matrix_id') ? 'show active' : '' }}" id="pills-register" role="tabpanel">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               placeholder="John Doe" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student / Supervisor ID</label>
                        <input type="text" name="matrix_id" class="form-control @error('matrix_id') is-invalid @enderror" 
                               placeholder="SW0108XXX / S12345" value="{{ old('matrix_id') }}" required>
                        @error('matrix_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               placeholder="name@varsity.edu.my" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Company / Internship Place</label>
                        <input type="text" name="company" class="form-control" 
                               placeholder="ABC Tech Solutions" value="{{ old('company') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="regRole">
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        </select>
                    </div>
                    <div class="mb-3" id="supervisorField">
                        <label class="form-label">Select Supervisor</label>
                        <select class="form-select" name="supervisor_id">
                            <option value="">Choose...</option>
                            @foreach($supervisors ?? [] as $supervisor)
                                <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Required for students only.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required id="regPassword">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('regPassword', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="form-control" required id="regConfirmPassword">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('regConfirmPassword', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">Register Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('regRole').addEventListener('change', function() {
    document.getElementById('supervisorField').style.display = this.value === 'student' ? 'block' : 'none';
});

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endpush
