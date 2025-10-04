<?php
require_once 'conexao.php';

header('Content-Type: application/json');
session_start();

$response = [];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($login) || empty($senha)) {
        $erros[] = "Preencha todos os campos!";
    } 
    else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR nome_user = ?");
            $stmt->bind_param("ss", $login, $login);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($senha, $user['senha'])) {
                    // Senha correta, login bem-sucedido
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nome_user'];
                    $_SESSION['user_name_completo'] = $user['nome_completo'];
                    $_SESSION['tipo_criador'] = $user['user_tag'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['avatar'] = $user['user_avatar'];
                    $_SESSION['user_bio'] = $user['bio'];

                    $response['success'] = true;
                    $response['redirect_url'] = '../Layout/load.html?message=Login realizado com sucesso!&action=login'; // Redireciona para a página principal do usuário
                } else {
                    $erros[] = "Senha incorreta!";
                }
            } else {
                $erros[] = "Email ou nome de usuário não encontrado!";
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
?>