<?php
require "protect.php";
require "conexao.php";

$tipo_feed = $_GET['feed'] ?? 'foryou';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$userId = $_SESSION['user_id'];

if ($tipo_feed === 'foryou') {
    $sql = "
    SELECT O.*, u.nome_user, u.user_avatar
    FROM obras O
    JOIN users u ON O.portfolio_id = u.id
    ORDER BY O.data_publicacao DESC
    LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($tipo_feed === 'seguindo') {
    $sql = "
    SELECT O.*, u.nome_user, u.user_avatar
    FROM obras O
    JOIN users u ON O.portfolio_id = u.id
    JOIN seguidores s ON s.seguido_id = u.id
    WHERE s.seguidor_id = ?
    ORDER BY O.data_publicacao DESC
    LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $userId, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo json_encode(["posts" => []]);
    exit;
}

$posts = [];
while ($post = $result->fetch_assoc()) {
    $posts[] = $post;
}
header('Content-Type: application/json');
echo json_encode(["posts" => $posts]);
