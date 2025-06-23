<?php
require_once 'conexao.php';// Protege a página para usuários autenticados

session_start(); // Inicia a sessão

$erros = [];

if (isset($_POST['login']) && isset($_POST['senha'])) {
    $login = $_POST['login']; // Pode ser email ou nome_user
    $senha = $_POST['senha'];

    if (empty($login) || empty($senha)) {
        $erros[] = "Preencha todos os campos!";
    } else {
        // Usando prepared statements para evitar SQL Injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR nome_user = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verifica a senha usando password_verify
            if (password_verify($senha, $user['senha'])) {
                // Configura as variáveis de sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome_user'];
                $_SESSION['user_email'] = $user['email'];

                // Redireciona para a página de carregamento
                header("Location: ../Layout/load.html?message=Login realizado com sucesso!&action=login");
                exit();
            } else {
                $erros[] = "Senha incorreta!";
            }
        } else {
            $erros[] = "Email ou nome de usuário não encontrado!";
            
            
        }
    }
}
// Exibe os erros, se houver
if (!empty($erros)) {
    foreach ($erros as $erro) {
        echo "<script>alert('Erro: " . $erro . "');
        location.href = '../Layout/login.html';
        </script>";
        
    }
}
?>