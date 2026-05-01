@extends('layouts.app')

@section('title', 'Profile - LIMS')

@section('sidebar-menu')
<a class="nav-link" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.milestones') }}"><i class="fas fa-flag-checkered"></i> Milestones</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
<a class="nav-link active" href="{{ route('supervisor.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'My Profile')

@section('main-content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/supervisor-profile.css') }}">
@endpush

<div class="row">
    @if(session('success'))
    <div class="col-12 mb-3">
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('supervisor.profile.update') }}" enctype="multipart/form-data" class="col-12 w-100 d-flex flex-wrap" id="profile-form">
        @csrf
        
        <!-- Right Column: Information Sections (Swapped) -->
        <div class="col-12 col-lg-8 ps-lg-4 order-2 order-lg-2">
            <!-- Personal Info -->
            <div class="profile-card p-4">
                <div class="profile-card-header">
                    <div>
                        <h5 class="profile-card-title">Personal info</h5>
                        <p class="profile-card-subtitle">Upload your personal information here</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label-custom">Full name</label>
                        <input type="text" class="form-control-custom" value="{{ $user->name }}" readonly disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label-custom">Email</label>
                        <input type="email" class="form-control-custom" value="{{ $user->email }}" readonly disabled>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label-custom">Phone Number</label>
                        <div class="phone-input-group">
                            <div class="phone-prefix">+60</div>
                            <input type="text" name="phone" id="phone_input" 
                                   class="form-control-custom @error('phone') is-invalid @enderror" 
                                   value="{{ ltrim(str_replace('+60', '', $user->phone ?? ''), '0') }}" 
                                   placeholder="123456789" inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label-custom">Employee / Staff ID</label>
                        <input type="text" name="employee_id" class="form-control-custom @error('employee_id') is-invalid @enderror" 
                               value="{{ old('employee_id', $user->employee_id) }}" placeholder="e.g. EMP12345">
                        @error('employee_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Supervision Criteria -->
            <div class="profile-card p-4">
                <div class="profile-card-header mb-4">
                    <div>
                        <h5 class="profile-card-title">Supervision Criteria</h5>
                        <p class="profile-card-subtitle mb-0">Students with matching Faculty, Class, and Programme Code will be automatically assigned to you.</p>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-building info-icon"></i>
                        <div class="info-content w-100">
                            <div class="label">Faculty</div>
                            <input type="text" name="faculty" class="form-control-custom py-1 px-2 mt-1" value="{{ $user->faculty }}" placeholder="e.g. Faculty of Computing">
                        </div>
                    </div>
                    <div class="info-item full-width">
                        <i class="fas fa-id-badge info-icon"></i>
                        <div class="info-content w-100">
                            <div class="label">Programme Code(s)</div>
                            <div class="tags-input-wrapper mt-1" id="programme_tags_wrapper">
                                <div class="tags-container" id="tags_container">
                                    @foreach($user->programme_codes as $code)
                                        <span class="tag-item">
                                            {{ $code }}
                                            <input type="hidden" name="programme_code[]" value="{{ $code }}">
                                            <button type="button" class="tag-remove" onclick="removeTag(this)">&times;</button>
                                        </span>
                                    @endforeach
                                    <input type="text" class="tag-input" id="programme_tag_input" placeholder="Type code & press Enter" onkeydown="handleTagInput(event, 'programme_code')">
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Press Enter to add, click × to remove</small>
                        </div>
                    </div>
                    <div class="info-item full-width">
                        <i class="fas fa-users info-icon"></i>
                        <div class="info-content w-100">
                            <div class="label">Class(es)</div>
                            <div class="tags-input-wrapper mt-1" id="class_tags_wrapper">
                                <div class="tags-container" id="class_tags_container">
                                    @foreach($user->classes as $c)
                                        <span class="tag-item">
                                            {{ $c }}
                                            <input type="hidden" name="class[]" value="{{ $c }}">
                                            <button type="button" class="tag-remove" onclick="removeTag(this)">&times;</button>
                                        </span>
                                    @endforeach
                                    <input type="text" class="tag-input" id="class_tag_input" placeholder="Type class & press Enter" onkeydown="handleTagInput(event, 'class')">
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Press Enter to add, click × to remove</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Students -->
            <div class="profile-card p-4 mb-lg-0">
                <div class="profile-card-header mb-4">
                    <div>
                        <h5 class="profile-card-title">Assigned Students</h5>
                        <p class="profile-card-subtitle mb-0">Students currently under your supervision</p>
                    </div>
                    <span class="student-count-badge">{{ $students->count() }}</span>
                </div>
                
                @if($students->count() > 0)
                <div class="student-list">
                    @foreach($students as $student)
                    <div class="student-list-item">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $student->avatar ? (str_starts_with($student->avatar, 'http') ? $student->avatar : asset('storage/' . $student->avatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name) . '&background=E0E7FF&color=4F46E5&size=40' }}" 
                                 class="student-avatar" alt="{{ $student->name }}">
                            <div>
                                <div class="student-name">{{ $student->name }}</div>
                                <div class="student-email">{{ $student->email }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="student-company">{{ $student->company ?? 'N/A' }}</div>
                            <div class="student-code">{{ $student->programme_code ?? '—' }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-users" style="font-size: 2rem; color: #cbd5e1;"></i>
                    <p class="text-muted mt-2 mb-0">No students assigned yet.</p>
                    <small class="text-muted">Students will appear here when they register with matching criteria.</small>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Left Column: Profile Card (Swapped) -->
        <div class="col-12 col-lg-4 order-1 order-lg-1 mb-4 mb-lg-0">
            <!-- Main Profile Avatar Card -->
            <div class="profile-card p-4 text-center pb-5" style="border-radius: 2rem;">
                <!-- Hidden File Input -->
                <input type="file" name="avatar" id="avatar_input" class="d-none" accept="image/*" onchange="previewAvatar(event)">

                <div class="mt-4 mb-3">
                    <div class="avatar-wrapper">
                        @if($user->avatar)
                            <img id="avatar_preview" src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar) }}" alt="Profile avatar">
                        @else
                            <img id="avatar_preview" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=E0E7FF&color=4F46E5&size=150" alt="Profile avatar default">
                        @endif
                        <div class="avatar-upload-btn" onclick="document.getElementById('avatar_input').click();">
                            <i class="fas fa-plus" style="font-size: 0.9rem;"></i>
                        </div>
                    </div>
                </div>
                
                <h4 class="fw-bold mb-3" style="color: #0f172a;">{{ $user->name }}</h4>
                
                <div class="d-flex justify-content-center flex-wrap">
                    <span class="tag-badge text-capitalize">{{ $user->role }}</span>
                    @if($user->faculty)
                    <span class="tag-badge">{{ $user->faculty }}</span>
                    @endif
                </div>
            </div>
            
            <!-- Location Card -->
            <div class="profile-card p-4" style="border-radius: 1.5rem; z-index: 10; overflow: visible;">
                <div class="profile-card-header mb-3">
                    <h5 class="profile-card-title">Location</h5>
                    <button type="button" class="action-btn" id="geolocate_btn" title="Use my current location" onclick="detectMyLocation()" style="width: 28px; height: 28px;">
                        <i class="fas fa-crosshairs" style="font-size: 0.75rem;" id="geo_icon"></i>
                    </button>
                </div>
                
                <!-- Hidden real input that stores the selected value -->
                <input type="hidden" name="location" id="location_value" value="{{ old('location', $user->location) }}">
                
                <div class="position-relative" id="location_wrapper">
                    <input type="text" id="location_search" class="form-control-custom border" autocomplete="off"
                           value="{{ old('location', $user->location) }}"
                           placeholder="Search for a location..." oninput="searchLocation(this.value)" onfocus="showDropdown()">
                    <i class="fas fa-search position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; pointer-events: none;"></i>
                    
                    <!-- Dropdown suggestions -->
                    <div id="location_dropdown" class="location-dropdown" style="display: none;">
                        <div class="location-dropdown-empty" id="location_empty">Type to search for a location...</div>
                    </div>
                </div>
            </div>
            
            <!-- About You Card -->
            <div class="profile-card p-4" style="border-radius: 1.5rem;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="profile-card-title">About You</h5>
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;" id="word_count">0/120 words</span>
                </div>
                
                <textarea name="about" id="about_text" class="form-control-custom border" rows="5" style="resize: none;" oninput="updateWordCount(this)" placeholder="A dedicated supervisor overseeing internship activities.">{{ old('about', $user->about) }}</textarea>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary-custom px-4" style="border-radius: 1rem; padding: 0.8rem; font-weight: 500;">Save Profile Changes</button>
            </div>
        </div>
    </form>
</div>

<script src="{{ asset('js/supervisor/profile.js') }}"></script>
@endsection
