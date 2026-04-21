<?php
$page_title = 'Edit Item';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$item_id = $_GET['id'] ?? 0;

if (!$item_id) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit;
}

$item = new Item($conn);
$item_data = $item->getItemById($item_id);

if (!$item_data || $item_data['user_id'] != $_SESSION['user_id']) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $status = trim($_POST['status'] ?? '');

        if (empty($title) || empty($description) || empty($location) || empty($status)) {
            $error = 'All fields are required';
        } else {
            $result = $item->updateItem($item_id, $title, $description, $location, $status);
            if ($result['success']) {
                $success = $result['message'];
                $item_data = $item->getItemById($item_id);
            } else {
                $error = $result['message'];
            }
        }
    }
}

$statuses = ['active', 'claimed', 'resolved'];
?>

<div class="container mx-auto px-4 py-12 max-w-2xl">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6">Edit Item</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo escape($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <?php csrfField(); ?>

            <div>
                <label class="block text-sm font-semibold mb-2">Title</label>
                <input type="text" name="title" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($item_data['title']); ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Description</label>
                <textarea name="description" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 h-32"><?php echo escape($item_data['description']); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Location</label>
                <input type="text" name="location" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($item_data['location']); ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Status</label>
                <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $item_data['status'] == $st ? 'selected' : ''; ?>>
                            <?php echo ucfirst($st); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700">
                    Save Changes
                </button>
                <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="flex-1 bg-gray-400 text-white font-bold py-2 rounded-lg hover:bg-gray-500 text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>