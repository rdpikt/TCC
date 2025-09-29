document.addEventListener('DOMContentLoaded', () => {
    const listaNotificacoes = document.querySelector('.lista-notificacoes');
    const notificacaoExpandida = document.querySelector('.notificacao-expandida');
    const btnFechar = notificacaoExpandida ? notificacaoExpandida.querySelector('.btn-fechar') : null;
    let itemClicado = null; // Guarda a referência do item que foi aberto

    if (!listaNotificacoes || !notificacaoExpandida || !btnFechar) {
        return; // Aborta se os elementos essenciais não existirem na página
    }

    // Função para enviar requisições para o backend
    async function atualizarNotificacao(id, action) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);

        try {
            const response = await fetch('atualizar_notificacao.php', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                throw new Error(`Erro de rede: ${response.status} ${response.statusText}`);
            }
            return await response.json();
        } catch (error) {
            console.error('Erro na requisição:', error);
            // Agora a mensagem de erro será mais específica (ex: "Erro de rede: 404 Not Found")
            return { success: false, message: error.message || 'Erro de conexão.' };
        }
    }

    // Event listener para a lista de notificações (usando delegação de eventos)
    listaNotificacoes.addEventListener('click', async (e) => {
        const item = e.target.closest('.notificacao-item');
        const btnExcluir = e.target.closest('.btn-excluir');

        // Se o botão de excluir foi clicado
        if (btnExcluir) {
            e.stopPropagation(); // Impede que o clique no botão abra o modal
            const id = btnExcluir.dataset.id;
            if (confirm('Tem certeza que deseja excluir esta notificação?')) {
                const resultado = await atualizarNotificacao(id, 'delete');
                if (resultado.success) {
                    const itemParaRemover = document.querySelector(`.notificacao-item[data-id='${id}']`);
                    if (itemParaRemover) itemParaRemover.remove();
                } else {
                    alert('Erro ao excluir a notificação: ' + (resultado.message || 'Tente novamente.'));
                }
            }
            return;
        }

        // Se um item de notificação foi clicado
        if (item) {
            itemClicado = item; // Guarda o item que foi clicado
            const id = item.dataset.id;

            // Preenche o modal com os dados do item
            // Usamos .innerHTML porque o conteúdo pode ter tags como <strong> e <em>
            notificacaoExpandida.querySelector('.mensagem-completa').innerHTML = item.dataset.conteudo;
            notificacaoExpandida.querySelector('.data-expandida').textContent = item.dataset.data;
            
            // Adiciona a prévia do post, se houver
            const previewContainer = notificacaoExpandida.querySelector('.post-preview-container');
            // O atributo data-post-preview contém a tag <img> completa
            previewContainer.innerHTML = item.dataset.postPreview || ''; 

            // Mostra o modal
            notificacaoExpandida.classList.add('expandido');

            // Marca como lida no backend se ainda tiver a classe 'nao-lida'
            if (item.classList.contains('nao-lida')) {
                await atualizarNotificacao(id, 'mark_read');
            }
        }
    });

    // Função para fechar o modal e atualizar a UI
    function fecharModal() {
        notificacaoExpandida.classList.remove('expandido');
        // Ao fechar, remove a classe 'nao-lida' do item que foi aberto
        if (itemClicado && itemClicado.classList.contains('nao-lida')) {
            itemClicado.classList.remove('nao-lida');
        }
        itemClicado = null; // Limpa a referência
    }

    btnFechar.addEventListener('click', fecharModal);
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && notificacaoExpandida.classList.contains('expandido')) fecharModal();
    });
});
