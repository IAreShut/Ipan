/**
 * Supervisor Dashboard Scripts
 * - DataTable initialization for student list
 */
$(document).ready(function() {
    $('#supervisorStudentTable').DataTable();

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
