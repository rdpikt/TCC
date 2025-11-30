// modais.js
// =======================================================
// CORREÇÃO: Caminhos ajustados para pasta /TCC/ e variável 'id'
// =======================================================

(function () {
    'use strict';
  
    // ----------------------------
    // 1. INJEÇÃO DE CSS
    // ----------------------------
    const injectedCSS = `
  .modal-opcoes-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0); z-index: 1500; }
  .modal-opcoes-post { position: fixed; background-color: #1a1a1a; border-radius: 12px; box-shadow: 0 6px 30px rgba(0,0,0,0.45); overflow: hidden; z-index: 2000; animation: fadeIn 0.12s ease-out; min-width: 180px; max-width: 320px; }
  .modal-opcoes-post ul { list-style: none; padding: 0; margin: 0; }
  .modal-opcoes-post li { padding: 12px 16px; font-size: 0.95rem; color: #f1f1f1; cursor: pointer; transition: background-color 0.14s; border-bottom: 1px solid rgba(255,255,255,0.04); text-align: start; }
  .modal-opcoes-post li:last-child { border-bottom: none; }
  .modal-opcoes-post li:hover { background-color: rgba(255,255,255,0.03); }
  .modal-opcoes-post li.delete-option { color: #ff6b6b; font-weight: 600; }
  .modal-opcoes-post li.delete-option:hover { background-color: rgba(255, 107, 107, 0.08); }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px) scale(0.99); } to { opacity: 1; transform: translateY(0) scale(1); } }
  @media (max-width: 480px) { .modal-opcoes-post { right: 8px !important; left: auto !important; top: 8px !important; min-width: 140px; } }
    `;
    
    if (!document.getElementById('modais-js-styles')) {
      const styleEl = document.createElement('style');
      styleEl.id = 'modais-js-styles';
      styleEl.appendChild(document.createTextNode(injectedCSS));
      document.head.appendChild(styleEl);
    }
  
    // ----------------------------
    // 2. HELPERS (CAMINHOS CORRIGIDOS PARA /TCC/)
    // ----------------------------
    function phpPath(filename) {
      // CORREÇÃO: Adicionada a pasta /TCC/ nos caminhos
      const candidate1 = `../PHP/${filename}`;      // Relativo (se estiver em /TCC/pages/)
      const candidate2 = `/TCC/PHP/${filename}`;    // Absoluto (se estiver na raiz)
      return {candidate1, candidate2};
    }
  
    async function fetchJsonWithFallback(filename, options) {
      const {candidate1, candidate2} = phpPath(filename);
      try {
        const r1 = await fetch(candidate1, options);
        if (r1.ok) return await r1.json();
      } catch (err) { } // silencia erro da rota 1
  
      try {
        const r2 = await fetch(candidate2, options);
        // Tenta ler o JSON mesmo com erro para pegar msg do PHP
        const text = await r2.text(); 
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("Erro ao ler JSON do PHP:", text); // Mostra o erro HTML no console se houver
            throw new Error("Resposta inválida do servidor (HTML em vez de JSON).");
        }
      } catch (err) {
        throw err;
      }
    }
  
    // ----------------------------
    // 3. CONTAINER DO MODAL DE PERFIL
    // ----------------------------
    function ensureModalPerfilContainer() {
      let container = document.querySelector('.modal-other-perfil-container');
      if (!container) {
        container = document.createElement('div');
        container.className = 'modal-other-perfil-container';
        document.body.appendChild(container);
      }
      return container;
    }
  
    let ModalOtherPerfil = document.querySelector(".modal-other-perfil-container");
    if (!ModalOtherPerfil) ModalOtherPerfil = ensureModalPerfilContainer();
  
    // ----------------------------
    // 4. FUNÇÕES DE MODAL DE PERFIL
    // ----------------------------
    function abrirModalPerfil() {
      ModalOtherPerfil = document.querySelector(".modal-other-perfil-container") || ensureModalPerfilContainer();
      if (!ModalOtherPerfil) return;
      ModalOtherPerfil.classList.add("active");
      document.body.style.overflow = "hidden";
    }
  
    function fecharModalPerfil() {
      ModalOtherPerfil = document.querySelector(".modal-other-perfil-container");
      if (!ModalOtherPerfil) return;
      ModalOtherPerfil.classList.remove("active");
      document.body.style.overflow = "auto";
      ModalOtherPerfil.innerHTML = ''; 
    }
  
    async function carregarPerfilOutroUsuario(idUsuario) {
      ModalOtherPerfil = document.querySelector(".modal-other-perfil-container") || ensureModalPerfilContainer();
      if (!ModalOtherPerfil) return;
  
      try {
        const data = await fetchJsonWithFallback(`carregar_perfil.php?userID=${encodeURIComponent(idUsuario)}`, { method: 'GET' });
        
        if (!data || !data.success) {
          const msg = (data && data.message) ? data.message : "Não foi possível carregar o perfil.";
          ModalOtherPerfil.innerHTML = `<div class="modal-other-perfil-content-wrapper"><p style="padding:20px; color:white;">Erro: ${msg}</p></div>`;
          return;
        }
  
        const u = data.usuario || {};
        const posts = data.posts || [];
  
        const rightColumnHTML = `
              <div class="modal-right-column">
                  <div class="seguidores-suggestions">
                      <div class="titulo"><p>Sugestão de Artistas</p></div>
                      <ul class="sugestoes">
                          </ul>
                      <div style="font-size: 0.8rem; color: #71767b; margin-top: 10px;">© 2025 HARPHUB</div>
                  </div>
              </div>`;
  
        const profileHTML = `
              <div class="modal-other-perfil-content-wrapper">
                  <div class="modal-other-perfil">
                      <div class="close-button modal-other-fechar"><i class="fas fa-arrow-left"></i></div>
                      <div class="modal-other-header">
                          <div class="header-top-row">
                              <div class="modal-other-avatar"><img src="../images/avatares/Users/${u.avatar || 'profile.png'}" alt="avatar"></div>
                              ${u.jaSegue !== null && u.jaSegue !== undefined ? `
                                  <button class="seguir-perfil-btn" id="btn-seguir" data-target-id="${u.id}">
                                      ${u.jaSegue ? "Seguindo" : "Seguir"}
                                  </button>` : ''}
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
        
        const btnSeguir = ModalOtherPerfil.querySelector('.seguir-perfil-btn');
        if (btnSeguir && btnSeguir.textContent.trim() === "Seguindo") {
            btnSeguir.classList.add("seguindo");
        }
  
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
          <div class="post-item-modal"><img src="../images/uploads/${post.imagemUrl}" alt="post image"></div>
      `).join("");
    }
  
    async function atualizarContadoresSeguidores(idUsuario, modalElement) {
      try {
        const data = await fetchJsonWithFallback(`contador_seguidores.php?id=${encodeURIComponent(idUsuario)}`, { method: 'GET' });
        if (data && data.success && modalElement) {
          const followInfo = modalElement.querySelector(".follow-info");
          if (followInfo) {
            followInfo.innerHTML = `<span><strong>${data.seguindo}</strong> Seguindo</span><span><strong>${data.seguidores}</strong> Seguidores</span>`;
          }
        }
      } catch (err) {}
    }
  
    // ----------------------------
    // 5. MODAL OPÇÕES E APAGAR
    // ----------------------------
    function fecharModalOpcoesPost() {
      const m = document.querySelector(".modal-opcoes-overlay");
      if (m) m.remove();
    }
  
    function abrirModalOpcoesPost(postId, menuButton) {
      fecharModalOpcoesPost();
      const postEl = document.querySelector(`.posts[data-post-id='${postId}']`);
      if (!postEl || !menuButton) return;
      const postOwnerId = postEl.dataset.userId;
      const isOwner = typeof loggedInUserId !== 'undefined' && loggedInUserId && postOwnerId === String(loggedInUserId);
  
      const modalOverlay = document.createElement("div");
      modalOverlay.className = "modal-opcoes-overlay";
      const modalContent = document.createElement("div");
      modalContent.className = "modal-opcoes-post";
      
      const ul = `<ul><li class="report-option" data-post-id="${postId}">Denunciar</li><li class="hide-option" data-post-id="${postId}">Ocultar Post</li>${isOwner ? `<li class="delete-option" data-post-id="${postId}">Apagar post</li>` : ''}</ul>`;
      modalContent.innerHTML = ul;
      
      modalOverlay.appendChild(modalContent);
      document.body.appendChild(modalOverlay);
  
      const rect = menuButton.getBoundingClientRect();
      let left = rect.left; let top = rect.bottom + 6;
      const modalRect = modalContent.getBoundingClientRect();
      
      if (left + modalRect.width > window.innerWidth - 8) left = (rect.right - modalRect.width >= 8) ? rect.right - modalRect.width : window.innerWidth - modalRect.width - 8;
      if (top + modalRect.height > window.innerHeight - 8) top = (rect.top - modalRect.height - 6 > 8) ? rect.top - modalRect.height - 6 : window.innerHeight - modalRect.height - 8;
      
      modalContent.style.left = `${Math.max(8, left)}px`; modalContent.style.top = `${Math.max(8, top)}px`;
      modalOverlay.addEventListener('click', (ev) => { if (ev.target === modalOverlay) fecharModalOpcoesPost(); });
    }
  
    // ----------------------------
    // 6. EVENT LISTENERS
    // ----------------------------
    document.addEventListener('DOMContentLoaded', () => {
      
      const modalPost = document.querySelector('.modal-post');
      const modalOverlayMain = document.querySelector('.modal-overlay');
      const posts = document.querySelectorAll('.posts');
      let commentsInterval;
  
      posts.forEach(post => {
        const imgPost = post.querySelector('.img-post');
        if (imgPost) {
          imgPost.addEventListener('click', (e) => {
            e.stopPropagation();
            const postId = post.dataset.postId;
            if (!modalPost) return;
            // Preencher modal aqui (resumido para caber)
            const modalContent = document.querySelector('.modal-post-content');
            modalContent.innerHTML = `
              <div class="modal-image-container"><img src="../images/uploads/${post.dataset.imagemUrl}" class="post-image"></div>
              <div class="modal-sidebar"><div class="modal-post-header"><span class="user-name">@${post.dataset.userName}</span></div><div id="comments-list"></div></div>`;
            
            if (typeof loadComments === 'function') {
                loadComments(postId);
                if (commentsInterval) clearInterval(commentsInterval);
                commentsInterval = setInterval(() => loadComments(postId), 10000);
            }
            modalPost.classList.add('active');
            if (modalOverlayMain) modalOverlayMain.classList.add('active');
          });
        }
      });
  
      const fecharLightbox = () => {
        if (modalPost) modalPost.classList.remove('active');
        if (modalOverlayMain) modalOverlayMain.classList.remove('active');
        if (commentsInterval) clearInterval(commentsInterval);
      };
      const closeBtn = document.querySelector('.modal-post .close-button');
      if (closeBtn) closeBtn.addEventListener('click', fecharLightbox);
      window.addEventListener('click', (e) => { if (e.target === modalPost || e.target === modalOverlayMain) fecharLightbox(); });
  
      document.addEventListener("click", (e) => {
        const menuButton = e.target.closest("#menu-dots, .menu-dots, [data-menu-dots]");
        if (menuButton) abrirModalOpcoesPost(menuButton.closest(".posts").dataset.postId, menuButton);
      });
  
      // APAGAR POST
      document.addEventListener("click", async (e) => {
        const deleteButton = e.target.closest(".delete-option");
        if (!deleteButton) return;
        const postId = deleteButton.dataset.postId;
        if (!confirm("Tem certeza?")) return;
        
        const formData = new FormData(); formData.append("postId", postId);
        try {
            const {candidate1, candidate2} = phpPath('apagar_post.php');
            // Tenta fetch na rota 2 (absoluta /TCC/)
            const r = await fetch(candidate2, { method: 'POST', body: formData });
            const result = await r.json();
            if (result.success) { 
                document.querySelector(`.posts[data-post-id='${postId}']`)?.remove();
                fecharModalOpcoesPost();
            } else { alert(result.message); }
        } catch (error) { alert("Erro ao apagar."); }
      });
  
      // PERFIL E SEGUIR
      document.addEventListener("click", (e) => {
        if (e.target.closest(".modal-other-fechar")) fecharModalPerfil();
      });
  
      document.addEventListener("click", async (e) => {
        if (e.target.closest('.modal-other-perfil')) return;
        const avatar = e.target.closest('.avatar-desc img, .avatar-desc, [data-avatar-post]');
        if (avatar) {
            const postEl = avatar.closest(".posts");
            if (postEl) { abrirModalPerfil(); await carregarPerfilOutroUsuario(postEl.dataset.userId); }
        }
      });
  
      document.addEventListener("click", (e) => {
         const aba = e.target.closest(".aba");
         if(!aba) return;
         const w = aba.closest('.modal-other-perfil-content-wrapper');
         w.querySelectorAll(".aba").forEach(a => a.classList.remove("active"));
         aba.classList.add("active");
         w.querySelectorAll(".aba-content").forEach(c => c.style.display = "none");
         w.querySelector(`#aba-${aba.dataset.aba}`).style.display = (aba.dataset.aba==='posts')?'grid':'block';
      });
  
     // >>> BOTÃO SEGUIR (Trecho final do modais.js corrigido)
      document.addEventListener("click", async (e) => {
        const btn = e.target.closest(".seguir-perfil-btn");
        if (!btn || btn.disabled) return;
  
        const usuarioID = btn.dataset.targetId;
        const textoOriginal = btn.textContent;
  
        btn.disabled = true; btn.textContent = "...";
        
        const formData = new FormData();
        // AGORA SIM: Nome 'seguido_id' batendo com o PHP
        formData.append("seguido_id", usuarioID); 
  
        try {
          // Usa o caminho absoluto para evitar erro 404
          const {candidate2} = phpPath('seguir.php'); 
          
          const r = await fetch(candidate2, { method: 'POST', body: formData });
          const text = await r.text(); 
          
          let data;
          try {
             data = JSON.parse(text);
          } catch(err) {
             console.error("Erro Fatal PHP (HTML retornado):", text);
             throw new Error("Erro no servidor: verifique o console.");
          }
  
          if (data && (data.success || data.status === "success")) {
             const action = data.action || data.acao;
             if (action === "followed" || action === "seguido") {
                 btn.textContent = "Seguindo"; btn.classList.add("seguindo");
             } else {
                 btn.textContent = "Seguir"; btn.classList.remove("seguindo");
             }
             atualizarContadoresSeguidores(usuarioID, btn.closest('.modal-other-perfil'));
          } else {
             alert(data.message || "Erro desconhecido.");
             btn.textContent = textoOriginal;
          }
        } catch (err) {
          console.error(err);
          alert("Erro de conexão ou sessão expirada.");
          btn.textContent = textoOriginal;
        } finally {
          btn.disabled = false;
        }
      });
  
    });
  })();