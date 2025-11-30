document.addEventListener("DOMContentLoaded", () => {
    // Seleciona o botão e o contador
    const followButton = document.querySelector("#btn-seguir");
    const followersElement = document.getElementById("followers-count");

    // 1. Verificação de Segurança: Se o botão não existir na página, para a execução.
    // Isso evita o erro "addEventListener of null"
    if (!followButton) return;

    followButton.addEventListener("click", function (e) {
        e.preventDefault(); // Previne comportamentos padrões (caso seja um link ou submit)

        // Captura o ID. Certifique-se que seu HTML tem: data-profile-id="123"
        const profileId = this.dataset.profileId;

        if (!profileId) {
            console.error("Erro: ID do perfil não encontrado no botão.");
            return;
        }

        // Bloqueia o botão para evitar cliques duplos enquanto carrega
        this.disabled = true;
        const textoOriginal = this.textContent;
        this.textContent = "Carregando...";

        const formData = new FormData();
        formData.append("seguido_id", profileId);

        fetch("../PHP/seguir.php", {
            method: "POST",
            body: formData
        })
        .then(r => {
            // Verifica se a resposta HTTP foi bem sucedida (status 200-299)
            if (!r.ok) {
                throw new Error("Erro na resposta da rede: " + r.status);
            }
            return r.json();
        })
        .then(data => {
            if (data.status === "success") {
                // Pega o número atual, garantindo que seja um número (ou 0 se falhar)
                let count = 0;
                if (followersElement) {
                    count = parseInt(followersElement.textContent, 10) || 0;
                }

                if (data.action === "followed") {
                    this.textContent = "Seguindo";
                    this.classList.add("seguindo");
                    
                    if (followersElement) {
                        followersElement.textContent = count + 1;
                    }

                } else if (data.action === "unfollowed") {
                    this.textContent = "Seguir";
                    this.classList.remove("seguindo");

                    if (followersElement) {
                        // Math.max evita números negativos
                        followersElement.textContent = Math.max(0, count - 1);
                    }
                }
            } else {
                // Se o PHP retornar erro lógico (ex: usuário não logado)
                alert(data.message || "Erro ao tentar seguir.");
                this.textContent = textoOriginal; // Restaura o texto antigo
            }
        })
        .catch(err => {
            console.error("Erro:", err);
            alert("Erro interno ao processar a requisição.");
            this.textContent = textoOriginal; // Restaura o texto antigo em caso de erro
        })
        .finally(() => {
            // Reabilita o botão independente do resultado
            this.disabled = false;
        });
    });
});