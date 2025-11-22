// =======================================================
// FUNÇÕES GLOBAIS DE CONTROLE DO MODAL DE PERFIL
// =======================================================

const ModalOtherPerfil = document.querySelector(".modal-other-perfil-container");

function abrirModalPerfil() {
    if (!ModalOtherPerfil) return;
    ModalOtherPerfil.classList.add("active");
    document.body.style.overflow = "hidden";
}

function fecharModalPerfil() {
    if (!ModalOtherPerfil) return;
    ModalOtherPerfil.classList.remove("active");
    document.body.style.overflow = "auto";
    ModalOtherPerfil.innerHTML = ''; // Limpa o conteúdo ao fechar
}

async function carregarPerfilOutroUsuario(idUsuario) {
    if (!ModalOtherPerfil) return;
    
    try {
        const response = await fetch(`../PHP/carregar_perfil.php?userID=${idUsuario}`);
        const data = await response.json();

        if (!data.success) {
            ModalOtherPerfil.innerHTML = `<div class="modal-other-perfil-content-wrapper"><p style="padding:20px; color:white;">Erro ao carregar perfil: ${data.message}</p></div>`;
            return;
        }

        const u = data.usuario;
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
                            <img src="../images/avatares/Users/padrao.png">
                            <div>
                                <strong>KAWS</strong>
                                <span style="color:#71767b">@kaws</span>
                            </div>
                            <button>Seguir</button>
                        </li>
                         <li>
                            <img src="../images/avatares/Users/padrao.png">
                            <div>
                                <strong>Takashi</strong>
                                <span style="color:#71767b">@takashipom</span>
                            </div>
                            <button>Seguir</button>
                        </li>
                         <li>
                            <img src="../images/avatares/Users/padrao.png">
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
                    <div class="close-button modal-other-fechar">
                        <i class="fas fa-arrow-left"></i>
                    </div>
                    <div class="modal-other-header">
                        <div class="header-top-row">
                            <div class="modal-other-avatar">
                                <img src="../images/avatares/Users/${u.avatar || 'profile.png'}">
                            </div>
                            ${u.jaSegue !== null ? `
                                <button class="seguir-perfil-btn" data-target-id="${u.id}">
                                    ${u.jaSegue ? "Seguindo" : "Seguir"}
                                </button>
                            ` : ''}
                        </div>
                        <div class="modal-other-username">
                            <strong class="nome-completo">${u.nome_completo}</strong>
                            <span class="user-tag">@${u.tipo}</span> 
                        </div>
                        <p class="bio-user">${u.bio || "Sem biografia."}</p>
                        <div class="follow-info">
                            <span><strong>${u.seguindo}</strong> Seguindo</span>
                            <span><strong>${u.seguidores}</strong> Seguidores</span>
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
            <img src="../images/uploads/${post.imagemUrl}">
        </div>
    `).join("");
}

async function atualizarContadoresSeguidores(idUsuario, modalElement) {
    try {
        const response = await fetch(`../PHP/contador_seguidores.php?id=${idUsuario}`);
        const data = await response.json();

        if (data.success && modalElement) {
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


// =======================================================
// EVENT LISTENERS DO DOM
// =======================================================
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

                const tags = (tagsData && tagsData.trim() !== '') ? JSON.parse(tagsData) : [];
                const tagsHtml = tags.map(tag => `<li>${tag}</li>`).join('');
                
                modalPostContent.innerHTML = `
                <div class="modal-image-container">
                    ${imagemUrl ? `<img src="../images/uploads/${imagemUrl}" class="post-image">` : ''}
                </div>
                <div class="modal-sidebar">
                    <div class="modal-post-header">
                        <img src="../images/avatares/Users/${userAvatar}" class="user-avatar">
                        <span class="user-name">@${userName}</span>
                    </div>

                    <div class="modal-post-details">
                        <p><strong>${nome_completo}</strong> ${descricao}</p>
                        <ul>${tagsHtml}</ul>
                    </div>

                    <div id="comments-list" class="modal-comments-list"></div>

                    <div class="comment-form-container">
                        <textarea id="new-comment-content" placeholder="Escreva um comentário..."></textarea>
                        <button id="submit-comment-btn" data-post-id="${postId}">Comentar</button>
                    </div>
                </div>`;

                loadComments(postId);
                if (commentsInterval) clearInterval(commentsInterval);
                commentsInterval = setInterval(() => loadComments(postId), 10000);

                modalPost.classList.add('active');
                modalOverlay.classList.add('active');
            });
        }
    });

    if (closeModalButton) {
        closeModalButton.addEventListener('click', () => {
            modalPost.classList.remove('active');
            modalOverlay.classList.remove('active');
            if (commentsInterval) clearInterval(commentsInterval);
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === modalPost || e.target === modalOverlay) {
            modalPost.classList.remove('active');
            modalOverlay.classList.remove('active');
            if (commentsInterval) clearInterval(commentsInterval);
        }
    });

    // ---------------------------------------------------------
    // LÓGICA DE EVENTOS PARA O MODAL DE PERFIL
    // ---------------------------------------------------------

    // Evento para FECHAR modal de perfil
    document.addEventListener("click", (e) => {
        if (e.target.closest(".modal-other-fechar")) {
            fecharModalPerfil();
        }
    });

    // Evento para ABRIR modal de perfil (clicando no avatar de um post)
    document.addEventListener("click", async (e) => {
        const avatar = e.target.closest('.avatar-desc img');
        if (!avatar) return;

        const postEl = avatar.closest(".posts");
        if (!postEl) return;
        
        const idUsuario = postEl.dataset.userId;
        if (!idUsuario) return;
        
        abrirModalPerfil();
        await carregarPerfilOutroUsuario(idUsuario);
    });

    // Evento para TROCAR DE ABA
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
            abaContent.style.display = "grid";
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
            const resposta = await fetch("../PHP/seguir_usuario.php", {
                method: "POST",
                body: formData
            });
            const data = await resposta.json();

            if (data.success) {
                btn.textContent = data.acao === "seguido" ? "Seguindo" : "Seguir";
                atualizarContadoresSeguidores(usuarioID, btn.closest('.modal-other-perfil'));
            }
        } catch (err) {
            console.error("Erro ao seguir:", err);
        }
    });
});