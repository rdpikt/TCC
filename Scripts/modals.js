//modal-perfil
const userSpan = document.querySelector('.nav-user li:nth-child(2) span img');
const modalPerfil = document.querySelector('.modal-perfil');
userSpan.addEventListener('click', () => {
  modalPerfil.classList.toggle('active');
});
window.addEventListener('click', (e) => {
  if (!modalPerfil.contains(e.target) && e.target !== userSpan) {
    modalPerfil.classList.remove('active');
  }
});

// Modal Post
const modalPost = document.querySelector('.modal-post');
const modalPostContent = document.querySelector('.modal-post-content');
const closeModalButton = document.querySelector('.modal-post .close-button');
const posts = document.querySelectorAll('.posts');

posts.forEach(post => {
    post.addEventListener('click', () => {
        const postId = post.dataset.postId;
        const userName = post.dataset.userName;
        const titulo = post.dataset.titulo;
        const descricao = post.dataset.descricao;
        const imagemUrl = post.dataset.imagemUrl;
        const userAvatar = post.dataset.userAvatar;

        const postHtml = `
            <div class="post-header">
                <img src="../images/avatares/Users/${userAvatar}" alt="Avatar do usuário" class="user-avatar">
                <span class="user-name">${userName}</span>
            </div>
            <div class="post-content">
                <h2>${titulo}</h2>
                <p>${descricao}</p>
                ${imagemUrl ? `<img src="../images/uploads/${imagemUrl}" alt="Imagem do post" class="post-image">` : ''}
            </div>
        `;

        modalPostContent.innerHTML = postHtml;
        modalPost.classList.add('active');
    });
});

closeModalButton.addEventListener('click', () => {
    modalPost.classList.remove('active');
});

window.addEventListener('click', (e) => {
    if (e.target === modalPost) {
        modalPost.classList.remove('active');
    }
});

window.addEventListener('DOMContentLoaded', () => {
    const sharedPostId = document.body.dataset.sharedPostId;
    if (sharedPostId) {
        const postToOpen = document.querySelector(`.posts[data-post-id="${sharedPostId}"]`);
        if (postToOpen) {
            postToOpen.click();
        }
    }
});
