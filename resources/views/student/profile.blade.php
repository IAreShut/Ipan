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
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/student-profile.css') }}">
@endpush

<div class="row">
    @if(session('success'))
    <div class="col-12 mb-3">
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data" class="col-12 w-100 d-flex flex-wrap" id="profile-form">
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
                        <label class="form-label-custom">Student ID</label>
                        <div class="position-relative">
                            <input type="text" class="form-control-custom" value="{{ $user->matrix_id ?? 'Not specified' }}" readonly disabled>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Info -->
            <div class="profile-card p-4">
                <div class="profile-card-header mb-4">
                    <h5 class="profile-card-title">Student info</h5>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-building info-icon"></i>
                        <div class="info-content w-100">
                            <div class="label">Faculty</div>
                            <input type="text" name="faculty" class="form-control-custom py-1 px-2 mt-1" value="{{ $user->faculty }}" placeholder="e.g. Faculty of Technology">
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-users info-icon"></i>
                        <div class="info-content w-100">
                            <div class="label">Class</div>
                            <input type="text" name="class" class="form-control-custom py-1 px-2 mt-1" value="{{ $user->class }}" placeholder="e.g. CS240">
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-id-badge info-icon"></i>
                        <div class="info-content w-100">
                            <div class="label">Programme Code</div>
                            <input type="text" name="programme_code" class="form-control-custom py-1 px-2 mt-1" value="{{ $user->programme_code }}" placeholder="e.g. BEng SE">
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-user-tie info-icon"></i>
                        <div class="info-content w-100" style="padding-top: 5px;">
                            <div class="label">Supervisor Name</div>
                            <div class="value">{{ $user->supervisor->name ?? 'Not assigned' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Internship Info -->
            <div class="profile-card p-4 mb-lg-0">
                <div class="profile-card-header mb-4">
                    <h5 class="profile-card-title">Internship info</h5>
                </div>
                
                <div class="mb-4">
                    <label class="form-label-custom">Company / Internship Place</label>
                    <input type="text" class="form-control-custom" value="{{ $user->company ?? 'Not specified' }}" readonly disabled>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label-custom">Internship Start Date</label>
                        @if($internship && $internship->start_date)
                            {{-- Start date already set — lock it to prevent progress calculation issues --}}
                            <input type="hidden" name="start_date" value="{{ $internship->start_date->format('Y-m-d') }}">
                            <input type="date" class="form-control-custom" value="{{ $internship->start_date->format('Y-m-d') }}" disabled style="opacity: 0.7; cursor: not-allowed;">
                            <small class="text-muted mt-1 d-block"><i class="fas fa-lock me-1"></i>Start date is locked after being set.</small>
                        @else
                            <input type="date" name="start_date" class="form-control-custom @error('start_date') is-invalid @enderror" 
                                   value="{{ old('start_date', '') }}">
                            @error('start_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label-custom">Internship End Date</label>
                        <input type="date" name="end_date" class="form-control-custom @error('end_date') is-invalid @enderror" 
                               value="{{ old('end_date', $internship ? $internship->end_date->format('Y-m-d') : '') }}">
                        @error('end_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
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
                    @if($user->company)
                    <span class="tag-badge position-relative">
                        Intern
                        <!-- small purple dot indicator -->
                        <span class="position-absolute" style="width: 14px; height: 14px; background: #6366f1; color: white; border-radius: 50%; font-size: 0.5rem; display: flex; align-items: center; justify-content: center; top: -5px; right: -5px; border: 2px solid white;">&times;</span>
                    </span>
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
                <input type="hidden" name="location" id="location_value" value="{{ old('location', $user->location) ?? $internship->company_address ?? '' }}">
                
                <div class="position-relative" id="location_wrapper">
                    <input type="text" id="location_search" class="form-control-custom border" autocomplete="off"
                           value="{{ old('location', $user->location) ?? $internship->company_address ?? '' }}"
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
                
                <textarea name="about" id="about_text" class="form-control-custom border" rows="5" style="resize: none;" oninput="updateWordCount(this)" placeholder="A dedicated student looking forward to gaining practical experience.">{{ old('about', $user->about) }}</textarea>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary-custom px-4" style="border-radius: 1rem; padding: 0.8rem; font-weight: 500;">Save Profile Changes</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/student/profile.js') }}"></script>
@endpush

