/**
 * UX Helpers — Global Spinner & SweetAlert2 Notifications
 * ========================================================
 * Loaded on every page via master.blade.php.
 *
 * 1. Intercepts ALL form submissions → shows spinner + disables button.
 * 2. Reads hidden <meta> tags for Laravel flash messages → fires SweetAlert2.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─── References ──────────────────────────────────────────────
    const overlay = document.getElementById('globalSpinnerOverlay');

    // ─── 1. FORM INTERCEPTION ────────────────────────────────────
    // Track which submit button was actually clicked
    let clickedBtn = null;
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('button[type="submit"], input[type="submit"]');
        if (btn) clickedBtn = btn;
    });

    document.addEventListener('submit', function (e) {
        const form = e.target;

        // Skip forms that opt-out (e.g. logout or inline delete forms)
        if (form.classList.contains('no-spinner')) return;

        // Show the spinner overlay
        if (overlay) overlay.classList.add('active');

        // Use the actually-clicked button, or fall back to the first one
        const btn = clickedBtn || 
            form.querySelector('button[type="submit"]') ||
            form.querySelector('input[type="submit"]');

        if (btn) {
            // IMPORTANT: If the button has a name (e.g. save_draft), inject a hidden
            // input to preserve its value — disabled buttons are excluded from form data
            if (btn.name) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = btn.name;
                hidden.value = btn.value || '';
                form.appendChild(hidden);
            }

            btn.disabled = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing…';
        }

        // Reset for next submission
        clickedBtn = null;
    });

    // ─── 2. SWEETALERT2 FLASH MESSAGES ───────────────────────────
    // The meta tags are injected by Blade in master.blade.php

    const successMsg = getMeta('flash-success');
    const errorMsg   = getMeta('flash-error');
    const validationErrors = getMeta('flash-validation');

    if (successMsg && typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: successMsg,
            confirmButtonColor: '#10B981',
            timer: 4000,
            timerProgressBar: true,
            showClass:   { popup: 'animate__animated animate__fadeInDown' },
            hideClass:   { popup: 'animate__animated animate__fadeOutUp' }
        });
    }

    if (errorMsg && typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: errorMsg,
            confirmButtonColor: '#EF4444'
        });
    }

    if (validationErrors && typeof Swal !== 'undefined') {
        // validationErrors is a JSON-encoded array of strings
        try {
            const errors = JSON.parse(validationErrors);
            if (errors.length) {
                const list = errors.map(function (err) {
                    return '<li style="text-align:left;">' + escapeHtml(err) + '</li>';
                }).join('');

                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    html: '<ul style="padding-left:1.2rem;margin:0;">' + list + '</ul>',
                    confirmButtonColor: '#F59E0B'
                });
            }
        } catch (_) { /* ignore parse errors */ }
    }

    // ─── Helpers ──────────────────────────────────────────────────
    function getMeta(name) {
        const el = document.querySelector('meta[name="' + name + '"]');
        return el ? el.getAttribute('content') : null;
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
});
