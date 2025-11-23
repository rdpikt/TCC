<?php
// Pega o nome do arquivo da página atual E CONVERTE PARA MINÚSCULAS
$current_page = strtolower(basename($_SERVER['SCRIPT_NAME']));
$feed = $_GET['feed'] ?? 'foryou'; // Pega o tipo de feed
?>

<nav class="nav-side" id="menu">
    <h1 class="logotipo">
      <a href="UsuarioLogado.php?feed=foryou" class="logo-link">
        <span class="marca">Harp</span><span class="nome">Hub</span>
      </a>
    </h1>

    <ul class="pages">
      
      <li id="nav-inicio"><a 
        class="<?= ($current_page == 'usuariologado.php' && $feed == 'foryou') ? 'selecionado' : '' ?>" 
        href="UsuarioLogado.php?feed=foryou"><i class="fi fi-br-home"></i>Página Inicial</a>
      </li>
      
      <li id="nav-explorar"><a 
        class="<?= ($current_page == 'explorar.php') ? 'selecionado' : '' ?>" 
        href="explorar.php"><i class="fi fi-br-search"></i>Explorar</a>
      </li>
      
      <li id="nav-seguindo"><a 
        class="<?= ($current_page == 'usuariologado.php' && $feed == 'seguindo') ? 'selecionado' : '' ?>" 
        href="UsuarioLogado.php?feed=seguindo"><i class="fi fi-br-user-add"></i>Seguindo</a>
      </li>
      
      <li id="nav-galeria"><a 
        class="<?= ($current_page == 'galeria.php') ? 'selecionado' : '' ?>" 
        href="Galeria.php"><i class="fi fi-br-picture"></i>Galeria</a>
      </li>
      
      <li id="nav-criar"><a 
        class="<?= ($current_page == 'enviararquivos.php') ? 'selecionado' : '' ?>" 
        href="EnviarArquivos.php"><i class="fi fi-br-pencil"></i>Criar post</a>
      </li>
      
      <li id="nav-comunidades"><a 
        class="<?= ($current_page == 'explorar_comunidades.php') ? 'selecionado' : '' ?>" 
        href="explorar_comunidades.php"><i class="fi fi-br-users"></i>Comunidades</a>
      </li>
      
      <li id="nav-perfil"><a 
        class="<?= ($current_page == 'perfil.php') ? 'selecionado' : '' ?>" 
        href="perfil.php"><i class="fi fi-br-portrait"></i>Perfil</a>
      </li>
    </ul>

    <div class="tools">
      <ul>
        <li id="nav-config"><a 
          class="<?= ($current_page == 'config.php') ? 'selecionado' : '' ?>" 
          href="config.php"><i class="fi fi-rr-settings"></i>Configurações</a>
        </li>
        <li id="nav-ajuda"><a 
          class="<?= ($current_page == 'ajuda.php') ? 'selecionado' : '' ?>" 
          href="ajuda.php"><i class="fi fi-rr-info"></i>Ajuda</a>
        </li>
      </ul>
    </div>
</nav>