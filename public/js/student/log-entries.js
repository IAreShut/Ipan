/**
 * Student Log Entries Scripts
 * Expects window.LIMS to be set in the Blade template with:
 *   - internshipStartDate (string|null)
 *   - maxWeeks (number)
 *   - profileComplete (boolean)
 *   - profileUrl (string)
 *   - aiSummaryUrl (string)
 */
$(document).ready(function() {
    $('#logEntriesTable').DataTable({
        pageLength: 10,
        order: [],
        language: {
            search: 'Search:',
            paginate: { previous: '‹', next: '›' }
        }
    });

    // Auto-calculate week based on date
    const entryDateInput = document.getElementById('entryDate');
    const weekSelect = document.getElementById('weekSelect');

    if (window.LIMS && window.LIMS.internshipStartDate) {
        const internshipStartDate = new Date(window.LIMS.internshipStartDate);

        function calculateWeek() {
            if (!entryDateInput || !entryDateInput.value) return;
            const selectedDate = new Date(entryDateInput.value);

            // Set time to 00:00:00 to avoid timezone calculation issues
            selectedDate.setHours(0,0,0,0);
            const startDate = new Date(internshipStartDate);
            startDate.setHours(0,0,0,0);

            // Calculate difference in days
            const diffTime = selectedDate.getTime() - startDate.getTime();
            const diffDays = Math.floor(diffTime / (1000 * 3600 * 24));

            // Calculate week number (1-based)
            let weekNum = Math.floor(diffDays / 7) + 1;

            // Ensure week number doesn't go below 1 or above total weeks
            const maxWeeks = window.LIMS.maxWeeks || 12;
            if (weekNum < 1) weekNum = 1;
            if (weekNum > maxWeeks) weekNum = maxWeeks;

            // Update select dropdown
            if (weekSelect) {
                weekSelect.value = weekNum;
            }
        }

        // Calculate on load and on change
        calculateWeek();
        if (entryDateInput) {
            entryDateInput.addEventListener('change', calculateWeek);
        }
    }
});

// ===== PROFILE COMPLETENESS CHECK =====
(function() {
    if (window.LIMS && window.LIMS.profileComplete) return; // profile is complete, skip

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
                    window.location.href = window.LIMS.profileUrl;
                }
            });
        });
    }
})();

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

        fetch(window.LIMS.aiSummaryUrl, {
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

// ===== LOG TYPE AUTO-FILL & AI TOGGLE =====
document.addEventListener('DOMContentLoaded', function() {
    const logTypeSelect = document.getElementById('logTypeSelect');
    const taskDescription = document.getElementById('taskDescription');
    const btnGenerateSummary = document.getElementById('btnGenerateSummary');

    if (logTypeSelect && taskDescription) {
        function handleLogTypeChange() {
            const val = logTypeSelect.value;
            const currentDesc = taskDescription.value.trim();

            if (val === 'holiday') {
                if (currentDesc === '' || currentDesc === 'Medical Leave / Personal Leave - Excused from daily tasks.') {
                    taskDescription.value = 'Public Holiday - Excused from daily tasks.';
                }
                if (btnGenerateSummary) btnGenerateSummary.style.display = 'none';
            } else if (val === 'leave') {
                if (currentDesc === '' || currentDesc === 'Public Holiday - Excused from daily tasks.') {
                    taskDescription.value = 'Medical Leave / Personal Leave - Excused from daily tasks.';
                }
                if (btnGenerateSummary) btnGenerateSummary.style.display = 'none';
            } else {
                if (currentDesc === 'Public Holiday - Excused from daily tasks.' || currentDesc === 'Medical Leave / Personal Leave - Excused from daily tasks.') {
                    taskDescription.value = '';
                }
                if (btnGenerateSummary) btnGenerateSummary.style.display = 'block';
            }
        }

        logTypeSelect.addEventListener('change', handleLogTypeChange);
        // Run once on load to set initial state (e.g. when editing)
        handleLogTypeChange();
    }
});
