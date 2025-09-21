<?php

if (!isset($_SESSION)) {
    session_start();
}
// Verifica se a sessão está ativa
if (isset($_SESSION['user_id'])) {
    // Destrói a sessão
    session_destroy();

    // Redireciona para a página de login
    header("Location: ../Layout/load.html?message=Logout realizado com sucesso!&action=logout");
    exit();

} else {
    // Se não houver sessão ativa, redireciona para a página de login
    header("Location: ../Layout/load.html?message=Nenhuma sessão ativa!&action=logout");
    exit();
}