{{-- Chatbot Widget (Enterprise Flat Design) --}}
<div id="chatbot-container" style="position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 1050;">
    
    {{-- Chat Window --}}
    <div id="chatbot-window" class="card border-0 rounded-0 shadow-sm border-top border-dark border-4 d-none" style="width: 24rem; height: 35rem; display: flex; flex-direction: column;">
        
        {{-- Header --}}
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-success rounded-0 font-monospace me-2" style="font-size: 10px;">ONLINE</span>
                <h6 class="mb-0 uppercase fw-bold small text-white">Zoe Asisten Virtual</h6>
            </div>
            <button id="chatbot-close" type="button" class="btn-close btn-close-white rounded-0" aria-label="Close"></button>
        </div>

        {{-- Messages Area --}}
        <div id="chatbot-messages" class="card-body bg-white p-4" style="overflow-y: auto; flex-grow: 1;">
             
            {{-- Pesan Selamat Datang Awal --}}
             <div class="d-flex mb-3 justify-content-start">
                <div class="p-3 rounded-0 border border-secondary border-opacity-25 bg-light text-dark small" style="max-width: 85%;">
                   <strong class="uppercase fw-bold d-block mb-1 text-primary font-monospace">Zoe:</strong>
                   Shalom! Ada yang bisa saya bantu terkait layanan sistem akademik hari ini?
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="card-footer bg-light p-3 border-top rounded-0">
            <div class="input-group rounded-0">
                <input type="text" id="chatbot-input" class="form-control rounded-0 font-monospace small" placeholder="KETIK PESAN...">
                <button id="chatbot-send" class="btn btn-dark rounded-0 px-3 uppercase fw-bold small">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Tombol Toggle Flat/Siku 0px --}}
    <button id="chatbot-toggle" class="btn btn-dark rounded-0 shadow-sm p-0 d-flex align-items-center justify-content-center border border-white border-2" style="width: 55px; height: 55px;">
        <i class="bi bi-chat-square-text-fill fs-4"></i>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSend = document.getElementById('chatbot-send');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const toggleWindow = () => {
        chatbotWindow.classList.toggle('d-none');
        chatbotToggle.classList.toggle('d-none');
        if (!chatbotWindow.classList.contains('d-none')) {
            chatbotInput.focus();
        }
    };

    chatbotToggle.addEventListener('click', toggleWindow);
    chatbotClose.addEventListener('click', toggleWindow);

    const handleSend = () => {
        if (chatbotInput.value.trim() !== '') {
            const userMessage = chatbotInput.value.trim();
            addMessage(userMessage, 'user');
            sendMessageToServer(userMessage);
            chatbotInput.value = '';
        }
    };

    chatbotInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSend();
        }
    });
    
    chatbotSend.addEventListener('click', handleSend);

    function addMessage(message, sender) {
        const messageWrapper = document.createElement('div');
        messageWrapper.className = `d-flex mb-3 ${sender === 'user' ? 'justify-content-end' : 'justify-content-start'}`;
        
        const messageBubble = document.createElement('div');
        messageBubble.className = 'p-3 rounded-0 small ';
        messageBubble.style.maxWidth = '85%';
        messageBubble.style.wordWrap = 'break-word'; 
        
        if (sender === 'user') {
            messageBubble.className += 'bg-dark text-white';
            messageBubble.innerHTML = `<strong class="uppercase fw-bold d-block mb-1 text-light font-monospace text-end">Anda:</strong> ${message}`;
        } else {
            messageBubble.className += 'bg-light text-dark border border-secondary border-opacity-25';
            messageBubble.innerHTML = `<strong class="uppercase fw-bold d-block mb-1 text-primary font-monospace">Zoe:</strong> ${message}`;
        }
        
        messageWrapper.appendChild(messageBubble);
        chatbotMessages.appendChild(messageWrapper);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    async function sendMessageToServer(message) {
        const typingIndicator = document.createElement('div');
        typingIndicator.id = 'typing-indicator';
        typingIndicator.className = 'd-flex mb-3 justify-content-start';
        typingIndicator.innerHTML = `
            <div class="p-3 rounded-0 bg-light text-muted font-monospace small border border-secondary border-opacity-25" style="max-width: 85%;">
                MENCARI JAWABAN...
            </div>`;
        chatbotMessages.appendChild(typingIndicator);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;

        try {
            const response = await fetch("{{ route('chatbot.handle') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ message: message })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            document.getElementById('typing-indicator')?.remove();
            addMessage(data.reply, 'bot');

        } catch (error) {
            console.error('Error:', error);
            document.getElementById('typing-indicator')?.remove();
            addMessage('MAAF, TERJADI KESALAHAN TEKNIS SAAT MENGHUBUNGI SERVER.', 'bot');
        }
    }
});
</script>