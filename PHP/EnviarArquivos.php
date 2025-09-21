<?php
require "conexao.php";
require "protect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  //recebe os dados enviados
  $UserID = $_SESSION['user_id'];
  $TituloPost = (isset($_POST['titulo'])) ? $_POST['titulo'] : '';
  $DescricaoPost = (isset($_POST['descricao'])) ? $_POST['titulo'] : '';
  $ImagemUrl = (isset($_POST['arquivo'])) ? $_POST['arquivo'] : '';
  $tipo_obra = (isset($_POST['tipo_obra'])) ? $_POST['tipo_obra'] : '';
  $tipos_validos = ['Áudio', 'Visual', 'ÁudioVisual'];
  if (!in_array($tipo_obra, $tipos_validos)) {
    echo "Tipo de obra inválido.";
    exit;
  }


  //verificações
  $erros = [];


  //
  if (isset($_FILES['arquivo']) && $_FILES['arquivo']['size'] > 0) {
    $extensoes_aceitas = array('png', 'jpeg', 'jpg');

    $aux = explode('.', $_FILES['arquivo']['name']);
    $extensao = end($aux);

    //validação de extensao aceita
    if (array_search($extensao, $extensoes_aceitas) === false):
      echo "<h1>Extensão Inválida</h1>";
      exit;
    endif;


    if (is_uploaded_file($_FILES['arquivo']['tmp_name'])):
      if (!file_exists('../images/uploads')) {
        mkdir('../images/uploads');
      }

      $nome_foto = date('dmYs') . '_' . $_FILES['arquivo']['name'];

      if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], '../images/uploads/' . $nome_foto)) {
        echo "houve um erro ao gravar arquivo na pasta";
      }

    endif;

  }

  $sql = "INSERT INTO obras (portfolio_id,tipo_obra,titulo, descricao, arquivo, tipo_imagem) VALUES (?,?,?,?,?,?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('isssss', $UserID, $tipo_obra, $TituloPost, $DescricaoPost, $nome_foto, $extensao);
  $result = $stmt->execute();



  if ($result) {
    echo "<div class='alert alert-sucess' role='alert'>Conteúdo publicado com sucesso</div>";
  } else {
    echo "<div class='alert alert-danger' role='alert'>Erro ao publicar</div>";
  }
  echo "<meta http-equiv=refresh content='3;URL=UsuarioLogado.php'>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Postar Imagem</title>
</head>

<body>
  <form action="Enviararquivos.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="titulo">
    <textarea rows="4" name="descricao"></textarea>
    <label for="image-postCriar">Escolha uma imagem(opcional)</label>
    <input type="file" name="arquivo" id="image-postCriar" accept="image/png, image/jpg, image/jpeg">
    <input type="radio" name="tipo_obra" id="" value="Áudio" checked>
    <label for="desenho">Áudio</label>
    <input type="radio" name="tipo_obra" id="" value="Visual">
    <label for="pintura">Visual</label>
    <input type="radio" name="tipo_obra" id="" value="ÁudioVisual">
    <label for="escultura">ÁudioVisual</label>
    <input type="submit" value="Postar">
  </form>
</body>

</html>