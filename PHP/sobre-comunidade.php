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

$comunidade = $result->fetch_assoc();

if ($comunidade) {
  $qtdMembros = $comunidade["qtd_membros"];
} else {
  $qtdMembros = 0;
}

$userId = $_SESSION['user_id'];

$sql_membro = "SELECT id FROM comunidade_membros WHERE comunidade_id = ? AND usuario_id = ? AND cargo = 'membro'";
$stmt_membro = $conn->prepare($sql_membro);
$stmt_membro->bind_param("ii", $comunidade_id, $userId);
$stmt_membro->execute();
$result_membro = $stmt_membro->get_result();

$is_member = $result_membro->num_rows > 0;

// --- CORREÇÃO AQUI ---
// O texto permanece o mesmo
$button_text = $is_member ? 'Sair' : 'Entrar';

// A classe DEVE conter 'btn-sair' ou 'btn-entrar' para o JavaScript funcionar.
// Adicionamos 'btn-seguir' apenas para puxar o CSS de estilo.
if ($is_member) {
    // É membro: precisa ter a classe 'btn-sair' para o JS
    $button_class = 'btn-sair btn-seguir seguindo'; 
} else {
    // Não é membro: precisa ter a classe 'btn-entrar' para o JS
    $button_class = 'btn-entrar btn-seguir';
}
// ---------------------

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($comunidade['nome']) ? htmlspecialchars($comunidade['nome']) : 'Comunidade'; ?></title>
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
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
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
      
      <div class="profile-header-container">
        
        <div class="profile-avatar-container">
           <img class="avatar-large" src="../images/avatares/Comunidades/<?php echo htmlspecialchars($comunidade['imagem']); ?>" alt="Avatar da Comunidade">
        </div>

        <div class="profile-info-container">
          <div class="profile-top-row">
            <div class="name-group">
              <h2><?php echo htmlspecialchars($comunidade['nome']); ?></h2>
              <span class="user-badge"><?php echo htmlspecialchars($comunidade['tipo_comunidade']); ?></span>
            </div>
            
            <div class="action-group">
               <div class="ret">
                  <button><i class="fi fi-br-menu-dots options-icon" id="menu-dots"></i></button>
                  <div class="modal-reticencias">
                    <ul class="options-list">
                      <li><button class="denunciar-comunidade"><i class="fi fi-br-flag"></i><a href="gerenciar_membro_comunidade.php"> Denunciar</a></button></li>
                      <li><button class="editar-comunidade"><i class="fi fi-br-edit"></i> Editar</button></li>
                    </ul>
                  </div>
               </div>
               
               <button class="<?= htmlspecialchars($button_class) ?>" data-comunidade-id="<?= $comunidade_id ?>">
                 <?= htmlspecialchars($button_text) ?>
               </button>
            </div>
          </div>

          <div class="profile-handle">
             <p>Comunidade</p>
          </div>

          <div class="profile-bio">
            <p><?php echo htmlspecialchars($comunidade['descricao']); ?></p>
          </div>

          <div class="profile-stats">
            <div class="stat-item">
              <strong><?php echo htmlspecialchars($qtdMembros); ?></strong> <span>Membros</span>
            </div>
          </div>
        </div>
      </div>

      <div class="perfil-categorias">
        <ul>
            <li class="active">Geral</li>
            <li>Comentários</li>
            <li>Regras</li>
            <?php if ($is_member): ?>
                <li><a href="EnviarArquivos.php?comunidade=<?= $comunidade_id ?>" style="color:inherit;">Criar Post</a></li>
            <?php endif; ?>
        </ul>
      </div>

      <div class="profile-section-content" id="posts-section">
        <div class="profile-posts-grid">
          <?php
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
              $extensao = strtolower($post['tipo_imagem']);
              $is_image = in_array($extensao, ['png', 'jpg', 'jpeg']);
              $is_video = in_array($extensao, ['mp4', 'avi', 'wmv']);

              if ($is_image) {
                  $caminhoArquivo = "../images/uploads/" . htmlspecialchars($post['arquivo_url']);
              } elseif ($is_video) {
                  $caminhoArquivo = "../images/uploads/videos/" . htmlspecialchars($post['arquivo_url']);
              } else {
                  $caminhoArquivo = '';
              }
          ?>
              <div class="comunidade-post-card">
                
                <div class="post-header-simple">
                  <img src="../images/avatares/Users/<?= htmlspecialchars($avatarAutor) ?>" alt="Avatar">
                  <span>@<?= htmlspecialchars($post['user_handle']) ?></span>
                </div>

                <div class="post-media">
                  <?php if ($is_image && !empty($post['arquivo_url'])): ?>
                    <img src="<?= $caminhoArquivo ?>" alt="Post imagem">
                  <?php elseif ($is_video && !empty($post['arquivo_url'])): ?>
                     <video controls>
                        <source src="<?= $caminhoArquivo ?>" type="video/mp4">
                     </video>
                  <?php elseif (!empty($post['descricao'])): ?>
                      <div class="text-only-post">
                          <p><?= htmlspecialchars($post['titulo']) ?></p>
                          <small><?= mb_strimwidth(htmlspecialchars($post['descricao']), 0, 100, "...") ?></small>
                      </div>
                  <?php endif; ?>
                </div>

              </div>
              <?php 
            endwhile; 
          else: 
          ?>
            <p style='color: #888; padding: 20px; text-align: center; width:100%; grid-column: 1/-1;'>Nenhuma publicação nesta comunidade ainda.</p>
          <?php 
          endif; 
          $stmt_posts->close(); 
          ?>
        </div>
      </div>

    </section>

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

  </main>

  <div class="modal-overlay"></div>
  <div class="modal-other-perfil-container">
    <div class="modal-other-perfil-content-wrapper"></div>
  </div>
</body>
<script src="../Scripts/TelaInicial.js"></script>
<script src="../Scripts/modals.js"></script>
<script src="../Scripts/comunidades.js"></script>
</html>