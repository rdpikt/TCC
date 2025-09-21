<?php
require "protect.php";
require "conexao.php";

$userId = $_SESSION['user_id'];
$user_avatar = $_SESSION['avatar'] ?? 'profile.png';



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $novoNome = ($_POST['alterar_nome']) ?? '';
  $bio = $_POST['alterar_bio'] ??'';
  $novaFoto = date('dmYs') . '_' . $_FILES['mudar_avatar']['name'];

  if (strlen($novoNome) < 3 || strlen($novoNome) > 20) {
    echo '<script>
            alert("Nome de usuário inválido! Deve ter entre 3 e 20 caracteres.");
            window.location.href = "../PHP/config.php";
        </script>';
    exit();
  }

  $erros = [];

  if (isset($_FILES['mudar_avatar']) && $_FILES['mudar_avatar']['size'] > 0) {
    $extensaoAceitas = array('png', 'jpg', 'jpg');

    $aux = explode('.', $_FILES['mudar_avatar']['name']);
    $extensao = end($aux);
    if (array_search($extensao, $extensaoAceitas) === false) {
      $erros[] = 'Extensão inválida';
    }

    if (is_uploaded_file($_FILES['mudar_avatar']['tmp_name'])) {
      if (!file_exists('../images/avatares/Users')) {
        mkdir('../images/avatares/Users');
      }

      

      if (!move_uploaded_file($_FILES['mudar_avatar']['tmp_name'], '../images/avatares/Users/' . $novaFoto)) {
        $erros[] = 'Houve um erro ao gravar o arquivo na pasta';
      }

    }

  }
 $sql = 'UPDATE users U SET user_avatar = ?, nome_user = ?, bio = ? WHERE U.id = ?';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sssi', $novaFoto, $novoNome, $bio, $userId);
  $result = $stmt->execute();


  if ($stmt) {
    $_SESSION['avatar'] = $novaFoto;
    $user['user_name'] = $novoNome;
    $user['user_bio'] = $bio;
    echo "<div class='alert alert-sucess' role='alert'>Conteúdo publicado com sucesso</div>";
  } else {
    echo "<div class='alert alert-danger' role='alert'>Erro ao publicar</div>";
  }
  echo "<meta http-equiv=refresh content='3;URL=config.php'>";
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de Usuário</title>
  <link rel="stylesheet" href="../Styles/telainicial.css">
  <link rel="stylesheet" href="../Styles/config.css">
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
          <img src="<?php echo "../images/avatares/Users/" . htmlspecialchars($user_avatar); ?>"
            alt="Avatar do usuário">
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
      <form action="config.php" method="post" enctype="multipart/form-data">
        <img src="<?php echo "../images/avatares/Users/" . htmlspecialchars($user_avatar); ?>" alt="">
        <input type="file" name="mudar_avatar" id="MudarAvatar" value="Mudar Foto de Perfil"
          accept="image/png, image/jpg, image/jpeg">
        <input type="text" name="alterar_nome" id="AlterarNome" placeholder="Alterar seu Nickname">
        <textarea name="alterar_bio" id="AlterarBio" placeholder="Mude aqui sua bio" style="resize: none"></textarea>

        <input type="submit" value="Salvar alterações">
      </form>
    </section>
</body>
<script src="../Scripts/modals.js"></script>

</html>