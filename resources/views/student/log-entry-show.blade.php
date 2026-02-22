@extends('layouts.app')

@section('title', 'Log Entry Details - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-log-entries.css') }}">
<style>
    .detail-header {
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .detail-meta {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .detail-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
    }
    .detail-meta-item i {
        color: #6366f1;
        width: 18px;
        text-align: center;
    }
    .detail-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .detail-section-title {
        font-weight: 700;
        font-size: 1rem;
        color: #334155;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .detail-section-title i {
        color: #6366f1;
    }
    .task-content {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1.25rem;
        line-height: 1.7;
        white-space: pre-wrap;
        color: #334155;
    }
    .attachment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
    }
    .attachment-card {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .attachment-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    }
    .attachment-card img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        display: block;
    }
    .attachment-card .file-name {
        padding: 8px 10px;
        font-size: 0.75rem;
        color: #64748b;
        background: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .feedback-box {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        padding: 1.25rem;
    }
    .feedback-box.no-feedback {
        background: #f8fafc;
        border-color: #e2e8f0;
        text-align: center;
        color: #94a3b8;
    }
</style>
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Log Entry Details')

@section('header-right')
<a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-1"></i> Back
</a>
@endsection

@section('main-content')
{{-- Header with date, week, status --}}
<div class="detail-header">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-2">
                Week {{ $logEntry->week_number }} — {{ $logEntry->entry_date->format('d F Y') }}
            </h4>
            <div class="detail-meta">
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
        <div>
            @if($logEntry->status === 'approved')
                <span class="badge badge-status-approved rounded-pill px-4 py-2 fs-6">
                    <i class="fas fa-check-circle me-1"></i> Approved
                </span>
            @elseif($logEntry->status === 'rejected')
                <span class="badge badge-status-rejected rounded-pill px-4 py-2 fs-6">
                    <i class="fas fa-times-circle me-1"></i> Rejected
                </span>
            @elseif($logEntry->status === 'pending')
                <span class="badge badge-status-pending rounded-pill px-4 py-2 fs-6">
                    <i class="fas fa-clock me-1"></i> Pending Review
                </span>
            @else
                <span class="badge bg-secondary rounded-pill px-4 py-2 fs-6">
                    <i class="fas fa-file-alt me-1"></i> Draft
                </span>
            @endif
        </div>
    </div>
</div>

{{-- Task Description --}}
<div class="detail-section">
    <div class="detail-section-title">
        <i class="fas fa-tasks"></i> Task Description
    </div>
    <div class="task-content">{{ $logEntry->task_description }}</div>
</div>

{{-- Attachments --}}
<div class="detail-section">
    <div class="detail-section-title">
        <i class="fas fa-paperclip"></i> Attachments
        @if($logEntry->attachments->count() > 0)
            <span class="badge bg-primary rounded-pill ms-1">{{ $logEntry->attachments->count() }}</span>
        @endif
    </div>
    @if($logEntry->attachments->count() > 0)
        <div class="attachment-grid">
            @foreach($logEntry->attachments as $attachment)
                <div class="attachment-card" 
                     data-bs-toggle="modal" 
                     data-bs-target="#imageModal"
                     data-img-src="{{ asset('storage/' . $attachment->file_path) }}"
                     data-img-name="{{ $attachment->file_name }}">
                    <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->file_name }}">
                    <div class="file-name">
                        <i class="fas fa-image me-1 text-primary"></i>{{ $attachment->file_name }}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-muted py-4">
            <i class="fas fa-image fa-2x mb-2 d-block opacity-50"></i>
            <span>No attachments uploaded for this entry.</span>
        </div>
    @endif
</div>

{{-- Supervisor Feedback --}}
<div class="detail-section">
    <div class="detail-section-title">
        <i class="fas fa-comment-dots"></i> Supervisor Feedback
    </div>
    @if($logEntry->supervisor_comment)
        <div class="feedback-box">
            <p class="mb-0">{{ $logEntry->supervisor_comment }}</p>
        </div>
    @else
        <div class="feedback-box no-feedback">
            <i class="fas fa-comment-slash fa-lg mb-2 d-block"></i>
            <span>No feedback yet.</span>
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
<script>
    document.getElementById('imageModal')?.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        document.getElementById('modalImage').src = trigger.dataset.imgSrc;
        document.getElementById('imageModalLabel').textContent = trigger.dataset.imgName;
    });
</script>
@endpush
