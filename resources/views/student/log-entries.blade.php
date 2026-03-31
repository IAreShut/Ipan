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
                    <span>You are editing a draft entry from <strong>{{ $logEntry->entry_date->format('d M Y') }}</strong>.
                    <!-- <a href="{{ route('student.log-entries') }}" class="ms-2">Cancel</a></span> -->
                </div>
            @endif
            <form action="{{ isset($logEntry) ? route('student.log-entries.update', $logEntry->id) : route('student.log-entries.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($logEntry))
                    @method('PUT')
                @endif
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror" 
                               value="{{ old('entry_date', isset($logEntry) ? $logEntry->entry_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                        @error('entry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Week</label>
                        <select class="form-select" name="week_number">
                            @for($i = 1; $i <= ($internship->total_weeks ?? 12); $i++)
                                <option value="{{ $i }}" {{ old('week_number', isset($logEntry) ? $logEntry->week_number : '') == $i ? 'selected' : '' }}>Week {{ $i }}</option>
                            @endfor
                        </select>
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
                    <td>W{{ $log->week_number }}</td>
                    <td>{{ $log->entry_date->format('d M Y') }}</td>
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
                    <td>
                        <a href="{{ route('student.log-entries.show', $log->id) }}" class="btn btn-sm btn-light text-primary" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($log->status === 'draft')
                        <a href="{{ route('student.log-entries.edit', $log->id) }}" class="btn btn-sm btn-light text-warning" title="Edit Draft">
                            <i class="fas fa-pen"></i>
                        </a>
                        @endif
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
    $(document).ready(function() {
        $('#logEntriesTable').DataTable({
            pageLength: 10,
            order: [],
            language: {
                search: 'Search:',
                paginate: { previous: '‹', next: '›' }
            }
        });
    });

    // ===== PROFILE COMPLETENESS CHECK =====
    @if(!$profileComplete)
    (function() {
        const form = document.querySelector('form[action*="log-entries"]');
        if (!form) return;

        // Find the main submit button (not the draft button)
        const submitBtn = form.querySelector('button[type="submit"]:not([name="save_draft"])');
        if (submitBtn) {
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Complete Your Profile First!',
                    html: 'You need to complete your profile before submitting a log entry.<br><br>',
                    confirmButtonText: '<i class="fas fa-user-edit me-1"></i> Go to Profile',
                    confirmButtonColor: '#0f172a',
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#6c757d',
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("student.profile") }}';
                    }
                });
            });
        }
    })();
    @endif

    // ===== DELETE ATTACHMENT VIA AJAX =====
    document.querySelectorAll('.delete-attachment-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Delete this attachment?')) return;

            const url = this.dataset.deleteUrl;
            const attachmentId = this.dataset.attachmentId;
            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            }).then(function(response) {
                if (response.ok) {
                    const el = document.getElementById('attachment-' + attachmentId);
                    if (el) el.remove();
                } else {
                    alert('Failed to delete attachment.');
                }
            }).catch(function() {
                alert('Network error. Please try again.');
            });
        });
    });

    // ===== AI GENERATE SUMMARY =====
    const btnGenerate = document.getElementById('btnGenerateSummary');
    if (btnGenerate) {
        btnGenerate.addEventListener('click', function() {
            const textarea = document.getElementById('taskDescription');
            const rawText = textarea.value.trim();

            if (rawText.length < 5) {
                Swal.fire({ icon: 'warning', title: 'Too Short', text: 'Please write at least a few words about your tasks before generating a summary.', confirmButtonColor: '#F59E0B' });
                return;
            }

            // Build FormData with text + images
            const formData = new FormData();
            formData.append('task_description', rawText);

            // Grab files from the attachment input
            const fileInput = document.getElementById('attachments');
            if (fileInput && fileInput.files.length > 0) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append('images[]', fileInput.files[i]);
                }
            }

            // Show loading state
            const btnIcon = btnGenerate.querySelector('i');
            const btnText = btnGenerate.querySelector('span');
            const originalIcon = btnIcon.className;
            const originalText = btnText.textContent;

            btnIcon.className = 'fas fa-spinner fa-spin';
            btnText.textContent = 'Generating...';
            btnGenerate.disabled = true;
            btnGenerate.style.opacity = '0.7';

            fetch('{{ route("student.ai-generate-summary") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if (ok && data.summary) {
                    textarea.value = data.summary;
                    Swal.fire({ icon: 'success', title: 'Summary Generated!', text: 'The AI summary has been placed in the text box. You can edit it before saving.', confirmButtonColor: '#10B981', timer: 4000, timerProgressBar: true });
                } else {
                    Swal.fire({ icon: 'error', title: 'AI Error', text: data.error || 'Failed to generate summary.', confirmButtonColor: '#EF4444' });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect to the AI service. Please check your internet and try again.', confirmButtonColor: '#EF4444' });
            })
            .finally(() => {
                btnIcon.className = originalIcon;
                btnText.textContent = originalText;
                btnGenerate.disabled = false;
                btnGenerate.style.opacity = '1';
            });
        });
    }

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
