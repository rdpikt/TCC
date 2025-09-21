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
  <link rel="stylesheet" href="../Styles/comunidade.css">
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