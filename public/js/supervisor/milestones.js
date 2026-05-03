/**
 * Supervisor Milestones Scripts
 * - DataTable initialization for milestones table
 */
$(document).ready(function() {
    const table = $('#milestonesTable').DataTable({
        pageLength: 10,
        order: [[ 2, "asc" ]], // Sort by due date ascending
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search milestones...",
            paginate: { previous: '‹', next: '›' },
            emptyTable: "No milestones have been assigned yet."
        }
    });

    // Inject the "+ Add New Milestone" button to the left of the search input
    const addButton = `
        <button type="button" class="btn btn-primary-custom btn-md me-2 px-3 rounded-pill text-nowrap" data-bs-toggle="modal" data-bs-target="#addMilestoneModal" title="Add New Milestone">
            <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add New Milestone</span>
        </button>
    `;
    
    // Find the DataTables filter container and make it responsive
    const filterContainer = $('.dataTables_filter');
    filterContainer.addClass('d-flex align-items-center justify-content-end flex-wrap gap-2 mb-3');
    filterContainer.prepend(addButton);
    
    // Style the search input — responsive max-width instead of fixed width
    $('.dataTables_filter input').addClass('form-control form-control-sm border-secondary-subtle').css({
        'max-width': '250px',
        'min-width': '120px',
        'display': 'inline-block',
        'margin-left': '0'
    });
});
