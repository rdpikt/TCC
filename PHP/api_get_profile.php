<?php
require "conexao.php";

header("Content-Type: application/json");

if (!isset($_GET['user_id'])) {
    echo json_encode(["error" => "User ID missing"]);
    exit;
}

$user_id = intval($_GET['user_id']);

// --- Dados básicos ---
$sql = "SELECT id, nome_user, nome_completo, user_avatar, user_tag, bio
        FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

// --- Seguidores e Seguindo ---
$sql = "SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user['seguindo'] = $stmt->get_result()->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user['seguidores'] = $stmt->get_result()->fetch_assoc()['total'];

// --- POSTS DO USUÁRIO ---
$sql = "SELECT id, arquivo_url, titulo, descricao
        FROM obras
        WHERE portfolio_id = ?
        ORDER BY data_publicacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$user['posts'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($user);
