<?php
$servername = "localhost";
$username = "root";
$password = ""; // Senha padrão do XAMPP é vazia
$dbname = "banco";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}