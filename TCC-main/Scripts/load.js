const urlParams = new URLSearchParams(window.location.search);
const message = urlParams.get('message');
const action = urlParams.get('action'); // Obtém o parâmetro 'action'

if (message) {
    const text = document.querySelector("h1");
    text.innerHTML = message;

    // Define estilos diferentes para cadastro e login
    if (action === 'cadastro') {
        text.style.color = "green";
        text.style.fontSize = "2rem";
        setTimeout(() => {
          window.location.href = "../Layout/login.html"; // Redireciona para UserPerfil.php
      }, 2000);
    } else if (action === 'login') {
        text.style.color = "blue";
        text.style.fontSize = "2rem";
        setTimeout(() => {
          window.location.href = "../PHP/UsuarioLogado.php"; // Redireciona para UserPerfil.php
      }, 2000);
    }else if(action === 'logout'){
        text.style.color = "red";
        text.style.fontSize = "2rem";
        setTimeout(() => {
          window.location.href = "../Layout/index.html"; // Redireciona para UserPerfil.php
      }, 2000);
    }
}

// Redireciona após 2 segundos
