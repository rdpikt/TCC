<?php
require "protect.php";
require "conexao.php";

$userId = $_SESSION['user_id'] ?? null;
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajuda | HarpHub</title>

  <link rel="stylesheet" href="../Styles/global.css">
  <link rel="stylesheet" href="../Styles/telainicial.css"><!-- reaproveita layout -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
   <link rel="stylesheet" href="../Styles/ajuda.css">
  
</head>

<body>
  <header>
    <div class="search-container">
      <div class="search-bar-wrapper">
        <i class="fi-rr-search"></i>
        <input type="search" id="search-bar" class="search-bar" name="query" placeholder="Buscar">
      </div>
      <div id="suggestions-box">
      </div>
    </div>
    <div class="nav-user">
      <ul>
        <li><a href="notificacoes.php"><i class="fi fi-rs-bell"></i></a></li>
        <li><span><img src="../images/avatares/Users/<?php echo htmlspecialchars($user_avatar); ?>"
              alt="Avatar do usuário"></span></li>
      </ul>
    </div>
    <div class="modal-perfil">
      <ul>
        <li><a href="perfil.php">Perfil</a></li>
        <li>Trocar de conta</li>
        <li>
          <form action="logout.php">
            <input type="submit" value="Sair da conta">
          </form>
        </li>
      </ul>
    </div>
  </header>

  <main>
    <nav class="nav-side" id="menu">
      <h1 class="logo">
        <a href="#inicio" class="logo-link">
          <span class="marca">Harp</span><span class="nome">Hub</span>
        </a>
      </h1>
      <ul class="pages">
        <li><a href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a>
        </li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a>
        </li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
        <li><a href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>

      </ul>
      <div class="tools">
        <ul>
          <li><a href="config.php"><i class="fi fi-rr-settings"></i>Configurações</a></li>
          <li><a class="selecionado" href="ajuda.php"><i class="fi fi-rr-info"></i>Ajuda</a></li>
        </ul>
      </div>
    </nav>

    <!-- Conteúdo principal de ajuda -->
    <section class="navigation-user">
  <div class="ajuda-container">
      <div class="ajuda-header">
        <h1>Central de Ajuda</h1>
        <p>Encontre respostas rápidas sobre como usar o HarpHub e descobrir cada funcionalidade da plataforma.</p>
      </div>

      <div class="ajuda-search">
        <i class="fi-rr-search"></i>
        <input type="text" id="ajuda-search-input" placeholder="Busque por um assunto (ex.: postar, seguir, comentários)">
      </div>

      <div class="ajuda-grid">
        <!-- Perguntas rápidas / FAQ -->
        <article class="ajuda-card">
          <h2><i class="fi fi-rr-interrogation"></i>Perguntas rápidas</h2>
          <ul class="ajuda-lista" id="ajuda-faq-list">
            <li data-target="sec-conta">
              <span class="pergunta">Como editar meu perfil?</span>
              <span class="tag">Perfil</span>
            </li>
            <li data-target="sec-postar">
              <span class="pergunta">Como criar um novo post?</span>
              <span class="tag">Posts</span>
            </li>
            <li data-target="sec-feed">
              <span class="pergunta">Qual a diferença entre “Para você” e “Seguindo”?</span>
              <span class="tag">Feed</span>
            </li>
            <li data-target="sec-interacoes">
              <span class="pergunta">Como funcionam curtidas, reposts e comentários?</span>
              <span class="tag">Interações</span>
            </li>
            <li data-target="sec-comunidades">
              <span class="pergunta">O que são Comunidades e como participar?</span>
              <span class="tag">Comunidades</span>
            </li>
            <li data-target="sec-seguranca">
              <span class="pergunta">Como denunciar conteúdo ou usuário?</span>
              <span class="tag">Segurança</span>
            </li>
          </ul>
        </article>

        <!-- Dicas principais -->
        <article class="ajuda-card">
          <h2><i class="fi fi-rr-lightbulb-on"></i>Primeiros passos</h2>
          <p>Se você acabou de chegar ao HarpHub, siga esta sequência:</p>
          <ul class="ajuda-secao-lista">
            <li>Complete seu perfil com foto, @username e bio.</li>
            <li>Escolha interesses no primeiro acesso para personalizar o feed.</li>
            <li>Siga artistas que você curte para ver mais conteúdo deles.</li>
            <li>Publique sua primeira obra em <strong>Criar Post</strong>.</li>
          </ul>
        </article>
      </div>

      <!-- Seções detalhadas (âncoras para os itens da lista) -->
      <div class="ajuda-secao" id="sec-conta">
        <h3>Como editar meu perfil?</h3>
        <p>Acesse o menu lateral e clique em <strong>Perfil</strong>. Na página de perfil você pode alterar foto, nome de exibição, @username, biografia e outras informações públicas.</p>
        <p>Alguns ajustes de conta, como senha e preferências de privacidade, ficam em <strong>Configurações</strong> no final do menu lateral.</p>
      </div>

      <div class="ajuda-secao" id="sec-postar">
        <h3>Como criar um novo post?</h3>
        <p>Use a opção <strong>Criar Post</strong> no menu lateral. Lá você envia imagens, textos ou outros arquivos, adiciona título, descrição, tags e escolhe se a obra é pública.</p>
        <p>Depois de publicar, o post aparecerá no feed dos usuários que seguem você e poderá ser descoberto por outros artistas.</p>
      </div>

      <div class="ajuda-secao" id="sec-feed">
        <h3>Diferença entre “Para você” e “Seguindo”</h3>
        <p>No topo da página inicial existem dois modos de feed:</p>
        <ul>
          <li><strong>Para você:</strong> mistura conteúdos recomendados com base nos seus interesses e interações.</li>
          <li><strong>Seguindo:</strong> mostra apenas posts de artistas e perfis que você segue.</li>
        </ul>
      </div>

      <div class="ajuda-secao" id="sec-interacoes">
        <h3>Curtidas, reposts, comentários e salvar</h3>
        <ul>
          <li><strong>Curtir:</strong> o ícone de coração aumenta o contador do post e avisa o autor.</li>
          <li><strong>Repostar:</strong> compartilha o post no seu próprio feed, dando mais alcance ao autor original.</li>
          <li><strong>Comentar:</strong> abre a área de comentários para você conversar com outros usuários.</li>
          <li><strong>Salvar:</strong> guarda o post em uma lista privada para você rever depois.</li>
        </ul>
      </div>

      <div class="ajuda-secao" id="sec-comunidades">
        <h3>O que são Comunidades?</h3>
        <p>Comunidades são espaços temáticos para artistas com interesses em comum (por exemplo, fotografia, poesia, design gráfico).</p>
        <p>Use o menu <strong>Comunidades</strong> para explorar, entrar em grupos e acompanhar posts específicos daquele tema.</p>
      </div>

      <div class="ajuda-secao" id="sec-seguranca">
        <h3>Segurança, denúncias e suporte</h3>
        <p>Se você encontrar conteúdo ofensivo, plágio ou comportamento inadequado, utilize as opções de denúncia disponíveis no post ou no perfil do usuário, quando houver.</p>
        <p>Para dúvidas mais específicas, entre em contato pelo e‑mail de suporte informado nas configurações da conta ou pela seção “Contato” no rodapé do site (caso sua equipe crie essa página).</p>
      </div>

      <div class="ajuda-contato">
        Ainda com dúvidas? Fale com a equipe do HarpHub pelo e‑mail de suporte configurado na plataforma ou envie feedback pelo formulário em <strong>Configurações &gt; Ajuda e feedback</strong> (quando estiver disponível).
      </div>
     </div>
    </section>
  </main>

  <script>
    // Rolagem suave ao clicar nos itens da lista de perguntas
    document.querySelectorAll('#ajuda-faq-list li').forEach(function (item) {
      item.addEventListener('click', function () {
        const targetId = item.getAttribute('data-target');
        const section = document.getElementById(targetId.replace('#', ''));
        if (section) {
          section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

    // Filtro simples de perguntas pelo campo de busca
    const ajudaSearchInput = document.getElementById('ajuda-search-input');
    const faqItems = document.querySelectorAll('#ajuda-faq-list li');

    if (ajudaSearchInput) {
      ajudaSearchInput.addEventListener('input', function () {
        const termo = this.value.toLowerCase();
        faqItems.forEach(li => {
          const texto = li.querySelector('.pergunta').textContent.toLowerCase();
          li.style.display = texto.includes(termo) ? 'flex' : 'none';
        });
      });
    }
  </script>

  <script src="../Scripts/TelaInicial.js"></script>
  <script src="../Scripts/modals.js"></script>
  <script src="../Scripts/ajaxInteractions.js"></script>
</body>
</html>
