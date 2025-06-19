<?php
    require 'conexao.php';

    $erros = [];

    if (isset($_POST['email']) && isset($_POST['senha'])) {
        $login = $_POST['email'];
        $senha = $_POST['senha'];

        if (empty($login) || empty($senha)) {
            $erros[] = "Preencha todos os campos!";
        } else {
            // Usando prepared statements para evitar SQL Injection
            $login = $conn->real_escape_string($login);
            $senha = $conn->real_escape_string($senha);
            $stmt = $conn->prepare("SELECT * FROM clientes WHERE email = ? AND senha = ?");
            $stmt->bind_param("ss", $login, $senha);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {   
                $user = $result->fetch_assoc();
                // Iniciar sessão e armazenar informações do usuário
                if(!isset($_SESSION)) {
                    session_start();
                }

                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['user_email'] = $user['email'];

                // Redirecionar para UserPerfil.html
                echo "<script>
                    window.location.href = '../html/load.html';
                </script>";
                exit();
            } else {
                $erros[] = "Login ou senha incorretos!";
            }
        }
    }

    // Exibe os erros, se houver
    if (!empty($erros)) {
        foreach ($erros as $erro) {
            echo "<script>alert('Erro: " . $erro . "');</script>";
        }
    }
?>