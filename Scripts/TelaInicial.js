const btnOptions = document.querySelectorAll('.btn-options');
const modalOptions = document.querySelectorAll('.modal-options');

//barra de pesquisa
const searchBar = document.getElementById('search-bar');
searchBar.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') {
    const query = searchBar.value.trim();
    if (query) {
      window.location.href = `../PHP/Pesquisa.php?query=${encodeURIComponent(query)}`;
    }
  }
});
  
//modal-options
  btnOptions.forEach((btn, index) => {
    btn.addEventListener('click', () => {
      modalOptions.forEach((modal, i) => {
        if(i === index){
          modal.classList.toggle('active');
        }
      })
        
    });
  });
  window.addEventListener('click', (e) => {
    modalOptions.forEach((modal) => {
        if (!modal.contains(e.target) && !Array.from(btnOptions).some(btn => btn === e.target)) {
            modal.classList.remove('active');
        }
    });
  });
