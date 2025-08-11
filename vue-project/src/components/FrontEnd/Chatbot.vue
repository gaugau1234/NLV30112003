<script setup>
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';

const isOpen = ref(false);
const messages = ref([
    { from: 'bot', text: 'Chào bạn! Tôi có thể giúp gì cho bạn?' }
]);
const input = ref('');
const route = useRoute();

// Simple function to simulate dynamic response based on current route and user input
function getBotResponse(userText) {
    const path = route.path;
    if (path.includes('TaskList')) {
        if (userText.includes('làm việc') || userText.includes('công việc')) {
            return 'Để quản lý công việc, bạn có thể tạo task mới, phân công và theo dõi tiến độ trong trang Quản lý Công việc.';
        }
    } else if (path.includes('Notifications')) {
        if (userText.includes('thông báo')) {
            return 'Trang Thông báo giúp bạn xem các cập nhật và nhắc nhở quan trọng.';
        }
    } else if (path.includes('groups')) {
        if (userText.includes('nhóm')) {
            return 'Bạn có thể quản lý nhóm của mình tại trang Quản lý Nhóm, thêm hoặc xóa thành viên dễ dàng.';
        }
    }
    return 'Xin lỗi, tôi chưa hiểu câu hỏi của bạn. Bạn có thể hỏi lại hoặc thử câu khác.';
}

function sendMessage() {
    if (!input.value.trim()) return;
    messages.value.push({ from: 'user', text: input.value });
    const response = getBotResponse(input.value.toLowerCase());
    setTimeout(() => {
        messages.value.push({ from: 'bot', text: response });
    }, 500);
    input.value = '';
}
</script>

<template>
    <div class="chatbot-container" :class="{ open: isOpen }">
        <button class="chatbot-toggle" @click="isOpen = !isOpen">
            <span class="toggle-icon">{{ isOpen ? '✕' : '💬' }}</span>
            <span class="toggle-text">{{ isOpen ? 'Đóng' : 'Chatbot' }}</span>
        </button>

        <div v-if="isOpen" class="chatbot-window">
            <div class="chatbot-header">
                <div class="bot-avatar">🤖</div>
                <div class="bot-info">
                    <h4>AI Assistant</h4>
                    <span class="status">Đang hoạt động</span>
                </div>
                <button class="close-btn" @click="isOpen = false">✕</button>
            </div>

            <div class="chatbot-messages">
                <div v-for="(msg, index) in messages" :key="index" :class="['message', msg.from]">
                    <div class="message-content">{{ msg.text }}</div>
                    <div class="message-time">{{ new Date().toLocaleTimeString('vi-VN', {
                        hour: '2-digit', minute:
                        '2-digit' }) }}</div>
                </div>
            </div>

            <div class="chatbot-input">
                <input v-model="input" @keyup.enter="sendMessage" placeholder="Nhập tin nhắn..." type="text" />
                <button @click="sendMessage" :disabled="!input.trim()">
                    <span>📤</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.chatbot-container {
    position: fixed;
    bottom: var(--space-6);
    right: var(--space-6);
    width: 380px;
    font-family: var(--font-family, 'Inter', sans-serif);
    z-index: 1000;
    transition: all var(--transition-normal);
}

/* Toggle Button */
.chatbot-toggle {
    background: var(--gradient-primary);
    color: white;
    border: none;
    border-radius: var(--radius-2xl);
    padding: var(--space-4) var(--space-6);
    font-weight: 600;
    cursor: pointer;
    box-shadow: var(--shadow-xl);
    transition: all var(--transition-normal);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-sm);
    position: relative;
    overflow: hidden;
}

.chatbot-toggle::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.chatbot-toggle:hover::before {
    transform: translateX(100%);
}

.chatbot-toggle:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-2xl);
}

.toggle-icon {
    font-size: var(--font-size-lg);
    transition: transform var(--transition-fast);
}

.chatbot-toggle:hover .toggle-icon {
    transform: scale(1.1);
}

/* Chatbot Window */
.chatbot-window {
    margin-top: var(--space-4);
    background: var(--bg-primary);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-2xl);
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    height: 500px;
    overflow: hidden;
    animation: slideInUp 0.4s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Header */
.chatbot-header {
    background: var(--gradient-primary);
    color: white;
    padding: var(--space-4) var(--space-6);
    display: flex;
    align-items: center;
    gap: var(--space-3);
    position: relative;
}

.bot-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-lg);
    backdrop-filter: blur(10px);
}

.bot-info {
    flex: 1;
}

.bot-info h4 {
    margin: 0;
    font-size: var(--font-size-base);
    font-weight: 600;
}

.status {
    font-size: var(--font-size-xs);
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: var(--space-1);
}

.status::before {
    content: '';
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

.close-btn {
    background: none;
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-sm);
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

/* Messages */
.chatbot-messages {
    flex: 1;
    padding: var(--space-4);
    overflow-y: auto;
    background: var(--bg-secondary);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.chatbot-messages::-webkit-scrollbar {
    width: 6px;
}

.chatbot-messages::-webkit-scrollbar-track {
    background: var(--secondary-100);
    border-radius: var(--radius-sm);
}

.chatbot-messages::-webkit-scrollbar-thumb {
    background: var(--secondary-300);
    border-radius: var(--radius-sm);
}

.message {
    display: flex;
    flex-direction: column;
    max-width: 80%;
    animation: messageSlide 0.3s ease-out;
}

@keyframes messageSlide {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message.bot {
    align-self: flex-start;
}

.message.user {
    align-self: flex-end;
}

.message-content {
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-xl);
    line-height: 1.5;
    word-wrap: break-word;
    font-size: var(--font-size-sm);
    position: relative;
}

.message.bot .message-content {
    background: var(--bg-primary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-bottom-left-radius: var(--radius-sm);
}

.message.bot .message-content::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: -8px;
    width: 0;
    height: 0;
    border: 8px solid transparent;
    border-right-color: var(--bg-primary);
    border-bottom: 0;
    border-left: 0;
    margin-bottom: -8px;
}

.message.user .message-content {
    background: var(--gradient-primary);
    color: white;
    border-bottom-right-radius: var(--radius-sm);
}

.message.user .message-content::before {
    content: '';
    position: absolute;
    bottom: 0;
    right: -8px;
    width: 0;
    height: 0;
    border: 8px solid transparent;
    border-left-color: var(--primary-500);
    border-bottom: 0;
    border-right: 0;
    margin-bottom: -8px;
}

.message-time {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    margin-top: var(--space-1);
    opacity: 0.7;
}

.message.bot .message-time {
    align-self: flex-start;
    margin-left: var(--space-2);
}

.message.user .message-time {
    align-self: flex-end;
    margin-right: var(--space-2);
}

/* Input */
.chatbot-input {
    display: flex;
    padding: var(--space-4);
    border-top: 1px solid var(--border-color);
    background: var(--bg-primary);
    gap: var(--space-3);
}

.chatbot-input input {
    flex: 1;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    padding: var(--space-3) var(--space-4);
    font-size: var(--font-size-sm);
    background: var(--bg-secondary);
    color: var(--text-primary);
    transition: all var(--transition-fast);
}

.chatbot-input input:focus {
    outline: none;
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    background: var(--bg-primary);
}

.chatbot-input input::placeholder {
    color: var(--text-secondary);
}

.chatbot-input button {
    background: var(--primary-500);
    border: none;
    color: white;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-base);
}

.chatbot-input button:hover:not(:disabled) {
    background: var(--primary-600);
    transform: scale(1.05);
}

.chatbot-input button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Responsive */
@media (max-width: 768px) {
    .chatbot-container {
        width: calc(100vw - var(--space-8));
        bottom: var(--space-4);
        right: var(--space-4);
    }

    .chatbot-window {
        height: 400px;
    }

    .chatbot-toggle {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .chatbot-container {
        width: calc(100vw - var(--space-4));
        bottom: var(--space-2);
        right: var(--space-2);
    }

    .chatbot-window {
        height: 350px;
    }

    .chatbot-header {
        padding: var(--space-3) var(--space-4);
    }

    .chatbot-messages {
        padding: var(--space-3);
    }

    .chatbot-input {
        padding: var(--space-3);
    }
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
    .message.bot .message-content {
        background: var(--secondary-800);
        border-color: var(--secondary-700);
    }

    .message.bot .message-content::before {
        border-right-color: var(--secondary-800);
    }
}

/* Typing indicator */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-4);
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    max-width: 80px;
    align-self: flex-start;
}

.typing-dot {
    width: 6px;
    height: 6px;
    background: var(--text-secondary);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-dot:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {

    0%,
    60%,
    100% {
        transform: translateY(0);
    }

    30% {
        transform: translateY(-10px);
    }
}
</style>