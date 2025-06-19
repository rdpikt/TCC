<?php
require 'conexao.php';

$nome_completo = $_POST['nome_completo'];
$nome_user = $_POST['nome_user'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$data_nascimento = $_POST['data_nasc'];
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

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

// verificações 
try{
if (empty($nome_user) || empty($email) || empty($senha) || empty($data_nascimento)) {
    echo '<script>
        alert("Preencha todos os campos!");
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

if (!filter_var($nome_completo, FILTER_SANITIZE_STRING)) {
    echo '<script>
        alert("Nome completo inválido!");
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

if (strlen($senha) < 5 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
    echo '<script>
        alert("Senha inválida! A senha deve ter pelo menos 5 caracteres, incluindo letras maiúsculas, minúsculas e números.");
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
    $erros[] = "Você deve ter pelo menos 18 anos para se cadastrar!";
    exit;
}

if(!isset($_POST['terms'])) {
    $erros[]= "Você deve aceitar os termos de uso!";
    echo '<script>
        alert("Você deve aceitar os termos de uso!");
        window.location.href = "../Layout/cadastro.html";
    </script>';
    exit; 
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $erros[] = "Email já cadastrado!";
    echo '<script>
        alert("Email já cadastrado!");
        window.location.href = "../Layout/cadastro.html";
    </script>';
    exit;
}
$stmt = $conn->prepare('SELECT * FROM users WHERE nome_user = ?');
$stmt->bind_param("s", $nome_user);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0){
    $erros[] = "Nome de usuário já cadastrado!";
    echo '<script>
        alert("Nome de usuário já cadastrado!");
        window.location.href = "../Layout/cadastro.html";
    </script>';
    exit;
}


$stmt->close();


if(empty($erros)) {
    $stmt = $conn->prepare("INSERT INTO users (nome_completo, nome_user, email, senha, data_nasc) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nome_completo, $nome_user, $email, $senha_hash, $data_nascimento);

    if ($stmt->execute()) {
        header("Location: ../Layout/load.html?message=Cadastro realizado com sucesso!&action=cadastro");
    } else {
       throw new Exception("Erro ao cadastrar: " . $stmt->error);
    }

    $stmt->close();
} else {
    foreach ($erros as $erro) {
        echo '<script>
            alert("Erro: ' . $erro . '");
        </script>';
    }
    header("refresh:2; url=../Layout/cadastro.html");

    }
}
catch(Exception $e) {
    echo '<script>
        alert("Erro ao cadastrar: ' . $e->getMessage() . '");
        window.location.href = "../Layout/cadastro.html";
    </script>';
}






