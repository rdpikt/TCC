<?php
  if(!isset($_SESSION)) {
      session_start();
  }
  if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_name']) || !isset($_SESSION['user_email'])) {
      die("Você não tem permissão para acessar esta página. <p><a href='..\Layout\login.html'>Faça login</a></p>");
  }
?>