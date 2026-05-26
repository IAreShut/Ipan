function openImageModal(imgUrl) {
    document.getElementById('popupImagePreview').src = imgUrl;
    var modal = new bootstrap.Modal(document.getElementById('imagePopupModal'));
    modal.show();
}

function resetFeedFilter() {
    var logItems = document.querySelectorAll('.log-item');
    var weekCells = document.querySelectorAll('.week-cell');
    logItems.forEach(function(item) { item.style.display = ''; });
    weekCells.forEach(function(c) { c.style.transform = ''; c.style.boxShadow = ''; });
    document.getElementById('showAllBtn').classList.add('d-none');
    document.getElementById('weekEmptyState').classList.add('d-none');
    activeWeek = null;
}

var activeWeek = null;

document.addEventListener('DOMContentLoaded', function() {
    var weekCells = document.querySelectorAll('.week-cell');
    var logItems = document.querySelectorAll('.log-item');
    var emptyState = document.getElementById('weekEmptyState');

    weekCells.forEach(function(cell) {
        cell.addEventListener('click', function() {
            var week = this.getAttribute('data-week');

            if (activeWeek === week) {
                resetFeedFilter();
            } else {
                activeWeek = week;
                weekCells.forEach(function(c) {
                    c.style.transform = '';
                    c.style.boxShadow = '';
                });
                this.style.transform = 'scale(1.1)';
                this.style.boxShadow = '0 0 0 3px rgba(30, 64, 175, 0.3)';

                var hasVisible = false;
                logItems.forEach(function(item) {
                    if (item.getAttribute('data-week') === week) {
                        item.style.display = '';
                        hasVisible = true;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Show empty state if no logs for this week
                if (hasVisible) {
                    emptyState.classList.add('d-none');
                } else {
                    emptyState.classList.remove('d-none');
                }

                document.getElementById('showAllBtn').classList.remove('d-none');
            }
        });
    });
});
