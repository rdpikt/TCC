<?php
require "protect.php";
require "conexao.php";
$userId = $_SESSION['user_id'];
// Buscar notificações do usuário
$sql = "SELECT N.*, u.nome_user FROM notificacoes N JOIN users u ON N.de_usuario_id = u.id WHERE N.para_usuario_id = ? ORDER BY N.data_envio DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$notificacoes = [];
while ($notificacao = $result->fetch_assoc()) {
  $notificacoes[] = $notificacao;
}
$conn->close();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notificações</title>
  <link rel="stylesheet" href="../Styles/telainicial.css">
  <link rel="stylesheet" href="../Styles/notificacoes.css">
</head>

<body>
  <header>
    <div class="logotipo">LOGO</div>
    <input type="search" name="search-bar" id="search-bar" placeholder="Barra de pesquisa">
    <div class="nav-user">
      <ul>
        <li><span>notificações</span></li>
        <li><span><?php echo $_SESSION['user_name']; ?></span></li>
      </ul>
    </div>
  </header>
  <nav class="nav-side">
    <div class="user-avatar">
      <div class="user-avatar-img">
        <img
          src="<?php echo "../images/avatares/Users/".htmlspecialchars($user_avatar); ?>"
          alt="Avatar">
      </div>
      <span><?php echo $_SESSION['user_name']; ?></span>
    </div>
    <ul>
      <li><a href="UsuarioLogado?feed=foryou">Página Inicial</a></li>
      <li><a href="?feed=seguindo">Seguindo</a></li>
      <li><a href="Galeria.php">Galeria</a></li>
      <li><a href="EnviarArquivos.php">Criar Post</a></li>
      <li><a href="comunidades.php">Comunidades</a></li>
      <li><a href="perfil.php">Perfil</a></li>
    </ul>
    <div class="tools">
      <ul>
        <li><a href="config.php">Configurações</a></li>
        <li>Ajuda</li>
      </ul>
    </div>
  </nav>
  <main class="content">
    <div class="titulo">
      <h1>Notificações</h1>
    </div>

    <?php if (empty($notificacoes)): ?>
      <p>Você não tem notificações.</p>
    <?php else: ?>
      <ul class="notificacoes-list">
        <?php foreach ($notificacoes as $notificacao): ?>
          <li class="notificacao-item" data-nome="<?php echo htmlspecialchars($notificacao['nome_user']); ?>"
            data-conteudo="<?php echo htmlspecialchars($notificacao['conteudo']); ?>"
            data-data="<?php echo date('d/m/Y H:i', strtotime($notificacao['data_envio'])); ?>">
            <div class="text">
              <h2><strong>de: <?php echo htmlspecialchars($notificacao['nome_user']); ?></strong></h2>
              <p></strong><?php echo htmlspecialchars($notificacao['conteudo']); ?></strong></p>
              <span class="data"><?php echo date('d/m/Y H:i', strtotime($notificacao['data_envio'])); ?></span>
            </div>
            <button class="btn-expandir">Expandir</button>
          </li>

        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
  <div class="notificacao-expandida">
    <button class="btn-fechar">X</button>
    <div class="conteudo-expandido">
      <h2 class="de"><?php echo htmlspecialchars($notificacao['nome_user']); ?></h2>
      <p class="mensagem"><?php echo htmlspecialchars($notificacao['conteudo']); ?></p>
      <span class="data-expandida"><?php echo date('d/m/Y H:i', strtotime($notificacao['data_envio'])); ?></span>
    </div>
  </div>

</body>
<script src="../Scripts/notificacoes.js"></script>
<script src="../Scripts/modals.js"></script>
</html>