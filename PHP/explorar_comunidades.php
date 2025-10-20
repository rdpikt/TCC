<?php
require "protect.php";
require "conexao.php";

$userId = $_SESSION['user_id'];

// Buscar a user_tag do usuário logado para as recomendações
$stmt_user_tag = $conn->prepare("SELECT user_tag FROM users WHERE id = ?");
$stmt_user_tag->bind_param("i", $userId);
$stmt_user_tag->execute();
$result_user_tag = $stmt_user_tag->get_result();
$user_tag = $result_user_tag->fetch_assoc()['user_tag'] ?? null;

//busca tipos de comunidades
$tipos_comunidades = [];
$cat_result = $conn->query("SELECT DISTINCT tipo_comunidade FROM comunidades");
while ($cat = $cat_result->fetch_assoc()) {
  $tipos_comunidades[] = $cat["tipo_comunidade"];
}
//buscar as comunidades e verificar se o usuário é membro
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
  <title>Perfil de Usuário</title>
  <link rel="stylesheet" href="../Styles/telainicial.css">
  <link rel="stylesheet" href="../Styles/global.css">
  <link rel="stylesheet" href="../Styles/comunidade.css">
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
        <input type="search" id="search-bar" class="search-bar" name="query" placeholder="🔍 Barra de pesquisa">
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
        <li ><a href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a></li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
        <li><a class="selecionado"  href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>

      </ul>
      <div class="tools">
        <ul>
          <li><a href="config.php"><i class="fi fi-rr-settings"></i>Config</a></li>
          <li><a href="ajuda.php"><i class="fi fi-rr-info"></i>Ajuda</a></li>
        </ul>
      </div>

    </nav>

    <section class="content">
      <div class="titulo">
        <h1>Explorar Comunidades</h1>
        <a href="criar_comunidade_form.php" class="btn-criar-comunidade">Criar Comunidade</a>
      </div>
      <div class="titulo-comunidade">
        <h2>Recomendado</h2>
      </div>
      <div class="comunidades-lista Recomendado">
        <?php 
        $itemRecomendado = 0;
        foreach ($comunidades as $cat):
          if (!empty($user_tag) && !empty($cat['imagem']) && $cat['tipo_comunidade'] === $user_tag):
            $itemRecomendado++;
            $is_member = $cat['is_member'] ?? false;
            $button_text = $is_member ? 'Sair' : 'Entrar';
            $button_class = $is_member ? 'Sair-Comunidade' : 'Entrar-Comunidade';
            ?>
            <div class="comunidade-card" data-cat="Recomendado">
              <div class="top">
                <img src="../images/avatares/Comunidades/<?php echo htmlspecialchars($cat['imagem']) ?>"
                  alt="Avatar da comunidade">
                <h2><?php echo htmlspecialchars($cat['nome']); ?></h2>
                <button class="<?= htmlspecialchars($button_class) ?>" data-comunidade-id="<?= $cat['id'] ?>"><?= htmlspecialchars($button_text) ?></button>
              </div>
              <div class="bottom">
                <p><?php echo htmlspecialchars($cat['descricao']) ?></p>
              </div>
            </div>
          <?php
          endif;
        endforeach;
 if ($itemRecomendado === 0) {
          echo "<p>Não há comunidades recomendados disponíveis no momento.</p>";
        }
        ?>
      </div>
      <div class="titulo-comunidade">
        <h2>Design e Crafts</h2>
      </div>
      <div class="comunidades-lista Design">
        <?php
        $itemDesign = 0;
        foreach ($comunidades as $cat):
          if (!empty($cat['imagem']) && $cat['tipo_comunidade'] === 'Design'):
            $itemDesign++;
            $is_member = $cat['is_member'] ?? false;
            $button_text = $is_member ? 'Sair' : 'Entrar';
            $button_class = $is_member ? 'Sair-Comunidade' : 'Entrar-Comunidade';
            ?>
            <div class="comunidade-card" data-cat="Design">
              <div class="top">
                <img src="../images/avatares/Comunidades/<?php echo htmlspecialchars($cat['imagem']) ?>"
                  alt="Avatar da comunidade">
                <h2><?php echo htmlspecialchars($cat['nome']); ?></h2>
                <button class="<?= htmlspecialchars($button_class) ?>" data-comunidade-id="<?= $cat['id'] ?>"><?= htmlspecialchars($button_text) ?></button>
              </div>
              <div class="bottom">
                <p><?php echo htmlspecialchars($cat['descricao']) ?></p>
              </div>
            </div>
            <?php
          endif;
        endforeach;
        if ($itemDesign === 0) {
          echo "<p>Não há comunidades de Design disponíveis no momento.</p>";
        }
        ?>
      </div>

      <div class="titulo-comunidade">
        <h2>literatura e Escrita</h2>
      </div>
      <div class="comunidades-lista literatura">
        <?php
        $itemLiteratura = 0;
        foreach ($comunidades as $cat):
          if (!empty($cat['imagem']) && ($cat['tipo_comunidade'] === 'literatura' || $cat['tipo_comunidade'] === 'escrita')):
            $itemLiteratura++;
            $is_member = $cat['is_member'] ?? false;
            $button_text = $is_member ? 'Sair' : 'Entrar';
            $button_class = $is_member ? 'Sair-Comunidade' : 'Entrar-Comunidade';
            ?>
            <div class="comunidade-card" data-cat="literatura">
              <div class="top">
                <img src="../images/avatares/Comunidades/<?php echo htmlspecialchars($cat['imagem']) ?>"
                  alt="Avatar da comunidade">
                <h2><?php echo htmlspecialchars($cat['nome']); ?></h2>
                <button class="<?= htmlspecialchars($button_class) ?>" data-comunidade-id="<?= $cat['id'] ?>"><?= htmlspecialchars($button_text) ?></button>
              </div>
              <div class="bottom">
                <p><?php echo htmlspecialchars($cat['descricao']) ?></p>
              </div>
            </div>
            <?php
          endif;
        endforeach;
        if ($itemLiteratura === 0) {
          echo "<p>Não há comunidades de literatura ou escrita no momento.</p>";
        }
        ?>
      </div>
    </section>
</body>
<script src="../Scripts/modals.js"></script>
<script src="../Scripts/comunidades.js"></script>

</html>