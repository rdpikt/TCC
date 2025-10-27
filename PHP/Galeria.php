<?php
require "protect.php";
require "conexao.php";

$obras = [];

// Buscar obras
$sql = "SELECT O.*, u.nome_user 
        FROM obras O 
        JOIN users u ON O.portfolio_id = u.id 
        ORDER BY O.data_publicacao DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
  $obras[] = $row;
}

// Buscar categorias reais do banco (para os botões)
$tags = [];
$tag_result = $conn->query("
  SELECT DISTINCT t.nome_tag 
  FROM tags t
  INNER JOIN obras_tags ot ON ot.id_tag = t.tag_id
  INNER JOIN obras o ON ot.id_obra = o.id ORDER BY t.nome_tag ASC

");

while ($tag = $tag_result->fetch_assoc()) {
  $tags[] = $tag['nome_tag'];
}

// Preparar statement para buscar tags de cada obra
$stmt = $conn->prepare("
  SELECT t.nome_tag
  FROM obras_tags ot
  INNER JOIN tags t ON ot.id_tag = t.tag_id
  WHERE ot.id_obra = ?
");

// Buscar tags de cada obra individualmente
foreach ($obras as &$obra) {
  $stmt->bind_param("i", $obra['id']);
  $stmt->execute();
  $result_tags = $stmt->get_result();
  $obra['tags'] = $result_tags->fetch_all(MYSQLI_ASSOC);
}

$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : '../images/profile.png';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tela Galeria</title>
  <link rel="stylesheet" href="../Styles/galeria.css">
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
        <input type="search" id="search-bar" class="search-bar" name="query" placeholder="Barra de pesquisa">
      </div>
      <div id="suggestions-box"></div>
    </div>
    <div class="nav-user">
      <ul>
        <li><a href="notificacoes.php"><i class="fi fi-rs-bell"></i></a></li>
        <li><span><img src="../images/avatares/Users/<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar do usuário"></span></li>
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

  <main class="galeria-main">
    <nav class="nav-side" id="menu">
      <div class="logotipo"><span>Harp</span>Hub</div>
      <ul>
        <li><a href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a></li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a class="selecionado" href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
        <li><a href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>
      </ul>
      <div class="tools">
        <ul>
          <li><a href="config.php"><i class="fi fi-rr-settings"></i>Config</a></li>
          <li><a href="ajuda.php"><i class="fi fi-rr-info"></i>Ajuda</a></li>
        </ul>
      </div>
    </nav>

    <h1>Galeria</h1>
    <div class="galeria-categorias" id="galeria-categorias">
      <button data-cat="all" class="cat-btn active">Todas</button>
      <?php if (empty($tags)): ?>
        <p>Não há postagens</p>
      <?php else: ?>
        <?php foreach ($tags as $tag): ?>
          <button data-cat="<?php echo htmlspecialchars($tag); ?>" class="cat-btn"><?php echo htmlspecialchars($tag); ?></button>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="galeria-grid" id="galeria-grid">
      <?php $col = 0; ?>
      <?php foreach ($obras as $obra): ?>
  <?php if (!empty($obra['arquivo_url']) && !empty($obra['tipo_imagem'])): ?>
    <?php
      // Gera lista de tags da obra
      $nomes_tags = array_column($obra['tags'], 'nome_tag');
      // Concatena as tags separadas por vírgula para usar no atributo data-cat
      $data_cat = htmlspecialchars(implode(',', $nomes_tags));

      // Alterna classes visuais
      $colClass = '';
      switch ($col % 4) {
        case 0: $colClass = 'wide short'; break;
        case 1: $colClass = 'narrow tall'; break;
        case 2: $colClass = 'wide short'; break;
        case 3: $colClass = ($col % 8 < 4) ? 'tall' : 'short'; break;
      }
    ?>
    <div class="galeria-item <?php echo $colClass; ?>" data-cat="<?php echo $data_cat; ?>">
      <img src="../images/uploads/<?php echo htmlspecialchars($obra['arquivo_url']); ?>" alt="Obra">
      <div class="overlay">
        <div class="titulo"><?php echo htmlspecialchars($obra['titulo']); ?></div>
        <div class="autor">por <?php echo htmlspecialchars($obra['nome_user']); ?></div>
        <?php if (!empty($nomes_tags)): ?>
          <div class="tags">
            <?php foreach ($nomes_tags as $tag_nome): ?>
              <span>#<?php echo htmlspecialchars($tag_nome); ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php $col++; ?>
  <?php endif; ?>
<?php endforeach; ?>
    </div>
  </main>

  <script src="../Scripts/modals.js"></script>
  <script src="../Scripts/FiltrarCategoria.js" defer></script>
</body>
</html>
