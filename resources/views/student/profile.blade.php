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
        
        <!-- Left Column: Information Sections -->
        <div class="col-12 col-lg-8 pe-lg-4">
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
                            <input type="text" name="phone" id="phone_input" class="form-control-custom" value="{{ str_replace('+60', '', $user->phone ?? '') }}" placeholder="123456789">
                        </div>
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
                
                @if($internship)
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label-custom">Internship Start Date</label>
                        <div class="position-relative">
                            <select class="form-control-custom appearance-none" style="appearance: none; -webkit-appearance: none; -moz-appearance: none; background-color: #f8fafc; opacity: 1;" disabled>
                                <option selected>{{ $internship->start_date->format('M d, Y') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label-custom">Internship End Date</label>
                        <div class="position-relative">
                            <select class="form-control-custom appearance-none" style="appearance: none; -webkit-appearance: none; -moz-appearance: none; background-color: #f8fafc; opacity: 1;" disabled>
                                <option selected>{{ $internship->end_date->format('M d, Y') }}</option>
                            </select>
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
        <div class="col-12 col-lg-4">
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
            <div class="profile-card p-4" style="border-radius: 1.5rem;">
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
                <button type="submit" class="btn btn-primary" style="border-radius: 1rem; padding: 0.8rem; font-weight: 500; background: #0f172a; border: none;">Save Profile Changes</button>
            </div>
        </div>
    </form>
</div>

<script>
    function previewAvatar(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('avatar_preview');
            output.src = reader.result;
        };
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    function updateWordCount(textarea) {
        var text = textarea.value.trim();
        var words = text === '' ? 0 : text.split(/\s+/).length;
        var display = document.getElementById('word_count');
        
        display.innerText = words + '/120 words';
        
        if (words > 120) {
            display.style.color = 'red';
        } else {
            display.style.color = '#64748b';
        }
    }

    // ======== Location Search (Nominatim / OpenStreetMap) ========
    let searchTimeout = null;

    function searchLocation(query) {
        clearTimeout(searchTimeout);
        var dropdown = document.getElementById('location_dropdown');
        var emptyMsg = document.getElementById('location_empty');

        if (query.length < 3) {
            dropdown.style.display = 'block';
            dropdown.innerHTML = '<div class="location-dropdown-empty">Type at least 3 characters...</div>';
            return;
        }

        dropdown.style.display = 'block';
        dropdown.innerHTML = '<div class="location-dropdown-empty"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</div>';

        searchTimeout = setTimeout(function() {
            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query) + '&limit=6&addressdetails=1', {
                headers: { 'Accept-Language': 'en' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                renderLocationResults(data);
            })
            .catch(function() {
                dropdown.innerHTML = '<div class="location-dropdown-empty">Failed to fetch results. Try again.</div>';
            });
        }, 400);
    }

    function renderLocationResults(results) {
        var dropdown = document.getElementById('location_dropdown');
        if (!results || results.length === 0) {
            dropdown.innerHTML = '<div class="location-dropdown-empty">No locations found.</div>';
            return;
        }
        var html = '';
        results.forEach(function(place) {
            var name = place.display_name;
            var icon = getPlaceIcon(place.type);
            html += '<div class="location-dropdown-item" onclick="selectLocation(\'' + name.replace(/'/g, "\\'") + '\')">'
                  + '<i class="fas ' + icon + ' me-2" style="color: #6366f1;"></i>'
                  + '<span>' + name + '</span>'
                  + '</div>';
        });
        dropdown.innerHTML = html;
    }

    function getPlaceIcon(type) {
        var map = {
            'city': 'fa-city', 'town': 'fa-city', 'village': 'fa-home',
            'suburb': 'fa-map-marker-alt', 'state': 'fa-flag',
            'country': 'fa-globe-asia', 'residential': 'fa-home',
            'administrative': 'fa-landmark'
        };
        return map[type] || 'fa-map-pin';
    }

    function selectLocation(name) {
        document.getElementById('location_search').value = name;
        document.getElementById('location_value').value = name;
        document.getElementById('location_dropdown').style.display = 'none';
    }

    function showDropdown() {
        var dropdown = document.getElementById('location_dropdown');
        dropdown.style.display = 'block';
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        var wrapper = document.getElementById('location_wrapper');
        if (wrapper && !wrapper.contains(e.target) && e.target.id !== 'geolocate_btn') {
            document.getElementById('location_dropdown').style.display = 'none';
        }
    });

    // ======== Browser Geolocation API ========
    function detectMyLocation() {
        var icon = document.getElementById('geo_icon');
        icon.className = 'fas fa-spinner fa-spin';

        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            icon.className = 'fas fa-crosshairs';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;

                // Reverse geocode with Nominatim
                fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lon + '&addressdetails=1', {
                    headers: { 'Accept-Language': 'en' }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    var address = data.display_name || (lat + ', ' + lon);
                    selectLocation(address);
                    icon.className = 'fas fa-crosshairs';
                })
                .catch(function() {
                    selectLocation(lat + ', ' + lon);
                    icon.className = 'fas fa-crosshairs';
                });
            },
            function(err) {
                alert('Unable to retrieve your location. Please allow location access.');
                icon.className = 'fas fa-crosshairs';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    // Initialize word count on load
    document.addEventListener('DOMContentLoaded', function() {
        var textArea = document.getElementById('about_text');
        if(textArea) {
            updateWordCount(textArea);
        }
    });
</script>
@endsection

