<?php
$page_title = 'Messages';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$message = new Message($conn);
$inbox = $message->getInbox($_SESSION['user_id']);
$unread_count = $message->getUnreadCount($_SESSION['user_id']);

$viewing_conversation = isset($_GET['from']) ? (int)$_GET['from'] : 0;
$conversation = [];

if ($viewing_conversation > 0) {
    $conversation = $message->getConversation($_SESSION['user_id'], $viewing_conversation);
    foreach ($conversation as $msg) {
        if ($msg['receiver_id'] == $_SESSION['user_id'] && !$msg['is_read']) {
            $message->markAsRead($msg['id']);
        }
    }
}

// Get unique conversations
$conversations = [];
foreach ($inbox as $msg) {
    $key = $msg['sender_id'];
    if (!isset($conversations[$key])) {
        $conversations[$key] = $msg;
    }
}
?>

<div class="container mx-auto px-4 py-6 md:py-12">
    <h1 class="text-2xl md:text-4xl font-bold mb-6 md:mb-8">Messages</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-8">
        <!-- Conversations List -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-blue-600 text-white px-4 md:px-6 py-3 md:py-4">
                    <h2 class="text-lg md:text-xl font-bold">
                        <i class="fas fa-inbox mr-2"></i> Conversations
                        <?php if ($unread_count > 0): ?>
                            <span class="bg-red-500 text-xs px-2 py-1 rounded-full ml-2"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </h2>
                </div>

                <div class="divide-y max-h-96 overflow-y-auto">
                    <?php if (count($conversations) > 0): ?>
                        <?php foreach ($conversations as $conv): ?>
                            <a href="<?php echo BASE_URL; ?>messages.php?from=<?php echo $conv['sender_id']; ?>"
                               class="block p-4 hover:bg-gray-50 transition <?php echo $viewing_conversation == $conv['sender_id'] ? 'bg-blue-50 border-l-4 border-blue-600' : ''; ?>">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($conv['profile_picture'])): ?>
                                        <img src="<?php echo BASE_URL . 'public/uploads/' . escape($conv['profile_picture']); ?>"
                                             alt="User" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                                            <?php echo strtoupper(substr($conv['username'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-sm"><?php echo escape($conv['username']); ?></p>
                                        <p class="text-xs text-gray-600 truncate"><?php echo escape(substr($conv['subject'], 0, 30)); ?></p>
                                    </div>
                                    <?php if (!$conv['is_read']): ?>
                                        <div class="w-3 h-3 rounded-full bg-blue-600 flex-shrink-0"></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-envelope text-4xl mb-3 block"></i>
                            <p>No conversations yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Conversation View -->
        <div class="lg:col-span-2">
            <?php if ($viewing_conversation > 0): ?>
                <?php
                $other_user = new User($conn);
                $other_user_data = $other_user->getProfile($viewing_conversation);
                ?>

                <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col h-screen md:max-h-96 lg:max-h-screen">
                    <!-- Header -->
                    <div class="bg-blue-600 text-white px-4 md:px-6 py-3 md:py-4 border-b flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($other_user_data['profile_picture'])): ?>
                                <img src="<?php echo BASE_URL . 'public/uploads/' . escape($other_user_data['profile_picture']); ?>"
                                     alt="User" class="w-10 h-10 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-white text-blue-600 flex items-center justify-center font-bold text-sm">
                                    <?php echo strtoupper(substr($other_user_data['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="font-bold text-sm md:text-base"><?php echo escape($other_user_data['username']); ?></p>
                                <p class="text-xs md:text-sm text-blue-200"><?php echo escape($other_user_data['email']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div id="chat-box" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4">
                        <?php if (count($conversation) > 0): ?>
                            <?php foreach ($conversation as $msg): ?>
                                <div class="<?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-right' : 'text-left'; ?>" data-msg-id="<?php echo $msg['id']; ?>">
                                    <div class="inline-block max-w-xs px-4 py-2 rounded-lg <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'; ?>">
                                        <p class="text-sm"><?php echo escape($msg['body']); ?></p>
                                        <p class="text-xs opacity-70 mt-1"><?php echo formatDate($msg['created_at']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-gray-500 py-6">
                                No messages yet. Start the conversation!
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Form - AJAX -->
                    <div class="border-t p-3 md:p-4 bg-gray-50 flex-shrink-0">
                        <div id="msg-error" class="hidden bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded text-sm mb-2"></div>
                        <div class="flex gap-2">
                            <textarea id="msg-input" placeholder="Type your message..."
                                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 h-12 text-sm resize-none"></textarea>
                            <button id="msg-send" class="bg-blue-600 text-white px-4 rounded-lg hover:bg-blue-700 font-bold transition text-sm flex-shrink-0">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="bg-white rounded-lg shadow-lg p-6 md:p-12 text-center h-96 md:h-full flex items-center justify-center">
                    <div>
                        <i class="fas fa-envelope text-6xl md:text-7xl text-gray-300 mb-4 block"></i>
                        <h3 class="text-xl md:text-2xl font-bold text-gray-600 mb-2">Select a Conversation</h3>
                        <p class="text-sm md:text-base text-gray-500">Choose a conversation from the list to start messaging</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
<?php if ($viewing_conversation > 0): ?>
const OTHER_USER = <?php echo $viewing_conversation; ?>;
const CURRENT_USER = <?php echo $_SESSION['user_id']; ?>;
const BASE = '<?php echo BASE_URL; ?>';
const CSRF = '<?php echo generateCSRFToken(); ?>';

let lastId = <?php echo !empty($conversation) ? max(array_column($conversation, 'id')) : 0; ?>;

const chatBox = document.getElementById('chat-box');

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function renderMessage(msg) {
    const isMine = msg.sender_id == CURRENT_USER;
    const div = document.createElement('div');
    div.className = isMine ? 'text-right' : 'text-left';
    div.dataset.msgId = msg.id;
    div.innerHTML = `
        <div class="inline-block max-w-xs px-4 py-2 rounded-lg ${isMine ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'}">
            <p class="text-sm">${escapeHtml(msg.body)}</p>
            <p class="text-xs opacity-70 mt-1">${msg.created_at}</p>
        </div>`;
    return div;
}

function scrollBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}
scrollBottom();

async function pollMessages() {
    try {
        const res = await fetch(`${BASE}get-messages.php?with=${OTHER_USER}&last_id=${lastId}`);
        const msgs = await res.json();
        if (msgs.length > 0) {
            msgs.forEach(msg => {
                chatBox.appendChild(renderMessage(msg));
                lastId = Math.max(lastId, parseInt(msg.id));
            });
            scrollBottom();
        }
    } catch(e) {}
}

setInterval(pollMessages, 3000);

async function sendMessage() {
    const input = document.getElementById('msg-input');
    const body = input.value.trim();
    if (!body) return;

    const btn = document.getElementById('msg-send');
    btn.disabled = true;

    const form = new FormData();
    form.append('receiver_id', OTHER_USER);
    form.append('body', body);
    form.append('csrf_token', CSRF);

    try {
        const res = await fetch(`${BASE}send-message.php`, { method: 'POST', body: form });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            await pollMessages();
        } else {
            const err = document.getElementById('msg-error');
            err.textContent = data.message;
            err.classList.remove('hidden');
            setTimeout(() => err.classList.add('hidden'), 3000);
        }
    } catch(e) {}

    btn.disabled = false;
    input.focus();
}

document.getElementById('msg-send').addEventListener('click', sendMessage);
document.getElementById('msg-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>