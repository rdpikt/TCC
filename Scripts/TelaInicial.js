// Modal Post
const modalPost = document.querySelector('.modal-post');
const modalPostContent = document.querySelector('.modal-post-content');
const closeModalButton = document.querySelector('.modal-post .close-button');
const posts = document.querySelectorAll('.posts');
const modalOverlay = document.querySelector('.modal-overlay');
let commentsInterval; // Variável para guardar o ID do intervalo

// Percorre todos os posts
posts.forEach(post => {
    // Procura a imagem dentro do post atual
    const imgPost = post.querySelector('.img-post');

    // Se o post tiver imagem, adiciona o evento apenas nela
    if (imgPost) {
        imgPost.addEventListener('click', (e) => {
            // Evita que o clique na imagem propague para outros elementos do card
            e.stopPropagation();

            const postId = post.dataset.postId;
            const userName = post.dataset.userName;
            const nome_completo = post.dataset.userNameCompleto;
            const titulo = post.dataset.titulo;
            const tagsData = post.dataset.tags;
            const imagemUrl = post.dataset.imagemUrl;
            const userAvatar = post.dataset.userAvatar;
            const descricao = post.dataset.descricao;
            const tags = (tagsData && tagsData.trim() !== '') ? JSON.parse(tagsData) : [];
            const tagsHtml = tags.map(tag => `<li>${tag}</li>`).join('');

            // Estrutura do modal em duas colunas
            modalPostContent.innerHTML = `
                <div class="modal-image-container">
                    ${imagemUrl ? `<img src="../images/uploads/${imagemUrl}" alt="Imagem do post" class="post-image">` : ''}
                </div>
                <div class="modal-sidebar">
                    <div class="modal-post-header">
                        <img src="../images/avatares/Users/${userAvatar}" alt="Avatar do usuário" class="user-avatar">
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
                </div>
            `;

            // Carregar os comentários e iniciar a atualização automática
            loadComments(postId);
            if (commentsInterval) clearInterval(commentsInterval);
            commentsInterval = setInterval(() => {
                loadComments(postId);
            }, 10000); // Atualiza a cada 10 segundos

            modalPost.classList.add('active');
            modalOverlay.classList.add('active');
        });
    }
});

// Fecha o modal
closeModalButton.addEventListener('click', () => {
    modalPost.classList.remove('active');
    modalOverlay.classList.remove('active');
    if (commentsInterval) clearInterval(commentsInterval); // Para a atualização automática
});

// Fecha ao clicar fora do conteúdo
window.addEventListener('click', (e) => {
    if (e.target === modalPost) {
        modalPost.classList.remove('active');
        modalOverlay.classList.remove('active');
        if (commentsInterval) clearInterval(commentsInterval); // Para a atualização automática
    }
});
