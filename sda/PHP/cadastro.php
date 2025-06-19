<?php
require 'conexao.php';

$nome_completo = $_POST['nome_completo'];
$nome_user = $_POST['nome_user'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$data_nascimento = $_POST['data_nasc'];
$data_nascimento = date('d/m/y', strtotime($data_nascimento));
$data_atual = date('d/m/y');
$idade = $data_atual - $data_nascimento;

$erros = [];

if(empty($nome_user) || empty($email) || empty($senha) || empty($data_nascimento)) {
    echo '<script>
        alert("Preencha todos os campos!");
        window.location.href = "../Layout/cadastro.html";';
}
if (strlen($nome_user) < 3 || strlen($nome_user) > 20) {
    echo '<script>
        alert("Nome de usuário inválido! Deve ter entre 3 e 20 caracteres.");
        window.location.href = "../Layout/cadastro.html";';
}
if(!filter_input($nome_completo, FILTER_SANITIZE_STRING)) {
    echo '<script>
        alert("Nome completo inválido!");
        window.location.href = "../Layout/cadastro.html";';
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo '<script>
        alert("Email inválido!");
        window.location.href = "../Layout/cadastro.html";';
}
if(strlen($senha) < 5 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[a-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
    echo '<script>
        alert("Senha inválida! A senha deve ter pelo menos 5 caracteres, incluindo letras maiúsculas, minúsculas e números.");
        window.location.href = "../Layout/cadastro.html";';

}

if($senha !== $_POST['confirmar_senha']){
    echo '<script>
        alert("As senhas não coincidem!");
        window.location.href = "../Layout/cadastro.html";';
}
if($idade < 18) {
    echo '<script>
        alert("Você deve ter pelo menos 18 anos para se cadastrar.");
        window.location.href = "../Layout/cadastro.html";';
}

