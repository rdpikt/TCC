<?php
require "protect.php";
require "conexao.php";

$userId = $_SESSION['user_id'];
$user_avatar = $_SESSION['avatar'] ?? 'profile.png';



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Busca o avatar antigo no banco de dados
    $sql_get_avatar = "SELECT user_avatar FROM users WHERE id = ?";
    $stmt_get_avatar = $conn->prepare($sql_get_avatar);
    $stmt_get_avatar->bind_param('i', $userId);
    $stmt_get_avatar->execute();
    $result_get_avatar = $stmt_get_avatar->get_result();
    $user_data = $result_get_avatar->fetch_assoc();
    $antigoAvatar = $user_data['user_avatar'] ?? 'profile.png';

    $avatarParaSalvar = $antigoAvatar;

    $novoNome = $_POST['alterar_nome'] ?? '';
    $bio = $_POST['alterar_bio'] ?? '';
  if(!empty($novoNome)){
    if (strlen($novoNome) < 3 || strlen($novoNome) > 20) {
        $erros = ['O nome de usuário deve ter entre 3 e 20 caracteres.'];
        exit();
    }
  }

    $erros = [];
    $novaFoto = '';

    if (isset($_FILES['mudar_avatar']) && $_FILES['mudar_avatar']['error'] == 0 && $_FILES['mudar_avatar']['size'] > 0) {
        $extensoesAceitas = ['png', 'jpg', 'jpeg', 'gif'];
        $extensao = strtolower(pathinfo($_FILES['mudar_avatar']['name'], PATHINFO_EXTENSION));

        if (!in_array($extensao, $extensoesAceitas)) {
            $erros[] = 'Extensão de arquivo inválida. Apenas JPG, JPEG, PNG e GIF são permitidos.';
        }

        if (empty($erros)) {
            $novaFoto = $_SESSION['user_name'] .'_' . date('dmYs') . '_' . $extensao;
            $caminhoUpload = '../images/avatares/Users/' . $novaFoto;

            if (!file_exists('../images/avatares/Users')) {
                mkdir('../images/avatares/Users', 0777, true);
            }

            if (move_uploaded_file($_FILES['mudar_avatar']['tmp_name'], $caminhoUpload)) {
                $avatarParaSalvar = $novaFoto;
                // Deleta a foto antiga se não for a padrão e o arquivo existir
                if ($antigoAvatar && $antigoAvatar != 'profile.png' && file_exists('../images/avatares/Users/' . $antigoAvatar)) {
                    unlink('../images/avatares/Users/' . $antigoAvatar);
                }
            } else {
                $erros[] = 'Houve um erro ao mover o arquivo de imagem.';
            }
        }
    }

    if (empty($erros)) {
      
        $updates = [];
        $params = [];
        $types = '';

        if ($avatarParaSalvar !== $antigoAvatar) {
            $updates[] = 'user_avatar = ?';
            $params[] = $avatarParaSalvar;
            $types .= 's';
        }

        if (!empty($novoNome)) {
            $updates[] = 'nome_user = ?';
            $params[] = $novoNome;
            $types .= 's';
        }

        if (!empty($bio)) {
            $updates[] = 'bio = ?';
            $params[] = $bio;
            $types .= 's';
        }

        if (!empty($updates)) {
            $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
            $params[] = $userId;
            $types .= 'i';

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                if ($avatarParaSalvar !== $antigoAvatar) {
                    $_SESSION['avatar'] = $avatarParaSalvar;
                }
                if (!empty($novoNome)) {
                    $_SESSION['user_name'] = $novoNome; // Atualiza o nome na sessão também
                }
                echo "<div class='alert alert-success' role='alert'>Perfil atualizado com sucesso!</div>";
            } else {
                echo "<div class='alert alert-danger' role='letras'>Erro ao atualizar o perfil.</div>";
            }
        } else {
            // Opcional: informar ao usuário que nada foi alterado
            echo "<div class='alert alert-info' role='alert'>Nenhuma alteração para salvar.</div>";
        }
    } else {
        foreach ($erros as $erro) {
            echo "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($erro) . "</div>";
        }
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
  <link rel="stylesheet" href="../Styles/global.css">
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
        <li><span><a href="notificacoes.php">notificações</a></span></li>
        <li><span><img src="../images/avatares/Users/<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar do usuário"></span></li>w
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
        <li><a href="UsuarioLogado.php?feed=foryou">Página Inicial</a></li>
        <li><a href="UsuarioLogado.php?feed=seguindo">Seguindo</a></li>
        <li><a href="Galeria.php">Galeria</a></li>
        <li><a href="EnviarArquivos.php">Criar Post</a></li>
        <li><a href="explorar_comunidades.php">Comunidades</a></li>
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
        <img src="<?php echo "../images/avatares/Users/" . htmlspecialchars($user_avatar); ?>" alt="" id="avatar-preview">
        <input type="file" name="mudar_avatar" id="MudarAvatar" value="Mudar Foto de Perfil"
          accept="image/png, image/jpg, image/jpeg">
        <input type="text" name="alterar_nome" id="AlterarNome" placeholder="Alterar seu Nickname">
        <textarea name="alterar_bio" id="AlterarBio" placeholder="Mude aqui sua bio" style="resize: none"></textarea>

        <input type="submit" value="Salvar alterações">
      </form>
    </section>
</body>
<script src="../Scripts/modals.js"></script>
<script src="../Scripts/TelaInicial.js"></script>
<script>
  document.getElementById('MudarAvatar').addEventListener('change', function(event) {
    const preview = document.getElementById('avatar-preview');
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onloadend = function() {
      preview.src = reader.result;
    }

    if (file) {
      reader.readAsDataURL(file);
    } else {
      preview.src = "<?php echo "../images/avatares/Users/" . htmlspecialchars($user_avatar); ?>";
    }
  });
</script>
</html>