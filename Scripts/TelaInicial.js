document.addEventListener('DOMContentLoaded', () => {
    // Seletores principais
    const modalPost = document.querySelector('.modal-post');
    const modalPostContent = document.querySelector('.modal-post-content');
    const closeModalButton = document.querySelector('.modal-post .close-button');
    const posts = document.querySelectorAll('.posts');
    const modalOverlay = document.querySelector('.modal-overlay');

    let commentsInterval;

    // Container do modal de perfil (o layout novo)
    const ModalOtherPerfil = document.querySelector(".modal-other-perfil-container");
    // O seletor abaixo não é mais estritamente necessário para injeção, pois injetamos no container principal,
    // mas mantemos referência caso precise limpar especificamente.
    let ModalOtherPerfilContentWrapper = document.querySelector(".modal-other-perfil-content-wrapper");
    
    // Botão de fechar original (caso exista no HTML estático)
    const closeOtherPerfilButton = document.querySelector('.modal-other-perfil .close-button');

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
                const titulo = post.dataset.titulo;
                const tagsData = post.dataset.tags;
                const imagemUrl = post.dataset.imagemUrl;
                const userAvatar = post.dataset.userAvatar;
                const descricao = post.dataset.descricao;
                const tipo = post.dataset.userType;

                const tags = (tagsData && tagsData.trim() !== '') ? JSON.parse(tagsData) : [];
                const tagsHtml = tags.map(tag => `<li>${tag}</li>`).join('');

                // Modal do post (imagem + sidebar)
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

                // Atualizar comentários
                loadComments(postId);
                if (commentsInterval) clearInterval(commentsInterval);
                commentsInterval = setInterval(() => loadComments(postId), 10000);

                modalPost.classList.add('active');
                modalOverlay.classList.add('active');
            });
        }
    });

    // Fechar modal de POST pelo X
    if (closeModalButton) {
        closeModalButton.addEventListener('click', () => {
            modalPost.classList.remove('active');
            modalOverlay.classList.remove('active');
            if (commentsInterval) clearInterval(commentsInterval);
        });
    }

    // Fechar modals ao clicar fora (Overlay)
    window.addEventListener('click', (e) => {
        // Fecha modal de post
        if (e.target === modalPost || e.target === modalOverlay) {
            modalPost.classList.remove('active');
            modalOverlay.classList.remove('active');
            if (commentsInterval) clearInterval(commentsInterval);
        }
        
        // NOTA: Não fechamos o modal de perfil ao clicar no fundo dele,
        // pois ele age como uma página. Só fechamos pelo botão de voltar.
    });


    // ---------------------------------------------------------
    // LÓGICA DO MODAL DE PERFIL (NOVO LAYOUT 3 COLUNAS)
    // ---------------------------------------------------------

    function abrirModalPerfil() {
        ModalOtherPerfil.classList.add("active");
        // Não ativamos modalOverlay para o perfil, pois ele tem seu próprio fundo
        // modalOverlay.classList.add("active"); 
        document.body.style.overflow = "hidden"; // Evita rolar o feed atrás
    }

    function fecharModalPerfil() {
        ModalOtherPerfil.classList.remove("active");
        // modalOverlay.classList.remove("active");
        document.body.style.overflow = "auto"; // Volta a rolar a página
        ModalOtherPerfil.innerHTML = ''; // Limpa o conteúdo ao fechar
    }

    // Evento para fechar modal de perfil (delegado para funcionar com HTML dinâmico)
    document.addEventListener("click", (e) => {
        // Verifica se clicou no botão de fechar ou no ícone dentro dele
        if (e.target.closest(".modal-other-fechar")) {
            fecharModalPerfil();
        }
    });

    // Abrir modal ao clicar no avatar do post
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

    // Função Principal: Carrega o perfil e a coluna de sugestões
    async function carregarPerfilOutroUsuario(idUsuario) {
        try {
            // Ajuste o caminho da API conforme necessário
            const response = await fetch(`../PHP/carregar_perfil.php?userID=${idUsuario}`);
            const data = await response.json();

            if (!data.success) {
                ModalOtherPerfil.innerHTML = `<p style="padding:20px; color:white;">Erro ao carregar perfil.</p>`;
                return;
            }

            const u = data.usuario;
            const posts = data.posts || [];

            // 1. HTML DA COLUNA DIREITA (Sugestões duplicada)
            // Estamos duplicando a estrutura visual para ficar idêntico à imagem
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
                </div>
            `;

            // 2. HTML DA COLUNA CENTRAL (Perfil)
            const profileHTML = `
                <div class="modal-other-perfil-content-wrapper">
                    <div class="modal-other-perfil">
                        
                        <div class="close-button modal-other-fechar">
                            <i class="fas fa-arrow-left"></i>
                        </div>

                        <div class="modal-other-header">
                            <div class="header-top-row">
                                <div class="modal-other-avatar">
                                    <img src="../images/avatares/Users/${u.avatar}">
                                </div>
                                <button class="seguir-perfil-btn" data-target-id="${u.id}">
                                    ${u.jaSegue ? "Seguindo" : "Seguir"}
                                </button>
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
                            <div class="aba-content" id="aba-posts"></div>
                            <div class="aba-content" id="aba-reposts" style="display:none"></div>
                            <div class="aba-content" id="aba-salvos" style="display:none"></div>
                            <div class="aba-content" id="aba-curtidas" style="display:none"></div>
                        </div>
                    </div>
                </div>
            `;

            // Injeta tudo no container principal
            // Ordem visual: Perfil (Centro) | Sugestões (Direita)
            // Como usamos justify-content: center e gap, eles ficarão lado a lado centralizados.
            ModalOtherPerfil.innerHTML = profileHTML + rightColumnHTML;

            // Carrega os posts iniciais
            carregarPostsNaAba(posts, "aba-posts");

        } catch (err) {
            console.error("Falha ao carregar perfil:", err);
            ModalOtherPerfil.innerHTML = `<p style="padding:20px; color:white;">Erro técnico ao carregar.</p>`;
        }
    }

    // Função auxiliar para renderizar os grids de posts
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

    // Controle de Abas
    document.addEventListener("click", (e) => {
        const aba = e.target.closest(".aba");
        if (!aba) return;

        // Garante que estamos clicando numa aba dentro do modal de perfil
        const modalWrapper = aba.closest('.modal-other-perfil-content-wrapper');
        if (!modalWrapper) return;

        modalWrapper.querySelectorAll(".aba").forEach(a => a.classList.remove("active"));
        aba.classList.add("active");

        const nomeAba = aba.dataset.aba;

        modalWrapper.querySelectorAll(".aba-content").forEach(c => c.style.display = "none");
        const abaContent = modalWrapper.querySelector(`#aba-${nomeAba}`);
        if (abaContent) {
            abaContent.style.display = "grid"; // Usa grid pois definimos display:grid no css do content
        }
    });

    // Follow / Unfollow
    document.addEventListener("click", async (e) => {
        const btn = e.target.closest(".seguir-perfil-btn");
        if (!btn) return;

        const usuarioID = btn.dataset.targetId;
        const formData = new FormData();
        formData.append("userID", usuarioID);

        try {
            // Ajuste o caminho da API conforme necessário
            const resposta = await fetch("../PHP/seguir_usuario.php", {
                method: "POST",
                body: formData
            });
            const data = await resposta.json();

            if (data.success) {
                btn.textContent = data.acao === "seguido" ? "Seguindo" : "Seguir";
                
                // Se quiser enviar notificação
                if (data.acao === "seguido") {
                    // enviarNotificacaoFollow(usuarioID); // Função externa se existir
                }
                
                // Atualizar contadores visualmente
                atualizarContadoresSeguidores(usuarioID, btn.closest('.modal-other-perfil'));
            }
        } catch (err) {
            console.error("Erro ao seguir:", err);
        }
    });

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
});