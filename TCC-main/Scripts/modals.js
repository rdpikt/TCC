//modal-perfil
const userSpan = document.querySelector('.nav-user li:nth-child(2) span');
const modalPerfil = document.querySelector('.modal-perfil');
userSpan.addEventListener('click', () => {
  modalPerfil.classList.toggle('active');
  
});
window.addEventListener('click', (e) => {
  if (!modalPerfil.contains(e.target) && e.target !== userSpan) {
    modalPerfil.classList.remove('active');
  }
});

