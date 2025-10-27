<?php
require "protect.php";
require "conexao.php";

$userId = $_SESSION['user_id'];
$user_avatar = $_SESSION['avatar'] ?? 'profile.png';
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
  <style>
    /* Fade-in */
    .fade-in {
      animation: fadeIn 0.7s;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: scale(0.95);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    /* Seções escondidas */
    .hidden {
      display: none;
    }

    .perfil-categorias ul li.active {
      font-weight: bold;
      color: #0077ff;
    }

    .post-item .overlay .autor {
      font-size: 0.8em;
      color: #ddd;
      margin-bottom: 5px;
    }
  </style>
</head>

<body>
 <header>
    <div class="search-container">
      <div class="search-bar-wrapper">
        <i class="fi-rr-search"></i>
        <input type="search" id="search-bar" class="search-bar" name="query" placeholder="Barra de pesquisa">
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

  <section class="main">
  <nav class="nav-side" id="menu">
      <div class="logotipo"><span>Harp</span>Hub</div>
      <ul id="pages">
        <li ><a  href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a></li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
        <li><a href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a class="selecionado" href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>
      </ul>
      <div class="tools">
        <ul>
          <li><a href="config.php"><i class="fi fi-rr-settings"></i>Config</a></li>
          <li><a href="ajuda.php"><i class="fi fi-rr-info"></i>Ajuda</a></li>
        </ul>
      </div>

    </nav>

    <section class="profile-section">
      <!-- Cabeçalho do Perfil -->
      <div class="profile-header">
        <div class="profile-avatar">
          <img class="avatar-user" src="<?php echo "../images/avatares/Users/" . htmlspecialchars($user_avatar); ?>"
            alt="Avatar do usuário">
        </div>
        <div class="profile-info">
          <h2><?php echo $_SESSION['user_name_completo']; ?></h2>
          <h3><?php echo $_SESSION['tipo_criador']; ?></h3>
          <p>@<?php echo $_SESSION['user_name']; ?></p>
          <p><?php echo $_SESSION['user_bio'] ?? 'Esta pessoa não adicionou uma bio ainda.'; ?></p>
        </div>
        <div class="seguidores-info">
          <div class="seguidores">
            <strong>Seguidores:</strong>
            <?php
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            echo $result->fetch_assoc()['total'];
            $stmt->close();
            ?>
          </div>
          <div class="seguindo">
            <strong>Seguindo:</strong>
            <?php
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            echo $result->fetch_assoc()['total'];
            $stmt->close();
            ?>
          </div>
        </div>
      </div>

      <!-- Categorias -->
      <div class="perfil-categorias">
        <ul>
          <li class="active" data-cat="portfolio">Portfolio</li>
          <li data-cat="posts">Posts</li>
          <li data-cat="reposts">Reposts</li>
          <li data-cat="salvos">Salvos</li>
          <li data-cat="curtidas">Curtidas</li>
        </ul>
      </div>

      <!-- Portfolio -->
      <div class="profile-section-content" id="portfolio-section">
        <h3>Meus Posts</h3>
        <div class="profile-posts-grid">
          <?php
          $stmt = $conn->prepare("SELECT * FROM obras WHERE portfolio_id = ? ORDER BY data_publicacao DESC");
          $stmt->bind_param("i", $userId);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows > 0):
            while ($post = $result->fetch_assoc()):
              ?>
              <div class="post-item fade-in">
                <?php if (!empty($post['arquivo_url'])): ?>
                  <img src="<?php echo "../images/uploads/" . htmlspecialchars($post['arquivo_url']); ?>" alt="Imagem do post">
                <?php endif; ?>
                <div class="overlay">
                  <div class="titulo"><?php echo htmlspecialchars($post['titulo']); ?></div>
                  <div class="descricao"><?php echo nl2br(htmlspecialchars($post['descricao'])); ?></div>
                  <span class="post-date"><?php echo date('d/m/Y H:i', strtotime($post['data_publicacao'])); ?></span>
                </div>
              </div>
              <?php
            endwhile;
          else:
            echo "<p>Você ainda não fez nenhum post.</p>";
          endif;
          $stmt->close();
          ?>
        </div>
      </div>

      <!-- Reposts -->
      <div class="profile-section-content hidden" id="reposts-section">
        <h3>Meus republicados</h3>
        <div class="profile-reposts-grid">
          <?php
          $stmt = $conn->prepare("
            SELECT r.id AS repost_id, r.created_at AS repost_date,
                   O.id AS post_id, O.titulo, O.descricao, O.arquivo_url, O.data_publicacao
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
                <div class="overlay">
                  <div class="titulo"><?php echo htmlspecialchars($post['titulo']); ?></div>
                  <div class="descricao"><?php echo nl2br(htmlspecialchars($post['descricao'])); ?></div>
                  <span class="post-date">
                    Publicado em: <?php echo date('d/m/Y H:i', strtotime($post['data_publicacao'])); ?>
                  </span><br>
                  <span class="repost-date">
                    Repostado em: <?php echo date('d/m/Y H:i', strtotime($post['repost_date'])); ?>
                  </span>
                </div>
              </div>
              <?php
            endwhile;
          else:
            echo "<p>Você ainda não republicou nenhum post.</p>";
          endif;
          $stmt->close();
          ?>
        </div>
      </div>

      <!-- Curtidas -->
      <div class="profile-section-content hidden" id="curtidas-section">
        <h3>Meus Posts Curtidos</h3>
        <div class="profile-posts-grid">
          <?php
          $stmt_curtidas = $conn->prepare("
            SELECT 
              c.data_curtida,
              O.id AS post_id, O.titulo, O.descricao, O.arquivo_url, O.data_publicacao,
              u.nome_user AS autor_nome
            FROM curtidas c
            JOIN obras O ON c.obra_id = O.id
            JOIN users u ON O.portfolio_id = u.id
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
                <div class="overlay">
                  <div class="titulo"><?php echo htmlspecialchars($post['titulo']); ?></div>
                  <div class="autor">por <?php echo htmlspecialchars($post['autor_nome']); ?></div>
                  <div class="descricao"><?php echo nl2br(htmlspecialchars($post['descricao'])); ?></div>
                  <span class="repost-date">
                    Curtido em: <?php echo date('d/m/Y H:i', strtotime($post['data_curtida'])); ?>
                  </span>
                </div>
              </div>
              <?php
            endwhile;
          else:
            echo "<p>Você ainda não curtiu nenhum post.</p>";
          endif;
          $stmt_curtidas->close();
          $conn->close();
          ?>
        </div>
      </div>
    </section>
  </section>

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