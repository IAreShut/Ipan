@extends('layouts.app')

@section('title', 'Review Logbook - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-log-entries.css') }}">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('supervisor.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link active" href="{{ route('supervisor.review-logbook') }}"><i class="fas fa-check-circle"></i> Review Logbook</a>
<a class="nav-link" href="{{ route('supervisor.analytics') }}"><i class="fas fa-chart-line"></i> Analytics</a>
@endsection

@section('page-title', 'Review Logbook')
@section('page-subtitle', 'Review and approve student log entries.')

@section('main-content')
<div class="card card-custom p-4">
    @forelse($logs as $log)
    <div class="border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex align-items-center">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($log->student->name ?? 'User') }}&background=random" class="rounded-circle me-2" width="40">
                <div>
                    <h6 class="mb-0 fw-bold">{{ $log->student->name ?? 'Unknown Student' }}</h6>
                    <small class="text-muted">Week {{ $log->week_number }} - {{ $log->entry_date->format('d M Y') }}</small>
                </div>
            </div>
            <span class="badge badge-status-pending">Pending Review</span>
        </div>
        
        <div class="bg-light p-3 rounded mb-3">
            <p class="mb-0">{{ $log->task_description }}</p>
            @if($log->attachments && $log->attachments->count() > 0)
                <div class="sv-attachment-gallery">
                    @foreach($log->attachments as $attachment)
                        <img src="{{ asset('storage/' . $attachment->file_path) }}" 
                             alt="{{ $attachment->file_name }}"
                             data-bs-toggle="modal" 
                             data-bs-target="#svImageModal"
                             data-img-src="{{ asset('storage/' . $attachment->file_path) }}"
                             data-img-name="{{ $attachment->file_name }}"
                             title="{{ $attachment->file_name }}">
                    @endforeach
                </div>
            @endif
        </div>

        <div class="d-flex gap-2">
            <form action="{{ route('supervisor.approve', $log->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-check me-1"></i> Approve
                </button>
            </form>
            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $log->id }}">
                <i class="fas fa-times me-1"></i> Reject
            </button>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal{{ $log->id }}" tabindex="-1">
            <div class="modal-dialog">
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
        <h5>All Caught Up!</h5>
        <p class="text-muted">No pending log entries to review.</p>
    </div>
    @endforelse

    {{ $logs->links() }}
</div>

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
<script>
    document.getElementById('svImageModal')?.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        document.getElementById('svModalImage').src = trigger.dataset.imgSrc;
        document.getElementById('svImageModalLabel').textContent = trigger.dataset.imgName;
    });
</script>
@endpush
