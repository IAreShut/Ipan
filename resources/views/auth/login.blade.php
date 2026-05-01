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
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               placeholder="John Doe" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student / Supervisor ID <span class="text-danger">*</span></label>
                        <input type="text" name="matrix_id" class="form-control @error('matrix_id') is-invalid @enderror" 
                               placeholder="e.g. 12345678" value="{{ old('matrix_id') }}" pattern="[0-9]+" title="Numbers only" required>
                        @error('matrix_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               placeholder="name@varsity.edu.my" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
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
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" id="regRole">
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        </select>
                    </div>
                    <div class="mb-3" id="companyField" style="{{ old('role') == 'supervisor' ? 'display: none;' : '' }}">
                        <label class="form-label">Company / Internship Place <span class="text-danger">*</span></label>
                        <input type="text" name="company" class="form-control @error('company') is-invalid @enderror" 
                               placeholder="ABC Tech Solutions" value="{{ old('company') }}" {{ old('role') != 'supervisor' ? 'required' : '' }}>
                        @error('company')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3" id="facultyField" style="{{ old('role') == 'supervisor' ? 'display: none;' : '' }}">
                        <label class="form-label">Faculty <span class="text-danger">*</span></label>
                        <select class="form-select @error('reg_faculty') is-invalid @enderror" name="reg_faculty" id="regFaculty" {{ old('role') != 'supervisor' ? 'required' : '' }}>
                            <option value="">-- Select Faculty --</option>
                        </select>
                        @error('reg_faculty')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3" id="classField" style="{{ old('role') == 'supervisor' ? 'display: none;' : '' }}">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-select @error('reg_class') is-invalid @enderror" name="reg_class" id="regClass" {{ old('role') != 'supervisor' ? 'required' : '' }} disabled>
                            <option value="">-- Select Class --</option>
                        </select>
                        @error('reg_class')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3" id="programmeField" style="{{ old('role') == 'supervisor' ? 'display: none;' : '' }}">
                        <label class="form-label">Programme Code <span class="text-danger">*</span></label>
                        <select class="form-select @error('reg_programme_code') is-invalid @enderror" name="reg_programme_code" id="regProgramme" {{ old('role') != 'supervisor' ? 'required' : '' }} disabled>
                            <option value="">-- Select Programme Code --</option>
                        </select>
                        @error('reg_programme_code')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3" id="svMatchResult" style="display: none;">
                        <div class="alert alert-info py-2 mb-0 d-flex align-items-center gap-2" id="svMatchAlert">
                            <i class="fas fa-user-tie"></i>
                            <span id="svMatchText">Supervisor will be auto-assigned</span>
                        </div>
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
// Supervisor criteria data from backend
var svCriteria = @json($supervisorCriteria ?? []);

// ======== Role Toggle ========
document.getElementById('regRole').addEventListener('change', function() {
    var isStudent = this.value === 'student';
    var studentFields = ['companyField', 'facultyField', 'classField', 'programmeField'];
    
    studentFields.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = isStudent ? 'block' : 'none';
    });

    // Toggle required
    var companyInput = document.querySelector('input[name="company"]');
    var facultySelect = document.getElementById('regFaculty');
    var classSelect = document.getElementById('regClass');
    var programmeSelect = document.getElementById('regProgramme');

    if (companyInput) companyInput.required = isStudent;
    if (facultySelect) facultySelect.required = isStudent;
    if (classSelect) classSelect.required = isStudent;
    if (programmeSelect) programmeSelect.required = isStudent;

    // Reset when switching to supervisor
    if (!isStudent) {
        if (companyInput) companyInput.value = '';
        if (facultySelect) { facultySelect.value = ''; facultySelect.disabled = false; }
        if (classSelect) { classSelect.value = ''; classSelect.disabled = true; }
        if (programmeSelect) { programmeSelect.value = ''; programmeSelect.disabled = true; }
        document.getElementById('svMatchResult').style.display = 'none';
    }
});

// ======== Cascading Dropdowns ========
(function() {
    var facultySelect = document.getElementById('regFaculty');
    var classSelect = document.getElementById('regClass');
    var programmeSelect = document.getElementById('regProgramme');
    if (!facultySelect || !classSelect || !programmeSelect) return;

    // Populate faculties
    var faculties = [];
    svCriteria.forEach(function(sv) {
        if (sv.faculty && faculties.indexOf(sv.faculty) === -1) {
            faculties.push(sv.faculty);
        }
    });
    faculties.sort();
    faculties.forEach(function(f) {
        var opt = document.createElement('option');
        opt.value = f;
        opt.textContent = f;
        if ('{{ old("reg_faculty") }}' === f) opt.selected = true;
        facultySelect.appendChild(opt);
    });

    // Faculty change → filter classes
    facultySelect.addEventListener('change', function() {
        var selectedFaculty = this.value;
        classSelect.innerHTML = '<option value="">-- Select Class --</option>';
        programmeSelect.innerHTML = '<option value="">-- Select Programme Code --</option>';
        programmeSelect.disabled = true;
        document.getElementById('svMatchResult').style.display = 'none';

        if (!selectedFaculty) {
            classSelect.disabled = true;
            return;
        }

        var classes = [];
        svCriteria.forEach(function(sv) {
            if (sv.faculty === selectedFaculty && sv.classes) {
                sv.classes.forEach(function(c) {
                    if (classes.indexOf(c) === -1) {
                        classes.push(c);
                    }
                });
            }
        });
        classes.sort();
        classes.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            if ('{{ old("reg_class") }}' === c) opt.selected = true;
            classSelect.appendChild(opt);
        });
        classSelect.disabled = false;
    });

    // Class change → filter programme codes
    classSelect.addEventListener('change', function() {
        var selectedFaculty = facultySelect.value;
        var selectedClass = this.value;
        programmeSelect.innerHTML = '<option value="">-- Select Programme Code --</option>';
        document.getElementById('svMatchResult').style.display = 'none';

        if (!selectedClass) {
            programmeSelect.disabled = true;
            return;
        }

        var codes = [];
        svCriteria.forEach(function(sv) {
            if (sv.faculty === selectedFaculty && sv.classes && sv.classes.indexOf(selectedClass) !== -1 && sv.programme_codes) {
                sv.programme_codes.forEach(function(code) {
                    if (codes.indexOf(code) === -1) {
                        codes.push(code);
                    }
                });
            }
        });
        codes.sort();
        codes.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            if ('{{ old("reg_programme_code") }}' === c) opt.selected = true;
            programmeSelect.appendChild(opt);
        });
        programmeSelect.disabled = false;
    });

    // Programme Code change → find matching SV
    programmeSelect.addEventListener('change', function() {
        var selectedFaculty = facultySelect.value;
        var selectedClass = classSelect.value;
        var selectedCode = this.value;
        var matchResult = document.getElementById('svMatchResult');
        var matchAlert = document.getElementById('svMatchAlert');
        var matchText = document.getElementById('svMatchText');

        if (!selectedCode) {
            matchResult.style.display = 'none';
            return;
        }

        // Find matching supervisor
        var matched = null;
        for (var i = 0; i < svCriteria.length; i++) {
            var sv = svCriteria[i];
            if (sv.faculty === selectedFaculty && sv.classes && sv.classes.indexOf(selectedClass) !== -1 && sv.programme_codes) {
                for (var j = 0; j < sv.programme_codes.length; j++) {
                    if (sv.programme_codes[j].toUpperCase() === selectedCode.toUpperCase()) {
                        matched = sv;
                        break;
                    }
                }
            }
            if (matched) break;
        }

        if (matched) {
            matchResult.style.display = 'block';
            matchAlert.className = 'alert alert-success py-2 mb-0 d-flex align-items-center gap-2';
            matchText.innerHTML = '<strong>Supervisor Assigned:</strong> ' + matched.name;
        } else {
            matchResult.style.display = 'block';
            matchAlert.className = 'alert alert-danger py-2 mb-0 d-flex align-items-center gap-2';
            matchText.innerHTML = 'No supervisor found for this combination.';
        }
    });

    // Trigger cascading on page load if old values exist
    if ('{{ old("reg_faculty") }}') {
        facultySelect.dispatchEvent(new Event('change'));
        setTimeout(function() {
            if ('{{ old("reg_class") }}') {
                classSelect.value = '{{ old("reg_class") }}';
                classSelect.dispatchEvent(new Event('change'));
                setTimeout(function() {
                    if ('{{ old("reg_programme_code") }}') {
                        programmeSelect.value = '{{ old("reg_programme_code") }}';
                        programmeSelect.dispatchEvent(new Event('change'));
                    }
                }, 50);
            }
        }, 50);
    }
})();

function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon = btn.querySelector('i');
    
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
