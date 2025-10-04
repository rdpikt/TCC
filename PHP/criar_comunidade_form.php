<?php
require "protect.php";
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Comunidade</title>
    <link rel="stylesheet" href="../Styles/telainicial.css">
    <link rel="stylesheet" href="../Styles/forms.css">
    <link rel="stylesheet" href="../Styles/global.css">
</head>
<body>
    <header>
      <div class="logotipo">LOGO</div>
      <input type="search" id="search-bar" placeholder="Barra de pesquisa">
      <div class="nav-user">
        <ul>
          <li><a href="notificacoes.php">Notificações</a></li>
          <li><span><?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
        </ul>
      </div>
    </header>
    <section class="main">
          <ul>
            <li><a href="UsuarioLogado.php?feed=foryou">Página Inicial</a></li>
            <li><a href="comunidades.php">Comunidades</a></li>
            <li><a href="perfil.php">Perfil</a></li>
          </ul>
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
    </section>
    <script src="../Scripts/modals.js"></script>
</body>
</html>