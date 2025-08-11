const Menu = document.querySelector('#menu');
const BtnMenu = document.querySelector('#BtnMenu')

BtnMenu.addEventListener('click', ()=>{
  Menu.classList.toggle('ExibirMenu');
  document.querySelector('#NavigationUser').classList.toggle('navmove')
})

