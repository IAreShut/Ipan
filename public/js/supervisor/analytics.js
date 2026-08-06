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

                    var lineLength = 10;
                    var x2 = element.x + cosA * (outerR + lineLength);
                    var y2 = element.y + sinA * (outerR + lineLength);

                    var isRight = cosA >= 0;
                    var elbowLength = 8;
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
                    var textX = x3 + (isRight ? 4 : -4);
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
                        top: 20,
                        bottom: 20,
                        left: 85,
                        right: 85
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

    // Clean Markdown text helper
    function cleanAiText(text) {
        if (!text) return '';
        return text
            .replace(/^#+\s*/gm, '')
            .replace(/[\*#]/g, '')
            .replace(/\n/g, '<br>');
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
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var aiModal = bootstrap.Modal.getInstance(document.getElementById('aiChatModal'));
                if (!aiModal) {
                    aiModal = new bootstrap.Modal(document.getElementById('aiChatModal'));
                }
                aiModal.show();
            } else {
                $('#aiChatModal').modal('show');
            }
            setTimeout(function() { $('#aiChatInput').focus(); }, 500);
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
                                html: '<div style="text-align: justify; font-size: 0.95rem;">' + cleanAiText(res.data) + '</div>',
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

    // AI Chat Modal Logic
    var chatHistoryKey = 'sv_ai_chat_history';
    var chatHistory = JSON.parse(localStorage.getItem(chatHistoryKey)) || [];

    function renderChat() {
        var $historyContainer = $('#aiChatHistory');
        $historyContainer.empty();
        
        if (chatHistory.length === 0) {
            $historyContainer.html('<div class="text-center text-muted mt-5"><i class="fas fa-comment-dots fa-3x mb-3 opacity-50"></i><p>No messages yet. Ask me anything about your data!</p></div>');
            return;
        }

        chatHistory.forEach(function(msg) {
            appendMessage(msg.role, msg.content, false);
        });
        scrollToBottom();
    }

    function scrollToBottom() {
        var $historyContainer = $('#aiChatHistory');
        if ($historyContainer.length) {
            $historyContainer.scrollTop($historyContainer[0].scrollHeight);
        }
    }

    function appendMessage(role, content, animate) {
        var $historyContainer = $('#aiChatHistory');
        var isUser = role === 'user';
        
        // Remove empty state if exists
        $historyContainer.find('.text-center.text-muted').remove();

        var avatar = isUser ? '<i class="fas fa-user-circle fa-2x text-secondary"></i>' : '<i class="fas fa-robot fa-2x text-primary"></i>';
        var alignment = isUser ? 'flex-row-reverse' : 'flex-row';
        var bgColor = isUser ? 'bg-primary text-white' : 'bg-white border shadow-sm';
        var textStyle = isUser ? '' : 'text-align: justify;';
        var parsedContent = isUser ? content : cleanAiText(content);

        var msgHtml = `
            <div class="d-flex ${alignment} mb-3" ${animate ? 'style="display:none;"' : ''}>
                <div class="flex-shrink-0 ${isUser ? 'ms-3' : 'me-3'}">
                    ${avatar}
                </div>
                <div class="p-3 rounded-4 ${bgColor}" style="max-width: 80%; ${textStyle}">
                    ${parsedContent}
                </div>
            </div>
        `;

        var $msgElement = $(msgHtml);
        $historyContainer.append($msgElement);
        if (animate) {
            $msgElement.fadeIn(300);
        }
        scrollToBottom();
    }

    $('#aiChatModal').on('show.bs.modal', function () {
        renderChat();
    });

    function sendAiMessage() {
        var $input = $('#aiChatInput');
        var text = $input.val().trim();
        if (!text) return;

        $input.val('');
        
        // Add user msg
        chatHistory.push({ role: 'user', content: text });
        localStorage.setItem(chatHistoryKey, JSON.stringify(chatHistory));
        appendMessage('user', text, true);

        // Add loading
        var $historyContainer = $('#aiChatHistory');
        var loadingHtml = `
            <div class="d-flex flex-row mb-3" id="aiChatLoading">
                <div class="flex-shrink-0 me-3">
                    <i class="fas fa-robot fa-2x text-primary"></i>
                </div>
                <div class="p-3 rounded-4 bg-white border shadow-sm d-flex align-items-center">
                    <div class="spinner-grow spinner-grow-sm text-primary me-1" role="status"></div>
                    <div class="spinner-grow spinner-grow-sm text-primary me-1" role="status" style="animation-delay: 0.2s"></div>
                    <div class="spinner-grow spinner-grow-sm text-primary" role="status" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        `;
        $historyContainer.append(loadingHtml);
        scrollToBottom();
        
        $('#sendAiChatBtn, #aiChatInput').prop('disabled', true);

        $.post('/supervisor/analytics/ai-chat', { question: text, history: chatHistory })
            .done(function(response) {
                $('#aiChatLoading').remove();
                if (response.success) {
                    var aiResponse = response.data;
                    chatHistory.push({ role: 'model', content: aiResponse });
                    localStorage.setItem(chatHistoryKey, JSON.stringify(chatHistory));
                    appendMessage('model', aiResponse, true);
                } else {
                    appendMessage('model', 'Error: ' + (response.error || 'Something went wrong.'), true);
                }
            })
            .fail(function() {
                $('#aiChatLoading').remove();
                appendMessage('model', 'Failed to connect to AI server.', true);
            })
            .always(function() {
                $('#sendAiChatBtn, #aiChatInput').prop('disabled', false);
                $('#aiChatInput').focus();
            });
    }

    $('#sendAiChatBtn').click(sendAiMessage);
    $('#aiChatInput').keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault();
            sendAiMessage();
        }
    });

    $('#clearAiChatBtn').click(function() {
        chatHistory = [];
        localStorage.removeItem(chatHistoryKey);
        renderChat();
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
