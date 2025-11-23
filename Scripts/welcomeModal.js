document.addEventListener('DOMContentLoaded', () => {
    const welcomeModal = document.getElementById('welcome-modal');
    const overlay = document.querySelector('.modal-overlay');
    const closeButtonMain = document.querySelector('.close-welcome-button-main');

    // A variável showWelcomeModal é definida no PHP
    const showModal = typeof showWelcomeModal !== 'undefined' && showWelcomeModal;


    if (showModal) {
        welcomeModal.style.display = 'block';
        overlay.style.display = 'block';
    }

    const closeModal = () => {
        welcomeModal.style.display = 'none';
        overlay.style.display = 'none';
    };

    closeButtonMain.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);
});
