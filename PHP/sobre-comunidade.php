<?php
require "protect.php";
require "conexao.php";

$comunidade_id = $_GET["id"];
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';
$sql = "SELECT c.*, SUM(IF(cm.cargo = 'membro', 1, 0)) as qtd_membros
        FROM comunidades as c 
        LEFT JOIN comunidade_membros as cm ON c.id = cm.comunidade_id 
        WHERE c.id = ?
        GROUP BY c.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $comunidade_id);
$stmt->execute();
$result = $stmt->get_result();

// Como você filtra por ID, só virá UMA linha. 
// Você não precisa de um 'while', pode usar 'fetch_assoc' direto.
$comunidade = $result->fetch_assoc();

// Agora você pode pegar a contagem pelo nome (alias) que demos a ela
if ($comunidade) {
  $qtdMembros = $comunidade["qtd_membros"];
} else {
  // Tratar o caso de não achar a comunidade
  $qtdMembros = 0;
}

$userId = $_SESSION['user_id']; // Assumindo que o ID do usuário está na sessão

$sql_membro = "SELECT id FROM comunidade_membros WHERE comunidade_id = ? AND usuario_id = ? AND cargo = 'membro'";
$stmt_membro = $conn->prepare($sql_membro);
$stmt_membro->bind_param("ii", $comunidade_id, $userId);
$stmt_membro->execute();
$result_membro = $stmt_membro->get_result();

$is_member = $result_membro->num_rows > 0;

$button_text = $is_member ? 'Sair' : 'Entrar';
$button_class = $is_member ? 'btn-sair' : 'btn-entrar';


?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Navegação</title>
  <link rel="stylesheet" href="../Styles/telainicial.css">
  <link rel="stylesheet" href="../Styles/global.css">
  <link rel="stylesheet" href="../Styles/sobre-comunidade.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>

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
        <li><a href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a></li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a>
        </li>
        <li><a class="selecionado" href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>

      </ul>
      <div class="tools">
        <ul>
          <li><a href="config.php"><i class="fi fi-rr-settings"></i>Configurações</a></li>
          <li><a href="ajuda.php"><i class="fi fi-rr-info"></i>Ajuda</a></li>
        </ul>
      </div>
    </nav>

    <section class="navigation-user">
      <a href="explorar_comunidades.php"><i class="fi fi-rr-arrow-left"></i></a>
      <article class="comunidade-container">
        <div class="options">
          <ul>
            <li><i class="fi fi-br-search"></i></li>
            <li><i class="fi fi-br-user-add"></i></li>
          </ul>
        </div>
        <div class="comunidade-info">
          <div class="ret">
            <button><i class="fi fi-br-menu-dots" id="menu-dots"></i></button>
          </div>
          <div class="modal-reticencias">
            <ul class="options-list">
              <li><button class="denunciar-comunidade"><i class="fi fi-br-flag"></i><a
                    href="gerenciar_membro_comunidade.php "> Denunciar Comunidade</a></button></li>
              <li><button class="editar-comunidade"><i class="fi fi-br-edit"></i> Editar Comunidade</button></li>
            </ul>
          </div>
          <button class="<?= htmlspecialchars($button_class) ?>" data-comunidade-id="<?= $comunidade_id ?>">
            <?= htmlspecialchars($button_text) ?>
          </button>
          <div class="text-comunidade">

            <img src="../images/avatares/Comunidades/<?php echo htmlspecialchars($comunidade['imagem']) ?>" alt="">
            <h1><?php echo htmlspecialchars($comunidade['nome']); ?></h1>
            <span>
              <p>Comunidade de <?php echo htmlspecialchars($comunidade['tipo_comunidade']) ?></p>
            </span>
          </div>
          <div class="desc-comunidade">
            <p><?php echo htmlspecialchars($comunidade['descricao']); ?></p>
            <p><?php echo htmlspecialchars($qtdMembros) ?> Membros</p>
          </div>
        </div>
        <div class="comunidade-options">
          <ul>
            <li class="comunidade_option active">Geral</li>
            <li class="comunidade_option">Comentários</li>
            <li class="comunidade_option">Regras</li>
            <?php if ($is_member): ?>
              <div class="header-action">
                <a href="EnviarArquivos.php?comunidade=<?= $comunidade_id ?>" class="btn-criar-post">
                  Criar Post
                </a>
              </div>
            <?php endif; ?>
          </ul>
        </div>
        <div class="comunidade-content">
          <?php
          // ... (QUERY SQL para buscar posts) ...
          $sql_posts = "SELECT o.*, u.nome_user as nome_autor, u.user_avatar as avatar_autor, u.nome_user as user_handle
                        FROM obras o
                        JOIN comunidade_obras co ON o.id = co.obra_id
                        JOIN users u ON o.portfolio_id = u.id
                        WHERE co.comunidade_id = ?
                        ORDER BY co.data_postagem DESC";

          $stmt_posts = $conn->prepare($sql_posts);
          $stmt_posts->bind_param("i", $comunidade_id);
          $stmt_posts->execute();
          $result_posts = $stmt_posts->get_result();

          if ($result_posts->num_rows > 0): 
            while ($post = $result_posts->fetch_assoc()): 
              
              $avatarAutor = !empty($post['avatar_autor']) ? $post['avatar_autor'] : 'profile.png';
              
              // Lógica de determinação do tipo de mídia baseada na extensão (tipo_imagem)
              $extensao = strtolower($post['tipo_imagem']);
              $is_image = in_array($extensao, ['png', 'jpg', 'jpeg']);
              $is_video = in_array($extensao, ['mp4', 'avi', 'wmv']); // Usando extensões comuns

              if ($is_image) {
                  // Assume: Imagens em "../images/uploads/"
                  $caminhoArquivo = "../images/uploads/" . htmlspecialchars($post['arquivo_url']);
              } elseif ($is_video) {
                  // Assume: Vídeos em "../images/uploads/videos/"
                  $caminhoArquivo = "../images/uploads/videos/" . htmlspecialchars($post['arquivo_url']);
              } else {
                  $caminhoArquivo = '';
              }
          ?>
              <div class="post-card" style="background: #1e1e1e; margin-bottom: 20px; padding: 15px; border-radius: 10px;">
                
                <div class="post-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                  <img src="../images/avatares/Users/<?= htmlspecialchars($avatarAutor) ?>" 
                       alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                  <div>
                    <h3 style="margin: 0; font-size: 1rem; color: #fff;"><?= htmlspecialchars($post['nome_autor']) ?></h3>
                    <span style="font-size: 0.8rem; color: #aaa;">@<?= htmlspecialchars($post['user_handle']) ?></span>
                  </div>
                </div>

                <div class="post-body">
                  <h4 style="color: #fff; margin-bottom: 5px;"><?= htmlspecialchars($post['titulo']) ?></h4>
                  
                  <?php if ($is_image && !empty($post['arquivo_url'])): ?>
                    <p style="color: #ccc; font-size: 0.9rem; margin-bottom: 10px;"><?= htmlspecialchars($post['descricao']) ?></p>
                    <img src="<?= $caminhoArquivo ?>" alt="Post imagem" style="width: 100%; border-radius: 8px; margin-top: 10px;">
                  
                  <?php elseif ($is_video && !empty($post['arquivo_url'])): ?>
                    <p style="color: #ccc; font-size: 0.9rem; margin-bottom: 10px;"><?= htmlspecialchars($post['descricao']) ?></p>
                     <video controls style="width: 100%; border-radius: 8px; margin-top: 10px;">
                        <source src="<?= $caminhoArquivo ?>" type="video/mp4">
                        Seu navegador não suporta vídeos.
                     </video>
                  <?php elseif (empty($post['arquivo_url']) && !empty($post['descricao'])): ?>
                      <div style="background-color: #2a2a2a; padding: 10px; border-radius: 5px;">
                          <p style="white-space: pre-wrap; color: #fff;"><?= htmlspecialchars($post['descricao']) ?></p>
                      </div>
                  <?php endif; ?>
                </div>
                
                <div class="post-footer" style="margin-top: 15px; border-top: 1px solid #333; padding-top: 10px; display: flex; gap: 15px;">
                   <button style="background: none; border: none; color: #aaa; cursor: pointer;"><i class="fi fi-rs-heart"></i> Curtir</button>
                </div>

              </div>
              <?php 
            endwhile; // Fim do loop WHILE
          else: // Se não houver posts
          ?>
            <div class="sem-posts" style="text-align: center; color: #aaa; padding: 20px;">
              <p>Nenhuma publicação nesta comunidade ainda. Seja o primeiro!</p>
            </div>
          <?php 
          endif; // Fim do bloco IF
          
          $stmt_posts->close(); // Fechar o statement da query
          ?>
        </div>

      </article>

      <section class="suggest">
        <article class="seguidores-suggestions">
          <div class="titulo">
            <h1>Sugestões de artistas</h1><a href="#">Ver mais</a>
          </div>
          <ul class="sugestoes">
            <li class="sugestao">
              <img src="../images/avatares/Users/profile.png" alt="Avatar do usuário">
              <div class="nome">
                <h1 class="name-exibição">teste 1</h1>
                <h2 class="name-user">@teste1</h2>
              </div>
              <button class="seguir-btn">Seguir</button>
            </li>
            <li class="sugestao">
              <img src="../images/avatares/Users/profile.png" alt="Avatar do usuário">
              <div class="nome">
                <h1 class="name-exibição">teste 1</h1>
                <h2 class="name-user">@teste1</h2>
              </div>
              <button class="seguir-btn">Seguir</button>
            </li>
            <li class="sugestao">
              <img src="../images/avatares/Users/profile.png" alt="Avatar do usuário">
              <div class="nome">
                <h1 class="name-exibição">teste 1</h1>
                <h2 class="name-user">@teste1</h2>
              </div>
              <button class="seguir-btn">Seguir</button>
            </li>
            <li class="sugestao">
              <img src="../images/avatares/Users/profile.png" alt="Avatar do usuário">
              <div class="nome">
                <h1 class="name-exibição">teste 1</h1>
                <h2 class="name-user">@teste1</h2>
              </div>
              <button class="seguir-btn">Seguir</button>
            </li>
            <li class="sugestao">
              <img src="../images/avatares/Users/profile.png" alt="Avatar do usuário">
              <div class="nome">
                <h1 class="name-exibição">teste 1</h1>
                <h2 class="name-user">@teste1</h2>
              </div>
              <button class="seguir-btn">Seguir</button>
            </li>
          </ul>
        </article>
        <footer>
          <p>Regras do HarpHub</p>
          <p>Política de Privacidade</p>
          <p>Contrato do Usuário</p>
          <p>Acessibilidade</p>
          <p>&copysr; 2025 HarpHub</p>
        </footer>
      </section>
    </section>
  </main>


  <div class="modal-overlay"></div>

  <div class="modal-other-perfil-container">
    <div class="modal-other-perfil-content-wrapper">
      <!-- O JS vai preencher aqui -->
    </div>
  </div>
</body>
<script src="../Scripts/TelaInicial.js"></script>
<script src="../Scripts/modals.js"></script>
<script src="../Scripts/comunidades.js"></script>


</html>