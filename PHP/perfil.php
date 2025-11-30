<?php
session_start();

require "protect.php";
require "conexao.php";

// ID do usuário logado (garantido por protect.php)
$logged_in_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($logged_in_user_id <= 0) {
  var_dump($logged_in_user_id);
    // Proteção extra caso protect.php não redirecione
    header('Location: login.php');
    exit();
}

// Determina o ID do perfil que está sendo visitado
$profile_id = $logged_in_user_id; // Por padrão, é o perfil do próprio usuário
if (isset($_GET['id']) && $_GET['id'] !== '' && is_numeric($_GET['id'])) {
    $profile_id = (int)$_GET['id'];
}

$is_own_profile = ($profile_id === $logged_in_user_id);

// Busca os dados do usuário do perfil visitado
$stmt = $conn->prepare("SELECT nome_completo, nome_user, user_tag, bio, user_avatar FROM users WHERE id = ?");
if (!$stmt) {
    die("Erro na preparação da consulta de usuário: " . $conn->error);
}
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Mensagem amigável — você pode redirecionar para uma página 404 personalizada
    die("Usuário não encontrado.");
}
$profile_data = $result->fetch_assoc();
$stmt->close();

// Busca contagens de seguidores e seguindo (usando $profile_id)
$stmt_following = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = ?");
if (!$stmt_following) {
    die("Erro na consulta following: " . $conn->error);
}
$stmt_following->bind_param("i", $profile_id);
$stmt_following->execute();
$following_count_row = $stmt_following->get_result()->fetch_assoc();
$following_count = isset($following_count_row['total']) ? (int)$following_count_row['total'] : 0;
$stmt_following->close();

$stmt_followers = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = ?");
if (!$stmt_followers) {
    die("Erro na consulta followers: " . $conn->error);
}
$stmt_followers->bind_param("i", $profile_id);
$stmt_followers->execute();
$followers_count_row = $stmt_followers->get_result()->fetch_assoc();
$followers_count = isset($followers_count_row['total']) ? (int)$followers_count_row['total'] : 0;
$stmt_followers->close();

// Verifica se o usuário logado já segue o dono do perfil (se não for o próprio perfil)
$ja_segue = false;
if (!$is_own_profile) {
    $stmt_check_follow = $conn->prepare("SELECT id FROM seguidores WHERE seguidor_id = ? AND seguido_id = ? LIMIT 1");
    if ($stmt_check_follow) {
        $stmt_check_follow->bind_param("ii", $logged_in_user_id, $profile_id);
        $stmt_check_follow->execute();
        $res_chk = $stmt_check_follow->get_result();
        $ja_segue = ($res_chk && $res_chk->num_rows > 0);
        $stmt_check_follow->close();
    }
}

// Tratamento de avatar (fallback caso não exista)
$avatar_filename = !empty($profile_data['user_avatar']) ? $profile_data['user_avatar'] : 'default.png';
$session_avatar = isset($_SESSION['avatar']) && $_SESSION['avatar'] !== '' ? $_SESSION['avatar'] : 'default.png';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de <?php echo htmlspecialchars($profile_data['nome_user'], ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="../Styles/telainicial.css">
  <link rel="stylesheet" href="../Styles/perfil.css">
  <link rel="stylesheet" href="../Styles/global.css">
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>

<body>
 <header>
    <div class="search-container">
        <div class="search-bar-wrapper">
            <i class="fi fi-rr-search"></i>
            <input type="search" id="search-bar" class="search-bar" name="query" placeholder="Barra de pesquisa">
        </div>
        <div id="suggestions-box"></div>
    </div>
    <div class="nav-user">
      <ul>
        <li><a href="notificacoes.php"><i class="fi fi-rs-bell"></i></a></li>
        <li>
            <span>
                <img src="../images/avatares/Users/<?php echo htmlspecialchars($session_avatar, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar do usuário" onerror="this.src='../images/avatares/Users/default.png'">
            </span>
        </li>
      </ul>
    </div>
    <div class="modal-perfil">
      <ul>
        <li><a href="perfil.php">Perfil</a></li>
        <li>Trocar de conta</li>
        <li>
          <form action="logout.php" method="POST">
            <input type="submit" value="Sair da conta">
          </form>
        </li>
      </ul>
    </div>
  </header>

    <main>
    <nav class="nav-side" id="menu">
      <div class="logotipo"><span>Harp</span>Hub</div>
      <ul id="pages">
        <li><a href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a></li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
        <li><a href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a class="selecionado" href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>
        <li><a href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a class="selecionado" href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>
      </ul>
      <div class="tools">
        <ul>
          <li><a href="config.php"><i class="fi fi-rr-settings"></i>Config</a></li>
          <li><a href="config.php"><i class="fi fi-rr-settings"></i>Config</a></li>
          <li><a href="ajuda.php"><i class="fi fi-rr-info"></i>Ajuda</a></li>
        </ul>
      </div>

    </nav>

    <section class="navigation-user">
      
      <div class="profile-header-container">
        <div class="profile-avatar-container">
          <img class="avatar-large" src="../images/avatares/Users/<?php echo htmlspecialchars($avatar_filename, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar de <?php echo htmlspecialchars($profile_data['nome_user'], ENT_QUOTES, 'UTF-8'); ?>" onerror="this.src='../images/avatares/Users/default.png'">
        </div>

        <div class="profile-info-container">
          <div class="profile-top-row">
            <div class="name-group">
              <h2><?php echo htmlspecialchars($profile_data['nome_completo'], ENT_QUOTES, 'UTF-8'); ?></h2>
              <span class="user-badge"><?php echo htmlspecialchars($profile_data['user_tag'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            
            <div class="action-group">
                <?php if ($is_own_profile): ?>
                    <a href="config.php" class="btn-editar-perfil">Editar Perfil</a>
                <?php else: ?>
                    <button id="btn-seguir" class="btn-seguir <?php echo $ja_segue ? 'seguindo' : ''; ?>" data-profile-id="<?php echo $profile_id; ?>">
                        <?php echo $ja_segue ? 'Seguindo' : 'Seguir'; ?>
                    </button>
                <?php endif; ?>
            </div>
          </div>

          <div class="profile-handle">
            <p>@<?php echo htmlspecialchars($profile_data['nome_user'], ENT_QUOTES, 'UTF-8'); ?></p>
          </div>

          <div class="profile-bio">
            <p><?php echo nl2br(htmlspecialchars($profile_data['bio'] || '', ENT_QUOTES, 'UTF-8')); ?></p>
          </div>

          <div class="profile-stats">
            <div class="stat-item">
              <strong><?php echo number_format($following_count); ?></strong> <span>Seguindo</span>
            </div>
            <div class="stat-item">
              <strong id="followers-count"><?php echo number_format($followers_count); ?></strong> <span>Seguidores</span>
            </div>
          </div>
        </div>
      </div>

      <div class="perfil-categorias">
        <ul>
          <li class="active" data-cat="posts">Posts</li>
          <li data-cat="reposts">Repost</li>
          <li class="active" data-cat="posts">Posts</li>
          <li data-cat="reposts">Repost</li>
          <li data-cat="salvos">Salvos</li>
          <li data-cat="curtidas">Curtidas</li>
        </ul>
      </div>

      <div class="profile-section-content" id="posts-section">
      <div class="profile-section-content" id="posts-section">
        <div class="profile-posts-grid">
          <?php
          $stmt_posts = $conn->prepare("SELECT * FROM obras WHERE portfolio_id = ? ORDER BY data_publicacao DESC");
          if ($stmt_posts) {
              $stmt_posts->bind_param("i", $profile_id);
              $stmt_posts->execute();
              $result_posts = $stmt_posts->get_result();

              if ($result_posts && $result_posts->num_rows > 0):
                while ($post = $result_posts->fetch_assoc()):
                  ?>
                  <div class="post-item fade-in">
                    <?php if (!empty($post['arquivo_url'])): ?>
                      <img src="../images/uploads/<?php echo htmlspecialchars($post['arquivo_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem do post" onerror="this.style.display='none'">
                    <?php endif; ?>
                  </div>
                  <?php
                endwhile;
              else:
                echo "<p style='color: #888; padding: 20px; text-align: center;'>Ainda não há posts.</p>";
              endif;
              $stmt_posts->close();
          } else {
              echo "<p style='color: #888; padding: 20px; text-align: center;'>Erro ao carregar posts.</p>";
          }
          ?>
        </div>
      </div>
      
      <!-- Outras seções (reposts, curtidas, etc.) podem ser adicionadas como divs com id "reposts-section", "salvos-section", "curtidas-section" -->

    </section>
    
    </main>
<script src="../Scripts/TelaInicial.js"></script>
<script src="../Scripts/modals.js"></script>
<script src="../Scripts/seguir.js"></script>
<script>
    // Script para alternar categorias (mantido aqui para simplicidade)
    document.querySelectorAll('.perfil-categorias ul li').forEach(btn => {
      btn.addEventListener('click', () => {
        const cat = btn.getAttribute('data-cat');
        
        document.querySelectorAll('.perfil-categorias ul li').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Esconde todas as seções com a classe profile-section-content
        document.querySelectorAll('.profile-section-content').forEach(sec => sec.classList.add('hidden'));
        
        // Mostra a seção correspondente, se existir
        const sectionToShow = document.getElementById(cat + '-section');
        if(sectionToShow) {
            sectionToShow.classList.remove('hidden');
        }
      });
    });
</script>
</body>
</html>
