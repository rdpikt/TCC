const BtnShare = document.querySelectorAll(".btn-share");

BtnShare.forEach(btn => {
    btn.addEventListener('click', (e) => {
        const postElement = e.target.closest('.posts');
        const postId = postElement.dataset.postId;
        const postTitle = postElement.dataset.titulo;
        const postDescription = postElement.dataset.descricao;

        const shareUrl = `${window.location.origin}/TCC/PHP/UsuarioLogado.php?post=${postId}`;

        const shareData = {
            title: postTitle,
            text: postDescription,
            url: shareUrl,
        };

        if (navigator.share) {
            navigator.share(shareData)
                .then(() => console.log('Post compartilhado com sucesso!'))
                .catch((error) => console.error('Erro ao compartilhar o post:', error));
        } else {
            // Fallback for browsers that don't support Web Share API
            alert(`Copie este link para compartilhar: ${shareUrl}`);
        }
    });
});