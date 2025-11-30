async function loadComments(postId) {
    const commentsList = document.getElementById('comments-list');
    if (!commentsList) return;

    try {
        const response = await fetch(`../PHP/comentarios_handler.php?action=fetch&post_id=${postId}`);
        const data = await response.json();

        if (data.success) {
            commentsList.innerHTML = ''; // Limpa a lista
            if (data.comments.length > 0) {
                data.comments.forEach(comment => {
                    const commentElement = createCommentElement(comment);
                    commentsList.appendChild(commentElement);
                });
            } else {
                commentsList.innerHTML = '<p>Nenhum comentário ainda. Seja o primeiro a comentar!</p>';
            }
        } else {
            commentsList.innerHTML = `<p>Erro ao carregar comentários: ${data.message}</p>`;
        }
    } catch (error) {
        console.error('Erro na requisição de comentários:', error);
        commentsList.innerHTML = '<p>Erro ao conectar com o servidor.</p>';
    }
}

function createCommentElement(comment) {
    const div = document.createElement('div');
    div.className = 'comment-item';
    div.setAttribute('data-comment-id', comment.id);

    const userAvatar = comment.user_avatar || 'profile.png';
    const userName = comment.nome_user || 'Usuário';
    const commentDate = new Date(comment.created_at).toLocaleString('pt-BR');

    let actions = '';
    // A variável loggedInUserId é definida em UsuarioLogado.php
    if (typeof loggedInUserId !== 'undefined' && comment.user_id == loggedInUserId) {
        actions = `
            <div class="comment-actions">
                <button class="edit-comment-btn">Editar</button>
                <button class="delete-comment-btn">Excluir</button>
            </div>
        `;
    }

    div.innerHTML = `
        <img src="../images/avatares/Users/${userAvatar}" alt="Avatar" class="user-avatar">
        <div class="comment-content">
            <div class="comment-header">
                <span class="user-name">@${userName}</span>
                <span class="comment-date">${commentDate}</span>
            </div>
            <div class="comment-body">
                <p>${comment.content}</p>
            </div>
            ${actions}
        </div>
    `;
    return div;
}

async function addComment(postId, content) {
    if (!content.trim()) {
        alert('O comentário não pode estar vazio.');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('post_id', postId);
    formData.append('content', content);

    try {
        const response = await fetch('../PHP/comentarios_handler.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            const commentsList = document.getElementById('comments-list');
            // Se for o primeiro comentário, limpa a mensagem "Nenhum comentário"
            if (commentsList.querySelector('p')) {
                commentsList.innerHTML = '';
            }
            const newCommentElement = createCommentElement(data.comment);
            commentsList.appendChild(newCommentElement);
            document.getElementById('new-comment-content').value = ''; // Limpa o textarea
        } else {
            alert(`Erro ao adicionar comentário: ${data.message}`);
        }
    } catch (error) {
        console.error('Erro na requisição para adicionar comentário:', error);
        alert('Erro de conexão ao tentar adicionar o comentário.');
    }
}

async function editComment(commentId, newContent, commentBody) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('comment_id', commentId);
    formData.append('content', newContent);

    try {
        const response = await fetch('../PHP/comentarios_handler.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            // Atualiza o texto no parágrafo original
            commentBody.innerHTML = `<p>${newContent}</p>`;
        } else {
            alert(`Erro ao editar comentário: ${data.message}`);
            // Opcional: restaurar a UI para o estado original se a edição falhar
        }
    } catch (error) {
        console.error('Erro na requisição para editar comentário:', error);
        alert('Erro de conexão ao tentar editar o comentário.');
    }
}

async function deleteComment(commentId, commentElement) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('comment_id', commentId);

    try {
        const response = await fetch('../PHP/comentarios_handler.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            commentElement.remove();
        } else {
            alert(`Erro ao excluir comentário: ${data.message}`);
        }
    } catch (error) {
        console.error('Erro na requisição para excluir comentário:', error);
        alert('Erro de conexão ao tentar excluir o comentário.');
    }
}

// Delegação de eventos para o conteúdo do modal
document.addEventListener('click', function(event) {
    const target = event.target;

    // Botão de submeter novo comentário
    if (target && target.id === 'submit-comment-btn') {
        const postId = target.dataset.postId;
        const content = document.getElementById('new-comment-content').value;
        addComment(postId, content);
    }

    // Botão de deletar comentário
    if (target && target.classList.contains('delete-comment-btn')) {
        const commentItem = target.closest('.comment-item');
        const commentId = commentItem.dataset.commentId;
        
        if (confirm('Tem certeza que deseja excluir este comentário?')) {
            deleteComment(commentId, commentItem);
        }
    }
    
    // Botão de editar comentário
    if (target && target.classList.contains('edit-comment-btn')) {
        const commentItem = target.closest('.comment-item');
        const commentBody = commentItem.querySelector('.comment-body');
        const currentText = commentBody.querySelector('p').textContent;

        // Salva o conteúdo original e cria a área de edição
        commentBody.setAttribute('data-original-content', commentBody.innerHTML);
        commentBody.innerHTML = `
            <textarea class="edit-textarea">${currentText}</textarea>
            <button class="save-edit-btn">Salvar</button>
            <button class="cancel-edit-btn">Cancelar</button>
        `;
    }

    // Botão de salvar edição
    if (target && target.classList.contains('save-edit-btn')) {
        const commentItem = target.closest('.comment-item');
        const commentId = commentItem.dataset.commentId;
        const commentBody = commentItem.querySelector('.comment-body');
        const newContent = commentBody.querySelector('.edit-textarea').value;

        editComment(commentId, newContent, commentBody);
    }

    // Botão de cancelar edição
    if (target && target.classList.contains('cancel-edit-btn')) {
        const commentItem = target.closest('.comment-item');
        const commentBody = commentItem.querySelector('.comment-body');
        // Restaura o conteúdo original
        commentBody.innerHTML = commentBody.getAttribute('data-original-content');
        commentBody.removeAttribute('data-original-content');
    }
});
