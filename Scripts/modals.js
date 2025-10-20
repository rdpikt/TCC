document.addEventListener('DOMContentLoaded', () => {
    const searchBar = document.getElementById('search-bar');
    const suggestionsBox = document.getElementById('suggestions-box');

    // Crie os botões e a lista de sugestões dinamicamente
    const optionsContainer = document.createElement('div');
    optionsContainer.className = 'search-options';
    optionsContainer.innerHTML = `
        <button class="btn-option active" data-type="usuarios">Usuários</button>
        <button class="btn-option" data-type="comunidades">Comunidades</button>
        <button class="btn-option" data-type="conteudo">Conteúdo</button>
    `;

    const suggestionsList = document.createElement('div');
    suggestionsList.id = 'suggestions-list';

    // Adicione os novos elementos à caixa de sugestões
    suggestionsBox.appendChild(optionsContainer);
    suggestionsBox.appendChild(suggestionsList);

    const searchOptions = suggestionsBox.querySelectorAll('.btn-option');
    let currentSearchType = 'usuarios';

    // --- Lógica para trocar o tipo de busca ---
    searchOptions.forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation(); // Impede que o clique feche a caixa de sugestões
            searchOptions.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            currentSearchType = button.dataset.type;
            fetchSuggestions();
        });
    });

    // --- Lógica para busca em tempo real ---
    searchBar.addEventListener('input', fetchSuggestions);
    searchBar.addEventListener('focus', fetchSuggestions); // Mostra as opções ao focar

    // --- Lógica para esconder sugestões ao clicar fora ---
    document.addEventListener('click', (e) => {
        if (!searchBar.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });


    // --- Função principal que busca e mostra as sugestões ---
    async function fetchSuggestions() {
        const query = searchBar.value.trim();
        suggestionsBox.style.display = 'block'; // Sempre exibe a caixa quando a função é chamada

        // Se a busca estiver vazia, limpa apenas a lista de resultados
        if (query.length === 0) {
            suggestionsList.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`../PHP/pesquisa.php?query=${encodeURIComponent(query)}&type=${currentSearchType}`);
            const suggestions = await response.json();

            suggestionsList.innerHTML = ''; // Limpa apenas a lista

            if (suggestions.error) {
                suggestionsList.innerHTML = `<div class="suggestion-item">${suggestions.error}</div>`;
            } else if (suggestions.length === 0) {
                suggestionsList.innerHTML = '<div class="suggestion-item">Nenhum resultado encontrado.</div>';
            } else {
                suggestions.forEach(item => {
                    const div = document.createElement('div');
                    div.classList.add('suggestion-item');
                    
                    let content = '';
                    let link = '#';

                    switch(currentSearchType) {
                        case 'usuarios':
                            link = `perfil.php?id=${item.id}`; 
                            content = `
                                <img src="../images/avatares/Users/${item.user_avatar || 'profile.png'}" alt="Foto de perfil">
                                <div>
                                    <strong>${item.nome_completo}</strong>
                                    <small>@${item.nome_user}</small>
                                </div>
                            `;
                            break;
                        case 'comunidades':
                            link = `comunidade.php?id=${item.id}`;
                            content = `
                                <img src="../images/avatares/Comunidades/${item.imagem || 'profile.png'}" alt="Ícone da comunidade">
                                <div>
                                    <strong>${item.nome}</strong>
                                </div>
                            `;
                            break;
                        case 'conteudo':
                             link = `pesquisa.php?categoria=${encodeURIComponent(item.nome_tag)}`;
                             content = `<strong>#${item.nome_tag}</strong>`;
                             break;
                    }

                    div.innerHTML = content;
                    div.addEventListener('click', () => {
                        window.location.href = link;
                    });
                    
                    suggestionsList.appendChild(div);
                });
            }
        } catch (error) {
            console.error('Erro ao buscar sugestões:', error);
            suggestionsList.innerHTML = '<div class="suggestion-item">Erro ao carregar resultados.</div>';
        }
    }
});
//modal-perfil
const userSpan = document.querySelector('.nav-user li:nth-child(2) span img');
const modalPerfil = document.querySelector('.modal-perfil');
userSpan.addEventListener('click', () => {
  modalPerfil.classList.toggle('active');
});
window.addEventListener('click', (e) => {
  if (!modalPerfil.contains(e.target) && e.target !== userSpan) {
    modalPerfil.classList.remove('active');
  }
});


window.addEventListener('DOMContentLoaded', () => {
    const sharedPostId = document.body.dataset.sharedPostId;
    if (sharedPostId) {
        const postToOpen = document.querySelector(`.posts[data-post-id="${sharedPostId}"]`);
        if (postToOpen) {
            postToOpen.click();
        }
    }
});
