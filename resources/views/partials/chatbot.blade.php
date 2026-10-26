{{-- Chatbot Widget (Enterprise Flat & Responsive Design) --}}
<style>
    #chatbot-container {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 1050;
    }
    #chatbot-window {
        width: 23rem;
        max-width: calc(100vw - 2rem);
        height: 32rem;
        max-height: calc(100vh - 7rem);
        flex-direction: column;
        border-radius: var(--siakad-radius, 10px) !important;
        overflow: hidden;
    }
    #chatbot-window:not(.d-none) {
        display: flex !important;
    }
    #chatbot-toggle {
        width: 54px;
        height: 54px;
        border-radius: var(--siakad-radius, 10px) !important;
    }
    @media (max-width: 991.98px) {
        #chatbot-container {
            bottom: 4.8rem;
            right: 1rem;
        }
        #chatbot-window {
            width: calc(100vw - 2rem);
            height: 28rem;
            max-height: calc(100vh - 9rem);
        }
        #chatbot-toggle {
            width: 48px;
            height: 48px;
        }
    }
</style>

<div id="chatbot-container">
    {{-- Chat Window --}}
    <div id="chatbot-window" class="card border-0 shadow-lg border-top border-dark border-4 d-none">
        
        {{-- Header --}}
        <div class="card-header bg-dark text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-success font-monospace me-2" style="font-size: 10px;">ONLINE</span>
                <h6 class="mb-0 uppercase fw-bold small text-white">Zoe — Asisten Akademik</h6>
            </div>
            <button id="chatbot-close" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
        </div>

        {{-- Messages Area --}}
        <div id="chatbot-messages" class="card-body bg-white p-3" style="overflow-y: auto; flex-grow: 1;">
            {{-- Pesan Selamat Datang Awal --}}
            <div class="d-flex mb-3 justify-content-start">
                <div class="p-3 border border-secondary border-opacity-25 bg-light text-dark small" style="max-width: 88%; border-radius: var(--siakad-radius, 8px);">
                    <strong class="uppercase fw-bold d-block mb-1 text-primary font-monospace">Zoe:</strong>
                    Shalom! Ada yang bisa saya bantu terkait layanan sistem akademik hari ini?
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="card-footer bg-light p-2.5 border-top">
            <div class="input-group">
                <input type="text" id="chatbot-input" class="form-control font-monospace small" placeholder="Ketik pertanyaan Anda..." maxlength="500" autocomplete="off">
                <button id="chatbot-send" class="btn btn-dark px-3 uppercase fw-bold small" type="button">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Tombol Toggle --}}
    <button id="chatbot-toggle" type="button" class="btn btn-dark shadow-lg p-0 d-flex align-items-center justify-content-center border border-white border-2" title="Tanya Zoe (Asisten Virtual)">
        <i class="bi bi-chat-square-text-fill fs-5"></i>
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
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    const toggleWindow = () => {
        chatbotWindow.classList.toggle('d-none');
        chatbotToggle.classList.toggle('d-none');
        if (!chatbotWindow.classList.contains('d-none')) {
            chatbotInput.focus();
        }
    };

    if (chatbotToggle) chatbotToggle.addEventListener('click', toggleWindow);
    if (chatbotClose) chatbotClose.addEventListener('click', toggleWindow);

    const handleSend = () => {
        if (chatbotInput.value.trim() !== '') {
            const userMessage = chatbotInput.value.trim();
            appendMessage(userMessage, 'user');
            chatbotInput.value = '';
            sendMessageToServer(userMessage);
        }
    };

    if (chatbotSend) chatbotSend.addEventListener('click', handleSend);
    if (chatbotInput) {
        chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleSend();
            }
        });
    }

    function appendMessage(message, sender) {
        const messageWrapper = document.createElement('div');
        messageWrapper.classList.add('d-flex', 'mb-3', sender === 'user' ? 'justify-content-end' : 'justify-content-start');
        
        const messageElement = document.createElement('div');
        messageElement.classList.add('p-3', 'small');
        messageElement.style.maxWidth = '88%';
        messageElement.style.borderRadius = 'var(--siakad-radius, 8px)';
        messageElement.style.wordBreak = 'break-word';
        
        if (sender === 'user') {
            messageElement.classList.add('bg-dark', 'text-white', 'shadow-sm');
            const senderLabel = document.createElement('strong');
            senderLabel.className = 'uppercase fw-bold d-block mb-1 text-white-50 font-monospace';
            senderLabel.style.fontSize = '10px';
            senderLabel.textContent = 'ANDA:';

            const bodyText = document.createElement('div');
            bodyText.textContent = message;

            messageElement.appendChild(senderLabel);
            messageElement.appendChild(bodyText);
        } else {
            messageElement.classList.add('bg-light', 'text-dark', 'border', 'border-secondary', 'border-opacity-25');
            messageElement.innerHTML = `<strong class="uppercase fw-bold d-block mb-1 text-primary font-monospace" style="font-size: 10px;">ZOE:</strong><div>${message}</div>`;
        }
        
        messageWrapper.appendChild(messageElement);
        chatbotMessages.appendChild(messageWrapper);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function showTypingIndicator() {
        const typingWrapper = document.createElement('div');
        typingWrapper.id = 'typing-indicator';
        typingWrapper.classList.add('d-flex', 'mb-3', 'justify-content-start');
        typingWrapper.innerHTML = `
            <div class="p-2 px-3 bg-light text-muted border border-secondary border-opacity-25 small font-monospace uppercase" style="font-size: 11px; border-radius: var(--siakad-radius, 8px);">
                <span class="spinner-grow spinner-grow-sm me-2 text-dark" role="status" aria-hidden="true" style="width: 0.6rem; height: 0.6rem;"></span>
                ZOE SEDANG MENGETIK...
            </div>
        `;
        chatbotMessages.appendChild(typingWrapper);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    async function sendMessageToServer(message) {
        showTypingIndicator();
        chatbotInput.disabled = true;
        chatbotSend.disabled = true;

        try {
            const response = await fetch('{{ route("chatbot.handle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: message })
            });

            removeTypingIndicator();

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            appendMessage(data.reply, 'bot');

        } catch (error) {
            removeTypingIndicator();
            appendMessage('Maaf, terjadi gangguan koneksi ke jaringan utama. Silakan coba beberapa saat lagi.', 'bot');
        } finally {
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            chatbotInput.focus();
        }
    }
});
</script>