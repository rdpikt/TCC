<?php
require "conexao.php";
require "protect.php";

$tipo_feed = $_GET['feed'] ?? 'foryou';
$userId = $_SESSION['user_id'];
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  //recebe os dados enviados
  $UserID = $_SESSION['user_id'];
  $TituloPost = (isset($_POST['titulo'])) ? $_POST['titulo'] : '';
  $DescricaoPost = (isset($_POST['descricao'])) ? $_POST['titulo'] : '';
  $ImagemUrl = (isset($_POST['arquivo'])) ? $_POST['arquivo'] : '';
  $tipo_obra = (isset($_POST['tipo_obra'])) ? $_POST['tipo_obra'] : '';
  $tipos_validos = ['Imagem', 'Texto', 'Video'];
  if (!in_array($tipo_obra, $tipos_validos)) {
    echo "Tipo de obra inválido.";
    exit;
  }


  //verificações
  $erros = [];

  //
  if (isset($_FILES['arquivo']) && $_FILES['arquivo']['size'] > 0) {
    $extensoes_aceitas = array('png', 'jpeg', 'jpg');

    $aux = explode('.', $_FILES['arquivo']['name']);
    $extensao = end($aux);

    //validação de extensao aceita
    if (array_search($extensao, $extensoes_aceitas) === false):
      echo "<h1>Extensão Inválida</h1>";
      exit;
    endif;


    if (is_uploaded_file($_FILES['arquivo']['tmp_name'])):
      if (!file_exists('../images/uploads')) {
        mkdir('../images/uploads');
      }

      $nome_foto = date('dmYs') . '_' . $_FILES['arquivo']['name'];

      if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], '../images/uploads/' . $nome_foto)) {
        echo "houve um erro ao gravar arquivo na pasta";
      }

    endif;

  }

  $sql = "INSERT INTO obras (portfolio_id,tipo_obra,titulo, descricao, arquivo_url, tipo_imagem) VALUES (?,?,?,?,?,?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('isssss', $UserID, $tipo_obra, $TituloPost, $DescricaoPost, $nome_foto, $extensao);
  $result = $stmt->execute();



  if ($result) {
    echo "<div class='alert alert-sucess' role='alert'>Conteúdo publicado com sucesso</div>";
  } else {
    echo "<div class='alert alert-danger' role='alert'>Erro ao publicar</div>";
  }
  echo "<meta http-equiv=refresh content='3;URL=UsuarioLogado.php'>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Postar Imagem</title>
    <link rel="stylesheet" href="../Styles/EnviarPost.css">
  <link rel="stylesheet" href="../Styles/telainicial.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
</head>
<body>
<header>
  <div class="logotipo">LOGO</div>
  <input type="search" id="search-bar" class="search-bar" placeholder="🔍 Barra de pesquisa">
  <div class="nav-user">
    <ul>
      <li><a href="notificacoes.php"><i class="fi fi-rs-bell"></i></a></li>
      <li><span><?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
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
</section>
  <section class="formulario">
  <form action="Enviararquivos.php" method="POST" enctype="multipart/form-data">
    <div class="content generos">
      <h1>Postar</h1>
     <div class="btns generos">
  <input type="radio" name="tipo_obra" id="Imagem" value="Imagem">
  <label class="button" for="Imagem">Imagem</label>

  <input type="radio" name="tipo_obra" id="Texto" value="Texto">
  <label class="button" for="Texto">Texto</label>

  <input type="radio" name="tipo_obra" id="Video" value="Video">
  <label class="button" for="Video">Vídeo</label>
</div>
</div>
    
    <div class="content">
    <input class="Titulo" type="text" name="titulo" placeholder="Digite o titulo do Post">
    </div>
    <textarea class="content areatxt" rows="4" name="descricao" placeholder="Coloque uma descrição ao seu post"></textarea>
    <input class="content" text="oi" type="file" name="arquivo" id="image-postCriar" accept="image/png, image/jpg, image/jpeg">
</div>
    <input class="Post-btn" type="submit" value="Postar">
  </form>
  </section>
</body>

</html>