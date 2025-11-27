document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.cadastro-form');
    const errorContainer = document.getElementById('error-container');

    const aceitar = document.querySelector("#btnAceitar");
    const checkboxterms = document.querySelector('#terms');

    if (aceitar) aceitar.disabled = true;

    if (checkboxterms) {
        checkboxterms.addEventListener("change", () => {
            if(aceitar) aceitar.disabled = !checkboxterms.checked;
        });
    }

    if (form) {
        form.addEventListener('submit', (event) => {
            event.preventDefault(); // Previne o envio padrão

            const formData = new FormData(form);

            fetch('../PHP/cadastro.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Limpa erros antigos
                errorContainer.innerHTML = '';
                errorContainer.classList.remove('show');

                if (data.success) {
                    // Redireciona para a página de carregamento em caso de sucesso
                    window.location.href = data.redirect_url;
                } else {
                    // Mostra os erros na tela
                    if (data.errors && data.errors.length > 0) {
                        const errorList = document.createElement('ul');
                        data.errors.forEach(errorText => {
                            const listItem = document.createElement('li');
                            listItem.textContent = errorText;
                            errorList.appendChild(listItem);
                        });
                        errorContainer.appendChild(errorList);
                        errorContainer.classList.add('show'); // Inicia o fade-in

                        // Inicia o timer para o fade-out
                        setTimeout(() => {
                            errorContainer.classList.remove('show');
                            // Espera a transição terminar para limpar o conteúdo
                            setTimeout(() => {
                                errorContainer.innerHTML = '';
                            }, 500); // Deve ser igual à duração da transição do CSS
                        }, 3000);
                    }
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                errorContainer.innerHTML = '<ul><li>Ocorreu um erro de comunicação com o servidor.</li></ul>';
                errorContainer.classList.add('show'); // Inicia o fade-in

                // Inicia o timer para o fade-out
                setTimeout(() => {
                    errorContainer.classList.remove('show');
                    // Espera a transição terminar para limpar o conteúdo
                    setTimeout(() => {
                        errorContainer.innerHTML = '';
                    }, 500); // Deve ser igual à duração da transição do CSS
                }, 3000);
            });
        });
    }
});
