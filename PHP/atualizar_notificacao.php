<?php
require "protect.php";
require "conexao.php";

header('Content-Type: application/json');

// Verifica se os dados necessários foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['action'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Dados insuficientes ou método inválido.']);
    exit;
}

$notificationId = intval($_POST['id']);
$action = $_POST['action'];
$userId = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Ação inválida.'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($action === 'mark_read') {
        // Atualiza o status da notificação para 'lida' (lida = 1)
        $stmt = $conn->prepare("UPDATE notificacoes SET lida = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $response = ['success' => true];
        } else {
            $response['message'] = 'Erro ao marcar como lida ou notificação não encontrada.';
        }

    } elseif ($action === 'delete') {
        // Deleta a notificação do banco de dados
        $stmt = $conn->prepare("DELETE FROM notificacoes WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $response = ['success' => true];
        } else {
            $response['message'] = 'Erro ao deletar ou notificação não encontrada.';
        }
    }
} catch (mysqli_sql_exception $e) {
    http_response_code(500); // Internal Server Error
    $response['message'] = "Erro de SQL: " . $e->getMessage();
}

echo json_encode($response);
exit;