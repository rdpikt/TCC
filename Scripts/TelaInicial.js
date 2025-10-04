const btnOptions = document.querySelectorAll('.btn-options');
const modalOptions = document.querySelectorAll('.modal-options');

//barra de pesquisa
const searchBar = document.getElementById('search-bar');
searchBar.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') {
    const query = searchBar.value.trim();
    if (query) {
      window.location.href = `../PHP/Pesquisa.php?query=${encodeURIComponent(query)}`;
    }response
  }
});
  
