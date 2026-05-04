/**
 * Supervisor Tasks Scripts
 * - DataTable initialization for tasks table
 */
$(document).ready(function() {
    $('#tasksTable').DataTable({
        pageLength: 10,
        order: [[ 2, "asc" ]], // Sort by due date ascending
        language: {
            search: 'Search Table:',
            paginate: { previous: '‹', next: '›' },
            emptyTable: "No tasks have been assigned yet."
        }
    });
});
