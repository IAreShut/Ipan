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
                        borderColor: '#1E3A8A',
                        backgroundColor: 'rgba(30, 58, 138, 0.12)',
                        pointBackgroundColor: '#1E3A8A',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Pending',
                        data: data.pending,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.08)',
                        pointBackgroundColor: '#3B82F6',
                        tension: 0.4,
                        fill: true
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
    
    // Interactive Pie Breakdown Chart with Leader Lines
    if (window.breakdownData && document.getElementById('breakdownChart')) {
        var bdCtx = document.getElementById('breakdownChart').getContext('2d');

        // Plugin to draw pointer leader lines & percentage callouts outside pie slices
        var pieCalloutPlugin = {
            id: 'pieCalloutPlugin',
            afterDraw: function(chart) {
                var ctx = chart.ctx;
                var meta = chart.getDatasetMeta(0);
                if (!meta || !meta.data || !meta.data.length) return;

                var total = (window.breakdownData && window.breakdownData.total > 0)
                    ? window.breakdownData.total
                    : chart.data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
                if (total <= 0) return;

                ctx.save();
                meta.data.forEach(function(element, index) {
                    var value = chart.data.datasets[0].data[index];
                    if (value <= 0) return;

                    var percentage = Math.round((value / total) * 100);
                    var labelText = chart.data.labels[index] + ' ' + percentage + '%';

                    // Calculate arc center angle
                    var alpha = (element.startAngle + element.endAngle) / 2;
                    var cosA = Math.cos(alpha);
                    var sinA = Math.sin(alpha);

                    // Pointer start at outer edge of arc
                    var outerR = element.outerRadius;
                    var x1 = element.x + cosA * (outerR + 2);
                    var y1 = element.y + sinA * (outerR + 2);

                    var lineLength = 16;
                    var x2 = element.x + cosA * (outerR + lineLength);
                    var y2 = element.y + sinA * (outerR + lineLength);

                    var isRight = cosA >= 0;
                    var elbowLength = 12;
                    var x3 = x2 + (isRight ? elbowLength : -elbowLength);
                    var y3 = y2;

                    var color = element.options.backgroundColor || '#3B82F6';

                    // Draw leader line
                    ctx.beginPath();
                    ctx.moveTo(x1, y1);
                    ctx.lineTo(x2, y2);
                    ctx.lineTo(x3, y3);
                    ctx.strokeStyle = color;
                    ctx.lineWidth = 1.5;
                    ctx.stroke();

                    // Draw subtle dot at arc start
                    ctx.beginPath();
                    ctx.arc(x1, y1, 2, 0, 2 * Math.PI);
                    ctx.fillStyle = color;
                    ctx.fill();

                    // Draw callout label text
                    ctx.font = '600 11px Inter, sans-serif';
                    ctx.fillStyle = '#3B82F6';
                    ctx.textAlign = isRight ? 'left' : 'right';
                    ctx.textBaseline = 'middle';
                    var textX = x3 + (isRight ? 5 : -5);
                    ctx.fillText(labelText, textX, y3);
                });
                ctx.restore();
            }
        };

        var breakdownPieChart = new Chart(bdCtx, {
            type: 'pie',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [window.breakdownData.approved, window.breakdownData.pending, window.breakdownData.rejected],
                    backgroundColor: ['#1E3A8A', '#3B82F6', '#60A5FA'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 12
                }]
            },
            plugins: [pieCalloutPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 25,
                        bottom: 25,
                        left: 55,
                        right: 55
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        titleFont: { family: 'Inter', weight: '600' },
                        bodyFont: { family: 'Inter' },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                var total = (window.breakdownData && window.breakdownData.total > 0)
                                    ? window.breakdownData.total
                                    : context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var val = context.parsed;
                                var pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                return ' ' + context.label + ': ' + val + ' logs (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Sync legend hover with chart segment highlight
        $('.breakdown-legend-item').on('mouseenter', function() {
            var index = $(this).data('index');
            if (breakdownPieChart && breakdownPieChart.getDatasetMeta(0).data[index]) {
                breakdownPieChart.setActiveElements([{ datasetIndex: 0, index: index }]);
                breakdownPieChart.update();
            }
        }).on('mouseleave', function() {
            if (breakdownPieChart) {
                breakdownPieChart.setActiveElements([]);
                breakdownPieChart.update();
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
