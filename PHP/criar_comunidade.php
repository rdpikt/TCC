<?php
require "protect.php";
require "conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $tipo = $_POST['tipo_comunidade'];
    $dono_id = $_SESSION['user_id'];
    $imagem_nome = null;

    // Validação básica
    if (empty($nome) || empty($descricao) || empty($tipo)) {
        die("Erro: Todos os campos são obrigatórios.");
    }

    // Processamento do upload da imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $target_dir = "../images/avatares/Comunidades/";
        $ext = strtolower(pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION));
        $imagem_nome = date("dmYHis") . "_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $imagem_nome;
        $allowed_types = ['jpg', 'png', 'jpeg'];

        // Verifica se é uma imagem real
        $check = getimagesize($_FILES["imagem"]["tmp_name"]);
        if ($check === false) {
            die("Erro: O arquivo não é uma imagem válida.");
        }

        // Verifica o tipo de arquivo
        if (!in_array($ext, $allowed_types)) {
            die("Erro: Apenas arquivos JPG, JPEG e PNG são permitidos.");
        }

        // Tenta mover o arquivo
        if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
            die("Erro: Houve um problema ao fazer o upload da sua imagem.");
        }
    }

    $conn->begin_transaction();

    try {
        // Inserir a comunidade na tabela 'comunidades'
        $stmt = $conn->prepare("INSERT INTO comunidades (nome, descricao, imagem, dono_id, tipo_comunidade) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $nome, $descricao, $imagem_nome, $dono_id, $tipo);
        $stmt->execute();
        
        // Obter o ID da comunidade recém-criada
        $comunidade_id = $conn->insert_id;

        // Inserir o criador como 'dono' na tabela 'comunidade_membros'
        $stmt_membro = $conn->prepare("INSERT INTO comunidade_membros (comunidade_id, usuario_id, cargo) VALUES (?, ?, 'dono')");
        $stmt_membro->bind_param("ii", $comunidade_id, $dono_id);
        $stmt_membro->execute();

        $conn->commit();

        header("Location: explorar_comunidades.php");
        exit();

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        die("Erro ao criar a comunidade: " . $e->getMessage());
    }
} else {
    header("Location: criar_comunidade_form.php");
    exit();
}