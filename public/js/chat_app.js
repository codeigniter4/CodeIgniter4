$(document).ready(function() {
    let csrfName = window.csrfTokenName;
    let csrfHash = window.csrfTokenValue;

    // Helper to update CSRF hash from response or global var
    function updateGlobalCsrfHash(newHash) {
        if (newHash) {
            csrfHash = newHash;
            // Update any visible CSRF fields if they exist and are named consistently
            $('input[name="' + csrfName + '"]').val(csrfHash);
            // Also update the global window.csrfTokenValue if other scripts might rely on it, though less common
            window.csrfTokenValue = newHash;
        }
    }

    // Setup AJAX to automatically update CSRF token from response headers
    // and send it with POST requests.
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                // Check if data is FormData, if so, append. Otherwise, modify data object/string.
                if (settings.data instanceof FormData) {
                    settings.data.append(csrfName, csrfHash);
                } else {
                    // Ensure settings.data is an object or query string
                    if (typeof settings.data === 'string') {
                        // Check if csrf already there (e.g. from form serialization)
                        if (settings.data.indexOf(csrfName + '=') === -1) {
                           settings.data += (settings.data ? '&' : '') + csrfName + '=' + csrfHash;
                        }
                    } else {
                        settings.data = settings.data || {};
                        settings.data[csrfName] = csrfHash;
                    }
                }
            }
        },
        complete: function(xhr) {
            var newCsrfHash = xhr.getResponseHeader('X-CSRF-TOKEN');
            if (newCsrfHash) {
                updateGlobalCsrfHash(newCsrfHash);
            }
        }
    });

    let rewriteContext = {
        sourceInputId: '',
        originalSendButtonId: '',
        messageType: ''
    };
    let originalMessageForRewrite = '';

    const rewriteModalEl = document.getElementById('rewriteModal');
    const rewriteModalInstance = new bootstrap.Modal(rewriteModalEl);

    const privateMessageModalEl = document.getElementById('privateMessageModal');
    let privateMessageModalInstance; // Initialize when needed or check if element exists
    if (privateMessageModalEl) {
        privateMessageModalInstance = new bootstrap.Modal(privateMessageModalEl);
    }


    function fetchMessages() {
        $.ajax({
            url: (typeof window.base_url !== 'undefined' ? window.base_url : '') + '/chat/messages',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#chat-messages').empty();
                if (response && response.length > 0) {
                    response.forEach(function(msg) {
                        let messageDate = new Date(msg.created_at.replace(/-/g, '/'));
                        let formattedTime = messageDate.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });

                        let messageHtml = '<div class="message">';
                        messageHtml += '<p>' + escapeHtml(msg.message) + '</p>';
                        messageHtml += '<small>' + formattedTime + '</small>';
                        messageHtml += '</div>';
                        $('#chat-messages').append(messageHtml);
                    });
                } else {
                    $('#chat-messages').html('<p class="text-center text-muted">هنوز پیامی وجود ندارد.</p>');
                }
                $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
            },
            error: function(xhr, status, error) {
                $('#chat-messages').html('<p class="text-center text-danger">خطا در بارگذاری پیام ها.</p>');
            }
        });
    }

    function escapeHtml(unsafe) {
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    $('#send-chat-message-btn').on('click', function() {
        let messageText = $('#chat-message-input').val().trim();
        if (messageText === '') return;

        originalMessageForRewrite = messageText;
        rewriteContext.sourceInputId = '#chat-message-input';
        rewriteContext.originalSendButtonId = '#send-chat-message-btn';
        rewriteContext.messageType = 'public';

        // CSRF data will be added by ajaxSetup
        let postData = { original_message: messageText };

        $.ajax({
            url: window.base_url + '/chat/rewrite',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#original-message-display').text(originalMessageForRewrite);
                    $('#rewritten-message-edit').val(response.rewritten_text);
                    if(response.simulated) {
                        $('#rewriteModalLabel').text('بازنویسی و ارسال پیام (شبیه سازی شده)');
                    } else {
                        $('#rewriteModalLabel').text('بازنویسی و ارسال پیام');
                    }
                    rewriteModalInstance.show();
                } else {
                    alert('خطا در بازنویسی پیام: ' + (response.error || 'خطای ناشناخته'));
                }
            },
            error: function() { alert('خطای سرور در ارتباط با سرویس بازنویسی.'); }
        });
    });

    $('#send-private-message-btn').on('click', function() {
        let privateMessageText = $('#private-message-input').val().trim();
        if (privateMessageText === '') return;

        originalMessageForRewrite = privateMessageText;
        rewriteContext.sourceInputId = '#private-message-input';
        rewriteContext.originalSendButtonId = '#send-private-message-btn'; // This ID is on the button inside the private message modal
        rewriteContext.messageType = 'private';

        // CSRF data will be added by ajaxSetup
        let postData = { original_message: privateMessageText };

        $.ajax({
            url: window.base_url + '/chat/rewrite',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#original-message-display').text(originalMessageForRewrite);
                    $('#rewritten-message-edit').val(response.rewritten_text);
                     if(response.simulated) {
                        $('#rewriteModalLabel').text('بازنویسی و ارسال پیام خصوصی (شبیه سازی شده)');
                    } else {
                        $('#rewriteModalLabel').text('بازنویسی و ارسال پیام خصوصی');
                    }
                    // Hide the current modal (private message) before showing the new one (rewrite)
                    if (privateMessageModalInstance) {
                        privateMessageModalInstance.hide();
                    }
                    rewriteModalInstance.show();
                } else {
                    alert('خطا در بازنویسی پیام خصوصی: ' + (response.error || 'خطای ناشناخته'));
                }
            },
            error: function() { alert('خطای سرور در ارتباط با سرویس بازنویسی.'); }
        });
    });

    $('#save-rewritten-btn').on('click', function() {
        let rewrittenText = $('#rewritten-message-edit').val().trim();
        if (rewrittenText === '') {
            alert('پیام بازنویسی شده نمی تواند خالی باشد.');
            return;
        }

        let endpoint = '';
        let dataPayload = {};

        if (rewriteContext.messageType === 'public') {
            endpoint = window.base_url + '/chat/send';
            dataPayload = { message: rewrittenText };
        } else if (rewriteContext.messageType === 'private') {
            endpoint = window.base_url + '/chat/send_private';
            dataPayload = { private_message: rewrittenText };
        } else {
            alert('خطای داخلی: نوع پیام مشخص نیست.');
            return;
        }
        // CSRF data will be added by ajaxSetup

        $.ajax({
            url: endpoint,
            type: 'POST',
            data: dataPayload,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $(rewriteContext.sourceInputId).val('');
                    rewriteModalInstance.hide();

                    if (rewriteContext.messageType === 'public') {
                        fetchMessages();
                    } else if (rewriteContext.messageType === 'private') {
                        // The private message modal was already hidden before showing rewrite modal.
                        // If it wasn't, this would be the place:
                        // if (privateMessageModalInstance) { privateMessageModalInstance.hide(); }
                        alert('پیام خصوصی شما با موفقیت ارسال شد.');
                    }
                } else {
                    alert('خطا در ارسال پیام نهایی: ' + (response.errors ? Object.values(response.errors).join(', ') : (response.error || 'خطای ناشناخته')));
                }
            },
            error: function() { alert('خطای سرور در ارسال پیام نهایی.'); }
        });
    });

    fetchMessages();
    setInterval(fetchMessages, 5000);
});
