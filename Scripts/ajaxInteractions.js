// Espera o documento HTML ser completamente carregado
document.addEventListener('DOMContentLoaded', () => {

    // Função para mostrar a notificação
    function showNotification(message, type = 'info') { // Adicionado 'type' para 'success' ou 'error'
        // Remove qualquer notificação antiga
        const oldNotification = document.querySelector('.notification');
        if (oldNotification) {
            oldNotification.remove();
        }

        // Cria o elemento da notificação
        const notification = document.createElement('div');
        // Adiciona a classe do tipo (ex: 'notification success' ou 'notification error')
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        // Força a transição de CSS a acontecer
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Esconde e remove a notificação depois de 3 segundos
        setTimeout(() => {
            notification.classList.remove('show');
            // Remove o elemento do DOM depois que a transição de fade-out terminar
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 3000);
    }

    // Usa delegação de eventos no container do feed para capturar envios de formulários
    // que são adicionados dinamicamente (scroll infinito).
    const feedContainer = document.getElementById('feed-conteudo');

    if (feedContainer) {
        feedContainer.addEventListener('submit', function(event) {
            // Verifica se o evento foi disparado por um formulário de interação de post
            const form = event.target;
            if (!form.matches('.footer-post form')) {
                return; // Se não for, ignora.
            }

            // 1. Previne o envio padrão do formulário (que recarrega a página)
            event.preventDefault();

            // Pega o botão que foi clicado para submeter o formulário
            const submitter = event.submitter;
            if (!submitter) {
                console.error("Formulário submetido sem um botão de envio.");
                showNotification('Erro: Ação não pôde ser determinada.', 'error');
                return;
            }

            const formData = new FormData(form);
            // Adiciona manualmente o nome e o valor do botão clicado ao FormData
            formData.append(submitter.name, submitter.value || '');

            const url = form.action;
            
            // Seleciona ambos os botões dentro do formulário que foi submetido
            const likeButton = form.querySelector('button[name="curtir_post"]');
            const repostButton = form.querySelector('button[name="repostar_post"]');

            // 2. Envia os dados para o servidor usando a API Fetch (AJAX)
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro de HTTP! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // 3. Processa a resposta do servidor
                if (data.success) {
                    // Se a ação foi curtir/descurtir
                    if (likeButton && typeof data.curtido !== 'undefined') {
                        likeButton.classList.toggle('curtido', data.curtido);
                    }

                    // Se a ação foi repostar/des-repostar
                    if (repostButton && typeof data.repostado !== 'undefined') {
                        repostButton.classList.toggle('repostado', data.repostado);
                    }
                    
                    // Mostra a mensagem de sucesso
                    showNotification(data.message, 'success');
                } else {
                    // Mostra a mensagem de erro vinda do servidor
                    showNotification(data.message || 'Ocorreu um erro inesperado.', 'error');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                showNotification('Erro de conexão. Verifique o console para detalhes.', 'error');
            });
        });
    }
});