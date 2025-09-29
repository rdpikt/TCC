<?php
if (!isset($_SESSION)) {
    session_start();
}

// Verifique se a sessão existe e se a requisição é para uma API/AJAX
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_name']) ||
    !isset($_SESSION['user_email'])
) {
    // Se a requisição for AJAX, retorne um JSON de erro
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Sessão expirada. Faça login novamente.', 'redirect' => '..\Layout\login.html']);
        exit; // Interrompe o script
    } else {
        // Se não for uma requisição AJAX, redirecione
        header('Location: ..\Layout\login.html');
        exit;
    }
}
?>