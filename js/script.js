// Main Script for LIMS Mockup logic

// Simple navigation simulation for Login
function handleLogin(event) {
    event.preventDefault();
    const role = document.getElementById('loginRole').value;
    
    if (role === 'student') {
        window.location.href = 'student/dashboard.html';
    } else if (role === 'supervisor') {
        window.location.href = 'supervisor/dashboard.html';
    } else {
        alert('Admin dashboard not included in this demo.');
    }
}

function handleRegister(event) {
    event.preventDefault();
    alert('Registration simulated! Please login.');
    // Switch to login tab
    const triggerEl = document.querySelector('#pills-login-tab');
    const tab = new bootstrap.Tab(triggerEl);
    tab.show();
}

// Check URL hash to switch to register tab if needed
document.addEventListener('DOMContentLoaded', () => {
    if(window.location.hash === '#register') {
        const triggerEl = document.querySelector('#pills-register-tab');
        const tab = new bootstrap.Tab(triggerEl);
        tab.show();
    }
});
