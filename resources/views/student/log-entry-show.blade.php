@extends('layouts.app')

@section('title', 'Log Entry Details - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-log-entries.css') }}">
<link rel="stylesheet" href="{{ asset('css/student-log-entry-show.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.notifications') }}"><i class="fas fa-bell"></i> Notifications</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Log Entry Details')

@section('header-right')
<a href="{{ url()->previous() }}" class="btn btn-primary-custom px-4">
    <i class="fas fa-arrow-left me-2"></i> Back
</a>
@endsection

@section('main-content')
{{-- Header Card --}}
<div class="detail-card bg-light-gradient">
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <h4 class="fw-bold mb-0 text-dark">
            Week {{ $logEntry->week_number }} — {{ $logEntry->entry_date->format('d F Y') }}
        </h4>
        <div>
            @if($logEntry->status === 'approved')
                <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fs-6">
                    <i class="fas fa-check-circle me-1"></i> Approved
                </span>
            @elseif($logEntry->status === 'rejected')
                <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fs-6">
                    <i class="fas fa-times-circle me-1"></i> Rejected
                </span>
            @elseif($logEntry->status === 'pending')
                <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 fs-6">
                    <i class="fas fa-clock me-1"></i> Pending Review
                </span>
            @else
                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 fs-6">
                    <i class="fas fa-file-alt me-1"></i> Draft
                </span>
            @endif
        </div>
    </div>
    <div class="d-flex flex-wrap gap-4 mt-2">
        <div class="detail-meta-item">
            <i class="far fa-calendar-alt"></i>
            <span>{{ $logEntry->entry_date->format('l') }}</span>
        </div>
        <div class="detail-meta-item">
            <i class="far fa-clock"></i>
            <span>Submitted {{ $logEntry->created_at->diffForHumans() }}</span>
        </div>
        <div class="detail-meta-item">
            <i class="fas fa-user"></i>
            <span>{{ $logEntry->student->name ?? 'Unknown' }}</span>
        </div>
    </div>
</div>

{{-- Task Description Card --}}
<div class="detail-card">
    <div class="section-title">
        <i class="fas fa-list-ul fa-sm"></i> Task Description
    </div>
    <div class="task-content-box">{{ $logEntry->task_description }}</div>
</div>

{{-- Attachments Card --}}
<div class="detail-card">
    <div class="section-title">
        <i class="fas fa-paperclip fa-sm"></i> Attachments
        @if($logEntry->attachments->count() > 0)
            <span class="badge bg-primary rounded-circle ms-1" style="font-size: 0.7rem; padding: 4px 6px;">{{ $logEntry->attachments->count() }}</span>
        @endif
    </div>
    @if($logEntry->attachments->count() > 0)
        <div class="attachment-grid">
            @foreach($logEntry->attachments as $attachment)
                <div class="attachment-card" 
                     data-bs-toggle="modal" 
                     data-bs-target="#imageModal"
                     data-img-src="{{ str_starts_with($attachment->file_path, 'http') ? $attachment->file_path : asset('storage/' . $attachment->file_path) }}"
                     data-img-name="{{ $attachment->file_name }}">
                    <img src="{{ str_starts_with($attachment->file_path, 'http') ? $attachment->file_path : asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->file_name }}">
                    <div class="file-name">
                        <i class="fas fa-image me-2"></i>{{ $attachment->file_name }}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-muted py-3">
            <i class="fas fa-image fa-2x mb-2 d-block opacity-25"></i>
            <span class="small">No attachments uploaded for this entry.</span>
        </div>
    @endif
</div>

{{-- Supervisor Feedback Card --}}
<div class="detail-card">
    <div class="section-title">
        <i class="fas fa-comment-dots fa-sm"></i> Supervisor Feedback
    </div>
    @if($logEntry->supervisor_comment)
        <div class="feedback-box has-feedback">
            <p class="mb-0 text-dark">{{ $logEntry->supervisor_comment }}</p>
        </div>
    @else
        <div class="feedback-box">
            <div class="feedback-empty">
                <i class="fas fa-comment-slash d-block"></i>
                <span class="small">No feedback.</span>
            </div>
        </div>
    @endif
</div>

{{-- Image Lightbox Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0">
                <h6 class="modal-title text-white" id="imageModalLabel">Attachment</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="" alt="" class="img-fluid rounded-bottom" style="max-height:80vh; object-fit:contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/student/log-entry-show.js') }}"></script>
@endpush
