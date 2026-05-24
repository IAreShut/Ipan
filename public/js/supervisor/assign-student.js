document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable.isDataTable('#assignedStudentsTable')) {
        $('#assignedStudentsTable').DataTable().destroy();
    }

    $('#assignedStudentsTable').DataTable({
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search students...",
        },
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [1, 7] }
        ]
    });
});
