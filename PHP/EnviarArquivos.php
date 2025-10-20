<?php
require "conexao.php";
require "protect.php";

$tipo_feed = $_GET['feed'] ?? 'foryou';
$userId = $_SESSION['user_id'];
$user_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'profile.png';

/**
 * Nova Função para processar e ligar as tags.
 *
 * @param mysqli $conn - A conexão com o banco
 * @param int $obra_id - O ID do post que acabamos de criar
 * @param string $tagsPostString - A string de tags (ex: "arte,desenho,3d")
 * @return bool - true se sucesso, false se falhar
 */
function processAndLinkTags($conn, $obra_id, $tagsPostString)
{
  if (empty($tagsPostString)) {
    return true; // Nenhuma tag para processar, sucesso.
  }

  $tagsArray = explode(',', $tagsPostString);

  foreach ($tagsArray as $tagName) {
    $tagName = trim($tagName);
    if (empty($tagName))
      continue; // Pula tags vazias

    $tag_id = null;

    // 1. Verifica se a tag já existe
    $stmtTag = $conn->prepare("SELECT tag_id FROM tags WHERE nome_tag = ?");
    $stmtTag->bind_param('s', $tagName);
    $stmtTag->execute();
    $resultTag = $stmtTag->get_result();

    if ($row = $resultTag->fetch_assoc()) {
      // Tag encontrada
      $tag_id = $row['tag_id'];
    } else {
      // 2. Se não existir, cria a tag
      $stmtTagInsert = $conn->prepare("INSERT INTO tags (nome_tag) VALUES (?)");
      $stmtTagInsert->bind_param('s', $tagName);
      if ($stmtTagInsert->execute()) {
        $tag_id = $stmtTagInsert->insert_id; // Pega o ID da nova tag
      } else {
        $stmtTagInsert->close();
        $stmtTag->close();
        return false; // Falha ao inserir nova tag
      }
      $stmtTagInsert->close();
    }
    $stmtTag->close();

    // 3. Liga a tag à obra na tabela 'obras_tags' 
    // (Verifique se o nome da sua tabela é 'obras_tags' ou 'obra_tags')
    if ($tag_id) {
      $stmtLink = $conn->prepare("INSERT INTO obras_tags (id_obra, id_tag) VALUES (?, ?)");
      $stmtLink->bind_param('ii', $obra_id, $tag_id);

      // Ignora erro de "entrada duplicada" (caso a ligação já exista)
      if (!$stmtLink->execute() && $conn->errno != 1062) {
        $stmtLink->close();
        return false; // Falha ao ligar a tag
      }
      $stmtLink->close();
    }
  }
  return true; // Todas as tags foram processadas
}
// Fim da nova função


if ($_SERVER['REQUEST_METHOD'] === 'POST'):

  $erros = [];

  $UserID = $_SESSION['user_id'];
  $TituloPost = $_POST['titulo'] ?? '';
  $DescricaoPost = $_POST['descricao'] ?? '';
  $tipo_obra = $_POST['tipo_obra'] ?? 'Imagem';
  $tagsPost = $_POST['tags'] ?? ''; // Recebe "arte,desenho,3d"

  $tipos_validos = ['Imagem', 'Texto', 'Video'];
  if (!in_array($tipo_obra, $tipos_validos)) {
    $erros[] = 'Tipo de obra inválido';
  }

  // Lógica de upload de arquivos
  $nome_arquivo_final = null;
  $extensao_final = null;

  if ($tipo_obra == 'Imagem' || $tipo_obra == 'Video') {
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {

      $extensoes_aceitas = ($tipo_obra == 'Imagem') ? ['png', 'jpeg', 'jpg'] : ['mov', 'mp4', 'wmv'];
      $caminho_pasta = ($tipo_obra == 'Imagem') ? '../images/uploads/' : '../images/uploads/videos/';

      $aux = explode('.', $_FILES['arquivo']['name']);
      $extensao = strtolower(end($aux));

      if (!in_array($extensao, $extensoes_aceitas)) {
        $erros[] = "Extensão inválida. Apenas " . implode(', ', $extensoes_aceitas) . " são aceitos.";
      } else {
        if (!is_dir($caminho_pasta)) {
          mkdir($caminho_pasta, 0755, true);
        }
        $nome_arquivo_final = date('dmYs') . '_' . uniqid() . '.' . $extensao;
        $extensao_final = $extensao;
        if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminho_pasta . $nome_arquivo_final)) {
          $erros[] = "Houve um erro ao gravar o arquivo na pasta.";
        }
      }
    } else {
      $erros[] = "Nenhum arquivo enviado ou erro no upload.";
    }
  }

  // Validações
  if (empty($DescricaoPost))
    $erros[] = "A descrição não pode ser vazia.";
  if ($tipo_obra == 'Texto' && empty($TituloPost))
    $erros[] = "O título não pode ser vazio.";


  // Se não houver erros, processa o tipo de obra
  if (count($erros) == 0) {

    // Inicia a transação
    $conn->begin_transaction();
    $obra_id_criada = null;
    $sucesso_obra = false;

    try {
      switch ($tipo_obra):
        case 'Imagem':
        case 'Video':
          $sql = "INSERT INTO obras (portfolio_id, tipo_obra, titulo, descricao, arquivo_url, tipo_imagem)
                            VALUES (?, ?, ?, ?, ?, ?)";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param('isssss', $UserID, $tipo_obra, $TituloPost, $DescricaoPost, $nome_arquivo_final, $extensao_final);

          if ($stmt->execute()) {
            $obra_id_criada = $stmt->insert_id; // Pega o ID do post
            $sucesso_obra = true;
          }
          $stmt->close();
          break;

        case 'Texto':
          $sql = "INSERT INTO obras (portfolio_id, tipo_obra, titulo, descricao)
                            VALUES (?, ?, ?, ?)";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param('isss', $UserID, $tipo_obra, $TituloPost, $DescricaoPost);

          if ($stmt->execute()) {
            $obra_id_criada = $stmt->insert_id; // Pega o ID do post
            $sucesso_obra = true;
          }
          $stmt->close();
          break;
      endswitch;

      // Se a obra foi salva E as tags também foram salvas...
      if ($sucesso_obra && processAndLinkTags($conn, $obra_id_criada, $tagsPost)) {
        // Sucesso! Salva tudo.
        $conn->commit();
        echo "<div class='alert alert-success' role='alert'>Conteúdo publicado com sucesso</div>";
        echo "<meta http-equiv='refresh' content='3;URL=UsuarioLogado.php'>";
      } else {
        // Falha! Desfaz tudo.
        $conn->rollback();
        $erros[] = "Erro ao salvar os dados da obra ou das tags.";
      }

    } catch (Exception $e) {
      // Se qualquer coisa der errado, desfaz tudo
      $conn->rollback();
      $erros[] = "Erro no banco de dados: " . $e->getMessage();
    }

  }

  // Se houver erros (de upload, validação ou banco de dados), mostra eles
  if (count($erros) > 0) {
    foreach ($erros as $erro):
      echo "<div class='alert alert-danger' role='alert'>$erro</div>";
    endforeach;
  }

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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <style>
    /* Estilos para a lista de tags visual */
    .tags-list li {
      display: inline-block;
      background-color: #eee;
      color: #333;
      padding: 5px 10px;
      border-radius: 20px;
      margin: 5px 5px 5px 0;
      font-size: 14px;
    }

    .tags-list li span {
      margin-left: 8px;
      color: #999;
      cursor: pointer;
    }

    .tags-list li span:hover {
      color: #000;
    }
  </style>
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
        <li><a href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a></li>
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
    <form action="EnviarArquivos.php" method="POST" enctype="multipart/form-data">
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
      <textarea class="content areatxt" rows="4" name="descricao"
        placeholder="Coloque uma descrição ao seu post"></textarea>

      <ul class="tags-list">
      </ul>
      <input class="Titulo content tags" type="text" id="tag-entry"
        placeholder="Digite as tags (separadas por espaço ou vírgula)">
      <input type="hidden" name="tags" id="tags-data">

      <button id="add-to-list" type="button">adicionar</button>

      <input class="content" type="file" name="arquivo" id="file-input">

      <input class="Post-btn" type="submit" value="Postar">
    </form>
  </section>
</body>
<script>
  // --- SCRIPT DE TAGS ---
  const TagList = document.querySelector(".tags-list");
  const addBtn = document.querySelector("#add-to-list");
  const tagEntry = document.querySelector("#tag-entry"); // Input para digitar
  const tagsData = document.querySelector("#tags-data");   // Input hidden para enviar

  let currentTags = []; // Array para guardar as tags

  // Atualiza a lista visual (UL) e o input hidden
  function updateTagsDisplay() {
    TagList.innerHTML = ''; // Limpa a lista visual

    currentTags.forEach((tag, index) => {
      const li = document.createElement('li');
      li.textContent = tag;

      // Botão de remover (X)
      const removeBtn = document.createElement('span');
      removeBtn.innerHTML = '&times;'; // 'x'
      removeBtn.style.cursor = 'pointer';
      removeBtn.onclick = () => {
        currentTags.splice(index, 1); // Remove do array
        updateTagsDisplay(); // Atualiza a tela
      };

      li.appendChild(removeBtn);
      TagList.appendChild(li);
    });

    // Atualiza o input hidden com as tags separadas por vírgula
    tagsData.value = currentTags.join(',');
  }

  // =================================================================
  // MELHORIA PRINCIPAL ESTÁ AQUI
  // Esta função agora é mais inteligente e aceita tags separadas
  // por espaço ou vírgula, corrigindo o bug de "apenas 1 tag".
  // =================================================================
  function addTag() {
    // 1. Pega o valor, limpa espaços e converte para minúsculo
    const input = tagEntry.value.trim().toLowerCase();

    if (!input) return; // Não faz nada se estiver vazio

    // 2. Substitui todos os espaços (mesmo vários) por uma vírgula
    //    e depois divide o resultado por vírgula.
    //    Ex: "arte, digital desenho" -> "arte,,digital,desenho" -> ["arte", "", "digital", "desenho"]
    const tagsArray = input.replace(/\s+/g, ',').split(',');

    // 3. Loop sobre as tags encontradas
    for (const tagValue of tagsArray) {
      const cleanTag = tagValue.trim();

      // 4. Adiciona se não for vazia E não estiver já na lista
      if (cleanTag && !currentTags.includes(cleanTag)) {
        currentTags.push(cleanTag);
      }
    }

    // 5. Limpa o campo de digitação e atualiza a tela
    tagEntry.value = '';
    updateTagsDisplay();
  }

  // Adiciona tag ao pressionar 'Enter'
  tagEntry.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault(); // Impede o 'Enter' de submeter o formulário
      addTag();
    }
  });

  // Adiciona tag ao clicar no botão 'adicionar'
  addBtn.addEventListener('click', () => {
    addTag();
  });

  // --- SCRIPT PARA UPLOAD DE ARQUIVO ---
  const fileInput = document.getElementById('file-input');
  const radioImagem = document.getElementById('Imagem');
  const radioVideo = document.getElementById('Video');
  const radioTexto = document.getElementById('Texto');

  function updateFileInput() {
    if (radioImagem.checked) {
      fileInput.accept = "image/png, image/jpg, image/jpeg";
      fileInput.style.display = 'block'; // Mostra o input
    } else if (radioVideo.checked) {
      fileInput.accept = "video/mp4, video/x-ms-wmv, video/quicktime"; // .mov, .mp4, .wmv
      fileInput.style.display = 'block'; // Mostra o input
    } else if (radioTexto.checked) {
      fileInput.style.display = 'none'; // Esconde o input para posts de texto
    }
  }

  // Adiciona os 'ouvintes' de evento
  radioImagem.addEventListener('change', updateFileInput);
  radioVideo.addEventListener('change', updateFileInput);
  radioTexto.addEventListener('change', updateFileInput);

  // Executa a função uma vez no carregamento da página
  document.addEventListener('DOMContentLoaded', updateFileInput);

</script>

</html>