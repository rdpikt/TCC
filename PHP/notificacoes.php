<?php
require "protect.php";
require "conexao.php";

$userId = $_SESSION['user_id'];
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';

// Busca as notificações do usuário, com detalhes do remetente e do post (se for de curtida)
$sql = "SELECT 
            n.id, n.tipo, n.link_id, n.data_envio, n.lida,
            remetente.nome_user AS remetente_nome,
            post.titulo AS post_titulo,
            post.arquivo_url AS post_arquivo
        FROM notificacoes AS n
        LEFT JOIN users AS remetente ON n.remetente_id = remetente.id
        LEFT JOIN obras AS post ON n.link_id = post.id AND n.tipo IN ('curtida', 'repost', 'comentario')
        WHERE n.user_id = ? 
        ORDER BY n.lida ASC, n.data_envio DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$notificacoes = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações</title>
    <link rel="stylesheet" href="../Styles/telainicial.css">
    <link rel="stylesheet" href="../Styles/notificacoes.css">
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
  <div class="modal-perfil">
      <ul>
        <li><a href="perfil.php">Perfil</a></li>
        <li>Trocar de conta</li>
        <li><form action="logout.php"><input type="submit" value="Sair da conta"></form></li>
      </ul>
    </div>
</header>

<section class="main">
  <nav class="nav-side" id="menu">
      <ul>
        <li><a href="UsuarioLogado?feed=foryou">Página Inicial</a></li>
        <li><a href="UsuarioLogado?feed=seguindo">Seguindo</a></li>
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
        <div class="titulo">
            <h1>Suas Notificações</h1>
        </div>

        <div class="lista-notificacoes">
            <?php if (empty($notificacoes)): ?>
                <p>Você não tem nenhuma notificação.</p>
            <?php else: ?>
                <?php foreach ($notificacoes as $notificacao): ?>
                    <?php
                        $classe_lida = $notificacao['lida'] == 0 ? 'nao-lida' : '';
                        $remetente_nome = htmlspecialchars($notificacao['remetente_nome'] ?? 'Sistema');
                        $mensagem_resumo = '';
                        $conteudo_completo = '';
                        $post_preview_html = '';

                        switch ($notificacao['tipo']) {
                            case 'curtida':
                                $titulo_post = htmlspecialchars($notificacao['post_titulo'] ?? 'uma publicação');
                                $mensagem_resumo = "<strong>{$remetente_nome}</strong> curtiu sua publicação: <em>{$titulo_post}</em>";
                                $conteudo_completo = $mensagem_resumo;
                                if (!empty($notificacao['post_arquivo'])) {
                                    $post_preview_html = "<img src='../images/uploads/" . htmlspecialchars($notificacao['post_arquivo']) . "' alt='Preview do post' class='post-preview-img'>";
                                }
                                break;
                            case 'repost':
                                $titulo_post = htmlspecialchars($notificacao['post_titulo'] ?? 'uma publicação');
                                $mensagem_resumo = "<strong>{$remetente_nome}</strong> repostou sua publicação: <em>{$titulo_post}</em>";
                                $conteudo_completo = $mensagem_resumo;
                                if (!empty($notificacao['post_arquivo'])) {
                                    $post_preview_html = "<img src='../images/uploads/" . htmlspecialchars($notificacao['post_arquivo']) . "' alt='Preview do post' class='post-preview-img'>";
                                }
                                break;
                            case 'seguimento':
                                $mensagem_resumo = "<strong>{$remetente_nome}</strong> começou a seguir você.";
                                $conteudo_completo = $mensagem_resumo;
                                break;
                            // Adicione outros casos aqui (comentario, sistema, etc.)
                            default:
                                $mensagem_resumo = "Você tem uma nova notificação.";
                                $conteudo_completo = $mensagem_resumo;
                        }
                    ?>
                    <div class="notificacao-item <?= $classe_lida ?>" 
                         data-id="<?= $notificacao['id'] ?>"
                         data-conteudo="<?= htmlspecialchars($conteudo_completo) ?>"
                         data-data="<?= date('d/m/Y H:i', strtotime($notificacao['data_envio'])) ?>"
                         data-post-preview="<?= htmlspecialchars($post_preview_html) ?>">
                        
                        <div class="notificacao-info">
                            <p class="notificacao-resumo"><?= $mensagem_resumo ?></p>
                            <span class="notificacao-data"><?= date('d/m/Y H:i', strtotime($notificacao['data_envio'])) ?></span>
                        </div>
                        
                        <div class="notificacao-acoes">
                            <button class="btn-excluir" data-id="<?= $notificacao['id'] ?>" title="Excluir notificação">&#x1F5D1;</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <div class="notificacao-expandida">
        <div class="notificacao-expandida-content">
            <button class="btn-fechar">&times;</button>
            <div class="mensagem-completa"></div>
            <div class="post-preview-container"></div>
            <span class="data-expandida"></span>
        </div>
    </div>
</section>

</body>
<script src="../Scripts/notificacoes.js"></script>
<script src="../Scripts/TelaInicial.js"></script>
<script src="../Scripts/modals.js"></script>
</html>