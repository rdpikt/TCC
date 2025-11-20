<?php
session_start();
require "conexao.php";

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Erro desconhecido.'];

// 1. VERIFICAR SE O ID DO USUÁRIO FOI ENVIADO E SE O USUÁRIO ESTÁ LOGADO
if (!isset($_GET['userID']) || !isset($_SESSION['user_id'])) {
    $response['message'] = 'ID do usuário não fornecido ou você não está logado.';
    echo json_encode($response);
    exit;
}

$perfilId = intval($_GET['userID']);
$logadoId = intval($_SESSION['user_id']);

try {
    // 2. BUSCAR DADOS DO USUÁRIO DO PERFIL
    $stmt = $conn->prepare("SELECT id, nome_completo, user_tag, bio, user_avatar FROM users WHERE id = ?");
    $stmt->bind_param("i", $perfilId);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        $response['message'] = 'Usuário não encontrado.';
        echo json_encode($response);
        exit;
    }

    // Renomear campos para corresponder ao que o JavaScript espera
    $usuario['avatar'] = $usuario['user_avatar'];
    $usuario['tipo'] = $usuario['user_tag'];
    unset($usuario['user_avatar'], $usuario['user_tag']);


    // 3. CALCULAR SEGUIDORES E SEGUINDO
    // Quantos o usuário do perfil segue (Seguindo)
    $stmt_seguindo = $conn->prepare("SELECT COUNT(*) as total FROM seguidores WHERE seguidor_id = ?");
    $stmt_seguindo->bind_param("i", $perfilId);
    $stmt_seguindo->execute();
    $usuario['seguindo'] = $stmt_seguindo->get_result()->fetch_assoc()['total'];

    // Quantos seguem o usuário do perfil (Seguidores)
    $stmt_seguidores = $conn->prepare("SELECT COUNT(*) as total FROM seguidores WHERE seguido_id = ?");
    $stmt_seguidores->bind_param("i", $perfilId);
    $stmt_seguidores->execute();
    $usuario['seguidores'] = $stmt_seguidores->get_result()->fetch_assoc()['total'];


    // 4. VERIFICAR SE O USUÁRIO LOGADO JÁ SEGUE O USUÁRIO DO PERFIL
    // Não faz sentido verificar se o usuário segue a si mesmo
    if ($logadoId === $perfilId) {
        $usuario['jaSegue'] = null; // Ou false, dependendo de como quer tratar no front-end
    } else {
        $stmt_ja_segue = $conn->prepare("SELECT id FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        $stmt_ja_segue->bind_param("ii", $logadoId, $perfilId);
        $stmt_ja_segue->execute();
        $usuario['jaSegue'] = $stmt_ja_segue->get_result()->num_rows > 0;
    }


    // 5. BUSCAR OS POSTS (OBRAS) DO USUÁRIO
    $stmt_posts = $conn->prepare("SELECT id, descricao, arquivo_url FROM obras WHERE portfolio_id = ? ORDER BY data_publicacao DESC");
    $stmt_posts->bind_param("i", $perfilId);
    $stmt_posts->execute();
    $result_posts = $stmt_posts->get_result();

    $posts = [];
    while ($post = $result_posts->fetch_assoc()) {
        // Renomear 'arquivo_url' para 'imagemUrl' para corresponder ao JS
        $posts[] = [
            'id' => $post['id'],
            'descricao' => $post['descricao'],
            'imagemUrl' => $post['arquivo_url']
        ];
    }

    // 6. MONTAR A RESPOSTA FINAL
    $response = [
        'success' => true,
        'usuario' => $usuario,
        'posts' => $posts
    ];

} catch (mysqli_sql_exception $e) {
    $response['message'] = "Erro de banco de dados: " . $e->getMessage();
}

echo json_encode($response);
exit;

?>