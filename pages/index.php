<?php
$page_title = 'Home';
require_once __DIR__ . '/../includes/header.php';

$item = new Item($conn);
$items = $item->searchItems('', '', '', 'active');
$lost_items = array_filter($items, fn($i) => $i['item_type'] == 'lost');
$found_items = array_filter($items, fn($i) => $i['item_type'] == 'found');
?>

<div class="container mx-auto px-4 py-6 md:py-12">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg p-6 md:p-12 mb-8 md:mb-12 text-center">
        <h1 class="text-2xl md:text-4xl font-bold mb-3 md:mb-4">Welcome to Lost & Found</h1>
        <p class="text-base md:text-xl mb-6 md:mb-8">Help reunite lost items with their owners. Report items you've lost or found.</p>
        
        <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-center">
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>pages/post-item.php" class="bg-white text-blue-600 px-6 md:px-8 py-2 md:py-3 rounded-lg font-bold hover:bg-gray-100 inline-block transition">
                    Post an Item
                </a>
                <a href="<?php echo BASE_URL; ?>pages/search.php" class="bg-blue-400 text-white px-6 md:px-8 py-2 md:py-3 rounded-lg font-bold hover:bg-blue-500 inline-block transition">
                    Search Items
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>pages/register.php" class="bg-white text-blue-600 px-6 md:px-8 py-2 md:py-3 rounded-lg font-bold hover:bg-gray-100 inline-block transition">
                    Get Started
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-8 md:mb-12">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center hover:shadow-xl transition">
            <i class="fas fa-search text-3xl md:text-4xl text-blue-600 mb-4 block"></i>
            <h3 class="text-2xl md:text-3xl font-bold"><?php echo count($items); ?></h3>
            <p class="text-gray-600">Total Items</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-lg text-center hover:shadow-xl transition">
            <i class="fas fa-frown text-3xl md:text-4xl text-red-600 mb-4 block"></i>
            <h3 class="text-2xl md:text-3xl font-bold"><?php echo count($lost_items); ?></h3>
            <p class="text-gray-600">Lost Items</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-lg text-center hover:shadow-xl transition">
            <i class="fas fa-smile text-3xl md:text-4xl text-green-600 mb-4 block"></i>
            <h3 class="text-2xl md:text-3xl font-bold"><?php echo count($found_items); ?></h3>
            <p class="text-gray-600">Found Items</p>
        </div>
    </div>

    <!-- Recent Items -->
    <div class="mb-8 md:mb-12">
        <h2 class="text-2xl md:text-3xl font-bold mb-6">Recent Items</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            <?php if (count($items) > 0): ?>
                <?php foreach (array_slice($items, 0, 6) as $item_data): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                        <?php if (!empty($item_data['image_url'])): ?>
                            <img src="<?php echo BASE_URL . 'public/uploads/' . escape($item_data['image_url']); ?>" 
                                 alt="Item" class="w-full h-40 md:h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-40 md:h-48 bg-gray-300 flex items-center justify-center">
                                <i class="fas fa-image text-4xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-4 md:p-5">
                            <div class="flex justify-between items-start mb-2 gap-2">
                                <h3 class="font-bold text-base md:text-lg"><?php echo escape($item_data['title']); ?></h3>
                                <span class="<?php echo $item_data['item_type'] == 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-2 py-1 rounded text-xs font-semibold whitespace-nowrap">
                                    <?php echo ucfirst($item_data['item_type']); ?>
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?php echo escape(substr($item_data['description'], 0, 60)) . '...'; ?></p>
                            
                            <div class="flex justify-between items-center text-xs md:text-sm text-gray-500 mb-4 flex-wrap gap-2">
                                <span><i class="fas fa-map-marker-alt mr-1"></i> <?php echo escape($item_data['location']); ?></span>
                                <span><?php echo getRelativeTime($item_data['date_posted']); ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center gap-2 mb-4">
                                <span class="text-xs bg-gray-200 px-2 py-1 rounded"><?php echo escape($item_data['category']); ?></span>
                            </div>

                            <a href="<?php echo BASE_URL; ?>pages/item-detail.php?id=<?php echo $item_data['id']; ?>" 
                               class="block w-full text-center text-blue-600 hover:text-blue-800 font-semibold py-2 hover:bg-blue-50 rounded transition">
                                View Details →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="col-span-full text-center text-gray-500 py-8">No items available yet. Be the first to post!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 md:p-8 text-center">
        <h2 class="text-xl md:text-2xl font-bold mb-4">Haven't found your item yet?</h2>
        <p class="text-gray-600 mb-6">Try using our advanced search feature to find lost or found items with specific filters.</p>
        <a href="<?php echo BASE_URL; ?>pages/search.php" class="inline-block bg-blue-600 text-white px-6 md:px-8 py-2 md:py-3 rounded-lg font-bold hover:bg-blue-700 transition">
            Go to Advanced Search
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>