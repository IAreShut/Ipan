@extends('layouts.app')

@section('title', 'Review Logbook - LIMS')

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
@endsection
