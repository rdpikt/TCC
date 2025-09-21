<?php
require "protect.php";
require "conexao.php";

// Buscar categorias reais do banco (exemplo)
$categorias = [];
$cat_result = $conn->query("SELECT DISTINCT tipo_obra FROM obras");
while ($cat = $cat_result->fetch_assoc()) {
  $categorias[] = $cat['tipo_obra'];
}
// Buscar obras
$sql = "SELECT O.*, u.nome_user FROM obras O JOIN users u ON O.portfolio_id = u.id ORDER BY O.data_publicacao DESC";
$result = $conn->query($sql);
$obras = [];
while ($obra = $result->fetch_assoc()) {
  $obras[] = $obra;
}

$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : '../images/profile.png';

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tela Galeria</title>
  <link rel="stylesheet" href="../Styles/telainicial.css">
  <link rel="stylesheet" href="../Styles/galeria.css">
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
  <nav class="nav-side" id="menu">
      <div class="user-avatar">
        <div class="user-avatar-img">
          <img src="<?php echo "../images/avatares/Users/" . htmlspecialchars($user_avatar); ?>"
            alt="Avatar do usuário">
        </div>
        <span><?php echo $_SESSION['user_name']; ?></span>
      </div>
      <ul>
        <li><a href="UsuarioLogado?feed=foryou">Página Inicial</a></li>
        <li><a href="UsuarioLogado?feed=seguindo">Seguindo</a></li>
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
  <main class="galeria-main">
    <h1 >Galeria</h1>
    <div class="galeria-categorias" id="galeria-categorias">
      <button data-cat="all" class="cat-btn active">Todas</button>
      <?php foreach ($categorias as $cat): ?>
        <button data-cat="<?php echo htmlspecialchars($cat); ?>"
          class="cat-btn"><?php echo htmlspecialchars($cat); ?></button>
      <?php endforeach; ?>
    </div>
    <div class="galeria-grid" id="galeria-grid">
      <?php $col = 0;
      foreach ($obras as $obra): ?>
        <?php if (!empty($obra['arquivo_url']) && !empty($obra['tipo_imagem'])): ?>
          <?php
          // Alterna classes para cada coluna
          $colClass = '';
          switch ($col % 4) {
            case 0:
              $colClass = 'wide short';
              break;
            case 1:
              $colClass = 'narrow tall';
              break;
            case 2:
              $colClass = 'wide short';
              break;
            case 3:
              $colClass = ($col % 8 < 4) ? 'tall' : 'short';
              break;
          }
          ?>
          <div class="galeria-item <?php echo $colClass; ?>" data-cat="<?php echo htmlspecialchars($obra['tipo_obra']); ?>">
            <img src="../images/uploads/<?php echo htmlspecialchars($obra['arquivo_url']); ?>" alt="Obra">
            <div class="overlay">
              <div class="titulo"><?php echo htmlspecialchars($obra['titulo']); ?></div>
              <div class="autor">por <?php echo htmlspecialchars($obra['nome_user']); ?></div>
            </div>
          </div>
          <?php $col++; ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </main>
  <script src="../Scripts/FiltrarCategoria.js"></script>
  <script src="../Scripts/modals.js"></script>
</body>

</html>