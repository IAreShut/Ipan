function openImageModal(imgUrl) {
    document.getElementById('popupImagePreview').src = imgUrl;
    var modal = new bootstrap.Modal(document.getElementById('imagePopupModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    var weekCells = document.querySelectorAll('.week-cell');
    var logItems = document.querySelectorAll('.log-item');
    var activeWeek = null;

    weekCells.forEach(function(cell) {
        cell.addEventListener('click', function() {
            var week = this.getAttribute('data-week');

            if (activeWeek === week) {
                activeWeek = null;
                this.style.transform = '';
                this.style.boxShadow = '';
                logItems.forEach(function(item) { item.style.display = 'flex'; });
            } else {
                activeWeek = week;
                weekCells.forEach(function(c) {
                    c.style.transform = '';
                    c.style.boxShadow = '';
                });
                this.style.transform = 'scale(1.1)';
                this.style.boxShadow = '0 0 0 3px rgba(30, 64, 175, 0.3)';

                logItems.forEach(function(item) {
                    if (item.getAttribute('data-week') === week) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }
        });
    });
});
