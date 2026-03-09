@extends('layouts.app')

@section('title', 'Weekly Logs - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-progress.css') }}">
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
<div class="mb-4">
    <a href="{{ route('student.progress') }}" class="btn-back-nav">
        <i class="fas fa-arrow-left"></i> Back to Weekly Grid
    </a>
</div>

<div class="card card-custom p-4 p-md-5 shadow-sm position-relative mb-5" style="border-radius: 1.5rem; border: 1px solid #E5E7EB;">
    <div class="border-bottom pb-4 mb-4">
        <h4 class="fw-bold text-lims-navy m-0">Daily Logs for Week {{ $week }}</h4>
        <p class="text-muted mt-1 mb-0"><i class="fas fa-info-circle me-1"></i> Review your daily entries and their approval statuses</p>
    </div>
    
    <div class="activity-stack">
        @if($weekLogs->count() > 0)
            @foreach($weekLogs as $log)
                <div class="activity-card" style="animation-delay: {{ $loop->index * 0.1 }}s">
                    <div class="row align-items-center gy-3">
                        <!-- Left Section: Date & Week -->
                        <div class="col-lg-3 col-md-4">
                            <h5 class="fw-bold text-dark mb-1">{{ $log->entry_date->format('l, j M') }}</h5>
                            <span class="badge bg-light text-secondary border px-2 py-1"><i class="fas fa-calendar-day me-1"></i> Week {{ $log->week_number }}</span>
                        </div>

                        <!-- Middle Section: Task preview & Thumbnail -->
                        <div class="col-lg-5 col-md-4">
                            <div class="d-flex align-items-center gap-3">
                                @php 
                                    $hasImage = false;
                                    $imageUrl = '';
                                    if($log->attachments && $log->attachments->count() > 0) {
                                        $firstImage = $log->attachments->firstWhere(function($a) {
                                            return str_starts_with($a->file_type, 'image/');
                                        });
                                        if($firstImage) {
                                            $hasImage = true;
                                            $imageUrl = asset('storage/' . $firstImage->file_path);
                                        }
                                    }
                                @endphp
                                
                                @if($hasImage)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $imageUrl }}" alt="Attachment" class="rounded object-fit-cover shadow-sm border border-light" style="width: 52px; height: 52px;">
                                    </div>
                                @endif
                                
                                <div class="text-muted small mb-0 lh-base line-clamp-2">
                                    {{ $log->task_description }}
                                </div>
                            </div>
                        </div>

                        <!-- Right Section: Status & Button -->
                        <div class="col-lg-4 col-md-4">
                            <div class="d-flex flex-md-column flex-row justify-content-between align-items-md-end align-items-center gap-3">
                                @if($log->status === 'approved')
                                    <span class="status-badge approved"><i class="fas fa-check-circle"></i> Approved</span>
                                @elseif($log->status === 'rejected')
                                    <span class="status-badge rejected"><i class="fas fa-times-circle"></i> Rejected</span>
                                @elseif($log->status === 'pending')
                                    <span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>
                                @else
                                    <span class="status-badge draft"><i class="fas fa-file-alt"></i> Draft</span>
                                @endif
                                
                                <a href="{{ route('student.log-entries.show', $log->id) }}" class="btn-navy-link">
                                    View Full Entry <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-5 text-muted my-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                    <i class="fas fa-calendar-times fs-2 text-secondary opacity-75"></i>
                </div>
                <h6 class="fw-bold text-dark">No Logs Found</h6>
                <p class="small">You haven't submitted any logs for this week yet.</p>
                <a href="{{ route('student.log-entries') }}" class="btn btn-sm btn-primary px-4 py-2 rounded-pill mt-3 shadow-sm">Log Activity Now</a>
            </div>
        @endif
    </div>
</div>
@endsection
