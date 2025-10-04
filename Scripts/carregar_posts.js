document.addEventListener('DOMContentLoaded', () => {
    const feedContainer = document.getElementById('feed-conteudo');
    // Se não houver container de feed, não faz nada.
    if (!feedContainer) return;

    let loading = false;
    // O offset inicial é o número de posts já carregados pelo PHP na página.
    let offset = feedContainer.querySelectorAll('.posts').length;
    const feedType = new URLSearchParams(window.location.search).get('feed') || 'foryou';

    /**
     * Cria o HTML para um único post.
     * @param {object} post - O objeto do post vindo do servidor.
     * @returns {string} - A string HTML do post.
     */
    function criarPostHTML(post) {
        const userName = post.nome_user || 'Usuário desconhecido';
        const titulo = post.titulo || '';
        const descricao = post.descricao || '';
        const arquivo = post.arquivo_url || post.arquivo;

        const imagemHTML = arquivo
            ? `<div class="img-post"><img src="../images/uploads/${arquivo}" alt="Imagem do post"></div>`
            : '<p>Sem imagem para este post.</p>';

        return `
        <article class="posts">
            <div class="descricao-post">
                <ul>
                    <li><span class="nome-desc">User: ${userName}</span></li>
                </ul>
                <h1 class="titulo">Titulo: ${titulo}</h1>
                <p>Descrição: <br>${descricao}</p>
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