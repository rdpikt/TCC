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
</head>

<body>
  <header>
    <div class="logotipo">LOGO</div>
    <input type="search" name="search-bar" id="search-bar" placeholder="Barra de pesquisa">
    <div class="nav-user">
      <ul>
        <li><span><a href="notificacoes.php">notificações</a></span></li>
        <li><span><?php echo $_SESSION['user_name']; ?></span></li>
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
      <div class="user-avatar">
        <div class="user-avatar-img">
          <img src="<?php echo "../images/avatares/Users/".htmlspecialchars($user_avatar); ?>" alt="Avatar do usuário">
        </div>
        <span><?php echo $_SESSION['user_name']; ?></span>
      </div>
      <ul>
      <li><a href="UsuarioLogado?feed=foryou">Página Inicial</a></li>
      <li><a href="?feed=seguindo">Seguindo</a></li>
      <li><a href="Galeria.php">Galeria</a></li>
      <li><a href="EnviarArquivos.php">Criar Post</a></li>
      <li><a href="comunidades.php">Comunidades</a></li>
      <li><a href="perfil.php">Perfil</a></li>
    </ul>
      <div class="tools">
        <ul>
          <li><a href="config.php">Configurações</a></li>
          <li><a href="ajuda.php">Ajuda</a></li>
        </ul>
      </div>
    </nav>

  <section class="profile-section">
    <div class="profile-header">
      <div class="profile-avatar">
        <img src="<?php echo "../images/avatares/Users/".htmlspecialchars($user_avatar); ?>" alt="Avatar do usuário">
      </div>
      <div class="profile-info">
        <h2><?php echo $_SESSION['user_name_completo']; ?></h2>
        <h2><?php echo $_SESSION['tipo_criador'];?></h2>
        <p>@<?php echo $_SESSION['user_name']; ?></p>
        <p><?php echo $_SESSION['user_bio'] ?? 'Esta pessoa não adicionou uma bio ainda.'; ?></p>
      </div>
      <div class="seguidores-info">
        <div class="seguidores">
          <strong>Seguidores:</strong> <?php
          $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = ?");
          $stmt->bind_param("i", $userId);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          echo $row['total'];
          $stmt->close();
          ?>
        </div>
        <div class="seguindo">
          <strong>Seguindo:</strong> <?php
          $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = ?");
          $stmt->bind_param("i", $userId);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          echo $row['total'];
          $stmt->close();
          ?>
        </div>
      </div>
    </div>
    <div class="perfil-categorias">
      <ul>
        <li class="active">Portfolio</li>
        <li>posts</li>
        <li>repost</li>
        <li>Salvos</li>
        <li>Curtidas</li>
      </ul>
    </div>
    <div class="profile-posts">
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
          <div class="post-item">
            <?php if (!empty($post['arquivo'])): ?>
              <img src="<?php echo "../images/uploads/". $post['arquivo']?>" alt="Imagem do post">
            <?php endif; ?>
            <div class="overlay">
              <div class="titulo"><?php echo htmlspecialchars($post['titulo']); ?></div>
              <div class="descricao"><?php echo nl2br(htmlspecialchars($post['descricao'])); ?></div>
              <span class="post-date"><?php echo date('d/m/Y H:i', strtotime($post['data_publicacao'])); ?></span>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>Você ainda não fez nenhum post.</p>
      <?php endif;
      $stmt->close();
      $conn->close();
      ?>
      </div>
    </div>
  </section>

  <section class="Posts_curtidas">

  </section>
    </section>
</body>
<script src="../Scripts/modals.js"></script>
<script src="../Scripts/PerfilOpcoes.js"></script>

</html>