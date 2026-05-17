/**
 * Supervisor Dashboard Scripts
 * - DataTable initialization for student list
 */
$(document).ready(function() {
    const table = $('#supervisorStudentTable').DataTable({
        "dom": '<"top">rt<"bottom"ip><"clear">',
        "pageLength": 5,
        "language": {
            "emptyTable": "No students found."
        }
    });

    $('#studentSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

   // Live Date Time functionality
    function updateDateTime() {
        const now = new Date();
        const options = { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        };
        const dateTimeStr = now.toLocaleDateString('en-MY', options);
        $('#liveDateTime').text(dateTimeStr);
    }
    
    // Initial call and set interval
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
});
