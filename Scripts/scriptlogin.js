let loginButton = document.querySelector(".loginButton");
let cadastroButton = document.querySelector(".cadastroButton");
let card = document.querySelector(".card");

loginButton.onclick = () => {
    card.classList.remove("cadastroActive");
    card.classList.add("loginActive");
}

cadastroButton.onclick = () => {
    card.classList.remove("loginActive");
    card.classList.add("cadastroActive");
}
