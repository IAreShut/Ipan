/**
 * Student Log Entry Show - Lightbox Modal
 */
document.getElementById('imageModal')?.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    document.getElementById('modalImage').src = trigger.dataset.imgSrc;
    document.getElementById('imageModalLabel').textContent = trigger.dataset.imgName;
});
