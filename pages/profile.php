<?php
$page_title = 'Profile';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$user = new User($conn);
$user_data = $user->getProfile($_SESSION['user_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($full_name)) {
            $error = 'Full name is required';
        } else {
            $result = $user->updateProfile($_SESSION['user_id'], $full_name, $phone, $address);
            if ($result['success']) {
                $success = $result['message'];
                $user_data = $user->getProfile($_SESSION['user_id']);
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Handle profile picture upload
if (!empty($_FILES['profile_picture']['name'])) {
    $upload_result = $user->uploadProfilePicture($_SESSION['user_id'], $_FILES['profile_picture']);
    if ($upload_result['success']) {
        $success = 'Profile picture updated!';
        $user_data['profile_picture'] = $upload_result['path'];
    } else {
        $error = $upload_result['message'];
    }
}
?>

<div class="container mx-auto px-4 py-12 max-w-2xl">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6">Edit Profile</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle"></i> <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle"></i> <?php echo escape($success); ?>
            </div>
        <?php endif; ?>

        <div class="mb-8">
            <h3 class="text-xl font-bold mb-4">Profile Picture</h3>
            <div class="flex items-center gap-6">
                <?php if (!empty($user_data['profile_picture'])): ?>
                    <img src="<?php echo BASE_URL . 'public/uploads/' . escape($user_data['profile_picture']); ?>" 
                         alt="Profile" class="w-24 h-24 rounded-full object-cover">
                <?php else: ?>
                    <div class="w-24 h-24 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl font-bold">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" onsubmit="this.submit(); location.reload();">
                    <input type="file" name="profile_picture" accept="image/*" required id="picInput" style="display: none;">
                    <button type="button" onclick="document.getElementById('picInput').click();" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Change Picture
                    </button>
                </form>
            </div>
        </div>

        <hr class="my-8">

        <form method="POST" class="space-y-4">
            <?php csrfField(); ?>

            <div>
                <label class="block text-sm font-semibold mb-2">Username</label>
                <input type="text" value="<?php echo escape($user_data['username']); ?>" disabled
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                <p class="text-xs text-gray-500 mt-1">Username cannot be changed</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" value="<?php echo escape($user_data['email']); ?>" disabled
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Full Name</label>
                <input type="text" name="full_name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($user_data['full_name']); ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Phone</label>
                <input type="tel" name="phone"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($user_data['phone'] ?? ''); ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Address</label>
                <textarea name="address"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 h-24"><?php echo escape($user_data['address'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Member Since</label>
                <input type="text" value="<?php echo formatDate($user_data['created_at']); ?>" disabled
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 mt-6">
                Save Changes
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>