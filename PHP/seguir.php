<?php
session_start();
// require "protect.php"; // Se o protect já inicia sessão, ok. Se não, mantenha o session_start acima.
require "conexao.php";

header('Content-Type: application/json');

// CORREÇÃO 1: Padronizar o nome da sessão. 
// Verifique no seu login.php qual variável você salva. 
// Geralmente é 'id' ou 'user_id'. Aqui estou verificando as duas para garantir.
if (isset($_SESSION['id'])) {
    $seguidor_id = $_SESSION['id'];
} elseif (isset($_SESSION['user_id'])) {
    $seguidor_id = $_SESSION['user_id'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não logado.']);
    exit();
}

// CORREÇÃO 2: Verificação do POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['seguido_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Requisição inválida (Faltando seguido_id).']);
    exit();
}

$seguido_id = (int)$_POST['seguido_id'];

if ($seguidor_id === $seguido_id) {
    echo json_encode(['status' => 'error', 'message' => 'Você não pode seguir a si mesmo.']);
    exit();
}

// Verifica se já segue
$stmt = $conn->prepare("SELECT id FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
$stmt->bind_param("ii", $seguidor_id, $seguido_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // UNFOLLOW
    $delete = $conn->prepare("DELETE FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
    $delete->bind_param("ii", $seguidor_id, $seguido_id);
    
    if($delete->execute()){
        echo json_encode(['status' => 'success', 'action' => 'unfollowed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao deixar de seguir.']);
    }
    exit();

} else {
    // FOLLOW
    $insert = $conn->prepare("INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
    $insert->bind_param("ii", $seguidor_id, $seguido_id);
    
    if($insert->execute()){
        echo json_encode(['status' => 'success', 'action' => 'followed']);
    } else {
         echo json_encode(['status' => 'error', 'message' => 'Erro ao seguir.']);
    }
    exit();
}
?>