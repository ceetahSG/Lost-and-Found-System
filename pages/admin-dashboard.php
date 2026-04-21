<?php
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$admin = new Admin($conn);
$stats = $admin->getStats();

$page = $_GET['page'] ?? 1;
$users = $admin->getAllUsers($page);
$total_users = $admin->getTotalUsers();
$items = $admin->getAllItems($page);
$total_items = $admin->getTotalItems();

$action = $_POST['action'] ?? '';
$result_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $result_msg = 'Invalid security token';
    } else {
        if ($action == 'ban_user') {
            $user_id = (int)$_POST['user_id'];
            $result = $admin->banUser($user_id);
            $result_msg = $result['message'];
        } elseif ($action == 'delete_item') {
            $item_id = (int)$_POST['item_id'];
            $result = $admin->deleteItem($item_id);
            $result_msg = $result['message'];
        }
    }
}
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold mb-2">Admin Dashboard</h1>
    <p class="text-gray-600 mb-8">System management and moderation tools</p>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-12">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <i class="fas fa-users text-3xl text-blue-600 mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo $stats['total_users']; ?></h3>
            <p class="text-gray-600">Total Users</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <i class="fas fa-list text-3xl text-green-600 mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo $stats['total_items']; ?></h3>
            <p class="text-gray-600">Total Items</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <i class="fas fa-check-circle text-3xl text-purple-600 mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo $stats['active_items']; ?></h3>
            <p class="text-gray-600">Active Items</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <i class="fas fa-frown text-3xl text-red-600 mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo $stats['lost_items']; ?></h3>
            <p class="text-gray-600">Lost Items</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <i class="fas fa-smile text-3xl text-yellow-600 mb-2"></i>
            <h3 class="text-2xl font-bold"><?php echo $stats['found_items']; ?></h3>
            <p class="text-gray-600">Found Items</p>
        </div>
    </div>

    <!-- Users Management -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">Users Management</h2>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Username</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Full Name</th>
                        <th class="px-4 py-2 text-left">Role</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Joined</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold"><?php echo escape($u['username']); ?></td>
                            <td class="px-4 py-3"><?php echo escape($u['email']); ?></td>
                            <td class="px-4 py-3"><?php echo escape($u['full_name']); ?></td>
                            <td class="px-4 py-3">
                                <span class="<?php echo $u['role'] == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?> px-3 py-1 rounded-full text-xs font-bold">
                                    <?php echo strtoupper($u['role']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="<?php echo $u['is_banned'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-3 py-1 rounded-full text-xs font-bold">
                                    <?php echo $u['is_banned'] ? 'BANNED' : 'ACTIVE'; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3"><?php echo formatDate($u['created_at']); ?></td>
                            <td class="px-4 py-3">
                                <?php if (!$u['is_banned']): ?>
                                    <form method="POST" style="display: inline;">
                                        <?php csrfField(); ?>
                                        <input type="hidden" name="action" value="ban_user">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-bold"
                                                onclick="return confirm('Ban this user?');">Ban</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Items Moderation -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Items Moderation</h2>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Title</th>
                        <th class="px-4 py-2 text-left">Posted By</th>
                        <th class="px-4 py-2 text-left">Type</th>
                        <th class="px-4 py-2 text-left">Category</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Posted</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($items as $i): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold"><?php echo escape($i['title']); ?></td>
                            <td class="px-4 py-3"><?php echo escape($i['username']); ?></td>
                            <td class="px-4 py-3">
                                <span class="<?php echo $i['item_type'] == 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> px-3 py-1 rounded text-xs font-bold">
                                    <?php echo strtoupper($i['item_type']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3"><?php echo escape($i['category']); ?></td>
                            <td class="px-4 py-3">
                                <span class="<?php 
                                    if ($i['status'] == 'active') echo 'bg-blue-100 text-blue-800';
                                    elseif ($i['status'] == 'claimed') echo 'bg-yellow-100 text-yellow-800';
                                    else echo 'bg-green-100 text-green-800';
                                ?> px-3 py-1 rounded text-xs font-bold">
                                    <?php echo ucfirst($i['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3"><?php echo formatDate($i['date_posted']); ?></td>
                            <td class="px-4 py-3">
                                <a href="<?php echo BASE_URL; ?>pages/item-detail.php?id=<?php echo $i['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-800 text-sm font-bold mr-2">View</a>
                                <form method="POST" style="display: inline;">
                                    <?php csrfField(); ?>
                                    <input type="hidden" name="action" value="delete_item">
                                    <input type="hidden" name="item_id" value="<?php echo $i['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-bold"
                                            onclick="return confirm('Delete this item?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>