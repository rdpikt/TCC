document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.cadastro-form');
  const errorContainer = document.getElementById('error-container');

  const aceitar = document.querySelector("#btnAceitar");
  const checkboxterms = document.querySelector('#terms');

  // se existir algum botão "aceitar" específico, começa desabilitado
  if (aceitar) aceitar.disabled = true;

  // quando marcar/desmarcar o checkbox de termos
  if (checkboxterms) {
    checkboxterms.addEventListener("change", () => {
      if (aceitar) aceitar.disabled = !checkboxterms.checked;
    });
  }

  // ======= MODAL DE TERMOS =======
  const btnAbrirTermos = document.getElementById('abrir-termos');
  const overlayTermos = document.getElementById('terms-overlay');
  const btnFecharTermos = document.getElementById('fechar-termos');
  const navLinks = document.querySelectorAll('.terms-nav-link');
  const termsContent = document.querySelector('.terms-content');
  let sectionOffsets = [];

  function atualizarOffsets() {
    sectionOffsets = [];
    if (!termsContent) return;
    const sections = termsContent.querySelectorAll(':scope > section');
    sections.forEach(sec => {
      sectionOffsets.push({ id: sec.id, offset: sec.offsetTop });
    });
  }

  function abrirModalTermos() {
    if (!overlayTermos) return;
    overlayTermos.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    atualizarOffsets();
  }

  function fecharModalTermos() {
    if (!overlayTermos) return;
    overlayTermos.style.display = 'none';
    document.body.style.overflow = '';
  }

  if (btnAbrirTermos) {
    btnAbrirTermos.addEventListener('click', (e) => {
      e.preventDefault();
      abrirModalTermos();
    });
  }

  if (btnFecharTermos) {
    btnFecharTermos.addEventListener('click', (e) => {
      e.preventDefault();
      fecharModalTermos();
    });
  }

  if (overlayTermos) {
    overlayTermos.addEventListener('click', (e) => {
      if (e.target === overlayTermos) {
        fecharModalTermos();
      }
    });
  }

  // Sidebar: scroll e marca ativo ao clicar
  navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();

      const alvo = link.getAttribute('data-target'); // "#sec-intro"
      const section = document.querySelector(alvo);

      if (section && termsContent) {
        termsContent.scrollTo({
          top: section.offsetTop,
          behavior: 'smooth'
        });
      }

      // troca o active no clique
      navLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
    });
  });

  // Marca o nav ativo conforme rola a área de conteúdo
  if (termsContent && navLinks.length) {
    termsContent.addEventListener('scroll', () => {
      if (!sectionOffsets.length) return;

      const pos = termsContent.scrollTop + 40;
      let currentId = sectionOffsets[0].id;

      sectionOffsets.forEach(sec => {
        if (pos >= sec.offset) currentId = sec.id;
      });

      navLinks.forEach(link => {
        const target = link.getAttribute('data-target'); // "#sec-intro"
        if (target === '#' + currentId) {
          link.classList.add('active');
        } else {
          link.classList.remove('active');
        }
      });
    });
  }

  // ======= ENVIO AJAX DO FORMULÁRIO (SEU CÓDIGO ORIGINAL) =======
  if (form) {
    form.addEventListener('submit', (event) => {
      event.preventDefault(); // Previne o envio padrão

      const formData = new FormData(form);

      fetch('../PHP/cadastro.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          // Limpa erros antigos
          errorContainer.innerHTML = '';
          errorContainer.classList.remove('show');

          if (data.success) {
            window.location.href = data.redirect_url;
          } else {
            if (data.errors && data.errors.length > 0) {
              const errorList = document.createElement('ul');
              data.errors.forEach(errorText => {
                const listItem = document.createElement('li');
                listItem.textContent = errorText;
                errorList.appendChild(listItem);
              });
              errorContainer.appendChild(errorList);
              errorContainer.classList.add('show');

              setTimeout(() => {
                errorContainer.classList.remove('show');
                setTimeout(() => {
                  errorContainer.innerHTML = '';
                }, 500);
              }, 3000);
            }
          }
        })
        .catch(error => {
          console.error('Erro na requisição:', error);
          errorContainer.innerHTML = '<ul><li>Ocorreu um erro de comunicação com o servidor.</li></ul>';
          errorContainer.classList.add('show');
          setTimeout(() => {
            errorContainer.classList.remove('show');
            setTimeout(() => {
              errorContainer.innerHTML = '';
            }, 500);
          }, 3000);
        });
    });
  }
});
