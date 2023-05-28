document.addEventListener('DOMContentLoaded', function () {
    const sendButton = document.getElementById('sendButton');
    const userInput = document.getElementById('userInput');
    const chatBox = document.getElementById('chatBox');
    const userModel = document.getElementById('modelSelect');

    userInput.value = '';
    userInput.rows = 1;


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
            const { response: generatedMessage, conversationId: newConversationId, tokenCount } = data;
            console.log("Token count: ", tokenCount);  // Logging the token count
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
        userInput.value = ''; // clear the textarea
        userInput.rows = 1; // reset rows

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
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault(); // prevent new line
            await processInput();
        }
    });

    // Dynamic text area. Adding/removing rows based on new lines


    // Create shadow textarea
    const shadowTextArea = document.createElement('textarea');
    shadowTextArea.style.position = 'absolute';
    shadowTextArea.style.top = '-9999px';
    shadowTextArea.style.left = '-9999px';

    const computedStyle = window.getComputedStyle(userInput);

    shadowTextArea.style.width = computedStyle.width; // match width
    shadowTextArea.style.fontSize = computedStyle.fontSize; // match font size
    shadowTextArea.style.fontFamily = computedStyle.fontFamily; // match font family
    shadowTextArea.style.lineHeight = computedStyle.lineHeight; // match line height
    shadowTextArea.style.padding = computedStyle.padding; // match padding
    shadowTextArea.style.border = computedStyle.border; // match border
    shadowTextArea.style.letterSpacing = computedStyle.letterSpacing; // match letter spacing
    shadowTextArea.style.wordSpacing = computedStyle.wordSpacing; // match word spacing
    shadowTextArea.style.textRendering = computedStyle.textRendering; // match text rendering
    shadowTextArea.style.whiteSpace = computedStyle.whiteSpace; // match white space
    shadowTextArea.style.overflowWrap = computedStyle.overflowWrap; // match overflow wrap
    shadowTextArea.style.boxSizing = computedStyle.boxSizing; // match box-sizing
    shadowTextArea.style.overflow = 'hidden'; // hide scrollbar
    document.body.appendChild(shadowTextArea);

    // Listen for input event and adjust rows as needed
    userInput.addEventListener('input', function () {
        // Calculate the number of rows
        const text = userInput.value;
        const lines = (text.match(/\n/g) || []).length;
        // Make sure it does not go below 1 and above 7
        const rows = Math.min(Math.max(lines + 1, 1), 7);
        // Update rows of the userInput
        userInput.rows = rows;
    });
});