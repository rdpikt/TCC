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
    // --- lógica para esconder sugestões ao escolher uma sugestão ---
    suggestionsBox.addEventListener('click', (e) => {
        if (e.target.classList.contains('suggestion-item') || e.target.closest('.suggestion-item')) {
            suggestionsBox.style.display = 'none';
        }
    });


    // --- Função principal que busca e mostra as sugestões ---
    async function fetchSuggestions() {
        const query = searchBar.value.trim();
        suggestionsBox.style.display = 'block';

        if (query.length === 0) {
            suggestionsList.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`../PHP/pesquisa.php?query=${encodeURIComponent(query)}&type=${currentSearchType}`);
            const suggestions = await response.json();

            suggestionsList.innerHTML = '';
            // Adiciona a classe para estilização e transforma em UL
            suggestionsList.className = 'sugestoes'; 

            if (suggestions.error) {
                suggestionsList.innerHTML = `<li class="suggestion-item" style="justify-content: center;">${suggestions.error}</li>`;
            } else if (suggestions.length === 0) {
                suggestionsList.innerHTML = '<li class="suggestion-item" style="justify-content: center;">Nenhum resultado encontrado.</li>';
            } else {
                suggestions.forEach(item => {
                    // Altera de <div> para <li>
                    const li = document.createElement('li');
                    // Mantém a classe para cliques, mas o estilo principal virá do <li> e da ul.sugestoes
                    li.classList.add('suggestion-item'); 
                    
                    let content = '';
                    let link = '#';

                    switch(currentSearchType) {
                        case 'usuarios':
                            link = `api_get_profile.php?id=${item.id}`; 
                            content = `
                                <img src="../images/avatares/Users/${item.user_avatar || 'profile.png'}" alt="Foto de perfil">
                                <div>
                                    <strong>${item.nome_completo}</strong>
                                    <span style="color:#71767b">@${item.nome_user}</span>
                                </div>
                                <button class="view-button">Ver</button>
                            `;
                            break;
                        case 'comunidades':
                            link = `sobre-comunidade.php?id=${item.id}`;
                            content = `
                                <img src="../images/avatares/Comunidades/${item.imagem || 'profile.png'}" alt="Ícone da comunidade">
                                <div>
                                    <strong>${item.nome}</strong>
                                </div>
                                <button class="view-button">Ver</button>
                            `;
                            break;
                        case 'conteudo':
                             link = `pesquisa.php?categoria=${encodeURIComponent(item.nome_tag)}`;
                             content = `
                                <div>
                                    <strong>#${item.nome_tag}</strong>
                                </div>
                                <button class="view-button">Ver</button>
                             `;
                             break;
                    }

                    li.innerHTML = content;
                    li.addEventListener('click', () => {
                        // Se o tipo for 'usuarios', abre o modal de perfil. Caso contrário, redireciona.
                        if (currentSearchType === 'usuarios') {
                            // Chama as funções globais expostas por TelaInicial.js
                            abrirModalPerfil();
                            carregarPerfilOutroUsuario(item.id);
                        } else {
                            window.location.href = link;
                            
                        }
                    });
                    
                    suggestionsList.appendChild(li);
                });
            }
        } catch (error) {
            console.error('Erro ao buscar sugestões:', error);
            suggestionsList.innerHTML = '<li class="suggestion-item" style="justify-content: center;">Erro ao carregar resultados.</li>';
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

document.addEventListener('DOMContentLoaded', () => {
    const menuDotsButton = document.getElementById('menu-dots');
    const modalReticencias = document.querySelector('.modal-reticencias');

    if (menuDotsButton && modalReticencias) {
        // Função para mostrar/esconder a modal
        menuDotsButton.addEventListener('click', (event) => {
            event.stopPropagation(); // Impede que o clique se propague para o document
            modalReticencias.style.display = modalReticencias.style.display === 'block' ? 'none' : 'block';
        });

        // Esconde a modal quando clicar em qualquer outro lugar da tela
        document.addEventListener('click', (event) => {
            if (!modalReticencias.contains(event.target) && event.target !== menuDotsButton) {
                modalReticencias.style.display = 'none';
            }
        });
        
        // **Lógica Adicional (Opcional):**
        // Você pode querer esconder o botão "Editar Comunidade" se o usuário não for o administrador/moderador.
        // Isso deve ser tratado com PHP no lado do servidor, mas um ajuste visual seria:
        // const isModerator = /* Variável JS definida por PHP para indicar permissão */;
        // if (!isModerator) {
        //     document.querySelector('.editar-comunidade').parentElement.style.display = 'none';
        // }
    }
});