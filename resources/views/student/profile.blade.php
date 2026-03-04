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
    <!-- Left Column: Information Sections -->
    <div class="col-lg-8">
        <!-- Personal Info -->
        <div class="profile-card p-4">
            <div class="profile-card-header">
                <div>
                    <h5 class="profile-card-title">Personal info</h5>
                    <p class="profile-card-subtitle">Upload your personal information here</p>
                </div>
                <a href="#" class="edit-btn"><i class="fas fa-ellipsis-h"></i></a>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label-custom">Full name</label>
                    <input type="text" class="form-control-custom" value="{{ $user->name }}" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label-custom">Email</label>
                    <input type="email" class="form-control-custom" value="{{ $user->email }}" readonly>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label-custom">Phone Number</label>
                    <div class="phone-input-group">
                        <div class="phone-prefix">+60</div>
                        <input type="text" class="form-control-custom" value="{{ str_replace('+60', '', $user->phone ?? '') }}" readonly>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label-custom">Student ID</label>
                    <div class="position-relative">
                        <input type="text" class="form-control-custom" value="{{ $user->matrix_id ?? 'Not specified' }}" readonly>
                        <a href="#" class="edit-btn position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px;"><i class="fas fa-edit" style="font-size: 0.7rem;"></i></a>
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
                    <div class="info-content">
                        <div class="label">Faculty</div>
                        <div class="value">{{ $user->faculty ?? 'Not specified' }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-users info-icon"></i>
                    <div class="info-content">
                        <div class="label">Class</div>
                        <div class="value">{{ $user->class ?? 'Not specified' }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-id-badge info-icon"></i>
                    <div class="info-content">
                        <div class="label">Programme Code</div>
                        <div class="value">{{ $user->programme_code ?? 'Not specified' }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-user-tie info-icon"></i>
                    <div class="info-content">
                        <div class="label">Supervisor Name</div>
                        <div class="value">{{ $user->supervisor->name ?? 'Not assigned' }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Internship Info -->
        <div class="profile-card p-4">
            <div class="profile-card-header mb-4">
                <h5 class="profile-card-title">Internship info</h5>
                <a href="#" class="action-btn"><i class="fas fa-external-link-alt" style="font-size: 0.8rem;"></i></a>
            </div>
            
            <div class="mb-4">
                <label class="form-label-custom">Company / Internship Place</label>
                <input type="text" class="form-control-custom" value="{{ $user->company ?? 'Not specified' }}" readonly>
            </div>
            
            @if($internship)
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label-custom">Internship Start Date</label>
                    <div class="position-relative">
                        <select class="form-control-custom appearance-none" style="appearance: none; -webkit-appearance: none; -moz-appearance: none;" disabled>
                            <option selected>{{ $internship->start_date->format('M d, Y') }}</option>
                        </select>
                        <i class="fas fa-chevron-down position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.8rem;"></i>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label-custom">Internship End Date</label>
                    <div class="position-relative">
                        <select class="form-control-custom appearance-none" style="appearance: none; -webkit-appearance: none; -moz-appearance: none;" disabled>
                            <option selected>{{ $internship->end_date->format('M d, Y') }}</option>
                        </select>
                        <i class="fas fa-chevron-down position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.8rem;"></i>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info py-2" style="border-radius: 0.75rem; font-size: 0.9rem;">
                Internship duration details not available.
            </div>
            @endif
        </div>
    </div>
    
    <!-- Right Column: Profile Card -->
    <div class="col-lg-4">
        <!-- Main Profile Avatar Card -->
        <div class="profile-card p-4 text-center pb-5" style="border-radius: 2rem;">
            <div class="top-action-btns">
                <div class="left-btns">
                    <a href="#" class="edit-btn"><i class="fas fa-edit" style="font-size: 0.8rem;"></i></a>
                    <a href="#" class="edit-btn"><i class="fas fa-external-link-alt" style="font-size: 0.8rem;"></i></a>
                </div>
                <a href="#" class="edit-btn"><i class="fas fa-cog" style="font-size: 0.8rem;"></i></a>
            </div>
            
            <div class="mt-4 mb-3">
                <div class="avatar-wrapper">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=E0E7FF&color=4F46E5&size=150" alt="Profile avatar">
                    <div class="avatar-upload-btn">
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
        <div class="profile-card p-4" style="border-radius: 1.5rem;">
            <div class="profile-card-header mb-3">
                <h5 class="profile-card-title">Location</h5>
                <a href="#" class="action-btn" style="width: 28px; height: 28px;"><i class="fas fa-plus" style="font-size: 0.7rem;"></i></a>
            </div>
            
            <div class="position-relative">
                <input type="text" class="form-control-custom" value="{{ $internship->company_address ?? ($user->location ?? 'Not specified') }}" readonly>
                <i class="fas fa-chevron-down position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.8rem;"></i>
            </div>
        </div>
        
        <!-- About You Card -->
        <div class="profile-card p-4" style="border-radius: 1.5rem;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="profile-card-title">About You</h5>
                <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">{{ str_word_count($user->about ?? 'A dedicated student looking forward to gaining practical experience.') }}/120 words</span>
            </div>
            
            <textarea class="form-control-custom" rows="4" style="resize: none;" readonly>{{ $user->about ?? 'A dedicated student looking forward to gaining practical experience.' }}</textarea>
        </div>
    </div>
</div>
@endsection

