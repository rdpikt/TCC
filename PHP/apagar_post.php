<?php

session_start();
require_once 'conexao.php';
require_once 'protect.php';

header('Content-Type: application/json');

// Verifica autenticação
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

$userId = $_SESSION['user_id']; // <-- CORRIGIDO

// Verifica método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

// Captura o ID do post
$postId = isset($_POST['postId']) ? (int)$_POST['postId'] : 0;

if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do post inválido.']);
    exit;
}

// Verifica se o post pertence ao usuário
$stmt = $conn->prepare("SELECT portfolio_id FROM obras WHERE id = ?");
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Post não encontrado.']);
    exit;
}

$post = $result->fetch_assoc();

if ($post['portfolio_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Você não tem permissão para apagar este post.']);
    exit;
}

// Apaga o post
$stmt = $conn->prepare("DELETE FROM obras WHERE id = ?");
$stmt->bind_param("i", $postId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post apagado com sucesso!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao apagar o post.']);
}

$stmt->close();
$conn->close();
