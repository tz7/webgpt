sessionStorage.removeItem('conversationId');

document.addEventListener('DOMContentLoaded', function () {
    async function loadConversation(conversationId) {
        sessionStorage.setItem('conversationId', conversationId);

        const response = await fetch(`/api/conversation/${conversationId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        clearChat();

        for (const message of data.messages) {
            addMessage(message.content, message.sender);
        }
    }

    function clearChat() {
        const chatBox = document.getElementById('chatBox');
        chatBox.innerHTML = '';
    }

    function addMessage(content, sender) {
        const chatBox = document.getElementById('chatBox');
        const messageCard = document.createElement('div');
        messageCard.classList.add('card', 'mb-3', 'card-api', 'rounded-3');

        const messageCardBody = document.createElement('div');
        messageCardBody.classList.add('card-body', 'row');

        const senderCol = document.createElement('div');
        senderCol.classList.add('col-1', 'font-weight-bold');
        senderCol.textContent = sender;

        const contentCol = document.createElement('div');
        contentCol.classList.add('col-11');
        contentCol.textContent = content;

        messageCardBody.appendChild(senderCol);
        messageCardBody.appendChild(contentCol);

        messageCard.appendChild(messageCardBody);
        chatBox.appendChild(messageCard);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function resetChat() {
        clearChat();
        sessionStorage.removeItem('conversationId');
    }

    const newChatButton = document.getElementById('newChatButton');
    newChatButton.addEventListener('click', resetChat);

    // Attach the loadConversation function to the global scope
    window.loadConversation = loadConversation;
});