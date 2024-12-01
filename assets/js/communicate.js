document.addEventListener('DOMContentLoaded', () => {
    const chatBox = document.getElementById('chat-box');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message');

    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Create a URLSearchParams object from the query string
        var params = new URLSearchParams(window.location.search);
        // Get the value of the 'user_id' parameter
        var receiverId = params.get('chat_id');

        const message = messageInput.value.trim();
        if (message === '') return;

        // Append the message to the chat box
        appendMessage('You', message, 'sent');

        // Clear the input
        messageInput.value = '';

        // Send the message to the server (you would implement this in PHP)
        try {
            const response = await fetch('/job_platform/utils/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `message=${encodeURIComponent(message)}&receiver_id=${encodeURIComponent(receiverId)}`
            });

            if (!response.ok) {
                throw new Error('Failed to send message');
            }

            // Optionally handle server response
            // const result = await response.json();
            // console.log(result);
        } catch (error) {
            console.error(error);
        }
    });

    function appendMessage(sender, message, type) {
        const messageElement = document.createElement('p');
        messageElement.classList.add('message', type);
        messageElement.textContent = `${sender}: ${message}`;
        chatBox.appendChild(messageElement);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Fetch new messages every 5 seconds
    fetchMessages()
    setInterval(fetchMessages, 5000);

    async function fetchMessages() {
        try {
            // Create a URLSearchParams object from the query string
            var params = new URLSearchParams(window.location.search);
            // Get the value of the 'user_id' parameter
            var receiverId = params.get('chat_id');

            const response = await fetch(`/job_platform/utils/fetch_message.php?receiver_id=${encodeURIComponent(receiverId)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch messages');
            }

            const messages = await response.json();

            // Clear the chat box
            chatBox.innerHTML = '';

            const thisId = document.getElementById('thisId');
            const yourUserId = thisId.textContent;

            messages.forEach(msg => {
                const sender = msg.sender_id == yourUserId ? 'You' : 'Other';
                const type = msg.sender_id == yourUserId ? 'sent' : 'received';
                appendMessage(sender, msg.message, type);
            });
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    }
});
