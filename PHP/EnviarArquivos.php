<?php
require "conexao.php";
require "protect.php";

$tipo_feed = $_GET['feed'] ?? 'foryou';
$userId = $_SESSION['user_id'];
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST'):

  $erros = [];

  $UserID = $_SESSION['user_id'];
  $TituloPost = $_POST['titulo'] ?? '';
  $DescricaoPost = $_POST['descricao'] ?? '';
  $tipo_obra = $_POST['tipo_obra'] ?? 'Imagem';
  $tagsPost = $_POST[''] ??'';

  $tipos_validos = ['Imagem', 'Texto', 'Video'];
  if (!in_array($tipo_obra, $tipos_validos)) {
    $erros[] = 'Tipo de obra inválido';
  }

  switch ($tipo_obra):
    case 'Imagem':
      if (isset($_FILES['arquivo']) && $_FILES['arquivo']['size'] > 0):
        $extensoes_aceitas = ['png', 'jpeg', 'jpg'];
        $aux = explode('.', $_FILES['arquivo']['name']);
        $extensao = strtolower(end($aux));

        if (!in_array($extensao, $extensoes_aceitas)):
          $erros[] = "Extensão inválida. Apenas PNG, JPEG e JPG são aceitos.";
        else:
          if (!file_exists('../images/uploads')) {
            mkdir('../images/uploads');
          }

          $nome_foto = date('dmYs') . '_' . $_FILES['arquivo']['name'];
          if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], '../images/uploads/' . $nome_foto)) {
            $erros[] = "Houve um erro ao gravar o arquivo na pasta.";
          }
        endif;
      else:
        $erros[] = "Nenhum arquivo enviado.";
      endif;

      if (empty($DescricaoPost)) $erros[] = "A descrição não pode ser vazia.";

      if (count($erros) == 0):
        $sql = "INSERT INTO obras (portfolio_id, tipo_obra, titulo, descricao, arquivo_url, tipo_imagem)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isssss', $UserID, $tipo_obra, $TituloPost, $DescricaoPost, $nome_foto, $extensao);
        $result = $stmt->execute();

        if ($result):
          echo "<div class='alert alert-success' role='alert'>Conteúdo publicado com sucesso</div>";
          echo "<meta http-equiv='refresh' content='3;URL=UsuarioLogado.php'>";
        else:
          echo "<div class='alert alert-danger' role='alert'>Erro ao publicar: {$stmt->error}</div>";
        endif;
        $stmt->close();
      else:
        foreach ($erros as $erro):
          echo "<div class='alert alert-danger' role='alert'>$erro</div>";
        endforeach;
      endif;
      break;

    case 'Video':
      if (isset($_FILES['arquivo']) && $_FILES['arquivo']['size'] > 0):
        $extensoes_aceitas = ['mov', 'mp4', 'wmv'];
        $aux = explode('.', $_FILES['arquivo']['name']);
        $extensao = strtolower(end($aux));

        if (!in_array($extensao, $extensoes_aceitas)):
          $erros[] = "Extensão inválida. Apenas MOV, MP4 e WMV são aceitos.";
        else:
          if (!file_exists('../images/uploads/videos')) {
            mkdir('../images/uploads/videos');
          }

          $nome_video = date('dmYs') . '_' . $_FILES['arquivo']['name'];
          if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], '../images/uploads/videos/' . $nome_video)) {
            $erros[] = "Houve um erro ao gravar o arquivo na pasta.";
          }
        endif;
      else:
        $erros[] = "Nenhum arquivo enviado.";
      endif;

      if (empty($DescricaoPost)) $erros[] = "A descrição não pode ser vazia.";

      if (count($erros) == 0):
        $sql = "INSERT INTO obras (portfolio_id, tipo_obra, titulo, descricao, arquivo_url, tipo_imagem)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isssss', $UserID, $tipo_obra, $TituloPost, $DescricaoPost, $nome_video, $extensao);
        $result = $stmt->execute();

        if ($result):
          echo "<div class='alert alert-success' role='alert'>Conteúdo publicado com sucesso</div>";
          echo "<meta http-equiv='refresh' content='3;URL=UsuarioLogado.php'>";
        else:
          echo "<div class='alert alert-danger' role='alert'>Erro ao publicar: {$stmt->error}</div>";
        endif;
        $stmt->close();
      else:
        foreach ($erros as $erro):
          echo "<div class='alert alert-danger' role='alert'>$erro</div>";
        endforeach;
      endif;
      break;

    case 'Texto':
      if (empty($DescricaoPost)) $erros[] = "A descrição não pode ser vazia.";
      if (empty($TituloPost)) $erros[] = "O título não pode ser vazio.";

      if (count($erros) == 0):
        $sql = "INSERT INTO obras (portfolio_id, tipo_obra, titulo, descricao)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isss', $UserID, $tipo_obra, $TituloPost, $DescricaoPost);
        $result = $stmt->execute();

        if ($result):
          echo "<div class='alert alert-success' role='alert'>Conteúdo publicado com sucesso</div>";
          echo "<meta http-equiv='refresh' content='3;URL=UsuarioLogado.php'>";
        else:
          echo "<div class='alert alert-danger' role='alert'>Erro ao publicar: {$stmt->error}</div>";
        endif;
        $stmt->close();
      else:
        foreach ($erros as $erro):
          echo "<div class='alert alert-danger' role='alert'>$erro</div>";
        endforeach;
      endif;
      break;
  endswitch;
endif;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Postar Imagem</title>
    <link rel="stylesheet" href="../Styles/EnviarPost.css">
  <link rel="stylesheet" href="../Styles/telainicial.css">
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
        <li ><a  href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a></li>
        <li><a href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a class="selecionado" href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
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
</section>
  <section class="formulario">
  <form action="Enviararquivos.php" method="POST" enctype="multipart/form-data">
    <div class="content generos">
      <h1>Postar</h1>
     <div class="btns generos">
  <input type="radio" name="tipo_obra" id="Imagem" value="Imagem" checked>
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