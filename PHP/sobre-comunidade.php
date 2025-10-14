<?php
require "protect.php"; 
require "conexao.php";

$comunidade_id = $_GET["id"];

$sql = "SELECT c.*, COUNT(cm.cargo='membros') from comunidades as c left JOIN comunidade_membros as cm on c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $comunidade_id);
$stmt->execute();
$result = $stmt->get_result();
$comunidade = $result->fetch_assoc();

$qtdMembros = $comunidade["COUNT(cm.cargo='membros')"];



?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Navegação</title>
<link rel="stylesheet" href="../Styles/telainicial.css">
<link rel="stylesheet" href="../Styles/global.css">
<link rel="stylesheet" href="../Styles/sobre-comunidade.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
    
</head>
<body>
<header>
  <div class="logotipo">LOGO</div>
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
      <li><span><img src="../images/avatares/Users/<?php echo htmlspecialchars( $_SESSION['avatar']); ?>" alt="Avatar do usuário"></span></li>
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
      <ul>
        <li><a href="UsuarioLogado.php?feed=foryou"><i style="color: white;" class="fi fi-br-home"></i>Página Inicial</a></li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
        <li><a href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a></li>
        <li><a href="explorar_comunidades.php"><i class="fi fi-br-search"></i>Explorar Comunidades</a></li>
        <li><a href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a></li>
      </ul>
      <div class="tools">
        <ul>
          <li><a href="config.php"><i class="fi fi-rr-settings"></i>Configurações</a></li>
          <li><a href="ajuda.php"><i class="fi fi-rr-info"></i>Ajuda</a></li>
        </ul>
      </div>
    </nav>

    <section class="global-comunidade">
        <i class="fi fi-rr-arrow-left"></i>
      <article class="comunidade-container">
        <div class="options">
          <ul>
            <li><i class="fi fi-br-search"></i></li>
            <li><i class="fi fi-br-user-add"></i></li>
          </ul>
        </div>
        <div class="comunidade-info">
          <div class="text-comunidade">
            <img src="../images/avatares/Comunidades/<?php echo htmlspecialchars($comunidade['imagem']) ?>" alt="">
            <h1><?php echo htmlspecialchars($comunidade['nome']); ?></h1>
            <span><p>Comunidade de <?php echo htmlspecialchars($comunidade['tipo_comunidade'])?></p></span>
          </div>
          <div class="desc-comunidade">
            <p><?php echo htmlspecialchars($comunidade['descricao']); ?></p>
            <p><?php echo htmlspecialchars($qtdMembros)?> Membros</p>
          </div>
        </div>
        <div class="comunidade-options">
          <ul>
            <li class="comunidade_option active" >Geral</li>
            <li class="comunidade_option">Comentários</li>
            <li class="comunidade_option">Regras</li>
            <button class="comunidade_option" id="criar_post">Criar Post</button>
          </ul>
        </div>
        <div class="comunidade-content">
          
        </div>
      </article>
    
    <section class="seguindo-inf">
      <div class="seguindo-content">
      <ul><li><h1>Sugestões de artistas</h1></li><li><a href="#" >ver mais</a></li></ul>
      </div>
      <footer class="footer-seguindo">
      <ul>
        <li><a href="#">regras de usuarios</a></li>
        <li><a href="#">regras de usuarios</a></li>
        <li><a href="#">regras de usuarios</a></li>
        <li><a href="#">regras de usuarios</a></li>
        <li><p>&copy; 2025 HARPHUB</p></li>
      </ul>
    </footer>

    </section>
    </section>

</body>
<script src="../Scripts/modals.js"></script>
<script src="../Scripts/TelaInicial.js"></script>
</html>