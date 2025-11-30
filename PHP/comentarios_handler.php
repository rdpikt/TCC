<?php
require "protect.php";
require "conexao.php";

header('Content-Type: application/json');

// Ativar relatório de erros para depuração
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$response = ['success' => false, 'message' => 'Ação inválida.'];
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$userId = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'fetch':
            $postId = intval($_GET['post_id'] ?? 0);
            if ($postId === 0) {
                throw new Exception('ID do post inválido.');
            }

            $sql = "SELECT c.id, c.content, c.created_at, c.user_id, u.nome_user, u.user_avatar 
                    FROM comentarios c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.post_id = ? 
                    ORDER BY c.created_at ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $postId);
            $stmt->execute();
            $result = $stmt->get_result();
            $comments = $result->fetch_all(MYSQLI_ASSOC);

            $response = ['success' => true, 'comments' => $comments];
            break;

        case 'create':
            $postId = intval($_POST['post_id'] ?? 0);
            $content = trim($_POST['content'] ?? '');
            $parentId = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

            if ($postId === 0 || empty($content)) {
                throw new Exception('Dados do comentário insuficientes.');
            }

            $sql = "INSERT INTO comentarios (post_id, user_id, parent_comment_id, content) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $postId, $userId, $parentId, $content);
            $stmt->execute();
            $newCommentId = $stmt->insert_id;

            // --- Lógica de Notificação ---
            $stmt_author = $conn->prepare("SELECT portfolio_id FROM obras WHERE id = ?");
            $stmt_author->bind_param("i", $postId);
            $stmt_author->execute();
            $author_result = $stmt_author->get_result();
            if ($author_row = $author_result->fetch_assoc()) {
                $authorId = $author_row['portfolio_id'];
                if ($authorId != $userId) {
                    $tipo_notificacao = 'comentario';
                    $stmt_notif = $conn->prepare(
                        "INSERT INTO notificacoes (user_id, remetente_id, tipo, link_id) VALUES (?, ?, ?, ?)"
                    );
                    $stmt_notif->bind_param("iisi", $authorId, $userId, $tipo_notificacao, $postId);
                    $stmt_notif->execute();
                }
            }

            // Retorna o comentário recém-criado para ser adicionado dinamicamente
            $sql_new = "SELECT c.id, c.content, c.created_at, c.user_id, u.nome_user, u.user_avatar 
                        FROM comentarios c 
                        JOIN users u ON c.user_id = u.id 
                        WHERE c.id = ?";
            $stmt_new = $conn->prepare($sql_new);
            $stmt_new->bind_param("i", $newCommentId);
            $stmt_new->execute();
            $newComment = $stmt_new->get_result()->fetch_assoc();

            $response = ['success' => true, 'comment' => $newComment];
            break;

        case 'update':
            $commentId = intval($_POST['comment_id'] ?? 0);
            $content = trim($_POST['content'] ?? '');

            if ($commentId === 0 || empty($content)) {
                throw new Exception('Dados insuficientes para atualizar.');
            }

            // Verifica se o usuário é o dono do comentário
            $stmt_owner = $conn->prepare("SELECT user_id FROM comentarios WHERE id = ?");
            $stmt_owner->bind_param("i", $commentId);
            $stmt_owner->execute();
            $owner_result = $stmt_owner->get_result();
            if ($owner_row = $owner_result->fetch_assoc()) {
                if ($owner_row['user_id'] != $userId) {
                    throw new Exception('Você não tem permissão para editar este comentário.');
                }
            } else {
                throw new Exception('Comentário não encontrado.');
            }

            $sql = "UPDATE comentarios SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $content, $commentId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response = ['success' => true, 'message' => 'Comentário atualizado.'];
            } else {
                $response['message'] = 'Nenhuma alteração feita.';
            }
            break;

        case 'delete':
            $commentId = intval($_POST['comment_id'] ?? 0);

            if ($commentId === 0) {
                throw new Exception('ID do comentário inválido.');
            }

            // Verifica se o usuário é o dono do comentário
            $stmt_owner = $conn->prepare("SELECT user_id FROM comentarios WHERE id = ?");
            $stmt_owner->bind_param("i", $commentId);
            $stmt_owner->execute();
            $owner_result = $stmt_owner->get_result();
            if ($owner_row = $owner_result->fetch_assoc()) {
                if ($owner_row['user_id'] != $userId) {
                    throw new Exception('Você não tem permissão para excluir este comentário.');
                }
            } else {
                throw new Exception('Comentário não encontrado.');
            }

            $sql = "DELETE FROM comentarios WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $commentId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response = ['success' => true, 'message' => 'Comentário excluído.'];
            } else {
                throw new Exception('Erro ao excluir o comentário.');
            }
            break;
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
