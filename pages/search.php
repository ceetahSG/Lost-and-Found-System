<?php
$page_title = 'Search Items';
require_once __DIR__ . '/../includes/header.php';

$category = $_GET['category'] ?? '';
$item_type = $_GET['item_type'] ?? '';
$location = $_GET['location'] ?? '';
$status = $_GET['status'] ?? 'active';

$item = new Item($conn);
$items = $item->searchItems($category, $item_type, $location, $status);

$categories = ['Electronics', 'Documents', 'Accessories', 'Clothing', 'Keys', 'Pets', 'Other'];
$item_types = ['lost', 'found'];
?>

<div class="container mx-auto px-4 py-6 md:py-12">
    <h1 class="text-2xl md:text-4xl font-bold mb-6 md:mb-8">Search Lost & Found Items</h1>

    <!-- Search Filters -->
    <div class="bg-white rounded-lg shadow-lg p-4 md:p-6 mb-8">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Category</label>
                    <select name="category" class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Type</label>
                    <select name="item_type" class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
                        <option value="">All Types</option>
                        <option value="lost" <?php echo $item_type == 'lost' ? 'selected' : ''; ?>>Lost Items</option>
                        <option value="found" <?php echo $item_type == 'found' ? 'selected' : ''; ?>>Found Items</option>
                    </select>
                </div>

                <!-- Location Filter -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Location</label>
                    <input type="text" name="location" placeholder="Search by location..."
                           class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                           value="<?php echo escape($location); ?>">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Status</label>
                    <select name="status" class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
                        <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="claimed" <?php echo $status == 'claimed' ? 'selected' : ''; ?>>Claimed</option>
                        <option value="resolved" <?php echo $status == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 md:gap-3">
                <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white px-6 py-2 md:py-3 rounded-lg hover:bg-blue-700 font-semibold transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                <a href="<?php echo BASE_URL; ?>pages/search.php" class="w-full sm:w-auto bg-gray-400 text-white px-6 py-2 md:py-3 rounded-lg hover:bg-gray-500 font-semibold text-center transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="mb-6 text-gray-600">
        <p>Found <strong><?php echo count($items); ?></strong> item(s)</p>
    </div>

    <!-- Results Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item_data): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-1">
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
                            <h3 class="font-bold text-base md:text-lg flex-1"><?php echo escape($item_data['title']); ?></h3>
                            <span class="<?php echo $item_data['item_type'] == 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-2 md:px-3 py-1 rounded text-xs font-bold whitespace-nowrap">
                                <?php echo strtoupper($item_data['item_type']); ?>
                            </span>
                        </div>

                        <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?php echo escape($item_data['description']); ?></p>

                        <div class="space-y-1 text-xs md:text-sm text-gray-600 mb-4">
                            <p><i class="fas fa-tag text-blue-600 w-4"></i> <?php echo escape($item_data['category']); ?></p>
                            <p><i class="fas fa-map-marker-alt text-red-600 w-4"></i> <?php echo escape($item_data['location']); ?></p>
                            <p><i class="fas fa-calendar text-green-600 w-4"></i> <?php echo formatDate($item_data['date_lost_found']); ?></p>
                            <p><i class="fas fa-clock text-gray-600 w-4"></i> <?php echo getRelativeTime($item_data['date_posted']); ?></p>
                        </div>

                        <div class="flex items-center mb-4 p-2 bg-gray-50 rounded gap-2">
                            <?php if (!empty($item_data['profile_picture'])): ?>
                                <img src="<?php echo BASE_URL . 'public/uploads/' . escape($item_data['profile_picture']); ?>" 
                                     alt="User" class="w-8 h-8 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    <?php echo strtoupper(substr($item_data['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <span class="text-sm font-semibold truncate"><?php echo escape($item_data['username']); ?></span>
                        </div>

                        <a href="<?php echo BASE_URL; ?>pages/item-detail.php?id=<?php echo $item_data['id']; ?>" 
                           class="w-full block bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 font-semibold transition">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full bg-gray-100 rounded-lg p-8 md:p-12 text-center">
                <i class="fas fa-search text-4xl md:text-6xl text-gray-400 mb-4 block"></i>
                <h3 class="text-xl md:text-2xl font-bold text-gray-700 mb-2">No Items Found</h3>
                <p class="text-gray-600 mb-6">Try adjusting your search filters</p>
                <a href="<?php echo BASE_URL; ?>pages/search.php" class="inline-block bg-blue-600 text-white px-6 py-2 md:py-3 rounded-lg hover:bg-blue-700 font-semibold">
                    Clear Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>