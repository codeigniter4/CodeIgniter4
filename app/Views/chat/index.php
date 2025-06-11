<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چت روم رایگان</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Tahoma', sans-serif; background-color: #f8f9fa; }
        #chat-container { max-width: 800px; margin: 30px auto; background-color: #fff; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        #chat-header { background-color: #0d6efd; color: white; padding: 15px; border-top-left-radius: 8px; border-top-right-radius: 8px; }
        #chat-header h4 { margin: 0; }
        #chat-messages { height: 400px; overflow-y: auto; padding: 15px; border-bottom: 1px solid #ddd; }
        .message { margin-bottom: 15px; }
        .message p {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 15px;
            display: inline-block;
            max-width: 70%;
            margin-bottom: 2px;
        }
        .message small { font-size: 0.75em; color: #6c757d; margin-right: 5px; } /* For timestamp */
        #chat-input-area { padding: 15px; display: flex; }
        #chat-input-area textarea { flex-grow: 1; margin-left: 10px; } /* Corrected margin-right to margin-left for RTL */
        #admin-message-btn-container { padding: 0 15px 15px; text-align: left; }
    </style>
</head>
<body>
    <div id="chat-container">
        <div id="chat-header">
            <h4>چت روم عمومی ناشناس</h4>
        </div>
        <div id="chat-messages">
            <!-- Messages will be loaded here by AJAX -->
            <p class="text-center text-muted">در حال بارگذاری پیام ها...</p>
        </div>
        <div id="chat-input-area">
            <textarea class="form-control" id="chat-message-input" rows="2" placeholder="پیام خود را بنویسید..."></textarea>
            <button class="btn btn-primary" id="send-chat-message-btn">ارسال</button>
        </div>
        <div id="admin-message-btn-container">
             <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#privateMessageModal">
                ارسال پیام خصوصی به مدیر
            </button>
        </div>
    </div>

    <!-- Private Message Modal -->
    <div class="modal fade" id="privateMessageModal" tabindex="-1" aria-labelledby="privateMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privateMessageModalLabel">ارسال پیام خصوصی به مدیر</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <form id="private-message-form">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="private-message-input" class="form-label">پیام شما:</label>
                            <textarea class="form-control" id="private-message-input" name="private_message" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="send-private-message-btn">ارسال پیام</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Temporary OpenAI Rewrite Modal (Structure only, functionality in later step) -->
    <div class="modal fade" id="rewriteModal" tabindex="-1" aria-labelledby="rewriteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rewriteModalLabel">بازنویسی و ارسال پیام</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">پیام اصلی شما:</label>
                        <p id="original-message-display" class="border p-2 rounded bg-light"></p>
                    </div>
                    <div class="mb-3">
                        <label for="rewritten-message-edit" class="form-label">پیام بازنویسی شده (می توانید ویرایش کنید):</label>
                        <textarea class="form-control" id="rewritten-message-edit" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="save-rewritten-btn">ارسال پیام بازنویسی شده</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Pass base_url and initial CSRF details to JavaScript
        window.base_url = '<?= rtrim(base_url(), '/') ?>'; // Ensure no trailing slash
        window.csrfTokenName = '<?= csrf_token() ?>'; // CSRF token name
        window.csrfTokenValue = '<?= csrf_hash() ?>';   // CSRF hash
    </script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/chat_app.js') ?>?v=<?= time() ?>"></script> <!-- Added version query for cache busting -->
</body>
</html>
