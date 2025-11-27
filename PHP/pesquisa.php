<?php

header("Content-Type: application/json");
require_once 'protect.php';
require_once 'conexao.php';


$query = $_GET['query'] ?? '';
$type = $_GET['type'] ?? '';

if(empty($query)){
  echo json_encode([]);
  exit();
}
$searchQuery = "%". $query ."%";
$sql = "";
$results = [];


switch  ($type){
  case "usuarios":
  $sql = "SELECT * from users WHERE nome_user LIKE ? or nome_completo LIKE ?";
  $stmt = $conn->prepare($sql);

  $stmt->bind_param("ss", $searchQuery, $searchQuery);
  break;

  case "comunidades":
    $sql = "SELECT * FROM comunidades WHERE nome LIKE ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("s", $searchQuery);
    break;

    case "conteudo":
      $sql = "SELECT * FROM tags WHERE nome_tag LIKE ?";
      $stmt = $conn->prepare($sql);
      $stmt-> bind_param("s", $searchQuery);
      break;

    default:
    echo json_encode(["error"=> "Não foi possivel achar algo relacionado :("]);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
  $results[] = $row;
}

$stmt->close();

echo json_encode($results);
