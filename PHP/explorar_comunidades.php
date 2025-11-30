<?php
require "protect.php";
require "conexao.php";

// ... (Toda a sua lógica PHP de 1 a 40) ...
$userId = $_SESSION['user_id'];

$stmt_user_tag = $conn->prepare("SELECT user_tag FROM users WHERE id = ?");
$stmt_user_tag->bind_param("i", $userId);
$stmt_user_tag->execute();
$result_user_tag = $stmt_user_tag->get_result();
$user_tag = $result_user_tag->fetch_assoc()['user_tag'] ?? null;

$stmt_count_member = $conn->prepare("SELECT COUNT(*) AS total_membro FROM comunidade_membros WHERE usuario_id = ?");
$stmt_count_member->bind_param("i", $userId);
$stmt_count_member->execute();
$result_count_member = $stmt_count_member->get_result();
$is_member_of_any = $result_count_member->fetch_assoc()['total_membro'] > 0;


if (!$is_member_of_any) {
    header('Location: criar_comunidade.php');
    exit(); 
}

$tipos_comunidades = [];
$cat_result = $conn->query("SELECT DISTINCT tipo_comunidade FROM comunidades");
while ($cat = $cat_result->fetch_assoc()) {
    $tipos_comunidades[] = $cat["tipo_comunidade"];
}

$sql = "SELECT 
    C.*, 
    (SELECT COUNT(1) FROM comunidade_membros WHERE comunidade_id = C.id AND usuario_id = ?) > 0 AS is_member 
    FROM comunidades C";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$comunidades = [];
if ($result->num_rows > 0) {
    while ($cat = $result->fetch_assoc()) {
        $comunidades[] = $cat;
    }
}

$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : '../images/profile.png';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comunidades</title>
  
  <link rel="stylesheet" href="../Styles/comunidade.css">
  <link rel="stylesheet" href="../Styles/global.css">
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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

    <section class="comunidade-main">
      
      <div class="header-comunidade">
        <div class="titulo-page">
           <h1>Explore Comunidades</h1>
        </div>
        <a href="criar_comunidade_form.php" class="btn-criar-comunidade">Criar Comunidade</a>
      </div>

      <h2 class="section-title">Recomendados para você</h2>
      <div class="comunidades-grid">
        <?php
        $itemRecomendado = 0;
        foreach ($comunidades as $cat):
          if (!empty($user_tag) && !empty($cat['imagem']) && $cat['tipo_comunidade'] === $user_tag):
            $itemRecomendado++;
            $is_member = $cat['is_member'] ?? false;
            $button_text = $is_member ? 'Sair' : 'Entrar';
            $button_class = $is_member ? 'btn-sair' : 'btn-entrar';
            ?>
            <div class="comunidade-card">
              <div class="card-img">
                <img src="../images/avatares/Comunidades/<?php echo htmlspecialchars($cat['imagem']) ?>" alt="Avatar">
              </div>
              
              <div class="card-info">
                <h3>
                  <?php if (isset($cat['privacidade']) && $cat['privacidade'] === 'publica'): ?>
                    <a href="sobre-comunidade.php?id=<?= $cat['id'] ?>"><?php echo htmlspecialchars($cat['nome']); ?></a>
                  <?php else: ?>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                  <?php endif; ?>
                </h3>
                <p><?php echo htmlspecialchars($cat['descricao']) ?></p>
              </div>

              <div class="card-action">
                <button class="<?= htmlspecialchars($button_class) ?>" data-comunidade-id="<?= $cat['id'] ?>">
                  <?= htmlspecialchars($button_text) ?>
                </button>
              </div>
            </div>
            <?php
          endif;
        endforeach;
        if ($itemRecomendado === 0) {
          echo "<p class='no-results'>Não há comunidades recomendadas disponíveis no momento.</p>";
        }
        ?>
      </div>

      <h2 class="section-title">Design e Crafts</h2>
      <div class="comunidades-grid">
        <?php
        $itemDesign = 0;
        foreach ($comunidades as $cat):
          if (!empty($cat['imagem']) && $cat['tipo_comunidade'] === 'Design'):
            $itemDesign++;
            $is_member = $cat['is_member'] ?? false;
            $button_text = $is_member ? 'Sair' : 'Entrar';
            $button_class = $is_member ? 'btn-sair' : 'btn-entrar';
            ?>
            <div class="comunidade-card">
              <div class="card-img">
                <img src="../images/avatares/Comunidades/<?php echo htmlspecialchars($cat['imagem']) ?>" alt="Avatar">
              </div>
              <div class="card-info">
                <h3>
                  <?php if (isset($cat['privacidade']) && $cat['privacidade'] === 'publica'): ?>
                    <a href="sobre-comunidade.php?id=<?= $cat['id'] ?>"><?php echo htmlspecialchars($cat['nome']); ?></a>
                  <?php else: ?>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                  <?php endif; ?>
                </h3>
                <p><?php echo htmlspecialchars($cat['descricao']) ?></p>
              </div>
              <div class="card-action">
                <button class="<?= htmlspecialchars($button_class) ?>" data-comunidade-id="<?= $cat['id'] ?>">
                  <?= htmlspecialchars($button_text) ?>
                </button>
              </div>
            </div>
            <?php
          endif;
        endforeach;
        if ($itemDesign === 0) {
          echo "<p class='no-results'>Não há comunidades de Design disponíveis no momento.</p>";
        }
        ?>
      </div>

      <h2 class="section-title">Literatura e Escrita</h2>
      <div class="comunidades-grid">
        <?php
        $itemLiteratura = 0;
        foreach ($comunidades as $cat):
          if (!empty($cat['imagem']) && ($cat['tipo_comunidade'] === 'literatura' || $cat['tipo_comunidade'] === 'escrita')):
            $itemLiteratura++;
            $is_member = $cat['is_member'] ?? false;
            $button_text = $is_member ? 'Sair' : 'Entrar';
            $button_class = $is_member ? 'btn-sair' : 'btn-entrar';
            ?>
            <div class="comunidade-card">
              <div class="card-img">
                 <img src="../images/avatares/Comunidades/<?php echo htmlspecialchars($cat['imagem']) ?>" alt="Avatar">
              </div>
              <div class="card-info">
                <h3>
                  <?php if (isset($cat['privacidade']) && $cat['privacidade'] === 'publica'): ?>
                    <a href="sobre-comunidade.php?id=<?= $cat['id'] ?>"><?php echo htmlspecialchars($cat['nome']); ?></a>
                  <?php else: ?>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                  <?php endif; ?>
                </h3>
                <p><?php echo htmlspecialchars($cat['descricao']) ?></p>
              </div>
              <div class="card-action">
                 <button class="<?= htmlspecialchars($button_class) ?>" data-comunidade-id="<?= $cat['id'] ?>">
                  <?= htmlspecialchars($button_text) ?>
                </button>
              </div>
            </div>
            <?php
          endif;
        endforeach;
        if ($itemLiteratura === 0) {
          echo "<p class='no-results'>Não há comunidades de literatura no momento.</p>";
        }
        ?>
      </div>

    </section>
  </main>
  <div class="modal-overlay"></div>

<div class="modal-other-perfil-container">
    <div class="modal-other-perfil-content-wrapper">
        <!-- O JS vai preencher aqui -->
    </div>
</div>
<script src="../Scripts/modals.js"></script>
<script src="../Scripts/comunidades.js"></script>
<script src="../Scripts/TelaInicial.js"></script>
</body>


</html>