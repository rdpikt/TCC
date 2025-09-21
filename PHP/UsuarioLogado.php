<?php
require "protect.php"; 
require "conexao.php";

$tipo_feed = $_GET['feed'] ?? 'foryou';
$userId = $_SESSION['user_id'];
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';

// --- CURTIDAS E REPOSTS (VERSÃO DE DEPURAÇÃO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = intval($_POST['post_id']);
    $response = ['success' => false, 'message' => 'Erro desconhecido.'];

    // Ativar relatório de erros do MySQLi para depuração
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // --- PASSO DE DEPURAÇÃO ---
    // Verifica se uma ação específica (curtir ou repostar) foi enviada.
    // Se nenhuma for encontrada, o script para e envia uma mensagem clara.
    if (!isset($_POST['repostar_post']) && !isset($_POST['curtir_post'])) {
        $response['message'] = 'Ação não especificada. O servidor não sabe se é para curtir ou repostar.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    try {
        if (isset($_POST['repostar_post'])) {
            $sql = "SELECT id FROM reposts WHERE user_id = ? AND original_post_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $userId, $postId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $sql = "DELETE FROM reposts WHERE user_id = ? AND original_post_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $userId, $postId);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'repostado' => false, 'message' => 'Repost removido.'];
                } else {
                    $response['message'] = 'Erro ao remover o repost.';
                }
            } else {
                $sql = "INSERT INTO reposts (original_post_id, user_id) VALUES (?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $postId, $userId);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'repostado' => true, 'message' => 'Post repostado!'];

                    // --- INÍCIO DA LÓGICA DE NOTIFICAÇÃO DE REPOST ---
                    // 1. Obter o ID do autor do post
                    $stmt_author = $conn->prepare("SELECT portfolio_id FROM obras WHERE id = ?");
                    $stmt_author->bind_param("i", $postId);
                    $stmt_author->execute();
                    $author_result = $stmt_author->get_result();
                    if ($author_row = $author_result->fetch_assoc()) {
                        $authorId = $author_row['portfolio_id'];

                        // 2. Não notificar o usuário se ele repostar o próprio post
                        if ($authorId != $userId) {
                            // 3. Inserir a notificação
                            $tipo_notificacao = 'repost';
                            $stmt_notif = $conn->prepare(
                                "INSERT INTO notificacoes (user_id, remetente_id, tipo, link_id) VALUES (?, ?, ?, ?)"
                            );
                            $stmt_notif->bind_param("iisi", $authorId, $userId, $tipo_notificacao, $postId);
                            $stmt_notif->execute();
                        }
                    }
                    // --- FIM DA LÓGICA DE NOTIFICAÇÃO DE REPOST ---
                } else {
                    $response['message'] = 'Erro ao repostar o post.';
                }
            }
        } elseif (isset($_POST['curtir_post'])) {
            $sql = "SELECT id FROM curtidas WHERE usuario_id = ? AND obra_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $userId, $postId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $sql = "DELETE FROM curtidas WHERE usuario_id = ? AND obra_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $userId, $postId);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'curtido' => false, 'message' => 'Post descurtido.'];
                } else {
                    $response['message'] = 'Erro ao descurtir o post.';
                }
            } else {
                $sql = "INSERT INTO curtidas (usuario_id, obra_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $userId, $postId);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'curtido' => true, 'message' => 'Post curtido!'];

                    // --- INÍCIO DA LÓGICA DE NOTIFICAÇÃO ---
                    // 1. Obter o ID do autor do post
                    $stmt_author = $conn->prepare("SELECT portfolio_id FROM obras WHERE id = ?");
                    $stmt_author->bind_param("i", $postId);
                    $stmt_author->execute();
                    $author_result = $stmt_author->get_result();
                    if ($author_row = $author_result->fetch_assoc()) {
                        $authorId = $author_row['portfolio_id'];

                        // 2. Não notificar o usuário se ele curtir o próprio post
                        if ($authorId != $userId) {
                            // 3. Inserir a notificação
                            $tipo_notificacao = 'curtida';
                            $stmt_notif = $conn->prepare(
                                "INSERT INTO notificacoes (user_id, remetente_id, tipo, link_id) VALUES (?, ?, ?, ?)"
                            );
                            $stmt_notif->bind_param("iisi", $authorId, $userId, $tipo_notificacao, $postId);
                            $stmt_notif->execute();
                        }
                    }
                    // --- FIM DA LÓGICA DE NOTIFICAÇÃO ---
                } else {
                    $response['message'] = 'Erro ao curtir o post.';
                }
            }
        }
    } catch (mysqli_sql_exception $e) {
        // Se qualquer consulta falhar, captura a exceção e monta a mensagem de erro
        $response['success'] = false;
        $response['message'] = "ERRO DE SQL: " . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// --- FEEDS ---
if ($tipo_feed === 'foryou') {
    $sql = "SELECT O.*, u.nome_user, u.user_avatar
            FROM obras O
            JOIN users u ON O.portfolio_id = u.id
            ORDER BY RAND()
            LIMIT 20";
    $result = $conn->query($sql);
} elseif ($tipo_feed === 'seguindo') {
    $sql = "SELECT O.*, u.nome_user, u.user_avatar
            FROM obras O
            JOIN users u ON O.portfolio_id = u.id
            JOIN seguidores s ON s.seguido_id = u.id
            WHERE s.seguidor_id = ?
            ORDER BY O.data_publicacao DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
}

// --- Verifica se segue alguém ---
$verifica_sql = "SELECT COUNT(*) as total FROM seguidores WHERE seguidor_id = ?";
$stmt_verifica = $conn->prepare($verifica_sql);
$stmt_verifica->bind_param("i", $userId);
$stmt_verifica->execute();
$row_verifica = $stmt_verifica->get_result()->fetch_assoc();
$segue_alguem = $row_verifica['total'] > 0;
$no_seguindo = $segue_alguem ? "" : "Você ainda não está seguindo ninguém. Explore a comunidade e comece a seguir autores que você gosta!";

// --- Verifica se há obras ---
$count_sql = "SELECT COUNT(*) as total FROM obras";
$count_row = $conn->query($count_sql)->fetch_assoc();
$no_obras = $count_row['total'] < 1 ? "Não há obras disponíveis" : "";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Navegação</title>
<link rel="stylesheet" href="../Styles/telainicial.css">
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

  <section class="navigation-user" id="NavigationUser">
    <div class="initial-options">
      <ul>
        <li><a href="?feed=foryou" <?= $tipo_feed === 'foryou' ? 'class="active"' : '' ?>>Para Você</a></li>
        <li><a href="?feed=seguindo" <?= $tipo_feed === 'seguindo' ? 'class="active"' : '' ?>>Seguindo</a></li>
      </ul>
    </div>
    <div class="conteudo" id="feed-conteudo">
      <?= $tipo_feed === 'seguindo' ? $no_seguindo : '' ?>
      <?= $tipo_feed === 'foryou' ? $no_obras : '' ?>

      <?php while ($post = $result->fetch_assoc()): ?>
      <?php
        // Verifica se o usuário curtiu este post
        $stmt_curtida = $conn->prepare("SELECT * FROM curtidas WHERE usuario_id = ? AND obra_id = ?");
        $stmt_curtida->bind_param("ii", $userId, $post['id']);
        $stmt_curtida->execute();
        $curtido = $stmt_curtida->get_result()->num_rows > 0;

        // VERIFICA SE O USUÁRIO REPOSTOU ESTE POST (CORREÇÃO DO ERRO)
        $stmt_repost = $conn->prepare("SELECT * FROM reposts WHERE user_id = ? AND original_post_id = ?");
        $stmt_repost->bind_param("ii", $userId, $post['id']);
        $stmt_repost->execute();
        $repostado = $stmt_repost->get_result()->num_rows > 0;
      ?>
      <article class="posts">
        <div class="descricao-post">
          <ul>
            <li><span class="nome-desc">User: <?= htmlspecialchars($post['nome_user']) ?></span></li>
          </ul>
          <h1 class="titulo">Titulo: <?= htmlspecialchars($post['titulo']) ?></h1>
          <p>Descrição: <br><?= htmlspecialchars($post['descricao']) ?></p>
        </div>
        
        <?php if (!empty($post['arquivo_url'])): ?>
        <div class="img-post">
          <img src="../images/uploads/<?= htmlspecialchars($post['arquivo_url']) ?>" alt="Imagem do post">
        </div>
        <?php else: ?>
        <p>Sem imagem para este post.</p>
        <?php endif; ?>
        <div class="footer-post">
          <form action="UsuarioLogado.php?feed=<?= $tipo_feed ?>" method="post">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <ul>
              <li><button type="button">Comentar</button></li>
              <li><button type="submit" name="repostar_post" class="repostar-btn <?= $repostado ? 'repostado' : '' ?>">Repostar</button></li>
              <li><button type="submit" name="curtir_post" class="curtida <?= $curtido ? 'curtido' : '' ?>">Curtir</button></li>
              <li><button type="button">Compartilhar</button></li>
            </ul>
          
          </form>
            <span>Salvar</span>
        </div>
      </article>
      <?php endwhile; ?>
    </div>
  </section>
</section>

<script src="../Scripts/TelaInicial.js"></script>
<script src="../Scripts/modals.js"></script>
<script src="../Scripts/ajaxInteractions.js"></script>
<script src="../Scripts/carregar_posts.js"></script> 
</body>
</html>