@extends('layouts.app')

@section('title', 'Log Entries - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-log-entries.css') }}">
<!-- Flatpickr CSS for Custom Calendar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link active" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.notifications') }}"><i class="fas fa-bell"></i> Notifications</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Daily Log Entry')

@section('header-right')
<a href="{{ url()->previous() }}" class="btn btn-primary-custom px-4">
    <i class="fas fa-arrow-left me-2"></i> Back
</a>
@endsection

@section('main-content')
<div class="row justify-content-between">
    <div class="col-12">
        <div class="card card-custom p-4">
            @if(isset($logEntry))
                <div class="alert alert-info d-flex align-items-center mb-3">
                    <i class="fas fa-edit me-2"></i>
                    <span>You are editing a draft entry from <strong>{{ $logEntry->entry_date->format('d/m/Y') }}</strong>.
                    <!-- <a href="{{ route('student.log-entries') }}" class="ms-2">Cancel</a></span> -->
                </div>
            @endif
            <form action="{{ isset($logEntry) ? route('student.log-entries.update', $logEntry->id) : route('student.log-entries.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($logEntry))
                    @method('PUT')
                @endif
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-calendar-day"></i></span>
                            <input type="text" id="entryDate" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror" 
                                   value="{{ old('entry_date', isset($logEntry) ? $logEntry->entry_date->format('Y-m-d') : date('Y-m-d')) }}"
                                   @if($internship && $internship->start_date && $internship->end_date)
                                       data-min-date="{{ $internship->start_date->format('Y-m-d') }}"
                                       data-max-date="{{ $internship->end_date->format('Y-m-d') }}"
                                   @endif
                                   placeholder="Select Date"
                                   required style="background-color: #fff; cursor: pointer;">
                        </div>
                        @error('entry_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Week</label>
                        <select id="weekSelect" class="form-select" name="week_number" style="pointer-events: none; background-color: #e9ecef;">
                            @for($i = 1; $i <= ($internship->total_weeks ?? 12); $i++)
                                <option value="{{ $i }}" {{ old('week_number', isset($logEntry) ? $logEntry->week_number : '') == $i ? 'selected' : '' }}>Week {{ $i }}</option>
                            @endfor
                        </select>
                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Auto-calculated.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Log Type</label>
                        <select id="logTypeSelect" class="form-select" name="log_type">
                            <option value="work" {{ old('log_type', isset($logEntry) ? $logEntry->log_type : 'work') == 'work' ? 'selected' : '' }}>Work Day</option>
                            <option value="holiday" {{ old('log_type', isset($logEntry) ? $logEntry->log_type : 'work') == 'holiday' ? 'selected' : '' }}>Public Holiday</option>
                            <option value="leave" {{ old('log_type', isset($logEntry) ? $logEntry->log_type : 'work') == 'leave' ? 'selected' : '' }}>MC / Leave</option>
                        </select>
                        <small class="text-muted d-block mt-1">Select log category.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Task Description</label>
                    <div class="position-relative">
                        <textarea id="taskDescription" name="task_description" class="form-control @error('task_description') is-invalid @enderror" 
                                  rows="6" placeholder="Describe your daily activities..." required>{{ old('task_description', isset($logEntry) ? $logEntry->task_description : '') }}</textarea>
                        <button type="button" id="btnGenerateSummary" class="btn-ai-generate position-absolute bottom-0 end-0 m-3" title="Generate AI Summary">
                            <i class="fas fa-wand-magic-sparkles"></i> <span>Generate Summary</span>
                        </button>
                    </div>
                    <div class="form-text">Write your raw task notes, then click <strong>Generate Summary</strong> to create a professional version.</div>
                    @error('task_description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Show existing attachments when editing --}}
                @if(isset($logEntry) && $logEntry->attachments->count() > 0)
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Attachments</label>
                    <div class="d-flex flex-wrap gap-2" id="currentAttachments">
                        @foreach($logEntry->attachments as $attachment)
                            <div class="position-relative border rounded p-1" style="width:80px;" id="attachment-{{ $attachment->id }}">
                                <img src="{{ str_starts_with($attachment->file_path, 'http') ? $attachment->file_path : asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->file_name }}" 
                                     class="rounded" style="width:100%; height:60px; object-fit:cover;">
                                <button type="button" class="btn btn-danger btn-sm rounded-circle p-0 position-absolute top-0 end-0 delete-attachment-btn"
                                        style="width:20px;height:20px;font-size:0.6rem;line-height:1;"
                                        data-attachment-id="{{ $attachment->id }}"
                                        data-delete-url="{{ route('student.log-attachments.destroy', $attachment->id) }}"
                                        title="Delete attachment">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mb-4">
                    <label class="form-label fw-bold">{{ isset($logEntry) ? 'Add More Attachments' : 'Attachments (Images)' }}</label>
                    <div class="upload-zone" id="uploadZone">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-2">Drag & drop files or <label for="attachments" class="text-primary fw-semibold" style="cursor:pointer">Browse</label></p>
                        <input type="file" name="attachments[]" id="attachments" multiple accept="image/*" class="d-none">
                        <small class="text-muted">Supported: JPG, PNG, GIF, WebP &bull; Max 5MB per file</small>
                    </div>
                    @error('attachments.*')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <!-- Image Preview Area -->
                    <div id="imagePreview" class="image-preview-grid mt-3"></div>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <button type="submit" name="save_draft" value="1" class="btn btn-outline-secondary px-4">Save Draft</button>
                    <button type="submit" class="btn btn-primary-custom px-4">{{ isset($logEntry) ? 'Update & Submit' : 'Submit Log' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Previous Entries -->
@if($logs->count() > 0)
<div class="card card-custom p-4 mt-4">
    <h5 class="fw-bold mb-3">Previous Entries</h5>
    <div class="table-responsive">
        <table id="logEntriesTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th>Week</th>
                    <th>Date</th>
                    <th>Task Summary</th>
                    <th>Attachments</th>
                    <th>Status</th>
                    <th>Feedback</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $loop->iteration }}.</td>
                    <td>W{{ $log->week_number }}</td>
                    <td>{{ $log->entry_date->format('d/m/Y') }}</td>
                    <td>{{ Str::limit($log->task_description, 50) }}</td>
                    <td>
                        @if($log->attachments->count() > 0)
                            <div class="attachment-thumbnails">
                                @foreach($log->attachments as $attachment)
                                    <img src="{{ str_starts_with($attachment->file_path, 'http') ? $attachment->file_path : asset('storage/' . $attachment->file_path) }}" 
                                         alt="{{ $attachment->file_name }}"
                                         class="attachment-thumb"
                                         data-bs-toggle="modal" 
                                         data-bs-target="#imageModal"
                                         data-img-src="{{ str_starts_with($attachment->file_path, 'http') ? $attachment->file_path : asset('storage/' . $attachment->file_path) }}"
                                         data-img-name="{{ $attachment->file_name }}"
                                         title="Click to enlarge">
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($log->status === 'approved')
                            <span class="badge-status approved">Approved</span>
                        @elseif($log->status === 'rejected')
                            <span class="badge-status rejected">Rejected</span>
                        @elseif($log->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @else
                            <span class="badge-status draft">Draft</span>
                        @endif
                    </td>
                    <td>{{ $log->supervisor_comment ?? '-' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('student.log-entries.show', $log->id) }}" class="btn-table-action action-view" title="View Details">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @if($log->status === 'draft')
                            <a href="{{ route('student.log-entries.edit', $log->id) }}" class="btn-table-action action-edit" title="Edit Draft">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Image Lightbox Modal -->
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
    window.LIMS = {
        internshipStartDate: @json($internship && $internship->start_date ? $internship->start_date->format('Y-m-d') : null),
        maxWeeks: {{ $internship->total_weeks ?? 12 }},
        profileComplete: @json($profileComplete ?? false),
        profileUrl: '{{ route("student.profile") }}',
        aiSummaryUrl: '{{ route("student.ai-generate-summary") }}'
    };
</script>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('js/student/log-entries.js') }}"></script>
@endpush
