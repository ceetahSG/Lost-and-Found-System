<?php
$page_title = 'Register';
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');

        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            $error = 'All fields are required';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters';
        } elseif (!validateEmail($email)) {
            $error = 'Invalid email format';
        } elseif (!validatePassword($password)) {
            $error = 'Password must be at least 8 characters with uppercase and numbers';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            $user = new User($conn);
            $result = $user->register($username, $email, $password, $full_name);
            
            if ($result['success']) {
                $success = $result['message'];
                echo '<div class="fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg">' . $success . '</div>';
                header('refresh:2;url=' . BASE_URL . 'pages/login.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<div class="container mx-auto px-4 py-12 max-w-md">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6 text-center">Create Account</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <?php csrfField(); ?>

            <div>
                <label class="block text-sm font-semibold mb-2">Full Name</label>
                <input type="text" name="full_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600" 
                       value="<?php echo escape($_POST['full_name'] ?? ''); ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($_POST['username'] ?? ''); ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($_POST['email'] ?? ''); ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       placeholder="Min 8 chars, 1 uppercase, 1 number">
                <p class="text-xs text-gray-500 mt-1">Min 8 characters, 1 uppercase letter, 1 number</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 mt-6">
                Register
            </button>
        </form>

        <p class="text-center mt-4 text-gray-600">
            Already have an account? <a href="<?php echo BASE_URL; ?>pages/login.php" class="text-blue-600 font-bold">Login</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>