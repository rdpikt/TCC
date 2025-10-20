// Modal Post
const modalPost = document.querySelector('.modal-post');
const modalPostContent = document.querySelector('.modal-post-content');
const closeModalButton = document.querySelector('.modal-post .close-button');
const posts = document.querySelectorAll('.posts');
const modalOverlay = document.querySelector('.modal-overlay');


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
            const tags = post.dataset.tags;
            const descricao = post.dataset.descricao;
            const imagemUrl = post.dataset.imagemUrl;
            const userAvatar = post.dataset.userAvatar;
            console.log(post.dataset)

            const postHtml = `
                ${imagemUrl ? `<img src="../images/uploads/${imagemUrl}" alt="Imagem do post" class="post-image">` : ''}
                <div class="post-content">
                <div class="post-header">
                    <img src="../images/avatares/Users/${userAvatar}" alt="Avatar do usuário" class="user-avatar">
                    <span class="user-name">${nome_completo}</span>
                    <span class="user-name">@${userName}</span>
                </div>
                <div class="post-text">
                    <h2>${titulo}</h2>
                    <p>${descricao}</p>
                    <ul>
                        <li>${tags}</li>
                    </ul>
                </div>
                 <div class="footer-post">
              <form action="UsuarioLogado.php?feed=<?= $tipo_feed ?>" method="post">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <ul>
                  <li><button type="button"><i class="fi fi-rr-comment"></i></button></li>
                  <li><button type="submit" name="repostar_post"
                      class="repostar-btn <?= $repostado ? 'repostado' : '' ?>"><i class="fi fi-rr-refresh"></i></button>
                  </li>
                  <li><button type="submit" name="curtir_post" class="curtida <?= $curtido ? 'curtido' : '' ?>"><svg
                        width="1.5rem" height="1.5rem" viewBox="0 0 24 24" fill="currentColor"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                          d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                      </svg>
                    </button></li>
                  <li><button class="btn-share" type="button"><i class="fi fi-rs-redo"></i></button></li>
                </ul>

              </form>
              <button type="submit" name="salvar_post" class="salvar-btn ww"><svg xmlns="http://www.w3.org/2000/svg"
                  height="1.5rem" width="1.5rem" viewBox="0 0 384 512">
                  <path d="M0 48C0 21.5 21.5 0 48 0H336c26.5 0 48 21.5 48 48V464L192 352 0 464V48z" />
                </svg></button>
            </div>
                </div>
            `;

            modalPostContent.innerHTML = postHtml;
            modalPost.classList.add('active');
            modalOverlay.classList.add('active');

        });
    }
});

// Fecha o modal
closeModalButton.addEventListener('click', () => {
    modalPost.classList.remove('active');
    modalOverlay.classList.remove('active');
});

// Fecha ao clicar fora do conteúdo
window.addEventListener('click', (e) => {
    if (e.target === modalPost) {
        modalPost.classList.remove('active');
        modalOverlay.classList.remove('active');
    }
});
