@extends('layouts.master')

@section('content')
<!-- Sidebar -->
@php
    $dashboardRoute = route('home');
    $profileRoute = '#';
    if (Auth::check()) {
        $dashboardRoute = Auth::user()->role === 'supervisor' ? route('supervisor.dashboard') : route('student.dashboard');
        $profileRoute = Auth::user()->role === 'supervisor' ? route('supervisor.profile') : route('student.profile');
    }
@endphp

<div class="offcanvas-lg offcanvas-start sidebar" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header sidebar-logo d-flex justify-content-between align-items-center">
        <a href="{{ $dashboardRoute }}" class="text-decoration-none text-dark">
            <h4 class="mb-0" id="sidebarMenuLabel"><i class="fas fa-book-reader me-2"></i>LIMS</h4>
        </a>
        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        <a href="{{ $profileRoute }}" class="text-decoration-none text-dark">
            <div class="d-flex align-items-center mb-4 p-2 bg-light rounded" style="transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                @php $authUser = Auth::user(); @endphp
                <img src="{{ $authUser->avatar ? (str_starts_with($authUser->avatar, 'http') ? $authUser->avatar : asset('storage/' . $authUser->avatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($authUser->name ?? 'User') . '&background=E0E7FF&color=4F46E5' }}" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;" alt="Avatar">
                <div>
                    <h6 class="mb-0 fw-bold">{{ Auth::user()->name ?? 'Guest' }}</h6>
                    <small class="text-muted text-capitalize">{{ Auth::user()->role ?? 'User' }}</small>
                </div>
            </div>
        </a>

        <nav class="nav flex-column gap-2">
            @yield('sidebar-menu')
            <a class="nav-link text-danger mt-4" href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none no-spinner">
                @csrf
            </form>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Mobile Header -->
    <div class="d-flex justify-content-between align-items-center d-lg-none mb-2">
        <h4 class="fw-bold text-primary-custom">@yield('page-title')</h4>
        <button class="btn btn-primary-custom" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Desktop Header -->
    <div class="d-none d-lg-flex justify-content-between align-items-center mb-2">
        <div>
            <h2 class="fw-bold text-dark">@yield('page-title')</h2>
            <p class="text-muted">@yield('page-subtitle')</p>
        </div>
        @yield('header-right')
    </div>

    @yield('main-content')
</div>
@endsection
