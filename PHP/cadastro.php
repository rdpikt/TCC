<?php
require_once 'conexao.php';

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

$erros = [];
$response = [];

// Validações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_completo = $_POST['nome_completo'] ?? '';
    $nome_user = $_POST['nome_user'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $data_nascimento = $_POST['data_nasc'] ?? '';
    $terms = isset($_POST['terms']);

    if (empty($nome_completo) || empty($nome_user) || empty($email) || empty($senha) || empty($data_nascimento)) {
        $erros[] = 'Preencha todos os campos obrigatórios!';
    }
    if (strlen($nome_user) < 3 || strlen($nome_user) > 20) {
        $erros[] = 'Nome de usuário deve ter entre 3 e 20 caracteres.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido!';
    }
    if (strlen($senha) < 8) {
        $erros[] = 'Senha deve ter pelo menos 8 caracteres.';
    }
    if ($senha !== $confirmar_senha) {
        $erros[] = 'As senhas não coincidem!';
    }
    if (!$terms) {
        $erros[] = 'Você deve aceitar os termos de uso.';
    }

    // Validação de idade
    if (!empty($data_nascimento)) {
        $data_nascimento_formatada = date('Y-m-d', strtotime($data_nascimento));
        if (!$data_nascimento_formatada || $data_nascimento_formatada === '1970-01-01') {
            $erros[] = 'Data de nascimento inválida!';
        } else {
            $idade = date_diff(date_create($data_nascimento_formatada), date_create('now'))->y;
            if ($idade < 16) {
                $erros[] = 'Você deve ter mais de 16 anos para se cadastrar!';
            }
        }
    }

    // Se não houver erros de validação inicial, verifica o banco de dados
    if (empty($erros)) {
        // Verifica se o email já está cadastrado
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $erros[] = "Este email já está em uso!";
        }
        $stmt->close();

        // Verifica se o nome de usuário já está cadastrado
        $stmt = $conn->prepare('SELECT id FROM users WHERE nome_user = ?');
        $stmt->bind_param("s", $nome_user);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $erros[] = "Este nome de usuário já está em uso!";
        }
        $stmt->close();
    }

    // Se, depois de todas as checagens, o array de erros ainda estiver vazio, tenta cadastrar
    if (empty($erros)) {
        try {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nome_completo, nome_user, email, senha, data_nasc) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nome_completo, $nome_user, $email, $senha_hash, $data_nascimento_formatada);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Cadastro realizado com sucesso!';
                $response['redirect_url'] = '../Layout/load.html?message=Cadastro realizado com sucesso!&ction=cadastro';
            } else {
                $erros[] = "Erro ao registrar no banco de dados.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $erros[] = "Erro no servidor: " . $e->getMessage();
        }
    }
} else {
    $erros[] = "Método de requisição inválido.";
}

// Se houver erros em qualquer ponto, prepara a resposta de erro
if (!empty($erros)) {
    $response['success'] = false;
    $response['errors'] = $erros;
}

// Envia a resposta final em JSON e termina o script
echo json_encode($response);
exit();