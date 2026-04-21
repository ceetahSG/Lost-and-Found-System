<?php
$page_title = 'Messages';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$message = new Message($conn);
$inbox = $message->getInbox($_SESSION['user_id']);
$unread_count = $message->getUnreadCount($_SESSION['user_id']);

// Mark message as read if viewing conversation
if (isset($_GET['from'])) {
    $from_user = (int)$_GET['from'];
    $conversation = $message->getConversation($_SESSION['user_id'], $from_user);
    foreach ($conversation as $msg) {
        if ($msg['receiver_id'] == $_SESSION['user_id'] && !$msg['is_read']) {
            $message->markAsRead($msg['id']);
        }
    }
}

// Handle sending reply - FIXED (No Subject Required)
$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_reply'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Invalid security token';
    } else {
        $receiver_id = (int)($_POST['receiver_id'] ?? 0);
        $body = trim($_POST['body'] ?? '');
        $subject = 'Reply'; // Default subject - not shown to user

        if (empty($body)) {
            $error_msg = 'Message cannot be empty';
        } elseif ($receiver_id <= 0) {
            $error_msg = 'Invalid recipient';
        } else {
            $result = $message->sendMessage($_SESSION['user_id'], $receiver_id, $subject, $body);
            
            if ($result['success']) {
                $success_msg = 'Message sent successfully!';
                // Refresh conversation
                $conversation = $message->getConversation($_SESSION['user_id'], $receiver_id);
                // Clear the form
                $_POST['body'] = '';
            } else {
                $error_msg = $result['message'];
            }
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

$viewing_conversation = isset($_GET['from']) ? (int)$_GET['from'] : 0;
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
                            <a href="<?php echo BASE_URL; ?>pages/messages.php?from=<?php echo $conv['sender_id']; ?>"
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
                $conversation = $message->getConversation($_SESSION['user_id'], $viewing_conversation);
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
                    <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4">
                        <?php if (!empty($error_msg)): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-3 md:px-4 py-2 md:py-3 rounded text-sm">
                                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo escape($error_msg); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success_msg)): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-3 md:px-4 py-2 md:py-3 rounded text-sm">
                                <i class="fas fa-check-circle mr-2"></i> <?php echo escape($success_msg); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (count($conversation) > 0): ?>
                            <?php foreach ($conversation as $msg): ?>
                                <div class="<?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-right' : 'text-left'; ?>">
                                    <div class="inline-block max-w-xs px-4 py-2 rounded-lg <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'; ?>">
                                        <p class="text-sm"><?php echo escape($msg['body']); ?></p>
                                        <p class="text-xs opacity-70 mt-1"><?php echo formatDate($msg['created_at']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-gray-500 py-6">
                                No messages in this conversation. Start by sending a message!
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Form - NO SUBJECT FIELD -->
                    <div class="border-t p-3 md:p-4 bg-gray-50 flex-shrink-0">
                        <form method="POST" class="space-y-2">
                            <?php csrfField(); ?>
                            <input type="hidden" name="send_reply" value="1">
                            <input type="hidden" name="receiver_id" value="<?php echo $viewing_conversation; ?>">
                            
                            <div>
                                <textarea name="body" placeholder="Type your message..." required
                                          class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 h-20 md:h-24 text-sm md:text-base resize-none"><?php echo isset($_POST['body']) ? escape($_POST['body']) : ''; ?></textarea>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 text-white py-2 md:py-3 rounded-lg hover:bg-blue-700 font-bold transition text-sm md:text-base">
                                <i class="fas fa-paper-plane mr-2"></i> Send Message
                            </button>
                        </form>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>