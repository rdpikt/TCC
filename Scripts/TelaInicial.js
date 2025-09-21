
let currentPage = 1;
let loading = false;
const feedConteudo = document.getElementById('feed-conteudo');
const tipoFeed = (new URLSearchParams(window.location.search)).get('feed') || 'foryou';
const btnOptions = document.querySelectorAll('.btn-options');
const modalOptions = document.querySelectorAll('.modal-options');

function renderPost(post) {
  const article = document.createElement('article');
  article.className = 'posts';
  article.innerHTML = `
    <h1 class="no-obra"></h1>
    <div class="descricao-post">
      <span class="nome-desc">User: ${post.nome_user}</span>
      <h1 class="titulo">Titulo: ${post.titulo}</h1>
      <p>descricao: <br>${post.descricao}</p>
    </div>
    ${post.arquivo && post.tipo_imagem ? `<div class='img-post'><img src='../images/uploads/${post.arquivo}' alt='Imagem do post'></div>` : '<p>Sem imagem para este post.</p>'}
    <div class="footer-post">
      <ul>
        <li>Comentários</li>
        <li>Repost</li>
        <li>Curtidas</li>
        <li>Compartilhar</li>
      </ul>
      <span>Salvar</span>
    </div>
  `;
  feedConteudo.appendChild(article);
}

function loadMorePosts() {
  if (loading) return;
  loading = true;
  currentPage++;
  fetch(`../PHP/feed.php?feed=${tipoFeed}&page=${currentPage}`)
    .then(res => res.json())
    .then(data => {
      if (data.posts && data.posts.length > 0) {
        data.posts.forEach(renderPost);
        loading = false;
      } else {
        window.removeEventListener('scroll', handleScroll);
        if (feedConteudo && !feedConteudo.querySelector('.no-more-posts')) {
          const endMsg = document.createElement('div');
          endMsg.className = 'no-more-posts';
          endMsg.textContent = 'Não há mais posts para mostrar.';
          feedConteudo.appendChild(endMsg);
        }
      }
    })
    .catch((err) => { console.error('Erro ao carregar posts:', err); loading = false; });
}

function loadInitialPosts() {
  fetch(`../PHP/feed.php?feed=${tipoFeed}&page=1`)
    .then(res => res.json())
    .then(data => {
      if (data.posts && data.posts.length > 0) {
        data.posts.forEach(renderPost);
      } else {
        if (feedConteudo) {
          feedConteudo.innerHTML = '<div class="no-posts">Nenhum post encontrado.</div>';
        }
      }
    })
    .catch((err) => { console.error('Erro ao carregar posts iniciais:', err); });
}

function handleScroll() {
  if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
    loadMorePosts();
  }
}

window.addEventListener('scroll', handleScroll);
window.addEventListener('DOMContentLoaded', loadInitialPosts);


//barra de pesquisa
const searchBar = document.getElementById('search-bar');
searchBar.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') {
    const query = searchBar.value.trim();
    if (query) {
      window.location.href = `../PHP/Pesquisa.php?query=${encodeURIComponent(query)}`;
    }
  }
});
  
//modal-options
  btnOptions.forEach((btn, index) => {
    btn.addEventListener('click', () => {
      modalOptions.forEach((modal, i) => {
        if(i === index){
          modal.classList.toggle('active');
        }
      })
        
    });
  });
  window.addEventListener('click', (e) => {
    modalOptions.forEach((modal) => {
        if (!modal.contains(e.target) && !Array.from(btnOptions).some(btn => btn === e.target)) {
            modal.classList.remove('active');
        }
    });
  });

