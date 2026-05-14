/**
 * Auth/Login Scripts
 * - Pre-assigned Supervisor Check (AJAX)
 * - Role toggle
 * - Password toggle
 */

var assignmentVerified = false;
var registerBtn = document.getElementById('registerBtn');
var regMatrixId = document.getElementById('regMatrixId');
var regRole = document.getElementById('regRole');
var checkResultEl = document.getElementById('assignmentCheckResult');

function checkMatrixIdAssignment() {
    var matrixId = regMatrixId.value.trim();
    var role = regRole.value;

    if (role === 'supervisor') {
        assignmentVerified = true;
        enableRegisterBtn();
        checkResultEl.innerHTML = '';
        clearAutoFillFields();
        return;
    }

    if (!matrixId || matrixId.length < 3) {
        assignmentVerified = false;
        disableRegisterBtn();
        checkResultEl.innerHTML = '';
        return;
    }

    checkResultEl.innerHTML = '<div class="text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Checking assignment...</div>';

    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(window.checkAssignmentUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ matrix_id: matrixId })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.found) {
            assignmentVerified = true;
            checkResultEl.innerHTML = '<div class="alert alert-success py-2 mb-0 d-flex align-items-center gap-2"><i class="fas fa-check-circle"></i><span><strong>Supervisor:</strong> ' + data.supervisor_name + '</span></div>';
            enableRegisterBtn();
            autoFillAcademicFields(data);
        } else {
            assignmentVerified = false;
            checkResultEl.innerHTML = '<div class="alert alert-warning py-2 mb-0 d-flex align-items-center gap-2"><i class="fas fa-exclamation-triangle"></i><span>' + data.message + '</span></div>';
            disableRegisterBtn();
            clearAutoFillFields();
        }
    })
    .catch(function() {
        checkResultEl.innerHTML = '<div class="alert alert-danger py-2 mb-0 d-flex align-items-center gap-2"><i class="fas fa-times-circle"></i><span>Error checking assignment. Please try again.</span></div>';
        assignmentVerified = false;
        disableRegisterBtn();
    });
}

function autoFillAcademicFields(data) {
    var facultyInput = document.getElementById('regFaculty');
    var programmeInput = document.getElementById('regProgramme');
    var classInput = document.getElementById('regClass');

    if (facultyInput) facultyInput.value = data.faculty || '';
    if (programmeInput) programmeInput.value = data.programme_code || '';
    if (classInput) classInput.value = data.class || '';
}

function clearAutoFillFields() {
    var facultyInput = document.getElementById('regFaculty');
    var programmeInput = document.getElementById('regProgramme');
    var classInput = document.getElementById('regClass');

    if (facultyInput) facultyInput.value = '';
    if (programmeInput) programmeInput.value = '';
    if (classInput) classInput.value = '';
    assignmentVerified = false;
}

function enableRegisterBtn() {
    if (registerBtn) {
        registerBtn.disabled = false;
        registerBtn.classList.remove('btn-secondary');
        registerBtn.classList.add('btn-success');
    }
}

function disableRegisterBtn() {
    if (registerBtn) {
        registerBtn.disabled = true;
        registerBtn.classList.remove('btn-success');
        registerBtn.classList.add('btn-secondary');
    }
}

if (regMatrixId) {
    regMatrixId.addEventListener('blur', function() { checkMatrixIdAssignment(); });
    var checkTimeout;
    regMatrixId.addEventListener('input', function() {
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(function() { checkMatrixIdAssignment(); }, 500);
    });
}

if (regRole) {
    regRole.addEventListener('change', function() {
        if (this.value === 'supervisor') {
            enableRegisterBtn();
            checkResultEl.innerHTML = '';
            assignmentVerified = true;
            clearAutoFillFields();
        } else {
            checkMatrixIdAssignment();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (regMatrixId && regMatrixId.value.trim()) {
        checkMatrixIdAssignment();
    }
});

document.querySelector('#pills-register form').addEventListener('submit', function(e) {
    var role = regRole ? regRole.value : '';
    if (role === 'student' && !assignmentVerified) {
        e.preventDefault();
        checkResultEl.innerHTML = '<div class="alert alert-danger py-2 mb-0 d-flex align-items-center gap-2"><i class="fas fa-times-circle"></i><span>Please verify your Student ID before registering.</span></div>';
        disableRegisterBtn();
    }
});

document.getElementById('regRole').addEventListener('change', function() {
    var isStudent = this.value === 'student';
    var studentFields = ['companyField', 'facultyField', 'classField', 'programmeField'];

    studentFields.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = isStudent ? 'block' : 'none';
    });

    var companyInput = document.querySelector('input[name="company"]');
    if (companyInput) companyInput.required = isStudent;

    if (!isStudent) {
        if (companyInput) companyInput.value = '';
    }
});

function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
