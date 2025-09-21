const button = document.querySelectorAll('.btn-expandir');
const notificacaoItem = document.querySelectorAll('.notificacao-item');
const btnFechar = document.querySelector('.btn-fechar');
const notificacaoExpandida = document.querySelector('.notificacao-expandida');

notificacaoItem.forEach((item, index) => {
  item.addEventListener('click', () => {
    notificacaoExpandida.classList.add('expandido');

    // Pega os dados do item clicado
    const nome = item.getAttribute('data-nome');
    const conteudo = item.getAttribute('data-conteudo');
    const data = item.getAttribute('data-data');

    // Preenche o modal
    notificacaoExpandida.querySelector('.de').textContent = nome;
    notificacaoExpandida.querySelector('.mensagem').textContent = conteudo;
    notificacaoExpandida.querySelector('.data-expandida').textContent = data;
  });
});

btnFechar.addEventListener('click', () => {
  notificacaoExpandida.classList.remove('expandido');
});

// Fecha ao clicar fora da notificação expandida
window.addEventListener('click', (e) => {
  if (!notificacaoExpandida.contains(e.target) && !e.target.classList.contains('btn-expandir') && !e.target.classList.contains('notificacao-item')) {
    notificacaoExpandida.classList.remove('expandido');
  }
});
// Fecha ao pressionar a tecla Esc
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && notificacaoExpandida.classList.contains('expandido')) {
    notificacaoExpandida.classList.remove('expandido');
    notificacaoExpandida.style.display = 'none';
  }
});



