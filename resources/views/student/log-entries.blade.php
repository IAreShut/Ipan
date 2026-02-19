@extends('layouts.app')

@section('title', 'Log Entries - LIMS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-log-entries.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@section('sidebar-menu')
<a class="nav-link" href="{{ route('student.dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
<a class="nav-link active" href="{{ route('student.log-entries') }}"><i class="fas fa-pen-fancy"></i> Log Entries</a>
<a class="nav-link" href="{{ route('student.progress') }}"><i class="fas fa-chart-bar"></i> View Progress</a>
<a class="nav-link" href="{{ route('student.profile') }}"><i class="fas fa-user-cog"></i> Profile</a>
@endsection

@section('page-title', 'Daily Log Entry')

@section('main-content')
<div class="row justify-content-between">
    <div class="col-12">
        <div class="card card-custom p-4">
            <form action="{{ route('student.log-entries.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror" 
                               value="{{ old('entry_date', date('Y-m-d')) }}" required>
                        @error('entry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Week</label>
                        <select class="form-select" name="week_number">
                            @for($i = 1; $i <= ($internship->total_weeks ?? 12); $i++)
                                <option value="{{ $i }}" {{ old('week_number') == $i ? 'selected' : '' }}>Week {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Task Description</label>
                    <div class="position-relative">
                        <textarea name="task_description" class="form-control @error('task_description') is-invalid @enderror" 
                                  rows="6" placeholder="Describe your daily activities..." required>{{ old('task_description') }}</textarea>
                        <button type="button" class="btn btn-sm btn-outline-primary position-absolute bottom-0 end-0 m-2" title="AI Suggest Summary">
                            <i class="fas fa-magic me-1"></i> AI Helper
                        </button>
                    </div>
                    <div class="form-text">Click the AI Helper button to generate a professional summary of your tasks.</div>
                    @error('task_description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Attachments (Images)</label>
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
                    <button type="submit" class="btn btn-primary-custom px-4">Submit Log</button>
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
                    <th>Week</th>
                    <th>Date</th>
                    <th>Task Summary</th>
                    <th>Attachments</th>
                    <th>Status</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>W{{ $log->week_number }}</td>
                    <td>{{ $log->entry_date->format('d M Y') }}</td>
                    <td>{{ Str::limit($log->task_description, 50) }}</td>
                    <td>
                        @if($log->attachments->count() > 0)
                            <div class="attachment-thumbnails">
                                @foreach($log->attachments as $attachment)
                                    <div class="thumb-wrapper">
                                        <img src="{{ asset('storage/' . $attachment->file_path) }}" 
                                             alt="{{ $attachment->file_name }}"
                                             class="attachment-thumb"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imageModal"
                                             data-img-src="{{ asset('storage/' . $attachment->file_path) }}"
                                             data-img-name="{{ $attachment->file_name }}"
                                             title="Click to enlarge">
                                        <form action="{{ route('student.log-attachments.destroy', $attachment->id) }}" method="POST" class="thumb-delete-form" onsubmit="return confirm('Delete this attachment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-thumb-delete" title="Delete attachment">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($log->status === 'approved')
                            <span class="badge badge-status-approved">Approved</span>
                        @elseif($log->status === 'rejected')
                            <span class="badge badge-status-rejected">Rejected</span>
                        @elseif($log->status === 'pending')
                            <span class="badge badge-status-pending">Pending</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </td>
                    <td>{{ $log->supervisor_comment ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
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
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#logEntriesTable').DataTable({
            paging: false,
            info: false,
            searching: true
        });
    });

    // ===== FILE PREVIEW =====
    const fileInput = document.getElementById('attachments');
    const previewContainer = document.getElementById('imagePreview');
    const uploadZone = document.getElementById('uploadZone');
    let selectedFiles = new DataTransfer();

    fileInput.addEventListener('change', function () {
        for (const file of this.files) {
            selectedFiles.items.add(file);
        }
        fileInput.files = selectedFiles.files;
        renderPreviews();
    });

    // Drag & drop
    uploadZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('drag-active');
    });
    uploadZone.addEventListener('dragleave', function () {
        this.classList.remove('drag-active');
    });
    uploadZone.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('drag-active');
        for (const file of e.dataTransfer.files) {
            if (file.type.startsWith('image/')) {
                selectedFiles.items.add(file);
            }
        }
        fileInput.files = selectedFiles.files;
        renderPreviews();
    });

    function renderPreviews() {
        previewContainer.innerHTML = '';
        Array.from(selectedFiles.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const wrapper = document.createElement('div');
                wrapper.className = 'preview-item';
                wrapper.innerHTML = `
                    <img src="${e.target.result}" alt="${file.name}">
                    <span class="preview-name">${file.name}</span>
                    <button type="button" class="btn-preview-remove" data-index="${index}" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewContainer.appendChild(wrapper);

                wrapper.querySelector('.btn-preview-remove').addEventListener('click', function () {
                    removeFile(parseInt(this.dataset.index));
                });
            };
            reader.readAsDataURL(file);
        });
    }

    function removeFile(index) {
        const dt = new DataTransfer();
        Array.from(selectedFiles.files).forEach((file, i) => {
            if (i !== index) dt.items.add(file);
        });
        selectedFiles = dt;
        fileInput.files = selectedFiles.files;
        renderPreviews();
    }

    // ===== LIGHTBOX MODAL =====
    document.getElementById('imageModal')?.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        document.getElementById('modalImage').src = trigger.dataset.imgSrc;
        document.getElementById('imageModalLabel').textContent = trigger.dataset.imgName;
    });
</script>
@endpush
