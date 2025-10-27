let loginButton = document.querySelector(".loginButton");
let cadastroButton = document.querySelector(".cadastroButton");
let card = document.querySelector(".card");

const urlparams = new URLSearchParams(window.location.search);
const page = urlparams.get('action');
switch(page){
    case 'login':
        card.classList.remove("cadastroActive");
        card.classList.add("loginActive");
        break;
    case 'cadastro':
        card.classList.remove("loginActive");
        card.classList.add("cadastroActive");
        break;
    default:
        card.classList.remove("loginActive");
        card.classList.remove("cadastroActive");
        break;
}
loginButton.onclick = () => {
    card.classList.remove("cadastroActive");
    card.classList.add("loginActive");
}

cadastroButton.onclick = () => {
    card.classList.remove("loginActive");
    card.classList.add("cadastroActive");
}
