document.addEventListener('DOMContentLoaded', () => {
    const feedContainer = document.getElementById('feed-conteudo');
    const seguindolink  = document.querySelector('.pages li:nth-child(2) a');
    const fylink = document.querySelector('.pages li:nth-child(1) a');



 

    // Se não houver container de feed, não faz nada.
    if (!feedContainer) return;

    let loading = false;
    // O offset inicial é o número de posts já carregados pelo PHP na página.
    let offset = feedContainer.querySelectorAll('.posts').length;
    const feedType = new URLSearchParams(window.location.search).get('feed') || 'foryou';

    switch(feedType) {
        case 'foryou':
            seguindolink.classList.remove('selecionado');
            fylink.classList.add('selecionado');
            break;
        case 'seguindo':
            seguindolink.classList.add('selecionado');
            fylink.classList.remove('selecionado');
            break;
            default:
                seguindolink.classList.remove('selecionado');
                fylink.classList.remove('selecionado');
    }

    /**
     * Cria o HTML para um único post.
     * @param {object} post - O objeto do post vindo do servidor.
     * @returns {string} - A string HTML do post.
     */
    function criarPostHTML(post) {
        const userName = post.nome_user || 'Usuário desconhecido';
        const userAvatar = post.user_avatar || 'profile.png';
        const nome_completo = post.nome_completo || 'Usuário desconhecido';
        const arquivo = post.arquivo_url || post.arquivo;
        const titulo = post.titulo || 'Sem título';
        const descricao = post.descricao || 'Sem descrição';




        const imagemHTML = arquivo
            ? `<div class="img-post"><img src="../images/uploads/${arquivo}" alt="Imagem do post"></div>`
            : '<p>Sem imagem para este post.</p>';

        return `
        <article class="posts">
            <div class="descricao-post">
                <ul>
                    <li><img src="../images/avatares/Users/${userAvatar}" alt="Avatar do usuário"></li>
                    <li><span class="nome-desc">${nome_completo}</span></li>
                    <li><span class="nome-desc">${userName}</span></li>
                </ul>
                <ul>
                    <li><h1>${titulo}</h1></li>
                    <li><p>${descricao}</p></li>
                </ul
            </div>
            ${imagemHTML}
            <div class="footer-post">
                <form action="UsuarioLogado.php?feed=${feedType}" method="post">
                    <input type="hidden" name="post_id" value="${post.id}">
                    <ul>
                        <li><button type="button">Comentar</button></li>
                        <li><button type="submit" name="repostar_post" class="repostar-btn ${post.repostado ? 'repostado' : ''}">Repostar</button></li>
                        <li><button type="submit" name="curtir_post" class="curtida ${post.curtido ? 'curtido' : ''}">Curtir</button></li>
                        <li><button type="button">Compartilhar</button></li>
                    </ul>
                </form>
                <span>Salvar</span>
            </div>
        </article>`;
    }

    function carregarMaisPosts() {
        if (loading) return;
        loading = true;

        fetch(`carregar_posts.php?feed=${feedType}&offset=${offset}`)
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    data.forEach(post => {
                        feedContainer.insertAdjacentHTML('beforeend', criarPostHTML(post));
                    });
                    offset += data.length;
                    loading = false;
                } else {
                    window.removeEventListener('scroll', handleScroll);
                }
            }).catch(err => { console.error("Erro ao carregar mais posts:", err); loading = false; });
    }

    const handleScroll = () => {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
            carregarMaisPosts();
        }
    };

    window.addEventListener('scroll', handleScroll);
});