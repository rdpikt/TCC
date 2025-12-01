<?php
require "protect.php";
require "conexao.php";

$userId = $_SESSION['user_id'];
$user_avatar = $_SESSION['avatar'] ?? 'profile.png';

$ja_segue = false;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de Usuário</title>
  <link rel="stylesheet" href="../Styles/telainicial.css">
  <link rel="stylesheet" href="../Styles/perfil.css">
  <link rel="stylesheet" href="../Styles/global.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
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
        <li><a href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a class="selecionado" href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>

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
          <img class="avatar-large" src="<?php echo "../images/avatares/Users/" . htmlspecialchars($user_avatar); ?>"
            alt="Avatar do usuário">
        </div>

        <div class="profile-info-container">
          <div class="profile-top-row">
            <div class="name-group">
              <h2><?php echo $_SESSION['user_name_completo']; ?></h2>
              <span class="user-badge"><?php echo $_SESSION['tipo_criador']; ?></span>
            </div>
            
            <div class="action-group">
               <i class="fi fi-br-menu-dots options-icon"></i>
               <button class="btn-seguir <?php echo $ja_segue ? 'seguindo' : ''; ?>">
                   <?php echo $ja_segue ? 'Seguindo' : 'Seguir'; ?>
               </button>
            </div>
          </div>

          <div class="profile-handle">
            <p>@<?php echo $_SESSION['user_name']; ?></p>
          </div>

          <div class="profile-bio">
            <p><?= $_SESSION['user_bio'] ?></p>
          </div>

          <div class="profile-stats">
            <div class="stat-item">
              <?php
              $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = ?");
              $stmt->bind_param("i", $userId);
              $stmt->execute();
              $result = $stmt->get_result();
              $seguindo = $result->fetch_assoc()['total'];
              $stmt->close();
              ?>
              <strong><?php echo $seguindo; ?></strong> <span>Seguindo</span>
            </div>
            <div class="stat-item">
              <?php
              $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = ?");
              $stmt->bind_param("i", $userId);
              $stmt->execute();
              $result = $stmt->get_result();
              $seguidores = $result->fetch_assoc()['total'];
              $stmt->close();
              ?>
              <strong><?php echo $seguidores; ?></strong> <span>Seguidores</span>
            </div>
          </div>
        </div>
      </div>

      <div class="perfil-categorias">
        <ul>
          <li class="active" data-cat="posts">Posts</li>
          <li data-cat="reposts">Repost</li>
          <li data-cat="salvos">Salvos</li>
          <li data-cat="curtidas">Curtidas</li>
        </ul>
      </div>

<div class="profile-section-content" id="posts-section">
        <div class="profile-posts-grid">
          <?php
          // CORREÇÃO 1: Atualizamos a Query para trazer o total de curtidas de cada post
          $stmt = $conn->prepare("
            SELECT O.*, 
            (SELECT COUNT(*) FROM curtidas C WHERE C.obra_id = O.id) AS total_curtidas 
            FROM obras O 
            WHERE portfolio_id = ? 
            ORDER BY data_publicacao DESC
          ");
          $stmt->bind_param("i", $userId);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows > 0):
            while ($post = $result->fetch_assoc()):
              
              // CORREÇÃO 2: Verificamos se EU (dono do perfil) curti meu próprio post (para pintar o coração de vermelho)
              $stmt_curtida = $conn->prepare("SELECT * FROM curtidas WHERE usuario_id = ? AND obra_id = ?");
              $stmt_curtida->bind_param("ii", $userId, $post['id']);
              $stmt_curtida->execute();
              $curtido = $stmt_curtida->get_result()->num_rows > 0;
              $stmt_curtida->close();

              // Tratamento de tags (caso use no futuro)
              // $tags = ... (lógica opcional mantida simples para não quebrar)
              ?>
              
              <article class="posts fade-in" data-post-id="<?= $post['id'] ?>">
                
                <div class="descricao-post">
                  <ul>
                    <li>
                        <span class="avatar-desc">
                            <img src="../images/avatares/Users/<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar">
                        </span>
                    </li>
                    <li><span class="nomeUsr"><?= htmlspecialchars($_SESSION['user_name_completo']) ?></span></li>
                    <li><span class="nomeEX-desc">@<?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
                  </ul>
                  <div class="options-post">
                    <i class="fi fi-br-menu-dots" id="menu-dots"></i>
                  </div>
                </div>

                <?php if (!empty($post['arquivo_url'])): ?>
                  <div class="img-post">
                    <img src="../images/uploads/<?= htmlspecialchars($post['arquivo_url']) ?>" alt="Imagem do post">
                  </div>
                <?php endif; ?>

                <div class="footer-post">
                  <ul class="interactions-post">
                    <li><button type="button"><i class="fi fi-rr-comment"></i></button></li>
                    <li><button class="repostar-btn"><i class="fi fi-rr-refresh"></i></button></li>
                    
                    <li>
                        <button class="curtida <?= $curtido ? 'curtido' : '' ?>">
                            <svg width="1.5rem" height="1.5rem" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                            </svg>
                        </button>
                        <p><?= $post['total_curtidas'] ?></p>
                    </li>

                    <li><button class="btn-share" type="button"><i class="fi fi-rs-redo"></i></button></li>
                    <li>
                        <button class="salvar-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1.5rem" width="1.5rem" viewBox="0 0 384 512">
                                <path d="M0 48C0 21.5 21.5 0 48 0H336c26.5 0 48 21.5 48 48V464L192 352 0 464V48z" />
                            </svg>
                        </button>
                    </li>
                  </ul>
                </div>

              </article>
              <?php
            endwhile;
          else:
            echo "<p style='color: #888; padding: 20px; text-align: center;'>Ainda não há posts.</p>";
          endif;
          $stmt->close();
          ?>
        </div>
      </div>

      <div class="profile-section-content hidden" id="reposts-section">
        <div class="profile-posts-grid">
          <?php
          $stmt = $conn->prepare("
            SELECT r.id AS repost_id, O.arquivo_url
            FROM reposts r
            JOIN obras O ON r.original_post_id = O.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
          ");
          $stmt->bind_param('i', $userId);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows > 0):
            while ($post = $result->fetch_assoc()):
              ?>
              <div class="post-item fade-in">
                <?php if (!empty($post['arquivo_url'])): ?>
                  <img src="<?php echo "../images/uploads/" . htmlspecialchars($post['arquivo_url']); ?>" alt="Imagem do post">
                <?php endif; ?>
              </div>
              <?php
            endwhile;
          else:
            echo "<p style='color: #888; padding: 20px; text-align: center;'>Nenhum repost ainda.</p>";
          endif;
          $stmt->close();
          ?>
        </div>
      </div>

      <div class="profile-section-content hidden" id="salvos-section">
         <p style='color: #888; padding: 20px; text-align: center;'>Itens salvos aparecerão aqui.</p>
      </div>

      <div class="profile-section-content hidden" id="curtidas-section">
        <div class="profile-posts-grid">
          <?php
          $stmt_curtidas = $conn->prepare("
            SELECT O.arquivo_url 
            FROM curtidas c
            JOIN obras O ON c.obra_id = O.id
            WHERE c.usuario_id = ?
            ORDER BY c.data_curtida DESC
          ");
          $stmt_curtidas->bind_param('i', $userId);
          $stmt_curtidas->execute();
          $result_curtidas = $stmt_curtidas->get_result();

          if ($result_curtidas->num_rows > 0):
            while ($post = $result_curtidas->fetch_assoc()):
              ?>
              <div class="post-item fade-in">
                <?php if (!empty($post['arquivo_url'])): ?>
                  <img src="<?php echo "../images/uploads/" . htmlspecialchars($post['arquivo_url']); ?>" alt="Imagem do post">
                <?php endif; ?>
              </div>
              <?php
            endwhile;
          else:
            echo "<p style='color: #888; padding: 20px; text-align: center;'>Nenhuma curtida ainda.</p>";
          endif;
          $stmt_curtidas->close();
          $conn->close();
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
        <p>&copy; 2025 HarpHub</p>
      </footer>
    </section>

  </main>
  <div class="modal-overlay"></div>

<div class="modal-other-perfil-container">
    <div class="modal-other-perfil-content-wrapper">
        <!-- O JS vai preencher aqui -->
    </div>
</div>
<script src="../Scripts/TelaInicial.js"></script>
<script src="../Scripts/modals.js"></script>
  <script>
    const btnsCategoria = document.querySelectorAll('.perfil-categorias ul li');
    const sections = {
      portfolio: document.getElementById('portfolio-section'),
      reposts: document.getElementById('reposts-section'),
      curtidas: document.getElementById('curtidas-section')
    };

    btnsCategoria.forEach(btn => {
      btn.addEventListener('click', () => {
        btnsCategoria.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const cat = btn.getAttribute('data-cat');

        // Esconde todas as seções
        Object.values(sections).forEach(sec => sec.classList.add('hidden'));

        // Mostra apenas a seção escolhida
        if (sections[cat]) {
          sections[cat].classList.remove('hidden');
        }
      });
    });
  </script>
  
</body>
</html>