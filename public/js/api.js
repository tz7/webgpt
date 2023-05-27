document.addEventListener('DOMContentLoaded', function () {
    const sendButton = document.getElementById('sendButton');
    const userInput = document.getElementById('userInput');
    const chatBox = document.getElementById('chatBox');
    const userModel = document.getElementById('modelSelect');


    function addMessage(content, sender) {
        const chatBox = document.getElementById('chatBox');
        const messageCard = document.createElement('div');
        messageCard.classList.add('card', 'mb-3', 'card-api', 'rounded-3');

        const messageCardBody = document.createElement('div');
        messageCardBody.classList.add('card-body', 'row');

        const senderCol = document.createElement('div');
        senderCol.classList.add('col-1', 'font-weight-bold', 'text-nowrap');
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
    // export { addMessage }; update JS...
    // window.addMessage = addMessage; ... don't like it

    async function sendMessage(message, selectedModel) {
        let conversationId = sessionStorage.getItem('conversationId');
        try {
            const response = await fetch('/api', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message, selectedModel: selectedModel, conversationId: conversationId })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            const { response: generatedMessage, conversationId: newConversationId } = data;
            if (newConversationId) {
                conversationId = newConversationId;
                sessionStorage.setItem('conversationId', conversationId);
            }
            return { response: generatedMessage };
        } catch (error) {
            console.error('Error in API call:', error);
            return { error: `Error: ${error.message}` };
        }
    }

    async function processInput() {
        const message = userInput.value.trim();
        if (message === '') return;

        const selectedModel = userModel.value;

        addMessage(message, 'You');
        userInput.value = '';

        // Show the loading indicator
        const loadingElement = document.getElementById('loading');
        loadingElement.classList.remove('d-none');

        const chatGPTResponse = await sendMessage(message, selectedModel);

        // Hide the loading indicator
        loadingElement.classList.add('d-none');

        if (chatGPTResponse.error) {
            addMessage(chatGPTResponse.error, 'Error');
        } else {
            addMessage(chatGPTResponse.response, 'GPT');
        }
    }

    sendButton.addEventListener('click', async function () {
        await processInput();
    });

    userInput.addEventListener('keydown', async function (event) {
        if (event.key === 'Enter') {
            await processInput();
        }
    });
});