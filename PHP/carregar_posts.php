<?php
require "protect.php";
require "conexao.php";

$userId = $_SESSION['user_id'];
$tipo_feed = $_GET['feed'] ?? 'foryou';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = 10; // Quantos posts por carregamento

// Seleciona posts
if ($tipo_feed === 'foryou') {
    $sql = "SELECT O.*, u.nome_user, u.user_avatar, u.nome_completo
            FROM obras O
            JOIN users u ON O.portfolio_id = u.id
            ORDER BY O.data_publicacao DESC
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $limit);
} elseif ($tipo_feed === 'seguindo') {
    $sql = "SELECT O.*, u.nome_user, u.user_avatar, u.nome_completo
            FROM obras O
            JOIN users u ON O.portfolio_id = u.id
            JOIN seguidores s ON s.seguido_id = u.id
            WHERE s.seguidor_id = ?
            ORDER BY O.data_publicacao DESC
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $userId, $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();
$posts = [];

while ($post = $result->fetch_assoc()) {
    // Verifica se o usuário curtiu
    $stmt2 = $conn->prepare("SELECT 1 FROM curtidas WHERE usuario_id = ? AND obra_id = ?");
    $stmt2->bind_param("ii", $userId, $post['id']);
    $stmt2->execute();
    $curtido = $stmt2->get_result()->num_rows > 0;
    
    // Verifica se o usuário repostou
    $stmt3 = $conn->prepare("SELECT 1 FROM reposts WHERE user_id = ? AND original_post_id = ?");
    $stmt3->bind_param("ii", $userId, $post['id']);
    $stmt3->execute();
    $repostado = $stmt3->get_result()->num_rows > 0;
    
    $post['curtido'] = $curtido;
    $post['repostado'] = $repostado;
    $posts[] = $post;
}

header('Content-Type: application/json');
echo json_encode($posts);
