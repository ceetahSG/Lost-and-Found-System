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

<div class="container mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold mb-8">Messages</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Conversations List -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-blue-600 text-white px-6 py-4">
                    <h2 class="text-xl font-bold">
                        <i class="fas fa-inbox"></i> Conversations
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
                                <div class="flex items-center">
                                    <?php if (!empty($conv['profile_picture'])): ?>
                                        <img src="<?php echo BASE_URL . 'public/uploads/' . escape($conv['profile_picture']); ?>" 
                                             alt="User" class="w-10 h-10 rounded-full mr-3 object-cover">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center mr-3 text-sm font-bold">
                                            <?php echo strtoupper(substr($conv['username'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-sm"><?php echo escape($conv['username']); ?></p>
                                        <p class="text-xs text-gray-600 truncate"><?php echo escape(substr($conv['subject'], 0, 30)); ?></p>
                                    </div>
                                    <?php if (!$conv['is_read']): ?>
                                        <div class="w-3 h-3 rounded-full bg-blue-600"></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-envelope text-4xl mb-2"></i>
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

                <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col h-96">
                    <!-- Header -->
                    <div class="bg-blue-600 text-white px-6 py-4 border-b">
                        <div class="flex items-center">
                            <?php if (!empty($other_user_data['profile_picture'])): ?>
                                <img src="<?php echo BASE_URL . 'public/uploads/' . escape($other_user_data['profile_picture']); ?>" 
                                     alt="User" class="w-10 h-10 rounded-full mr-3 object-cover">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-white text-blue-600 flex items-center justify-center mr-3 font-bold">
                                    <?php echo strtoupper(substr($other_user_data['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="font-bold"><?php echo escape($other_user_data['username']); ?></p>
                                <p class="text-sm text-blue-200"><?php echo escape($other_user_data['email']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        <?php if (count($conversation) > 0): ?>
                            <?php foreach ($conversation as $msg): ?>
                                <div class="<?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'text-right' : 'text-left'; ?>">
                                    <div class="inline-block max-w-xs px-4 py-2 rounded-lg <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'; ?>">
                                        <p class="font-bold text-sm"><?php echo escape($msg['subject']); ?></p>
                                        <p class="mt-1"><?php echo escape($msg['body']); ?></p>
                                        <p class="text-xs opacity-70 mt-1"><?php echo formatDate($msg['created_at']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-gray-500 py-6">
                                No messages in this conversation
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Reply Form -->
                    <div class="border-t p-4 bg-gray-50">
                        <form method="POST" action="<?php echo BASE_URL; ?>pages/item-detail.php" class="flex gap-2">
                            <?php csrfField(); ?>
                            <input type="text" name="subject" placeholder="Subject..." required
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 text-sm">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-semibold">
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-lg p-12 text-center h-96 flex items-center justify-center">
                    <div>
                        <i class="fas fa-envelope text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-600 mb-2">Select a Conversation</h3>
                        <p class="text-gray-500">Choose a conversation from the list to start messaging</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>