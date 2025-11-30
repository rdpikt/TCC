document.addEventListener('DOMContentLoaded', () => {
  const welcomeModal = document.getElementById('welcome-modal');
  const overlays = document.querySelectorAll('.modal-overlay');
  const overlay = overlays[0];
  const closeButtonMain = document.querySelector('.close-welcome-button-main');
  const checkboxes = document.querySelectorAll('#welcome-modal input[type="checkbox"][name="CC[]"]');
  const LIMITE = 3;

  const showModal = typeof showWelcomeModal !== 'undefined' && showWelcomeModal;

  // cria/pega um span de mensagem logo depois da lista (opcional)
  let limitMsg = document.querySelector('#welcome-limit-msg');
  if (!limitMsg) {
    limitMsg = document.createElement('p');
    limitMsg.id = 'welcome-limit-msg';
    limitMsg.style.color = '#ff8080';
    limitMsg.style.fontSize = '12px';
    limitMsg.style.marginTop = '8px';
    limitMsg.style.textAlign = 'center';
    const ul = welcomeModal.querySelector('ul');
    if (ul && ul.parentNode) {
      ul.parentNode.insertBefore(limitMsg, ul.nextSibling);
    }
  }

  function showLimitMessage() {
    limitMsg.textContent = 'Você pode selecionar somente três conteudos.';
    setTimeout(() => {
      limitMsg.textContent = '';
    }, 2500);
  }

  function updateButtonState() {
    if (!closeButtonMain) return;
    const selecionados = Array.from(checkboxes).filter(cb => cb.checked).length;
    closeButtonMain.disabled = selecionados === 0;
  }

  // limita a 3 e mostra mensagem se tentar passar disso
  checkboxes.forEach(cb => {
    cb.addEventListener('change', () => {
      const selecionados = Array.from(checkboxes).filter(c => c.checked);

      if (selecionados.length > LIMITE) {
        cb.checked = false;
        showLimitMessage();
        return;
      }

      updateButtonState();
    });
  });

  if (closeButtonMain) {
    closeButtonMain.disabled = true;
  }

  if (showModal && welcomeModal && overlay) {
    welcomeModal.style.display = 'flex';
    overlay.style.display = 'block';
  }

  const closeModal = () => {
    if (!welcomeModal || !overlay) return;
    welcomeModal.style.display = 'none';
    overlay.style.display = 'none';
  };

  if (closeButtonMain) {
    closeButtonMain.addEventListener('click', () => {
      closeModal();
    });
  }

  if (overlay) {
    overlay.addEventListener('click', closeModal);
  }
});
