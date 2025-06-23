<?php
require_once 'conexao.php';;

session_start(); // Inicia a sessão

$erros = [];

if (isset($_POST['email']) && isset($_POST['senha'])) {
    $login = $_POST['email'];
    $senha = $_POST['senha'];

    if (empty($login) || empty($senha)) {
        $erros[] = "Preencha todos os campos!";
    } else {
        // Usando prepared statements para evitar SQL Injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $login);
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

                // Redireciona para a página de usuário logado
                header("Location: ../Layout/load.html?message=Login realizado com sucesso!&action=login");
                exit();
            } else {
                $erros[] = "Senha incorreta!";
            }
        } else {
            $erros[] = "Email não encontrado!";
        }
    }
}

// Exibe os erros, se houver
if (!empty($erros)) {
    foreach ($erros as $erro) {
        echo "<script>alert('Erro: " . $erro . "');</script>";
    }
}

$nome_completo = $_POST['nome_completo'];
$nome_user = $_POST['nome_user'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$data_nascimento = $_POST['data_nasc'];
$senha_hash = password_hash($senha, PASSWORD_DEFAULT); // Cria um hash seguro para a senha
$tags_user = $_POST['tag']; // Corrigido para capturar as tags selecionadas

// Verifica se a data de nascimento é válida antes de continuar
$data_nascimento_formatada = date('Y-m-d', strtotime($data_nascimento));
if (!$data_nascimento_formatada || $data_nascimento_formatada === '1970-01-01') {
    echo '<script>
        alert("Data de nascimento inválida!");
        window.location.href = "../Layout/cadastro.html";
    </script>';
    exit;
}

$idade = date_diff(date_create($data_nascimento_formatada), date_create('now'))->y;
$erros = [];

// Verificações
try {
    if (empty($nome_user) || empty($email) || empty($senha) || empty($data_nascimento)) {
        echo '<script>
            alert("Preencha todos os campos!");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    // Verifica se pelo menos 3 tags foram selecionadas
    if (isset($tags_user) && count($tags_user) < 3) {
        echo '<script>
            alert("Selecione pelo menos 3 tags!");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    if (strlen($nome_user) < 3 || strlen($nome_user) > 20) {
        echo '<script>
            alert("Nome de usuário inválido! Deve ter entre 3 e 20 caracteres.");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>
            alert("Email inválido!");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    if (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/^[\w-.@]+$/')) {
        echo '<script>
            alert("Senha inválida! A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas e números e alfanuméricos.");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    if ($senha !== $_POST['confirmar_senha']) {
        echo '<script>
            alert("As senhas não coincidem!");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    if ($idade < 18) {
        echo '<script>
            alert("Você deve ter pelo menos 18 anos para se cadastrar!");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    if (!isset($_POST['terms'])) {
        echo '<script>
            alert("Você deve aceitar os termos de uso!");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    // Verifica se o email já está cadastrado
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<script>
            alert("Email já cadastrado!");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    // Verifica se o nome de usuário já está cadastrado
    $stmt = $conn->prepare('SELECT * FROM users WHERE nome_user = ?');
    $stmt->bind_param("s", $nome_user);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo '<script>
            alert("Nome de usuário já cadastrado!");
            window.location.href = "../Layout/cadastro.html";
        </script>';
        exit;
    }

    $stmt->close();

    // Insere os dados no banco de dados
    $stmt = $conn->prepare("INSERT INTO users (nome_completo, nome_user, email, senha, data_nasc, tags) VALUES (?, ?, ?, ?, ?, ?)");
    $tags_serializadas = implode(',', $tags_user); // Serializa as tags para salvar no banco
    $stmt->bind_param("ssssss", $nome_completo, $nome_user, $email, $senha_hash, $data_nascimento, $tags_serializadas);

    if ($stmt->execute()) {
        header("Location: ../Layout/load.html?message=Cadastro realizado com sucesso!&action=cadastro");
    } else {
        throw new Exception("Erro ao cadastrar: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    echo '<script>
        alert("Erro ao cadastrar: ' . $e->getMessage() . '");
        window.location.href = "../Layout/cadastro.html";
    </script>';
}
?>






