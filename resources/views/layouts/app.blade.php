@extends('layouts.master')

@section('content')
<!-- Sidebar -->
<div class="offcanvas-lg offcanvas-start sidebar" id="sidebarMenu">
    <div class="sidebar-logo">
        <h4><i class="fas fa-book-reader me-2"></i>LIMS</h4>
    </div>
    <div class="d-flex flex-column p-3">
        <div class="d-flex align-items-center mb-4 p-2 bg-light rounded">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=random" class="rounded-circle me-2" width="40" alt="Avatar">
            <div>
                <h6 class="mb-0 fw-bold">{{ Auth::user()->name ?? 'Guest' }}</h6>
                <small class="text-muted text-capitalize">{{ Auth::user()->role ?? 'User' }}</small>
            </div>
        </div>

        <nav class="nav flex-column gap-2">
            @yield('sidebar-menu')
            <a class="nav-link text-danger mt-4" href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Mobile Header -->
    <div class="d-flex justify-content-between align-items-center d-lg-none mb-4">
        <h4 class="fw-bold text-primary-custom">@yield('page-title')</h4>
        <button class="btn btn-primary-custom" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Desktop Header -->
    <div class="d-none d-lg-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">@yield('page-title')</h2>
            <p class="text-muted">@yield('page-subtitle')</p>
        </div>
        @yield('header-right')
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('main-content')
</div>
@endsection
