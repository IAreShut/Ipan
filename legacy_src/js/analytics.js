// Analytics Chart Logic

document.addEventListener('DOMContentLoaded', function() {
    // Main Line Chart
    const ctxMain = document.getElementById('mainChart').getContext('2d');
    new Chart(ctxMain, {
        type: 'line',
        data: {
            labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
            datasets: [{
                label: 'Total Active Students',
                data: [65, 45, 80, 200, 150, 180],
                borderColor: '#1E40AF',
                backgroundColor: 'rgba(30, 64, 175, 0.05)',
                tension: 0.4,
                fill: true,
                pointRadius: 0
            }, {
                label: 'Submissions',
                data: [30, 70, 60, 100, 90, 210],
                borderColor: '#9CA3AF',
                borderDash: [5, 5],
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                x: { grid: { display: false } }
            }
        }
    });

    // Pie Chart
    const ctxPie = document.getElementById('pieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Ogun State', 'Abuja', 'Lagos', 'Other'],
            datasets: [{
                data: [52.1, 22.8, 13.9, 11.2],
                backgroundColor: ['#1E40AF', '#10B981', '#3B82F6', '#D1D5DB'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
});
