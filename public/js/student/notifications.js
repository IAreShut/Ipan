/**
 * Student Notifications Scripts
 * Expects window.LIMS.calendarEvents to be set in the Blade template
 */
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    // Use events data passed from Blade via window.LIMS
    var events = (window.LIMS && window.LIMS.calendarEvents) ? window.LIMS.calendarEvents : [];

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: events,
        displayEventTime: false,
        dateClick: function(info) {
            // Pre-fill date when clicking on calendar
            document.getElementById('reminder_date').value = info.dateStr;
            var modal = new bootstrap.Modal(document.getElementById('addReminderModal'));
            modal.show();
        },
        eventClick: function(info) {
            // Show event details
            Swal.fire({
                title: info.event.title,
                text: 'Due: ' + info.event.start.toLocaleString(),
                icon: 'info',
                confirmButtonColor: '#3b82f6'
            });
        },
        height: 600
    });
    
    calendar.render();
});
