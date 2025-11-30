<?php
// UsuarioLogado.php (versão limpa e corrigida)

require "protect.php";
require "conexao.php";

// Variáveis iniciais (segurança)
if (!isset($_SESSION)) session_start();
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  // Redireciona para login caso não esteja autenticado
  header('Location: login.php');
  exit;
}
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';

// ---------- WELCOME MODAL: salvar interesses ----------
$show_welcome_modal = false;
if (isset($_SESSION['show_welcome_modal']) && $_SESSION['show_welcome_modal']) {
  $show_welcome_modal = true;
  unset($_SESSION['show_welcome_modal']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_interesses'])) {
  if (isset($_POST['CC']) && is_array($_POST['CC'])) {
    $interesses_json = json_encode($_POST['CC']);
    try {
      $stmt = $conn->prepare("UPDATE users SET interesses = ? WHERE id = ?");
      $stmt->bind_param("si", $interesses_json, $userId);
      $stmt->execute();
    } catch (mysqli_sql_exception $e) {
      error_log("Erro ao salvar interesses: " . $e->getMessage());
    }
  }
  header("Location: UsuarioLogado.php?feed=foryou");
  exit;
}

// ---------- AÇÕES AJAX: curtir / repost (espera post_id + curtir_post OR repostar_post) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id']) && (isset($_POST['curtir_post']) || isset($_POST['repostar_post']))) {
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  $postId = intval($_POST['post_id']);
  $response = ['success' => false, 'message' => 'Ação não realizada.'];

  try {
    // obter author do post (para notificações)
    $stmt_author = $conn->prepare("SELECT portfolio_id FROM obras WHERE id = ?");
    $stmt_author->bind_param("i", $postId);
    $stmt_author->execute();
    $authorResult = $stmt_author->get_result();
    $authorId = null;
    if ($ar = $authorResult->fetch_assoc()) $authorId = $ar['portfolio_id'];

    if (isset($_POST['repostar_post'])) {
      // toggle repost
      $stmt = $conn->prepare("SELECT id FROM reposts WHERE user_id = ? AND original_post_id = ?");
      $stmt->bind_param("ii", $userId, $postId);
      $stmt->execute();
      $res = $stmt->get_result();

      if ($res->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM reposts WHERE user_id = ? AND original_post_id = ?");
        $stmt->bind_param("ii", $userId, $postId);
        $stmt->execute();
        $response = ['success' => true, 'repostado' => false, 'message' => 'Repost removido.'];
      } else {
        $stmt = $conn->prepare("INSERT INTO reposts (original_post_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $postId, $userId);
        $stmt->execute();
        $response = ['success' => true, 'repostado' => true, 'message' => 'Post repostado!'];

        // notificação (se autor existir e não for o próprio usuário)
        if (!empty($authorId) && $authorId != $userId) {
          $tipo_notificacao = 'repost';
          $stmt_notif = $conn->prepare("INSERT INTO notificacoes (user_id, remetente_id, tipo, link_id) VALUES (?, ?, ?, ?)");
          $stmt_notif->bind_param("iisi", $authorId, $userId, $tipo_notificacao, $postId);
          $stmt_notif->execute();
        }
      }
    } elseif (isset($_POST['curtir_post'])) {
      // toggle curtida
      $stmt = $conn->prepare("SELECT id FROM curtidas WHERE usuario_id = ? AND obra_id = ?");
      $stmt->bind_param("ii", $userId, $postId);
      $stmt->execute();
      $res = $stmt->get_result();

      if ($res->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM curtidas WHERE usuario_id = ? AND obra_id = ?");
        $stmt->bind_param("ii", $userId, $postId);
        $stmt->execute();
        $response = ['success' => true, 'curtido' => false, 'message' => 'Post descurtido.'];
      } else {
        $stmt = $conn->prepare("INSERT INTO curtidas (usuario_id, obra_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $postId);
        $stmt->execute();
        $response = ['success' => true, 'curtido' => true, 'message' => 'Post curtido!'];

        // notificação (se autor existir e não for o próprio usuário)
        if (!empty($authorId) && $authorId != $userId) {
          $tipo_notificacao = 'curtida';
          $stmt_notif = $conn->prepare("INSERT INTO notificacoes (user_id, remetente_id, tipo, link_id) VALUES (?, ?, ?, ?)");
          $stmt_notif->bind_param("iisi", $authorId, $userId, $tipo_notificacao, $postId);
          $stmt_notif->execute();
        }
      }
    }
  } catch (mysqli_sql_exception $e) {
    $response = ['success' => false, 'message' => "ERRO DE SQL: " . $e->getMessage()];
  }

  header('Content-Type: application/json');
  echo json_encode($response);
  exit;
}

// ---------- FEED (foryou / seguindo) ----------
$tipo_feed = $_GET['feed'] ?? 'foryou';

if ($tipo_feed === 'foryou') {
  $sql = "SELECT O.*,
                 u.nome_user,
                 u.user_avatar,
                 u.nome_completo,
                 u.user_tag,
                 (SELECT COUNT(*) FROM curtidas C WHERE C.obra_id = O.id) AS total_curtidas,
                 (SELECT GROUP_CONCAT(T.nome_tag SEPARATOR ',') FROM obras_tags TO_ JOIN tags T ON TO_.id_tag = T.tag_id WHERE TO_.id_obra = O.id) AS tags
          FROM obras O
          JOIN users u ON O.portfolio_id = u.id
          ORDER BY O.data_publicacao DESC
          LIMIT 100";
  $result = $conn->query($sql);
} else { // seguindo
  $sql = "SELECT O.*,
                 u.nome_user,
                 u.user_avatar,
                 u.nome_completo,
                 u.user_tag,
                 (SELECT COUNT(*) FROM curtidas C WHERE C.obra_id = O.id) AS total_curtidas,
                 (SELECT GROUP_CONCAT(T.nome_tag SEPARATOR ',') FROM obras_tags TO_ JOIN tags T ON TO_.id_tag = T.tag_id WHERE TO_.id_obra = O.id) AS tags
          FROM obras O
          JOIN users u ON O.portfolio_id = u.id
          JOIN seguidores s ON s.seguido_id = u.id
          WHERE s.seguidor_id = ?
          ORDER BY O.data_publicacao DESC
          LIMIT 100";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $result = $stmt->get_result();
}

// ---------- UTIL: formata curtidas (1.2K / 3.4M) ----------
function formatarCurtidas($n) {
  if (!is_numeric($n)) return $n;
  $n = intval($n);
  if ($n >= 1000000) return number_format($n / 1000000, 1) . 'M';
  if ($n >= 1000) return number_format($n / 1000, 1) . 'K';
  return (string)$n;
}

// verifica se segue alguém
$verifica_sql = "SELECT COUNT(*) as total FROM seguidores WHERE seguidor_id = ?";
$stmt_verifica = $conn->prepare($verifica_sql);
$stmt_verifica->bind_param("i", $userId);
$stmt_verifica->execute();
$row_verifica = $stmt_verifica->get_result()->fetch_assoc();
$segue_alguem = ($row_verifica['total'] ?? 0) > 0;
$no_seguindo = $segue_alguem ? "" : "Você ainda não está seguindo ninguém. <br> Explore a comunidade e comece a seguir autores que você gosta!";

// verifica se há obras
$count_sql = "SELECT COUNT(*) as total FROM obras";
$count_row = $conn->query($count_sql)->fetch_assoc();
$no_obras = ($count_row['total'] ?? 0) < 1 ? "Não há obras disponíveis" : "";

// --- se chegou aqui, renderiza a página ---
$shared_post_id = $_GET['post'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Navegação - HarpHub</title>

  <script>
    var showWelcomeModal = <?php echo json_encode($show_welcome_modal); ?>;
    var loggedInUserId = <?php echo json_encode($userId); ?>;
  </script>

  <link rel="stylesheet" href="../Styles/telainicial.css">
  <link rel="stylesheet" href="../Styles/global.css">
  <link rel="stylesheet" href="../Styles/welcomeModal.css">
  <link rel="stylesheet" href="../Styles/comments.css">
  <link rel="stylesheet" href="../Styles/opcoesPostModal.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body <?= $shared_post_id ? 'data-shared-post-id="' . htmlspecialchars($shared_post_id) . '"' : '' ?>>
  <header>
    <div class="search-container">
      <div class="search-bar-wrapper">
        <i class="fi-rr-search"></i>
        <input type="search" id="search-bar" class="search-bar" name="query" placeholder="Barra de pesquisa">
      </div>
      <div id="suggestions-box"></div>
    </div>

    <div class="nav-user">
      <ul>
        <li><a href="notificacoes.php"><i class="fi fi-rs-bell"></i></a></li>
        <li><span><img src="../images/avatares/Users/<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar do usuário"></span></li>
      </ul>
    </div>

    <div class="modal-perfil">
      <ul>
        <li><a href="perfil.php">Perfil</a></li>
        <li>Trocar de conta</li>
        <li>
          <form action="logout.php"><input type="submit" value="Sair da conta"></form>
        </li>
      </ul>
    </div>
  </header>

  <main>
    <nav class="nav-side" id="menu">
      <div class="logotipo"><span>Harp</span>Hub</div>
      <ul class="pages">
        <li><a class="<?= $tipo_feed === 'foryou' ? 'selecionado' : '' ?>" href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a></li>
        <li><a class="<?= $tipo_feed === 'seguindo' ? 'selecionado' : '' ?>" href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a></li>
        <li><a href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a></li>
        <li><a href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar Post</a></li>
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

    <section class="navigation-user" id="NavigationUser">
      <div class="conteudo" id="feed-conteudo">
        <?= $tipo_feed === 'seguindo' ? $no_seguindo : '' ?>
        <?= $tipo_feed === 'foryou' ? $no_obras : '' ?>

        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($post = $result->fetch_assoc()): ?>
            <?php
              // verifica curtida e repost atual do usuário (eficiente o suficiente)
              $stmt_curtida = $conn->prepare("SELECT 1 FROM curtidas WHERE usuario_id = ? AND obra_id = ? LIMIT 1");
              $stmt_curtida->bind_param("ii", $userId, $post['id']);
              $stmt_curtida->execute();
              $curtido = $stmt_curtida->get_result()->num_rows > 0;

              $stmt_repost = $conn->prepare("SELECT 1 FROM reposts WHERE user_id = ? AND original_post_id = ? LIMIT 1");
              $stmt_repost->bind_param("ii", $userId, $post['id']);
              $stmt_repost->execute();
              $repostado = $stmt_repost->get_result()->num_rows > 0;

              $post['tags'] = $post['tags'] ? array_map('trim', explode(',', $post['tags'])) : [];
            ?>
            <article class="posts" data-post-id="<?= intval($post['id']) ?>"
              data-user-id="<?= intval($post['portfolio_id']) ?>"
              data-user-name="<?= htmlspecialchars($post['nome_user']) ?>"
              data-user-name-completo="<?= htmlspecialchars($post['nome_completo']) ?>"
              data-tags='<?= json_encode($post['tags'], JSON_UNESCAPED_UNICODE) ?>'
              data-imagem-url="<?= htmlspecialchars($post['arquivo_url'] ?? '') ?>"
              data-user-avatar="<?= htmlspecialchars($post['user_avatar']) ?>"
              data-count-curtidas="<?= intval($post['total_curtidas']) ?>"
              data-data-publicacao="<?= htmlspecialchars(date('d/m/Y H:i', strtotime($post['data_publicacao'] ?? date('Y-m-d H:i:s')))) ?>">
              
              <div class="descricao-post">
                <ul>
                  <li><span class="avatar-desc"><img src="../images/avatares/Users/<?php echo htmlspecialchars($post['user_avatar']); ?>" alt=""></span></li>
                  <li><span class="nomeUsr"><?= htmlspecialchars($post['nome_completo']) ?></span></li>
                  <li><span class="nomeEX-desc">@<?= htmlspecialchars($post['nome_user']) ?></span></li>
                </ul>
                <div class="options-post">
                  <i class="fi fi-br-menu-dots" data-menu-dots></i>
                </div>
              </div>

              <?php if (!empty($post['arquivo_url'])): ?>
                <div class="img-post">
                  <img src="../images/uploads/<?= htmlspecialchars($post['arquivo_url']) ?>" alt="Imagem do post">
                </div>
              <?php else: ?>
                <p>Sem imagem para este post.</p>
              <?php endif; ?>

              <div class="footer-post">
                <form class="interactions-form" data-post-id="<?= intval($post['id']) ?>">
                  <input type="hidden" name="post_id" value="<?= intval($post['id']) ?>">
                  <ul class="interactions-post">
                    <li><button type="button" class="btn-comment"><i class="fi fi-rr-comment"></i></button></li>

                    <li>
                      <button type="button" class="repostar-btn <?= $repostado ? 'repostado' : '' ?>" data-action="repostar" data-post-id="<?= intval($post['id']) ?>">
                        <i class="fi fi-rr-refresh"></i>
                      </button>
                    </li>

                    <li>
                      <button type="button" class="curtida <?= $curtido ? 'curtido' : '' ?>" data-action="curtir" data-post-id="<?= intval($post['id']) ?>">
                        <svg width="1.5rem" height="1.5rem" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                      </button>
                      <p class="count-curtidas"><?= formatarCurtidas($post['total_curtidas'] ?? 0) ?></p>
                    </li>

                    <li><button class="btn-share" type="button"><i class="fi fi-rs-redo"></i></button></li>

                    <li>
                      <button type="button" class="salvar-btn" data-post-id="<?= intval($post['id']) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" height="1.5rem" width="1.5rem" viewBox="0 0 384 512"><path d="M0 48C0 21.5 21.5 0 48 0H336c26.5 0 48 21.5 48 48V464L192 352 0 464V48z"/></svg>
                      </button>
                    </li>
                  </ul>
                </form>
              </div>
            </article>
          <?php endwhile; ?>
        <?php else: ?>
          <p style="padding:20px; color:#71767b;">Nenhum post encontrado.</p>
        <?php endif; ?>
      </div>
    </section>

    <section class="suggest">
      <article class="seguidores-suggestions">
        <div class="titulo">
          <h1>Sugestões de artistas</h1><a href="#">Ver mais</a>
        </div>
        <ul class="sugestoes">
          <!-- Sugestões estáticas: trocar por dinâmica se quiser -->
          <?php for ($i=0;$i<5;$i++): ?>
            <li class="sugestao">
              <img src="../images/avatares/Users/profile.png" alt="Avatar do usuário">
              <div class="nome">
                <h1 class="name-exibição">teste <?= $i+1 ?></h1>
                <h2 class="name-user">@teste<?= $i+1 ?></h2>
              </div>
              <button class="seguir-btn">Seguir</button>
            </li>
          <?php endfor; ?>
        </ul>
      </article>

      <footer>
        <p>Regras do HarpHub</p>
        <p>Política de Privacidade</p>
        <p>Contrato do Usuário</p>
        <p>Acessibilidade</p>
        <p>&copy; 2025 HarpHub</p>
      </footer>
    </section>
  </main>

  <!-- WELCOME MODAL -->
  <div id="welcome-modal" class="modal-welcome" style="display: none;">
    <div class="modal-welcome-content">
      <form action="UsuarioLogado.php?feed=foryou" method="post">
        <h1>Escolha os Conteúdos</h1>
        <p>Selecione os conteúdos que deseja priorizar na sua página, adaptando a experiência as suas inspirações artísticas</p>
        <ul>
          <li><input type="checkbox" name="CC[]" value="obras-literarias">Obras literárias</li>
          <li><input type="checkbox" name="CC[]" value="poemas">Poemas</li>
          <li><input type="checkbox" name="CC[]" value="fotografias">Fotografias</li>
          <li><input type="checkbox" name="CC[]" value="design-grafico">Design Gráfico</li>
          <li><input type="checkbox" name="CC[]" value="musicas">Músicas</li>
          <li><input type="checkbox" name="CC[]" value="ilustracoes">Ilustrações</li>
        </ul>
        <input type="submit" value="Concluir" name="salvar_interesses" class="close-welcome-button-main">
      </form>
    </div>
  </div>

  <div class="modal-overlay"></div>
  <div class="modal-post">
    <span class="close-button">&times;</span>
    <div class="modal-post-content"></div>
  </div>

  <div class="modal-other-perfil-container">
    <div class="modal-other-perfil-content-wrapper"><!-- preenchido via JS --></div>
  </div>

  <!-- SCRIPTS -->
  <script src="../Scripts/TelaInicial.js"></script>
  <script src="../Scripts/modais.js"></script>
  <script src="../Scripts/ajaxInteractions.js"></script>
  <script src="../Scripts/carregar_posts.js"></script>

  <script>
    // Mostrar welcome modal se necessário
    (function() {
      if (showWelcomeModal) {
        const m = document.getElementById('welcome-modal');
        if (m) { m.style.display = 'block'; }
      }
      // Delegação para botões de curtir/repost — caso não use ajaxInteractions.js
      document.addEventListener('click', async function(e){
        const curt = e.target.closest('[data-action="curtir"]');
        const repost = e.target.closest('[data-action="repostar"]');
        if (curt || repost) {
          const isCurt = !!curt;
          const btn = curt || repost;
          const postId = btn.dataset.postId;
          if (!postId) return;
          const form = new FormData();
          form.append('post_id', postId);
          if (isCurt) form.append('curtir_post', '1');
          else form.append('repostar_post', '1');

          try {
            const res = await fetch(window.location.href, { method: 'POST', body: form });
            const data = await res.json();
            if (data.success) {
              // Atualizar UI básica
              if (isCurt) {
                btn.classList.toggle('curtido', data.curtido ?? false);
                // atualizar contador visual (simples requisição ou incrementar localmente)
                const countEl = btn.closest('li').querySelector('.count-curtidas');
                if (countEl && typeof data.curtido !== 'undefined') {
                  // recarrega a contagem por segurança (poderia fazer outra requisição)
                  let current = parseInt(countEl.textContent.replace(/\D/g,'')) || 0;
                  current = data.curtido ? current + 1 : Math.max(0, current - 1);
                  countEl.textContent = current >= 1000 ? (current/1000).toFixed(1)+'K' : current;
                }
              } else {
                btn.classList.toggle('repostado', data.repostado ?? false);
              }
            } else {
              alert(data.message || 'Erro ao executar ação.');
            }
          } catch (err) {
            console.error(err);
            alert('Erro de conexão.');
          }
        }
      });
    })();
  </script>
</body>
</html>
