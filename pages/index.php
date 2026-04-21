<?php
$page_title = 'Home';
require_once __DIR__ . '/../includes/header.php';

$item = new Item($conn);
$items = $item->searchItems('', '', '', 'active');
$lost_items = array_filter($items, fn($i) => $i['item_type'] == 'lost');
$found_items = array_filter($items, fn($i) => $i['item_type'] == 'found');
?>

<div class="container mx-auto px-4 py-12">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg p-12 mb-12 text-center">
        <h1 class="text-4xl font-bold mb-4">Welcome to Lost & Found</h1>
        <p class="text-xl mb-8">Help reunite lost items with their owners. Report items you've lost or found.</p>
        
        <div class="space-x-4">
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>pages/post-item.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 inline-block">
                    Post an Item
                </a>
                <a href="<?php echo BASE_URL; ?>pages/search.php" class="bg-blue-400 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-500 inline-block">
                    Search Items
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>pages/register.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 inline-block">
                    Get Started
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <i class="fas fa-search text-4xl text-blue-600 mb-4"></i>
            <h3 class="text-2xl font-bold"><?php echo count($items); ?></h3>
            <p class="text-gray-600">Total Items</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <i class="fas fa-frown text-4xl text-red-600 mb-4"></i>
            <h3 class="text-2xl font-bold"><?php echo count($lost_items); ?></h3>
            <p class="text-gray-600">Lost Items</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <i class="fas fa-smile text-4xl text-green-600 mb-4"></i>
            <h3 class="text-2xl font-bold"><?php echo count($found_items); ?></h3>
            <p class="text-gray-600">Found Items</p>
        </div>
    </div>

    <!-- Recent Items -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <h2 class="col-span-full text-3xl font-bold mb-6">Recent Items</h2>
        
        <?php if (count($items) > 0): ?>
            <?php foreach (array_slice($items, 0, 6) as $item_data): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                    <?php if (!empty($item_data['image_url'])): ?>
                        <img src="<?php echo BASE_URL . 'public/uploads/' . escape($item_data['image_url']); ?>" 
                             alt="Item" class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-image text-4xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg"><?php echo escape($item_data['title']); ?></h3>
                            <span class="<?php echo $item_data['item_type'] == 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-3 py-1 rounded text-sm font-semibold">
                                <?php echo ucfirst($item_data['item_type']); ?>
                            </span>
                        </div>
                        
                        <p class="text-gray-600 text-sm mb-2"><?php echo escape(substr($item_data['description'], 0, 60)) . '...'; ?></p>
                        
                        <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo escape($item_data['location']); ?></span>
                            <span><?php echo getRelativeTime($item_data['date_posted']); ?></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-xs bg-gray-200 px-3 py-1 rounded"><?php echo escape($item_data['category']); ?></span>
                            <a href="<?php echo BASE_URL; ?>pages/item-detail.php?id=<?php echo $item_data['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800 font-semibold">View Details →</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-full text-center text-gray-500 py-8">No items available yet. Be the first to post!</p>
        <?php endif; ?>
    </div>

    <!-- Call to Action -->
    <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-8 mt-12 text-center">
        <h2 class="text-2xl font-bold mb-4">Haven't found your item yet?</h2>
        <p class="text-gray-600 mb-6">Try using our advanced search feature to find lost or found items with specific filters.</p>
        <a href="<?php echo BASE_URL; ?>pages/search.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700">
            Go to Advanced Search
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>