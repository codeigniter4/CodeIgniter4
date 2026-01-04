document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chat-messages');
    const chatInput = document.querySelector('input[placeholder="پیام خود را بنویسید..."]');
    const sendBtn = document.querySelector('button.btn-primary');
    const projectId = window.location.pathname.split('/').pop();

    if (!chatContainer || !projectId) return;

    // Load History
    loadHistory();

    // Send Message
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });

    function loadHistory() {
        chatContainer.innerHTML = '<div class="text-center text-muted small mt-3">در حال بارگذاری...</div>';

        fetch(`/api/chat/history/${projectId}`)
        .then(res => res.json())
        .then(data => {
            chatContainer.innerHTML = '';
            if (data.length === 0) {
                // If no history, maybe trigger analysis if it's empty project?
                // Or just show welcome
                appendMessage('system', 'سلام! من آماده‌ام تا در طراحی کاتالوگ به شما کمک کنم. فایل‌های خود را آپلود کنید و دکمه "تحلیل و شروع" را بزنید.');

                // Add Analyze Button
                const btnDiv = document.createElement('div');
                btnDiv.className = 'text-center my-3';
                btnDiv.innerHTML = '<button id="analyzeBtn" class="btn btn-sm btn-success">تحلیل محتوا و ساختاردهی اولیه</button>';
                chatContainer.appendChild(btnDiv);

                document.getElementById('analyzeBtn').addEventListener('click', triggerAnalysis);
            } else {
                data.forEach(msg => {
                    appendMessage(msg.role, msg.message);
                });
                scrollToBottom();
            }
        })
        .catch(err => {
            console.error(err);
            chatContainer.innerHTML = '<div class="text-center text-danger small">خطا در بارگذاری تاریخچه</div>';
        });
    }

    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        appendMessage('user', text);
        chatInput.value = '';
        scrollToBottom();

        const formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('message', text);
        addCsrf(formData);

        // Show typing indicator
        const typingId = 'typing-' + Date.now();
        appendMessage('assistant', '<div class="spinner-border spinner-border-sm" role="status"></div> در حال نوشتن...', typingId);

        fetch('/api/chat/send', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById(typingId).remove();
            if (data.message) {
                appendMessage('assistant', data.message);
            } else {
                appendMessage('system', 'خطا در دریافت پاسخ: ' + (data.error || 'Unknown'));
            }
            scrollToBottom();
        })
        .catch(err => {
            document.getElementById(typingId).remove();
            appendMessage('system', 'خطای شبکه');
            console.error(err);
        });
    }

    function triggerAnalysis() {
        const btn = document.getElementById('analyzeBtn');
        btn.disabled = true;
        btn.innerHTML = 'در حال تحلیل... (ممکن است تا ۱ دقیقه طول بکشد)';

        const formData = new FormData();
        formData.append('project_id', projectId);
        addCsrf(formData);

        fetch('/api/ai/analyze', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.parentElement.remove(); // Remove button
            if (data.success) {
                appendMessage('assistant', data.data.explanation || 'تحلیل انجام شد.');
                // Here we should ideally reload the preview panel to show new pages
                // For now, just reload page
                setTimeout(() => location.reload(), 2000);
            } else {
                appendMessage('system', 'خطا در تحلیل: ' + (data.error || 'Unknown'));
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = 'تلاش مجدد';
            appendMessage('system', 'خطای شبکه در تحلیل');
            console.error(err);
        });
    }

    function appendMessage(role, text, id = null) {
        const isUser = role === 'user';
        const align = isUser ? 'justify-content-start' : 'justify-content-end';
        // Note: In RTL, start is right, end is left.
        // Usually User is Right (Start), Bot is Left (End).
        // Let's check typical messaging apps.
        // Actually, in RTL:
        // User (Me) usually on Right? Or Left?
        // Telegram/WhatsApp: Me (Right), Others (Left).
        // So justify-content-start (Right in RTL) for User.

        const msgDiv = document.createElement('div');
        msgDiv.className = `d-flex mb-3 ${isUser ? 'flex-row' : 'flex-row-reverse'}`;
        // If user, flex-row (start->right). If bot, reverse (end->left).

        const avatar = isUser ? 'User' : 'AI';
        const bgClass = isUser ? 'bg-primary text-white' : 'bg-white text-dark border';

        if (id) msgDiv.id = id;

        msgDiv.innerHTML = `
            <div class="flex-shrink-0">
                <div class="avatar ${isUser ? 'bg-secondary' : 'bg-primary'} text-white rounded-circle d-flex align-items-center justify-content-center small" style="width: 32px; height: 32px;">${avatar}</div>
            </div>
            <div class="flex-grow-1 mx-2">
                <div class="${bgClass} p-3 rounded shadow-sm" style="max-width: 85%;">
                    ${formatText(text)}
                </div>
            </div>
        `;

        chatContainer.appendChild(msgDiv);
    }

    function formatText(text) {
        // Simple line break to br
        return text.replace(/\n/g, '<br>');
    }

    function scrollToBottom() {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function addCsrf(formData) {
        const csrfName = 'csrf_test_name';
        const csrfHash = document.querySelector('meta[name="csrf-token"]')?.content;
        if(csrfHash) formData.append(csrfName, csrfHash);
    }
});
