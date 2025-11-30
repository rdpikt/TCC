document.addEventListener('DOMContentLoaded', () => {

    // Usa delegação de eventos no corpo do documento para capturar cliques
    document.body.addEventListener('click', async (e) => {
        // Verifica se o elemento clicado é um botão de entrar ou sair
        if (e.target.matches('.btn-entrar, .btn-sair')) {
            e.preventDefault(); // Previne qualquer comportamento padrão do botão

            const button = e.target;
            const comunidadeId = button.dataset.comunidadeId;
            const action = button.classList.contains('btn-entrar') ? 'entrar' : 'sair';
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
                    const isDetailPage = window.location.href.includes('sobre-comunidade.php');

                    // Atualiza a aparência e o estado do botão
                    if (action === 'entrar') {
                        // Redireciona/recarrega. Adicionamos um parâmetro de cache-busting (timestamp)
                        // para garantir que o navegador não use uma versão em cache da página,
                        // forçando o PHP a rodar e a ler o novo estado do membro no DB.
                        const urlComunidade = `sobre-comunidade.php?id=${comunidadeId}&t=${Date.now()}`;
                        window.location.replace(urlComunidade);

                    } else { // action === 'sair'
                        if (isDetailPage) {
                            // Se estiver na página de detalhes e sair, recarrega para atualizar 
                            // a contagem de membros e o acesso ao conteúdo.
                            window.location.reload();
                        } else {
                            // Se estiver na página de listagem, apenas atualiza o botão localmente.
                            button.textContent = 'Entrar';
                            button.classList.remove('btn-sair');
                            button.classList.add('btn-entrar');
                        }
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
