<?php
require "protect.php"; // Protege a página para usuários autenticados
require "conexao.php";

$tipo_feed = $_GET['feed'] ?? 'foryou';

$userId = $_SESSION['user_id'];

if ($tipo_feed === 'foryou') {
  $sql = "
SELECT O.*, u.nome_user, u.user_avatar
FROM obras O
JOIN users u ON O.portfolio_id = u.id
ORDER BY RAND()
LIMIT 20
";

  $result = $conn->query($sql);
} elseif ($tipo_feed === 'seguindo') {
  $sql = "
    SELECT O.*, u.nome_user, u.user_avatar
    FROM obras O
    JOIN users u ON O.portfolio_id = u.id
    JOIN seguidores s ON s.seguido_id = u.id
    WHERE s.seguidor_id = ?
    ORDER BY O.data_publicacao DESC
    ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $result = $stmt->get_result();
}
// Verifica se o usuário segue alguém
$verifica_segue_sql = "SELECT COUNT(*) as total_seguidos FROM seguidores WHERE seguidor_id = ?";
$stmt_verifica = $conn->prepare($verifica_segue_sql);
$stmt_verifica->bind_param("i", $userId);
$stmt_verifica->execute();
$result_verifica = $stmt_verifica->get_result();
$row_verifica = $result_verifica->fetch_assoc();

$segue_alguem = $row_verifica['total_seguidos'] > 0;

if ($segue_alguem) {
  // Busca obras dos usuários seguidos
  $no_seguindo = "";
} else {
  $no_seguindo = "Você ainda não está seguindo ninguém. Explore a comunidade e comece a seguir autores que você gosta!";
}

// Verifica se existem obras
$count_sql = "SELECT COUNT(*) as total_linhas FROM obras";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();

if ($count_row['total_linhas'] < 1) {
  $no_obras = "Não há obras disponiveis";
} else {
  $no_obras = "";
}
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : '../images/profile.png';

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Navegação</title>
  <link rel="stylesheet" href="../Styles/telainicial.css">
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
        <li>perfil</li>
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

    <section class="navigation-user" id="NavigationUser">
      <div class="initial-options">
        <ul>
          <li><a href="?feed=foryou" <?php if ($tipo_feed === 'foryou')
            echo 'class="active"'; ?>>Para Você</a></li>
          <li><a href="?feed=seguindo" <?php if ($tipo_feed === 'seguindo')
            echo 'class="active"'; ?>>Seguindo</a></li>
        </ul>
      </div>
      <div class="conteudo" id="feed-conteudo">
        <?php if ($tipo_feed === 'seguindo') {
          echo $no_seguindo;
        } ?>
        <?php if ($tipo_feed === 'foryou') {
          echo $no_obras;
        } ?>
        <!-- Os posts iniciais são carregados aqui via PHP, mas os próximos serão via JS -->
        <?php while ($post = $result->fetch_assoc()) { ?>
          <article class="posts">
            <h1 class="no-obra"></h1>
            <div class="descricao-post">
              <ul>
                <li><span class="nome-desc">User: <?php echo htmlspecialchars($post['nome_user']); ?></span></li>
                <li>
                  <button class="btn-options">...</button>
                  <div class="modal-options">
                    <ul class="options-list">
                      <li>ocultar post</li>
                      <li>Denunciar post</li>
                    </ul>
                  </div>
                </li>
              </ul>
              <h1 class="titulo">Titulo: <?php echo htmlspecialchars($post['titulo']); ?></h1>
              <p>descricao: <br><?php echo htmlspecialchars($post['descricao']); ?></p>
            </div>
            <?php if (!empty($post['arquivo']) && !empty($post['tipo_imagem'])): ?>
              <div class="img-post">
                <img src="<?php echo "../images/uploads/" . $post['arquivo'] ?>" alt="Imagem do post">
              </div>
            <?php else: ?>
              <p>Sem imagem para este post.</p>
            <?php endif; ?>
            <div class="footer-post">
              <ul>
                <li>Comentários</li>
                <li>Repost</li>
                <li>Curtidas</li>
                <li>Compartilhar</li>
              </ul>
              <span>Salvar</span>
            </div>
          </article>
        <?php } ?>
      </div>
    </section>
  </section>
  <script src="../Scripts/TelaInicial.js"></script>
  <script src="../Scripts/modals.js"></script>
</body>

</html>