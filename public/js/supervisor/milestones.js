/**
 * Supervisor Milestones Scripts
 * - DataTable initialization for milestones table
 */
$(document).ready(function() {
    $('#milestonesTable').DataTable({
        pageLength: 10,
        order: [[ 2, "asc" ]], // Sort by due date ascending
        language: {
            search: 'Search Table:',
            paginate: { previous: '‹', next: '›' },
            emptyTable: "No milestones have been assigned yet."
        }
    });
});
