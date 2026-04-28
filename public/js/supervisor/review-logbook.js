/**
 * Supervisor Review Logbook Scripts
 * - Image lightbox modal
 */
document.getElementById('svImageModal')?.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    document.getElementById('svModalImage').src = trigger.dataset.imgSrc;
    document.getElementById('svImageModalLabel').textContent = trigger.dataset.imgName;
});
