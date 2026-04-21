<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$user = new User($conn);
$user_data = $user->getProfile($_SESSION['user_id']);

$item = new Item($conn);
$user_items = $item->getUserItems($_SESSION['user_id']);

$message = new Message($conn);
$unread_count = $message->getUnreadCount($_SESSION['user_id']);

// Filter items by status
$active_items = array_filter($user_items, fn($i) => $i['status'] == 'active');
$claimed_items = array_filter($user_items, fn($i) => $i['status'] == 'claimed');
$resolved_items = array_filter($user_items, fn($i) => $i['status'] == 'resolved');
?>

<div class="container mx-auto px-4 py-12">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-2">Welcome, <?php echo escape($user_data['full_name']); ?>!</h1>
        <p class="text-gray-600">Manage your lost and found items here</p>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <i class="fas fa-list text-3xl text-blue-600 mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo count($user_items); ?></h3>
            <p class="text-gray-600">Total Items</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <i class="fas fa-check-circle text-3xl text-green-600 mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo count($active_items); ?></h3>
            <p class="text-gray-600">Active Items</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <i class="fas fa-clock text-3xl text-yellow-600 mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo count($claimed_items); ?></h3>
            <p class="text-gray-600">Claimed Items</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <i class="fas fa-envelope text-3xl text-purple-600 mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo $unread_count; ?></h3>
            <p class="text-gray-600">Unread Messages</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-8 flex gap-4 flex-wrap">
        <a href="<?php echo BASE_URL; ?>pages/post-item.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-bold">
            <i class="fas fa-plus"></i> Post New Item
        </a>
        <a href="<?php echo BASE_URL; ?>pages/search.php" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-bold">
            <i class="fas fa-search"></i> Search Items
        </a>
        <a href="<?php echo BASE_URL; ?>pages/messages.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 font-bold">
            <i class="fas fa-envelope"></i> Messages <?php if ($unread_count > 0) echo '(' . $unread_count . ')'; ?>
        </a>
        <a href="<?php echo BASE_URL; ?>pages/profile.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 font-bold">
            <i class="fas fa-user"></i> Edit Profile
        </a>
    </div>

    <!-- My Items Tabs -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6">My Items</h2>

        <!-- Tab Navigation -->
        <div class="flex border-b mb-6 flex-wrap">
            <button class="tab-btn active px-4 py-2 font-semibold border-b-2 border-blue-600" data-tab="active-tab">
                Active (<?php echo count($active_items); ?>)
            </button>
            <button class="tab-btn px-4 py-2 font-semibold text-gray-600 hover:text-blue-600" data-tab="claimed-tab">
                Claimed (<?php echo count($claimed_items); ?>)
            </button>
            <button class="tab-btn px-4 py-2 font-semibold text-gray-600 hover:text-blue-600" data-tab="resolved-tab">
                Resolved (<?php echo count($resolved_items); ?>)
            </button>
        </div>

        <!-- Active Items Tab -->
        <div id="active-tab" class="tab-content">
            <?php if (count($active_items) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($active_items as $item_data): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-4 flex-1">
                                    <?php if (!empty($item_data['image_url'])): ?>
                                        <img src="<?php echo BASE_URL . 'public/uploads/' . escape($item_data['image_url']); ?>" 
                                             alt="Item" class="w-20 h-20 object-cover rounded">
                                    <?php else: ?>
                                        <div class="w-20 h-20 bg-gray-300 flex items-center justify-center rounded">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex-1">
                                        <h4 class="font-bold text-lg"><?php echo escape($item_data['title']); ?></h4>
                                        <p class="text-sm text-gray-600"><?php echo escape($item_data['location']); ?></p>
                                        <div class="mt-2">
                                            <span class="<?php echo $item_data['item_type'] == 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> text-xs px-2 py-1 rounded">
                                                <?php echo strtoupper($item_data['item_type']); ?>
                                            </span>
                                            <span class="bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded ml-2">
                                                <?php echo formatDate($item_data['date_posted']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>pages/item-detail.php?id=<?php echo $item_data['id']; ?>" 
                                       class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">View</a>
                                    <a href="<?php echo BASE_URL; ?>pages/edit-item.php?id=<?php echo $item_data['id']; ?>" 
                                       class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700">Edit</a>
                                    <a href="<?php echo BASE_URL; ?>api/delete-item.php?id=<?php echo $item_data['id']; ?>" 
                                       class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700"
                                       onclick="return confirm('Are you sure?');">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">No active items. <a href="<?php echo BASE_URL; ?>pages/post-item.php" class="text-blue-600">Post one now</a></p>
            <?php endif; ?>
        </div>

        <!-- Claimed Items Tab -->
        <div id="claimed-tab" class="tab-content hidden">
            <?php if (count($claimed_items) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($claimed_items as $item_data): ?>
                        <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-bold text-lg"><?php echo escape($item_data['title']); ?></h4>
                                    <p class="text-sm text-gray-600">Claimed on <?php echo formatDate($item_data['updated_at']); ?></p>
                                </div>
                                <span class="bg-yellow-200 text-yellow-800 px-3 py-1 rounded font-bold">CLAIMED</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">No claimed items</p>
            <?php endif; ?>
        </div>

        <!-- Resolved Items Tab -->
        <div id="resolved-tab" class="tab-content hidden">
            <?php if (count($resolved_items) > 0): ?>
                <div class="space-y-4">
                    <?php foreach ($resolved_items as $item_data): ?>
                        <div class="border border-green-200 bg-green-50 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-bold text-lg"><?php echo escape($item_data['title']); ?></h4>
                                    <p class="text-sm text-gray-600">Resolved on <?php echo formatDate($item_data['updated_at']); ?></p>
                                </div>
                                <span class="bg-green-200 text-green-800 px-3 py-1 rounded font-bold">RESOLVED</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">No resolved items</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active', 'border-b-2', 'border-blue-600');
            b.classList.add('text-gray-600');
        });
        this.classList.add('active', 'border-b-2', 'border-blue-600');
        this.classList.remove('text-gray-600');

        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
        document.getElementById(this.dataset.tab).classList.remove('hidden');
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>