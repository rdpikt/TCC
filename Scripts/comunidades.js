document.addEventListener('DOMContentLoaded', () => {
    
    // Usa delegação de eventos no corpo do documento para capturar cliques
    document.body.addEventListener('click', async (e) => {
        // Verifica se o elemento clicado é um botão de entrar ou sair
        if (e.target.matches('.Entrar-Comunidade, .Sair-Comunidade')) {
            e.preventDefault(); // Previne qualquer comportamento padrão do botão

            const button = e.target;
            const comunidadeId = button.dataset.comunidadeId;
            const action = button.classList.contains('Entrar-Comunidade') ? 'entrar':'sair';
            console.log(e)

          

            // Desabilita o botão para prevenir cliques múltiplos
            button.disabled = true;

            const formData = new FormData();
            formData.append('comunidade_id', comunidadeId);
            formData.append('action', action);

            try {
                const response = await fetch('gerenciar_membro_comunidade.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`Erro de rede: ${response.statusText}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Atualiza a aparência e o estado do botão
                    if (action === 'entrar') {
                        button.textContent = 'Sair';
                        button.classList.remove('Entrar-Comunidade');

                          console.log(comunidadeId)
                        button.classList.add('Sair-Comunidade');
                        window.location = `sobre-comunidade.php?id=${comunidadeId}`;
                    } else {
                        button.textContent = 'Entrar';
                        button.classList.remove('Sair-Comunidade');
                        button.classList.add('Entrar-Comunidade');
                    }
                } else {
                    alert(data.message || 'Ocorreu um erro inesperado.');
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                alert('Erro de conexão. Verifique o console para mais detalhes.');
            } finally {
                button.disabled = false;
            }
        }
    });
    //redireciona caso o usaurio clique no card
    
});
