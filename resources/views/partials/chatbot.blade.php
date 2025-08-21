{{-- Chatbot Widget --}}
<div id="chatbot-container" style="position: fixed; bottom: 1rem; right: 1rem; z-index: 1050;">
    
    {{-- Chat Window --}}
    <div id="chatbot-window" class="card shadow-lg d-none" style="width: 22rem; height: 32rem; transition: all 0.3s;">
        {{-- Header --}}
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">ZoeChat</h5>
            <button id="chatbot-close" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
        </div>

        {{-- Messages --}}
        <div id="chatbot-messages" class="card-body" style="overflow-y: auto; flex-grow: 1;">
             {{-- Pesan Selamat Datang Awal --}}
             <div class="d-flex mb-3 justify-content-start">
                <div class="p-2 rounded" style="background-color: #e9ecef; color: #000; max-width: 80%;">
                   Shalom! Ada yang bisa saya bantu?
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="card-footer bg-light p-2">
            <input type="text" id="chatbot-input" class="form-control" placeholder="Ketik pesanmu...">
        </div>
    </div>

    {{-- Tombol untuk Membuka/Menutup Chat --}}
    <button id="chatbot-toggle" class="btn btn-dark rounded-pill shadow-lg p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; transition: transform 0.2s;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-chat-dots-fill" viewBox="0 0 16 16">
            <path d="M16 8c0 3.866-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7zM5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
        </svg>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotMessages = document.getElementById('chatbot-messages');
    // Ambil token CSRF dari meta tag yang sudah kita tambahkan di app.blade.php
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Fungsi untuk membuka/menutup jendela chat
    const toggleWindow = () => {
        chatbotWindow.classList.toggle('d-none');
        chatbotToggle.classList.toggle('d-none');
    };

    chatbotToggle.addEventListener('click', toggleWindow);
    chatbotClose.addEventListener('click', toggleWindow);

    // Fungsi untuk mengirim pesan saat menekan tombol Enter
    chatbotInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter' && chatbotInput.value.trim() !== '') {
            const userMessage = chatbotInput.value.trim();
            addMessage(userMessage, 'user');
            sendMessageToServer(userMessage);
            chatbotInput.value = '';
        }
    });

    // Fungsi untuk menambahkan gelembung pesan ke tampilan
    function addMessage(message, sender) {
        const messageWrapper = document.createElement('div');
        messageWrapper.className = `d-flex mb-3 ${sender === 'user' ? 'justify-content-end' : 'justify-content-start'}`;
        
        const messageBubble = document.createElement('div');
        messageBubble.className = `p-2 rounded ${sender === 'user' ? 'bg-primary text-white' : ''}`;
        if (sender !== 'user') {
            messageBubble.style.backgroundColor = '#e9ecef';
            messageBubble.style.color = '#000';
        }
        messageBubble.style.maxWidth = '80%';
        messageBubble.textContent = message;
        
        messageWrapper.appendChild(messageBubble);
        chatbotMessages.appendChild(messageWrapper);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight; // Auto scroll ke pesan terbaru
    }

    // Fungsi untuk mengirim pesan ke server Laravel
    async function sendMessageToServer(message) {
        // Tampilkan indikator "mengetik" dari bot
        const typingIndicator = document.createElement('div');
        typingIndicator.id = 'typing-indicator';
        typingIndicator.className = 'd-flex mb-3 justify-content-start';
        typingIndicator.innerHTML = `<div class="p-2 rounded" style="background-color: #e9ecef; color: #000; max-width: 80%;">...</div>`;
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
            
            // Hapus indikator "mengetik"
            document.getElementById('typing-indicator').remove();
            
            // Tampilkan balasan dari bot
            addMessage(data.reply, 'bot');

        } catch (error) {
            console.error('Error:', error);
            document.getElementById('typing-indicator')?.remove();
            addMessage('Maaf, terjadi kesalahan saat menghubungi server.', 'bot');
        }
    }
});
</script>