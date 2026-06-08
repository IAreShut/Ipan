@extends('layouts.app')

@section('title', 'Review Logbook - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/supervisor-review-logbook.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link active" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.tasks')}}"><i class="fas fa-flag-checkered"></i> Tasks</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
<a class="nav-link" href="{{ route('supervisor.assigned-students') }}"><i class="fas fa-user-plus"></i> Assign Student</a>
<a class="nav-link" href="{{ route('supervisor.profile') }}"><i class="fa-solid fa-circle-user"></i> Profile</a>
@endsection

@section('page-title', 'Review Logbook')
@section('page-subtitle', 'Review and approve student log entries.')

@section('main-content')
@if($logs->count() > 0)
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
    @foreach($logs as $log)
    <div class="col">
        <div class="card review-card h-100 shadow-sm border" >
            <div class="card-body p-4 d-flex flex-column">
                <!-- Profile & Date -->
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
                    <div class="d-flex align-items-center">
                        <img src="{{ $log->student->avatar ? (str_starts_with($log->student->avatar, 'http') ? $log->student->avatar : asset('storage/' . $log->student->avatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($log->student->name ?? 'User') . '&background=E0E7FF&color=4F46E5' }}" class="rounded-circle shadow-sm me-2" width="28" height="28" style="object-fit: cover;">
                        <span class="fw-bold text-dark fs-6">{{ $log->student->name ?? 'Unknown Student' }}</span>
                    </div>
                    <div class="text-muted small fw-medium">
                        {{ $log->entry_date->format('d/m/Y') }}
                    </div>
                </div>

                <!-- Title & Attachment -->
                <div class="d-flex justify-content-between align-items-start flex-grow-1">
                    <div class="me-3">
                        <h6 class="mb-1 fw-bold text-dark">Week {{ $log->week_number }} Log Entry</h6>
                        <small class="text-muted d-block">{{ Str::limit($log->task_description, 100, '...') }}</small>
                    </div>
                    @if($log->attachments && $log->attachments->count() > 0)
                        <button type="button" class="btn btn-sm rounded-pill fw-bold border-0 px-3 text-nowrap" 
                                style="background-color: #E0F2FE; color: #0284C7; font-size: 0.75rem;"
                                data-bs-toggle="modal" data-bs-target="#viewLogModal{{ $log->id }}">
                            <i class="fas fa-paperclip me-1"></i> +{{ $log->attachments->count() }}
                        </button>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-auto">
                    <form action="{{ route('supervisor.approve', $log->id) }}" method="POST" class="flex-grow-1 m-0">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm w-100 rounded-3 fw-bold py-2" style="background-color: #10B981; border-color: #10B981;">
                            Approve
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-danger btn-sm flex-grow-1 rounded-3 fw-bold py-2" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $log->id }}">
                        Reject
                    </button>
                    <button type="button" class="btn btn-light btn-sm border rounded-3 d-flex align-items-center px-3 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#viewLogModal{{ $log->id }}">
                        View
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="mb-4">
    <div class="text-center py-5 bg-white rounded-4 border shadow-sm">
        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
        <h5>All Caught Up!</h5>
        <p class="text-muted">No pending log entries to review.</p>
    </div>
</div>
@endif

<div class="d-flex justify-content-center mt-4">
    {{ $logs->links() }}
</div>

{{-- Reject Modals - placed OUTSIDE the card to prevent positioning/flickering bugs --}}
@foreach($logs as $log)
<div class="modal fade" id="rejectModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('supervisor.reject', $log->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Log Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Reason for rejection (required)</label>
                    <textarea name="comment" class="form-control" rows="3" required placeholder="Please provide feedback for the student..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary-custom" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- View Log Modals -->
@foreach($logs as $log)
<div class="modal fade" id="viewLogModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-dark">Log Entry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <img src="{{ $log->student->avatar ? (str_starts_with($log->student->avatar, 'http') ? $log->student->avatar : asset('storage/' . $log->student->avatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($log->student->name ?? 'User') . '&background=E0E7FF&color=4F46E5' }}" class="rounded-circle shadow-sm me-3" width="48" height="48" style="object-fit: cover;">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">{{ $log->student->name ?? 'Unknown Student' }}</h6>
                        <small class="text-muted">{{ $log->student->email ?? 'student@email.com' }}</small>
                    </div>
                    <div class="ms-auto text-end">
                        <small class="text-muted d-block">Submitted:</small>
                        <small class="text-dark fw-medium">{{ $log->entry_date->format('l, d/m/Y') }}</small>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 mb-4 border">
                    <h6 class="fw-bold mb-2 text-dark">Task Description (Week {{ $log->week_number }})</h6>
                    <p class="mb-0 text-secondary" style="white-space: pre-wrap; line-height: 1.6;">{{ $log->task_description }}</p>
                </div>

                @if($log->attachments && $log->attachments->count() > 0)
                <h6 class="fw-bold mb-3 text-dark">Attachments ({{ $log->attachments->count() }})</h6>
                <div class="row g-3">
                    @foreach($log->attachments as $attachment)
                        @if(str_starts_with($attachment->file_type, 'image/'))
                        <div class="col-12">
                            <img src="{{ str_starts_with($attachment->file_path, 'http') ? $attachment->file_path : asset('storage/' . $attachment->file_path) }}" 
                                 class="img-fluid rounded border shadow-sm w-100 object-fit-contain bg-light" style="max-height: 400px;" alt="{{ $attachment->file_name }}">
                        </div>
                        @else
                        <div class="col-12">
                            <div class="d-flex align-items-center p-3 border rounded bg-light">
                                <i class="fas fa-file-alt fa-2x text-secondary me-3"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark">{{ $attachment->file_name }}</h6>
                                    <a href="{{ str_starts_with($attachment->file_path, 'http') ? $attachment->file_path : asset('storage/' . $attachment->file_path) }}" target="_blank" class="small fw-bold text-primary text-decoration-none">Download File</a>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
            <div class="modal-footer d-flex justify-content-between bg-light border-top">
                <div class="d-flex gap-2">
                    <form action="{{ route('supervisor.approve', $log->id) }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-success fw-bold px-4 py-2 rounded-3" style="background-color: #10B981; border-color: #10B981;">Approve</button>
                    </form>
                    <button type="button" class="btn btn-outline-danger fw-bold px-4 py-2 rounded-3" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $log->id }}">Reject</button>
                </div>
                <button type="button" class="btn btn-primary-custom fw-bold px-4 py-2 rounded-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Image Lightbox Modal -->
<div class="modal fade" id="svImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0">
                <h6 class="modal-title text-white" id="svImageModalLabel">Attachment</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="svModalImage" src="" alt="" class="img-fluid rounded-bottom" style="max-height:80vh; object-fit:contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/supervisor/review-logbook.js') }}"></script>
@endpush
