<?php
require "protect.php"; // Garante que o usuário está logado e inicia a sessão
require "conexao.php"; // Conexão com o banco de dados

// Define o cabeçalho para retornar JSON
header('Content-Type: application/json');

$userId = $_SESSION['user_id']; // <-- Confirme se é 'user_id' e não apenas 'id'
$sql_membro = "SELECT id FROM comunidade_membros WHERE comunidade_id = ? AND usuario_id = ? AND cargo = 'membros'";

$response = ['success' => false, 'message' => ''];

// Verifica se os dados necessários foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comunidade_id']) && isset($_POST['action'])) {
    
    // Obter dados
    $userId = $_SESSION['user_id']; // ID do usuário logado
    $comunidadeId = (int)$_POST['comunidade_id'];
    $action = $_POST['action']; // 'entrar' ou 'sair'

    if ($action === 'entrar') {
        // Ação: Adicionar membro (garante que não haja duplicidade usando INSERT IGNORE)
        $sql = "INSERT IGNORE INTO comunidade_membros (comunidade_id, usuario_id, cargo) VALUES (?, ?, 'membro')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $comunidadeId, $userId);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Você entrou na comunidade!';
        } else {
            $response['message'] = 'Erro ao entrar na comunidade.';
        }
        
    } elseif ($action === 'sair') {
        // Ação: Remover membro
        $sql = "DELETE FROM comunidade_membros WHERE comunidade_id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $comunidadeId, $userId);
        
        if ($stmt->execute()) {
             $response['success'] = true;
             $response['message'] = 'Você saiu da comunidade.';
        } else {
            $response['message'] = 'Erro ao sair da comunidade.';
        }

    } else {
        $response['message'] = 'Ação inválida.';
    }
} else {
    $response['message'] = 'Dados da requisição ausentes ou método incorreto.';
}

echo json_encode($response);
$conn->close();
?>