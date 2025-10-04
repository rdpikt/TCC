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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
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
      <ul>
        <li><a href="UsuarioLogado.php?feed=foryou"><i style="color: white;" class="fi fi-br-home"></i>Página Inicial</a></li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
        <li><a href="comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>
      </ul>
      <div class="tools">
        <ul>
          <li><a href="config.php"><i class="fi fi-rr-settings"></i>Configurações</a></li>
          <li><a href="ajuda.php"><i class="fi fi-rr-info"></i>Ajuda</a></li>
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