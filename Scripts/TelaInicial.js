// modais.js
// =======================================================
// FUNÇÕES GLOBAIS DE CONTROLE DE MODAIS (Perfil e Opções)
// Versão corrigida e robusta — Ingrid
// =======================================================

/*
  Recursos:
  - Cria .modal-other-perfil-container automaticamente se não existir (permite uso em qualquer página).
  - Fetch com fallback de caminho para PHP.
  - Modal de opções (3 pontos) com posicionamento exato, tratamento de overflow e fechamento por clique fora / ESC.
  - Injeta CSS para modal de opções (pode ser extraído para .css se preferir).
*/

(function () {
    'use strict';
  
    // ----------------------------
    // INJEÇÃO DE CSS (opçõesPost modal)
    // ----------------------------
    const injectedCSS = `
  /* Modal Opções - injetado por modais.js */
  .modal-opcoes-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0); /* overlay transparente - impede interação com fundo */
      z-index: 1500;
  }
  
  .modal-opcoes-post {
      position: fixed; /* fixed para coordenadas de tela absolutas */
      background-color: #1a1a1a;
      border-radius: 12px;
      box-shadow: 0 6px 30px rgba(0,0,0,0.45);
      overflow: hidden;
      z-index: 2000;
      animation: fadeIn 0.12s ease-out;
      min-width: 180px;
      max-width: 320px;
  }
  
  .modal-opcoes-post ul {
      list-style: none;
      padding: 0;
      margin: 0;
  }
  
  .modal-opcoes-post li {
      padding: 12px 16px;
      font-size: 0.95rem;
      color: #f1f1f1;
      cursor: pointer;
      transition: background-color 0.14s;
      border-bottom: 1px solid rgba(255,255,255,0.04);
      text-align: start;
  }
  
  .modal-opcoes-post li:last-child {
      border-bottom: none;
  }
  
  .modal-opcoes-post li:hover {
      background-color: rgba(255,255,255,0.03);
  }
  
  .modal-opcoes-post li.delete-option {
      color: #ff6b6b;
      font-weight: 600;
  }
  
  .modal-opcoes-post li.delete-option:hover {
      background-color: rgba(255, 107, 107, 0.08);
  }
  
  @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-6px) scale(0.99); }
      to { opacity: 1; transform: translateY(0) scale(1); }
  }
  
  /* Optional: small responsive tweak */
  @media (max-width: 480px) {
    .modal-opcoes-post { right: 8px !important; left: auto !important; top: 8px !important; min-width: 140px; }
  }
    `;
    // inject style once
    if (!document.getElementById('modais-js-styles')) {
      const styleEl = document.createElement('style');
      styleEl.id = 'modais-js-styles';
      styleEl.appendChild(document.createTextNode(injectedCSS));
      document.head.appendChild(styleEl);
    }
  
    // ----------------------------
    // HELPERS
    // ----------------------------
  
    // Retorna basePath para os PHP (tenta usar caminho relativo e fallback)
    function phpPath(filename) {
      // tenta dois caminhos comuns: ../PHP/... e /PHP/...
      // Se sua estrutura for diferente, ajuste aqui.
      const candidate1 = `../PHP/${filename}`;
      const candidate2 = `/PHP/${filename}`;
      return {candidate1, candidate2};
    }
  
    // Faz fetch com fallback entre dois caminhos (retorna objeto json ou lança)
    async function fetchJsonWithFallback(filename, options) {
      const {candidate1, candidate2} = phpPath(filename);
  
      try {
        const r1 = await fetch(candidate1, options);
        if (r1.ok) return await r1.json();
      } catch (err) {
        // continua para tentar candidate2
        console.warn(`fetch failed for ${candidate1}:`, err);
      }
  
      try {
        const r2 = await fetch(candidate2, options);
        if (r2.ok) return await r2.json();
        // se não ok, tenta ler json mesmo assim (para pegar mensagens de erro)
        try {
          return await r2.json();
        } catch (e) {
          throw new Error(`Erro ao acessar ${candidate2} (status ${r2.status})`);
        }
      } catch (err) {
        throw err;
      }
    }
  
    // ----------------------------
    // CONTAINER DO MODAL DE PERFIL (cria automaticamente quando necessário)
    // ----------------------------
    function ensureModalPerfilContainer() {
      let container = document.querySelector('.modal-other-perfil-container');
      if (!container) {
        container = document.createElement('div');
        container.className = 'modal-other-perfil-container';
        // Opcional: estilo básico para overlay do perfil (você já tem o CSS no projeto)
        // container.style.position = 'fixed'; container.style.top = 0; ...
        document.body.appendChild(container);
      }
      return container;
    }
  
    // Seleciona o container (cria se não houver)
    let ModalOtherPerfil = document.querySelector(".modal-other-perfil-container");
    if (!ModalOtherPerfil) ModalOtherPerfil = ensureModalPerfilContainer();
  
    // ----------------------------
    // FUNÇÕES DE MODAL DE PERFIL
    // ----------------------------
    function abrirModalPerfil() {
      ModalOtherPerfil = document.querySelector(".modal-other-perfil-container") || ensureModalPerfilContainer();
      if (!ModalOtherPerfil) return;
      ModalOtherPerfil.classList.add("active");
      // trava rolagem do body
      document.body.style.overflow = "hidden";
    }
  
    function fecharModalPerfil() {
      ModalOtherPerfil = document.querySelector(".modal-other-perfil-container");
      if (!ModalOtherPerfil) return;
      ModalOtherPerfil.classList.remove("active");
      document.body.style.overflow = "auto";
      ModalOtherPerfil.innerHTML = ''; // Limpa o conteúdo ao fechar
    }
  
    async function carregarPerfilOutroUsuario(idUsuario) {
      ModalOtherPerfil = document.querySelector(".modal-other-perfil-container") || ensureModalPerfilContainer();
      if (!ModalOtherPerfil) return;
  
      try {
        const data = await fetchJsonWithFallback(`carregar_perfil.php?userID=${encodeURIComponent(idUsuario)}`, { method: 'GET' });
        if (!data || !data.success) {
          const msg = data && data.message ? data.message : "Resposta inválida do servidor.";
          ModalOtherPerfil.innerHTML = `<div class="modal-other-perfil-content-wrapper"><p style="padding:20px; color:white;">Erro ao carregar perfil: ${msg}</p></div>`;
          return;
        }
  
        const u = data.usuario || {};
        const posts = data.posts || [];
  
        const rightColumnHTML = `
              <div class="modal-right-column">
                  <div class="seguidores-suggestions">
                      <div class="titulo">
                          <p>Sugestão de Artistas</p>
                          <a href="#">Ver mais</a>
                      </div>
                      <ul class="sugestoes">
                          <li>
                              <img src="../images/avatares/Users/padrao.png" alt="avatar">
                              <div>
                                  <strong>KAWS</strong>
                                  <span style="color:#71767b">@kaws</span>
                              </div>
                              <button>Seguir</button>
                          </li>
                          <li>
                              <img src="../images/avatares/Users/padrao.png" alt="avatar">
                              <div>
                                  <strong>Takashi</strong>
                                  <span style="color:#71767b">@takashipom</span>
                              </div>
                              <button>Seguir</button>
                          </li>
                          <li>
                              <img src="../images/avatares/Users/padrao.png" alt="avatar">
                              <div>
                                  <strong>Jeff Koons</strong>
                                  <span style="color:#71767b">@jeffkoons</span>
                              </div>
                              <button>Seguir</button>
                          </li>
                      </ul>
                      <div style="font-size: 0.8rem; color: #71767b; margin-top: 10px; line-height: 1.5;">
                          Regras do HarpHub · Política de Privacidade<br>
                          © 2025 HARPHUB
                      </div>
                  </div>
              </div>`;
  
        const profileHTML = `
              <div class="modal-other-perfil-content-wrapper">
                  <div class="modal-other-perfil">
                      <div class="close-button modal-other-fechar" role="button" aria-label="Fechar">
                          <i class="fas fa-arrow-left"></i>
                      </div>
                      <div class="modal-other-header">
                          <div class="header-top-row">
                              <div class="modal-other-avatar">
                                  <img src="../images/avatares/Users/${u.avatar || 'profile.png'}" alt="avatar">
                              </div>
                              ${u.jaSegue !== null && u.jaSegue !== undefined ? `
                                  <button class="seguir-perfil-btn" data-target-id="${u.id}">
                                      ${u.jaSegue ? "Seguindo" : "Seguir"}
                                  </button>
                              ` : ''}
                          </div>
                          <div class="modal-other-username">
                              <strong class="nome-completo">${u.nome_completo || ''}</strong>
                              <span class="user-tag">@${u.tipo || ''}</span>
                          </div>
                          <p class="bio-user">${u.bio || "Sem biografia."}</p>
                          <div class="follow-info">
                              <span><strong>${u.seguindo || 0}</strong> Seguindo</span>
                              <span><strong>${u.seguidores || 0}</strong> Seguidores</span>
                          </div>
                      </div>
                      <ul class="modal-other-categorys">
                          <li class="aba active" data-aba="posts">Posts</li>
                          <li class="aba" data-aba="reposts">Repost</li>
                          <li class="aba" data-aba="salvos">Salvos</li>
                          <li class="aba" data-aba="curtidas">Curtidas</li>
                      </ul>
                      <div class="modal-other-content">
                          <div class="aba-content" id="aba-posts" style="display: grid;"></div>
                          <div class="aba-content" id="aba-reposts" style="display:none"></div>
                          <div class="aba-content" id="aba-salvos" style="display:none"></div>
                          <div class="aba-content" id="aba-curtidas" style="display:none"></div>
                      </div>
                  </div>
              </div>`;
  
        ModalOtherPerfil.innerHTML = profileHTML + rightColumnHTML;
        carregarPostsNaAba(posts, "aba-posts");
      } catch (err) {
        console.error("Falha ao carregar perfil:", err);
        ModalOtherPerfil.innerHTML = `<div class="modal-other-perfil-content-wrapper"><p style="padding:20px; color:white;">Erro técnico ao carregar.</p></div>`;
      }
    }
  
    function carregarPostsNaAba(lista, idAba) {
      const aba = document.getElementById(idAba);
      if (!aba) return;
      if (!lista || !lista.length) {
        aba.innerHTML = `<p style="text-align:center; padding:20px; color:#71767b; grid-column: 1/-1;">Nenhum post encontrado.</p>`;
        return;
      }
      aba.innerHTML = lista.map(post => `
          <div class="post-item-modal">
              <img src="../images/uploads/${post.imagemUrl}" alt="post image">
          </div>
      `).join("");
    }
  
    async function atualizarContadoresSeguidores(idUsuario, modalElement) {
      try {
        const data = await fetchJsonWithFallback(`contador_seguidores.php?id=${encodeURIComponent(idUsuario)}`, { method: 'GET' });
        if (data && data.success && modalElement) {
          const followInfo = modalElement.querySelector(".follow-info");
          if (followInfo) {
            followInfo.innerHTML = `
              <span><strong>${data.seguindo}</strong> Seguindo</span>
              <span><strong>${data.seguidores}</strong> Seguidores</span>
            `;
          }
        }
      } catch (err) {
        console.error("Erro ao atualizar contadores:", err);
      }
    }
  
    // ----------------------------
    // FUNÇÕES DE CONTROLE DO MODAL DE OPÇÕES DE POST
    // ----------------------------
    function fecharModalOpcoesPost() {
      const modalOverlay = document.querySelector(".modal-opcoes-overlay");
      if (modalOverlay) modalOverlay.remove();
      // remove listener de keydown se necessário (checado abaixo).
    }
  
    function abrirModalOpcoesPost(postId, menuButton) {
      // Fecha qualquer modal de opções já aberto
      fecharModalOpcoesPost();
  
      const postEl = document.querySelector(`.posts[data-post-id='${postId}']`);
      if (!postEl || !menuButton) return;
  
      const postOwnerId = postEl.dataset.userId;
      const isOwner = typeof loggedInUserId !== 'undefined' && loggedInUserId && postOwnerId === String(loggedInUserId);
  
      // overlay
      const modalOverlay = document.createElement("div");
      modalOverlay.className = "modal-opcoes-overlay";
      modalOverlay.setAttribute('role', 'dialog');
      modalOverlay.setAttribute('aria-modal', 'true');
  
      // content
      const modalContent = document.createElement("div");
      modalContent.className = "modal-opcoes-post";
      modalContent.setAttribute('tabindex', '-1');
  
      const optionsList = document.createElement("ul");
      optionsList.innerHTML = `
          <li class="report-option" data-post-id="${postId}">Denunciar</li>
          <li class="hide-option" data-post-id="${postId}">Ocultar Post</li>
      `;
  
      if (isOwner) {
        const deleteOption = document.createElement("li");
        deleteOption.className = "delete-option";
        deleteOption.dataset.postId = postId;
        deleteOption.textContent = "Apagar post";
        optionsList.appendChild(deleteOption);
      }
  
      modalContent.appendChild(optionsList);
      modalOverlay.appendChild(modalContent);
      document.body.appendChild(modalOverlay);
  
      // POSICIONAMENTO: usamos getBoundingClientRect do botão,
      // colocamos o menu logo abaixo do botão e ajustamos se ultrapassar bordas.
      const rect = menuButton.getBoundingClientRect();
      // posição inicial (referência na viewport)
      let left = rect.left;
      let top = rect.bottom + 6; // gap de 6px
  
      // força render para obter dimensões do modalContent
      // OBS: offsetWidth só estará disponível agora que está no DOM
      const modalRect = modalContent.getBoundingClientRect();
      let modalWidth = modalRect.width || modalContent.offsetWidth;
      let modalHeight = modalRect.height || modalContent.offsetHeight;
  
      const viewportWidth = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
      const viewportHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
  
      // Se o modal estiver saindo pela direita, tenta alinhar à direita do rect
      if (left + modalWidth > viewportWidth - 8) {
        // tenta alinhar pelo lado direito do botão
        const rightAlignedLeft = rect.right - modalWidth;
        // se couber alinhado à direita do botão, usa; senão limita à borda
        left = (rightAlignedLeft >= 8) ? rightAlignedLeft : (viewportWidth - modalWidth - 8);
      }
  
      // Se o modal estiver saindo pela parte inferior, posiciona acima do botão
      if (top + modalHeight > viewportHeight - 8) {
        const aboveTop = rect.top - modalHeight - 6;
        top = (aboveTop > 8) ? aboveTop : (viewportHeight - modalHeight - 8);
      }
  
      // aplica posição
      modalContent.style.left = `${Math.max(8, left)}px`;
      modalContent.style.top = `${Math.max(8, top)}px`;
  
      // fechar ao clicar fora (overlay) — já inserido
      modalOverlay.addEventListener('click', (ev) => {
        if (ev.target === modalOverlay) fecharModalOpcoesPost();
      });
  
      // fechar ao pressionar ESC
      function onKeyDown(e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
          fecharModalOpcoesPost();
          document.removeEventListener('keydown', onKeyDown);
        }
      }
      document.addEventListener('keydown', onKeyDown);
    }
  
    // ----------------------------
    // EVENT LISTENERS DO DOM (inicialização)
    // ----------------------------
    document.addEventListener('DOMContentLoaded', () => {
      // Seletores principais
      const modalPost = document.querySelector('.modal-post');
      const modalPostContent = document.querySelector('.modal-post-content');
      const closeModalButton = document.querySelector('.modal-post .close-button');
      const posts = document.querySelectorAll('.posts');
      const modalOverlay = document.querySelector('.modal-overlay');
  
      let commentsInterval;
  
      // ------------------------------------------
      // LÓGICA DO MODAL DE POST (Lightbox)
      // ------------------------------------------
      posts.forEach(post => {
        const imgPost = post.querySelector('.img-post');
  
        if (imgPost) {
          imgPost.addEventListener('click', (e) => {
            e.stopPropagation();
  
            const postId = post.dataset.postId;
            const userName = post.dataset.userName;
            const nome_completo = post.dataset.userNameCompleto;
            const tagsData = post.dataset.tags;
            const imagemUrl = post.dataset.imagemUrl;
            const userAvatar = post.dataset.userAvatar;
            const descricao = post.dataset.descricao;
            const countCurtidas = post.dataset.countCurtidas;
  
            const tags = (tagsData && tagsData.trim() !== '') ? JSON.parse(tagsData) : [];
            const tagsHtml = tags.map(tag => `<li>${tag}</li>`).join('');
  
            if (!modalPost || !modalPostContent) return;
  
            modalPostContent.innerHTML = `
              <div class="modal-image-container">
                  ${imagemUrl ? `<img src="../images/uploads/${imagemUrl}" class="post-image" alt="post image">` : ''}
              </div>
              <div class="modal-sidebar">
                  <div class="modal-post-header">
                      <img src="../images/avatares/Users/${userAvatar}" class="user-avatar" alt="user avatar">
                      <span class="user-name">@${userName}</span>
                  </div>
  
                  <div class="modal-post-details">
                      <p><strong>${nome_completo}</strong> ${descricao || ''}</p>
                      <ul>${tagsHtml}</ul>
                  </div>
  
                  <div id="comments-list" class="modal-comments-list"></div>
  
                  <div class="comment-form-container">
                      <textarea id="new-comment-content" placeholder="Escreva um comentário..."></textarea>
                      <button id="submit-comment-btn" data-post-id="${postId}">Comentar</button>
                  </div>
              </div>`;
  
            if (typeof loadComments === 'function') {
              loadComments(postId);
              if (commentsInterval) clearInterval(commentsInterval);
              commentsInterval = setInterval(() => loadComments(postId), 10000);
            }
  
            modalPost.classList.add('active');
            if (modalOverlay) modalOverlay.classList.add('active');
          });
        }
      });
  
      if (closeModalButton) {
        closeModalButton.addEventListener('click', () => {
          if (modalPost) modalPost.classList.remove('active');
          if (modalOverlay) modalOverlay.classList.remove('active');
          if (commentsInterval) clearInterval(commentsInterval);
        });
      }
  
      window.addEventListener('click', (e) => {
        if (e.target === modalPost || e.target === modalOverlay) {
          if (modalPost) modalPost.classList.remove('active');
          if (modalOverlay) modalOverlay.classList.remove('active');
          if (commentsInterval) clearInterval(commentsInterval);
        }
      });
  
      // ---------------------------------------------------------
      // LÓGICA DE EVENTOS PARA O MODAL DE OPÇÕES DE POST
      // ---------------------------------------------------------
      document.addEventListener("click", (e) => {
        // procura pelo botão de menu — #menu-dots (pode ser id ou classe em sua marcação)
        const menuButton = e.target.closest("#menu-dots, .menu-dots, [data-menu-dots]");
        if (menuButton) {
          e.stopPropagation();
          const postEl = menuButton.closest(".posts");
          if (postEl) {
            const postId = postEl.dataset.postId;
            abrirModalOpcoesPost(postId, menuButton);
          }
          return;
        }
  
        // Fecha modal-opcoes se clicar no overlay (fora do conteúdo)
        const modalOverlayTarget = e.target.closest(".modal-opcoes-overlay");
        const cancelOption = e.target.closest(".cancel-option");
        if ((modalOverlayTarget && e.target === modalOverlayTarget) || cancelOption) {
          fecharModalOpcoesPost();
        }
      });
  
      // ---------------------------------------------------------
      // LÓGICA PARA APAGAR O POST (delegation)
      // ---------------------------------------------------------
      document.addEventListener("click", async (e) => {
        const deleteButton = e.target.closest(".delete-option");
        if (!deleteButton) return;
  
        const postId = deleteButton.dataset.postId;
        if (!postId) return;
  
        const userConfirmed = confirm("Tem certeza de que deseja apagar este post? Esta ação não pode ser desfeita.");
  
        if (!userConfirmed) {
          fecharModalOpcoesPost();
          return;
        }
  
        const formData = new FormData();
        formData.append("postId", postId);
  
        try {
          // usa fetchJsonWithFallback para acertar caminho
          const {candidate1, candidate2} = phpPath('apagar_post.php');
          // tenta first
          let result = null;
          try {
            const resp1 = await fetch(candidate1, { method: 'POST', body: formData });
            result = await resp1.json();
          } catch (err) {
            try {
              const resp2 = await fetch(candidate2, { method: 'POST', body: formData });
              result = await resp2.json();
            } catch (err2) {
              throw err2;
            }
          }
  
          if (result && result.success) {
            const postElement = document.querySelector(`.posts[data-post-id='${postId}']`);
            if (postElement) postElement.remove();
            alert(result.message || "Post apagado com sucesso!");
          } else {
            alert((result && result.message) ? result.message : "Ocorreu um erro ao tentar apagar o post.");
          }
        } catch (error) {
          console.error("Erro na requisição para apagar post:", error);
          alert("Erro de comunicação com o servidor.");
        } finally {
          fecharModalOpcoesPost();
        }
      });
  
      // ---------------------------------------------------------
      // LÓGICA DE EVENTOS PARA O MODAL DE PERFIL
      // ---------------------------------------------------------
  
      // Evento para FECHAR modal de perfil (botão de fechar)
      document.addEventListener("click", (e) => {
        if (e.target.closest(".modal-other-fechar")) {
          fecharModalPerfil();
        }
      });
  
      // Evento para ABRIR modal de perfil (clicando no avatar de um post)
      document.addEventListener("click", async (e) => {
        const avatar = e.target.closest('.avatar-desc img, .avatar-desc, [data-avatar-post]');
        if (!avatar) return;
  
        const postEl = avatar.closest(".posts");
        if (!postEl) return;
  
        const idUsuario = postEl.dataset.userId;
        if (!idUsuario) return;
  
        abrirModalPerfil();
        await carregarPerfilOutroUsuario(idUsuario);
      });
  
      // Evento para TROCAR DE ABA (perfil modal)
      document.addEventListener("click", (e) => {
        const aba = e.target.closest(".aba");
        if (!aba) return;
  
        const modalWrapper = aba.closest('.modal-other-perfil-content-wrapper');
        if (!modalWrapper) return;
  
        modalWrapper.querySelectorAll(".aba").forEach(a => a.classList.remove("active"));
        aba.classList.add("active");
  
        const nomeAba = aba.dataset.aba;
        modalWrapper.querySelectorAll(".aba-content").forEach(c => c.style.display = "none");
        const abaContent = modalWrapper.querySelector(`#aba-${nomeAba}`);
        if (abaContent) {
          abaContent.style.display = (nomeAba === 'posts') ? 'grid' : 'block';
        }
      });
  
      // Evento para SEGUIR / DEIXAR DE SEGUIR
      document.addEventListener("click", async (e) => {
        const btn = e.target.closest(".seguir-perfil-btn");
        if (!btn) return;
  
        const usuarioID = btn.dataset.targetId;
        const formData = new FormData();
        formData.append("userID", usuarioID);
  
        try {
          // usa fetch com fallback
          const {candidate1, candidate2} = phpPath('seguir_usuario.php');
          let data = null;
          try {
            const resp1 = await fetch(candidate1, { method: 'POST', body: formData });
            data = await resp1.json();
          } catch (err) {
            const resp2 = await fetch(candidate2, { method: 'POST', body: formData });
            data = await resp2.json();
          }
  
          if (data && data.success) {
            btn.textContent = data.acao === "seguido" ? "Seguindo" : "Seguir";
            atualizarContadoresSeguidores(usuarioID, btn.closest('.modal-other-perfil'));
          }
        } catch (err) {
          console.error("Erro ao seguir:", err);
        }
      });
  
    }); // DOMContentLoaded
  
  })(); // IIFE
  