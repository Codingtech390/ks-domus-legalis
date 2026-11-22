<script>
(function () {
    const modal = document.getElementById('consultation-payment-modal');
    const panel = document.getElementById('consultation-payment-panel');
    const openButtons = document.querySelectorAll(
        '#consultation-payment-open, [data-consultation-open]'
    );
    const closeButton = document.getElementById('consultation-payment-close');

    if (!modal || !panel || !openButtons.length || !closeButton) {
        return;
    }

    let lastOpenedButton = null;

    function openModal(event) {
        lastOpenedButton = event.currentTarget;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';

        closeButton.focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';

        if (lastOpenedButton) {
            lastOpenedButton.focus();
        }
    }

    // Attach the modal to BOTH consultation buttons
    openButtons.forEach(function (button) {
        button.addEventListener('click', openModal);
    });

    closeButton.addEventListener('click', closeModal);

    // Close when clicking outside the modal panel
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Close using Escape key
    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            !modal.classList.contains('hidden')
        ) {
            closeModal();
        }
    });
})();
</script>
