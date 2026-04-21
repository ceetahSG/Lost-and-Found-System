<?php
$page_title = 'Item Details';
require_once __DIR__ . '/../includes/header.php';

$item_id = $_GET['id'] ?? 0;

if (!$item_id) {
    header('Location: ' . BASE_URL . 'pages/search.php');
    exit;
}

$item = new Item($conn);
$item_data = $item->getItemById($item_id);

if (!$item_data) {
    header('Location: ' . BASE_URL . 'pages/search.php');
    exit;
}

$matches = $item->findMatches($item_id);

$error = '';
$success = '';

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    requireLogin();

    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');

        if (empty($subject) || empty($body)) {
            $error = 'Subject and message are required';
        } else if ($_SESSION['user_id'] == $item_data['user_id']) {
            $error = 'You cannot message yourself';
        } else {
            $message = new Message($conn);
            $result = $message->sendMessage($_SESSION['user_id'], $item_data['user_id'], $subject, $body, $item_id);
            
            if ($result['success']) {
                $success = 'Message sent successfully!';
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Item Image -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <?php if (!empty($item_data['image_url'])): ?>
                    <img src="<?php echo BASE_URL . 'public/uploads/' . escape($item_data['image_url']); ?>" 
                         alt="Item" class="w-full h-96 object-cover">
                <?php else: ?>
                    <div class="w-full h-96 bg-gray-300 flex items-center justify-center">
                        <i class="fas fa-image text-6xl text-gray-400"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Item Information -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-4xl font-bold mb-2"><?php echo escape($item_data['title']); ?></h1>
                        <span class="<?php echo $item_data['item_type'] == 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-4 py-2 rounded-full text-lg font-bold">
                            <?php echo strtoupper($item_data['item_type']); ?> ITEM
                        </span>
                    </div>
                    <span class="<?php 
                        if ($item_data['status'] == 'active') echo 'bg-blue-100 text-blue-800';
                        elseif ($item_data['status'] == 'claimed') echo 'bg-yellow-100 text-yellow-800';
                        else echo 'bg-green-100 text-green-800';
                    ?> px-4 py-2 rounded-full font-bold">
                        <?php echo ucfirst($item_data['status']); ?>
                    </span>
                </div>

                <hr class="my-4">

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-gray-600 text-sm">Category</p>
                        <p class="text-lg font-semibold"><?php echo escape($item_data['category']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Location</p>
                        <p class="text-lg font-semibold"><?php echo escape($item_data['location']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Date Lost/Found</p>
                        <p class="text-lg font-semibold"><?php echo formatDate($item_data['date_lost_found']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Posted</p>
                        <p class="text-lg font-semibold"><?php echo getRelativeTime($item_data['date_posted']); ?></p>
                    </div>
                </div>

                <?php if (!empty($item_data['color'])): ?>
                    <div class="mb-4">
                        <p class="text-gray-600 text-sm">Color</p>
                        <p class="text-lg font-semibold"><?php echo escape($item_data['color']); ?></p>
                    </div>
                <?php endif; ?>

                <div>
                    <p class="text-gray-600 text-sm mb-2">Description</p>
                    <p class="text-lg whitespace-pre-wrap"><?php echo escape($item_data['description']); ?></p>
                </div>

                <?php if (!empty($item_data['distinguishing_features'])): ?>
                    <hr class="my-4">
                    <div>
                        <p class="text-gray-600 text-sm mb-2">Distinguishing Features</p>
                        <p class="text-lg whitespace-pre-wrap"><?php echo escape($item_data['distinguishing_features']); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Suggested Matches -->
            <?php if (count($matches) > 0): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-4">
                        <i class="fas fa-lightbulb text-yellow-500"></i> Suggested Matches
                    </h2>
                    <p class="text-gray-600 mb-4">We found similar items that might match:</p>
                    
                    <div class="space-y-3">
                        <?php foreach ($matches as $match): ?>
                            <a href="<?php echo BASE_URL; ?>pages/item-detail.php?id=<?php echo $match['id']; ?>" 
                               class="block p-4 border border-gray-200 rounded-lg hover:border-blue-600 hover:bg-blue-50 transition">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold"><?php echo escape($match['title']); ?></h4>
                                        <p class="text-sm text-gray-600"><?php echo escape($match['location']); ?> • <?php echo getRelativeTime($match['date_posted']); ?></p>
                                    </div>
                                    <span class="<?php echo $match['item_type'] == 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-2 py-1 rounded text-xs font-bold">
                                        <?php echo strtoupper($match['item_type']); ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Owner Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold mb-4">Posted By</h3>

                <div class="flex items-center mb-4">
                    <?php if (!empty($item_data['profile_picture'])): ?>
                        <img src="<?php echo BASE_URL . 'public/uploads/' . escape($item_data['profile_picture']); ?>" 
                             alt="User" class="w-12 h-12 rounded-full mr-3 object-cover">
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center mr-3 font-bold">
                            <?php echo strtoupper(substr($item_data['username'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="font-bold"><?php echo escape($item_data['username']); ?></p>
                        <p class="text-sm text-gray-600">Member since <?php echo formatDate($item_data['date_posted']); ?></p>
                    </div>
                </div>

                <?php if (isLoggedIn() && $_SESSION['user_id'] != $item_data['user_id']): ?>
                    <a href="#message-form" class="w-full block bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-bold text-center mb-2">
                        <i class="fas fa-envelope"></i> Send Message
                    </a>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>pages/login.php" class="w-full block bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-bold text-center mb-2">
                        <i class="fas fa-envelope"></i> Login to Message
                    </a>
                <?php endif; ?>

                <p class="text-center text-gray-600 text-sm">
                    <i class="fas fa-phone"></i> <?php echo escape($item_data['phone'] ?? 'Not provided'); ?>
                </p>
            </div>

            <!-- Message Form -->
            <?php if (isLoggedIn() && $_SESSION['user_id'] != $item_data['user_id']): ?>
                <div class="bg-white rounded-lg shadow-lg p-6" id="message-form">
                    <h3 class="text-xl font-bold mb-4">Send Message</h3>

                    <?php if (!empty($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                            <?php echo escape($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                            <?php echo escape($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-3">
                        <?php csrfField(); ?>
                        <input type="hidden" name="send_message" value="1">

                        <div>
                            <input type="text" name="subject" placeholder="Message subject" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 text-sm">
                        </div>

                        <div>
                            <textarea name="body" placeholder="Type your message..." required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 h-24 text-sm"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-bold">
                            Send Message
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>