<?php
$page_title = 'Post Item';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $category = trim($_POST['category'] ?? '');
        $item_type = trim($_POST['item_type'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $date_lost_found = trim($_POST['date_lost_found'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $features = trim($_POST['distinguishing_features'] ?? '');

        if (empty($category) || empty($item_type) || empty($title) || empty($description) || empty($location) || empty($date_lost_found)) {
            $error = 'All required fields must be filled';
        } elseif (strlen($title) < 3) {
            $error = 'Title must be at least 3 characters';
        } else {
            $item = new Item($conn);
            $result = $item->postItem($_SESSION['user_id'], $category, $item_type, $title, $description, $location, $date_lost_found, $color, $features);
            
            if ($result['success']) {
                $item_id = $result['item_id'];

                // Handle image upload
                if (!empty($_FILES['image']['name'])) {
                    $upload_result = $item->uploadImage($item_id, $_FILES['image']);
                    if (!$upload_result['success']) {
                        $error = $upload_result['message'] . ' (but item was posted)';
                    }
                }

                $success = 'Item posted successfully!';
                echo '<div class="fixed top-4 right-4 bg-green-500 text-white p-3 md:p-4 rounded-lg shadow-lg z-50 text-sm md:text-base">' . $success . '</div>';
                header('refresh:2;url=' . BASE_URL . 'pages/dashboard.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}

$categories = ['Electronics', 'Documents', 'Accessories', 'Clothing', 'Keys', 'Pets', 'Other'];
$item_types = ['lost', 'found'];
?>

<div class="container mx-auto px-4 py-6 md:py-12 max-w-2xl">
    <div class="bg-white rounded-lg shadow-lg p-4 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold mb-2">Post an Item</h1>
        <p class="text-gray-600 mb-6">Report a lost or found item to help reunite it with the owner</p>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm md:text-base">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4 md:space-y-6">
            <?php csrfField(); ?>

            <!-- Item Type and Category -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Item Type <span class="text-red-500">*</span></label>
                    <select name="item_type" required class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
                        <option value="">Select Type</option>
                        <option value="lost" <?php echo ($_POST['item_type'] ?? '') == 'lost' ? 'selected' : ''; ?>>Lost Item</option>
                        <option value="found" <?php echo ($_POST['item_type'] ?? '') == 'found' ? 'selected' : ''; ?>>Found Item</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Category <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($_POST['category'] ?? '') == $cat ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-sm font-semibold mb-2">Item Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required placeholder="e.g., Black iPhone 13"
                       class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($_POST['title'] ?? ''); ?>" maxlength="150">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-semibold mb-2">Description <span class="text-red-500">*</span></label>
                <textarea name="description" required placeholder="Provide detailed information about the item..."
                          class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 h-24 md:h-32 resize-none"><?php echo escape($_POST['description'] ?? ''); ?></textarea>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-semibold mb-2">Location <span class="text-red-500">*</span></label>
                <input type="text" name="location" required placeholder="Where was it lost/found?"
                       class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($_POST['location'] ?? ''); ?>">
            </div>

            <!-- Date -->
            <div>
                <label class="block text-sm font-semibold mb-2">Date Lost/Found <span class="text-red-500">*</span></label>
                <input type="date" name="date_lost_found" required
                       class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($_POST['date_lost_found'] ?? ''); ?>">
            </div>

            <!-- Color -->
            <div>
                <label class="block text-sm font-semibold mb-2">Color</label>
                <input type="text" name="color" placeholder="e.g., Black, Blue, Multi-colored"
                       class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($_POST['color'] ?? ''); ?>">
            </div>

            <!-- Distinguishing Features -->
            <div>
                <label class="block text-sm font-semibold mb-2">Distinguishing Features</label>
                <textarea name="distinguishing_features" placeholder="Any unique marks, scratches, serial numbers, etc."
                          class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 h-20 md:h-24 resize-none"><?php echo escape($_POST['distinguishing_features'] ?? ''); ?></textarea>
            </div>

            <!-- Image Upload -->
            <div>
                <label class="block text-sm font-semibold mb-2">Item Image</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 md:p-8 text-center cursor-pointer hover:border-blue-600 transition bg-gray-50 hover:bg-blue-50"
                     onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-cloud-upload-alt text-3xl md:text-4xl text-gray-400 mb-2 block"></i>
                    <p class="text-gray-600 text-sm md:text-base">Click to upload or drag and drop</p>
                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 5MB</p>
                </div>
                <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
                <p id="fileName" class="text-sm text-gray-600 mt-2"></p>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 md:py-3 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-check-circle mr-2"></i> Post Item
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    document.getElementById('fileName').textContent = 'Selected: ' + this.files[0].name;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>