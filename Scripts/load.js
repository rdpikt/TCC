const urlParams = new URLSearchParams(window.location.search);
const message = urlParams.get('message');
const action = urlParams.get('action'); // Obtém o parâmetro 'action'

if (message) {
    // Define estilos diferentes para cadastro e login
    if (action === 'cadastro') {
        setTimeout(() => {
          window.location.href = "../PHP/UsuarioLogado.php"; // Redireciona para UserPerfil.php
      }, 2000);
    } else if (action === 'login') {
        setTimeout(() => {
          window.location.href = "../PHP/UsuarioLogado.php"; // Redireciona para UserPerfil.php
      }, 2000);
    }else if(action === 'logout'){
        setTimeout(() => {
          window.location.href = "../Layout/index.html"; // Redireciona para UserPerfil.php
      }, 2000);
    }
}

// Redireciona após 2 segundos
