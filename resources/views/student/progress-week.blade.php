@extends('layouts.app')

@section('title', 'Weekly Logs - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-progress.css') }}">
<style>
    .page-title-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 1px solid #E5E7EB;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .btn-close-custom {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6B7280;
        border: 1px solid #E5E7EB;
        border-radius: 50%;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .btn-close-custom:hover {
        background-color: #F3F4F6;
        color: #1F2937;
    }
</style>
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link active" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.notifications') }}"><i class="fas fa-bell"></i> Notifications</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Weekly Details')

@section('main-content')
<div class="card card-custom p-4 shadow-sm position-relative" style="border-radius: 1.5rem; border: 1px solid #E5E7EB;">
    <div class="page-title-header pe-2">
        <div>
            <h5 class="fw-bold text-lims-navy m-0">Daily Logs for Week {{ $week }}</h5>
            <p class="text-muted small mt-1 mb-0">Review your daily entries and their approval statuses</p>
        </div>
        <a href="{{ route('student.progress') }}" class="btn-close-custom shadow-sm" title="Close Details">
            <i class="fas fa-times"></i>
        </a>
    </div>
    
    <div>
        @if($weekLogs->count() > 0)
            <div class="timeline mt-2">
                @foreach($weekLogs as $log)
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div style="flex: 1; min-width: 250px;">
                                <div class="d-flex align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 text-dark">{{ $log->entry_date->format('j M Y, l') }}</h6>
                                    
                                    @if($log->status === 'approved')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2 py-1 ms-3" style="font-size: 0.75rem;"><i class="fas fa-check-circle me-1"></i> Approved</span>
                                    @elseif($log->status === 'rejected')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2 py-1 ms-3" style="font-size: 0.75rem;"><i class="fas fa-times-circle me-1"></i> Rejected</span>
                                    @elseif($log->status === 'pending')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-2 py-1 ms-3" style="font-size: 0.75rem;"><i class="fas fa-clock me-1"></i> Pending</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-2 py-1 ms-3" style="font-size: 0.75rem;"><i class="fas fa-file-alt me-1"></i> Draft</span>
                                    @endif
                                </div>
                                <p class="text-muted mb-2 small lh-base">{{ Str::limit($log->task_description, 120) }}</p>
                            </div>
                            <a href="{{ route('student.log-entries.show', $log->id) }}" class="btn btn-sm btn-light border text-primary rounded-circle shadow-sm hover-primary-button" title="View Detail Page" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                <i class="fas fa-eye fs-6"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                    <i class="fas fa-calendar-times fs-2 text-secondary opacity-75"></i>
                </div>
                <h6 class="fw-bold text-dark">No Logs Found</h6>
                <p class="small">You haven't submitted any logs for this week yet.</p>
                <a href="{{ route('student.log-entries') }}" class="btn btn-sm btn-primary px-3 rounded-pill mt-2">Log Activity Now</a>
            </div>
        @endif
    </div>
</div>
@endsection
