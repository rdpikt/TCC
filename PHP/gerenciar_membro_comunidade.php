<?php
require "protect.php";
require "conexao.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['comunidade_id']) || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}

$comunidade_id = intval($_POST['comunidade_id']);
$action = $_POST['action'];
$user_id = $_SESSION['user_id'];
$response = ['success' => false];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($action === 'entrar') {
        $stmt_check = $conn->prepare("SELECT id FROM comunidade_membros WHERE comunidade_id = ? AND usuario_id = ?");
        $stmt_check->bind_param("ii", $comunidade_id, $user_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $response['message'] = 'Você já é membro desta comunidade.';
        } else {
            $stmt = $conn->prepare("INSERT INTO comunidade_membros (comunidade_id, usuario_id, cargo) VALUES (?, ?, 'membro')");
            $stmt->bind_param("ii", $comunidade_id, $user_id);
            if ($stmt->execute()) {
                $response['success'] = true;
            }
        }
    } elseif ($action === 'sair') {
        $stmt_check = $conn->prepare("SELECT cargo FROM comunidade_membros WHERE comunidade_id = ? AND usuario_id = ?");
        $stmt_check->bind_param("ii", $comunidade_id, $user_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($row['cargo'] === 'dono') {
                $response['message'] = 'O dono não pode sair da comunidade que criou.';
            } else {
                $stmt = $conn->prepare("DELETE FROM comunidade_membros WHERE comunidade_id = ? AND usuario_id = ?");
                $stmt->bind_param("ii", $comunidade_id, $user_id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $response['success'] = true;
                }
            }
        } else {
             $response['message'] = 'Você não é membro desta comunidade.';
        }
    }
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    $response['message'] = 'Erro de banco de dados: ' . $e->getMessage();
}

echo json_encode($response);
exit;