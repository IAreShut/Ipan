/**
 * Supervisor Analytics Scripts
 * - Performance Trend Chart (Chart.js)
 * - AI Assistant actions
 * - DataTable initialization
 */
$(document).ready(function() {
    // Setup CSRF for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Performance Trend Chart
    if (window.chartData && document.getElementById('trendChart')) {
        var ctx = document.getElementById('trendChart').getContext('2d');
        var data = window.chartData;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.months,
                datasets: [
                    {
                        label: 'Approved',
                        data: data.approved,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Pending',
                        data: data.pending,
                        borderColor: '#ffc107',
                        backgroundColor: 'transparent',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
    
    // Doughnut Breakdown Chart
    if (window.breakdownData && document.getElementById('breakdownChart')) {
        var bdCtx = document.getElementById('breakdownChart').getContext('2d');
        new Chart(bdCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [window.breakdownData.approved, window.breakdownData.pending, window.breakdownData.rejected],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%', // Creates the thin doughnut look
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed + ' logs';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // AI Assistant Actions
    $('.ai-action').click(function(e) {
        e.preventDefault();
        var action = $(this).data('action');
        var endpoint = '';
        var payload = {};
        var title = '';

        if (action === 'summary') {
            endpoint = '/supervisor/analytics/ai-summary';
            title = 'Performance Summary';
        } else if (action === 'at-risk') {
            endpoint = '/supervisor/analytics/ai-at-risk';
            title = 'At-Risk Students';
        } else if (action === 'chat') {
            Swal.fire({
                title: 'Ask AI Assistant',
                input: 'text',
                inputLabel: 'What do you want to know about your data?',
                inputPlaceholder: 'e.g., Which student has the most rejected logs?',
                showCancelButton: true,
                confirmButtonText: 'Ask',
                showLoaderOnConfirm: true,
                preConfirm: function(question) {
                    return $.post('/supervisor/analytics/ai-chat', { question: question })
                        .then(function(response) {
                            if (!response.success) throw new Error(response.error);
                            return response.data;
                        })
                        .catch(function(error) {
                            Swal.showValidationMessage('Request failed: ' + error);
                        });
                },
                allowOutsideClick: function() { return !Swal.isLoading(); }
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'AI Answer',
                        html: '<div style="text-align: left; font-size: 0.95rem;">' + result.value.replace(/\n/g, '<br>') + '</div>',
                        icon: 'success'
                    });
                }
            });
            return;
        }

        Swal.fire({
            title: 'Generating ' + title + '...',
            html: 'Gemini AI is analyzing your data.',
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading();
                $.post(endpoint, payload)
                    .done(function(res) {
                        if (res.success) {
                            Swal.fire({
                                title: title,
                                html: '<div style="text-align: left; font-size: 0.95rem;">' + res.data.replace(/\n/g, '<br>') + '</div>',
                                icon: 'success',
                                confirmButtonColor: '#1E3A8A'
                            });
                        } else {
                            Swal.fire('Error', res.error, 'error');
                        }
                    })
                    .fail(function() {
                        Swal.fire('Error', 'Failed to connect to AI server.', 'error');
                    });
            }
        });
    });

    // Initialize DataTables
    if ($.fn.DataTable && document.getElementById('topPerformingTable')) {
        var table = $('#topPerformingTable').DataTable({
            pageLength: 5,
            lengthChange: false,
            language: {
                search: '',
                searchPlaceholder: 'Search students...'
            }
        });
        $('.dataTables_filter').appendTo('#topPerformingTableSearch');
        $('.dataTables_filter').addClass('mb-0');
        $('.dataTables_filter label').addClass('mb-0');
    }
});
