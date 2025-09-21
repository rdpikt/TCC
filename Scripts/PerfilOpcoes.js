
const btnsCategoria = document.querySelectorAll('.perfil-categorias ul li');

console.log(btnsCategoria)
btnsCategoria.forEach(btn => {
  
  btn.addEventListener('click', () => {
    btnsCategoria.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.getAttribute('data-cat');
    const items = listaCategorias.querySelectorAll('li');
    items.forEach(item => {
      if(cat === 'all' || item.getAttribute('data-cat') === cat) {
        item.style.display = 'flex';
        item.classList.add('fade-in');
      } else {
        item.style.display = 'none';
        item.classList.remove('fade-in');
      }
    });
  });
});
// Animação fade-in
const style = document.createElement('style');
style.innerHTML = `.fade-in { animation: fadeIn 0.7s; } @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }`;
document.head.appendChild(style);
