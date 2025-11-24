<?php
require "protect.php";
require "conexao.php";
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Nova Comunidade</title>
  <link rel="stylesheet" href="../Styles/forms.css">
  <link rel="stylesheet" href="../Styles/global.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
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
  </header>
  <main>
    <nav class="nav-side" id="menu">
      <div class="logotipo"><span>Harp</span>Hub</div>
      <ul class="pages">
        <li><a  href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a>
        </li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
        <li><a class="selecionado" href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
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
      <div class="form-container">
        <h1>Criar Nova Comunidade</h1>
        <form action="criar_comunidade.php" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="nome">Nome da Comunidade</label>
            <input type="text" id="nome" name="nome" required>
          </div>
          <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" rows="4" required></textarea>
          </div>
          <div class="form-group">
            <label for="tipo_comunidade">Tipo de Comunidade</label>
            <select id="tipo_comunidade" name="tipo_comunidade" required>
              <option value="Design">Design</option>
              <option value="Crafts">Crafts</option>
              <option value="literatura">Literatura</option>
              <option value="escrita">Escrita</option>
            </select>
          </div>
          <div class="form-group">
            <label for="imagem">Imagem da Comunidade (Avatar)</label>
            <input type="file" id="imagem" name="imagem" accept="image/png, image/jpeg, image/jpg">
          </div>
          <button type="submit" class="btn-submit">Criar Comunidade</button>
        </form>
      </div>
    </section>
  </main>
  <script src="../Scripts/modals.js"></script>
  <div class="modal-overlay"></div>

<div class="modal-other-perfil-container">
    <div class="modal-other-perfil-content-wrapper">
        <!-- O JS vai preencher aqui -->
    </div>
</div>
<script src="../Scripts/TelaInicial.js"></script>
<script src="../Scripts/modals.js"></script>

</body>

</html>