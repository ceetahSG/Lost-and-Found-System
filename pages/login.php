<?php
$page_title = 'Login';
require_once __DIR__ . '/../includes/header.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Email and password are required';
        } else {
            $user = new User($conn);
            $result = $user->login($email, $password);
            
            if ($result['success']) {
                header('Location: ' . BASE_URL . 'pages/dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<div class="container mx-auto px-4 py-12 max-w-md">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-6 text-center">Login</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle"></i> <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <?php csrfField(); ?>

            <div>
                <label class="block text-sm font-semibold mb-2">Email Address</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600"
                       value="<?php echo escape($_POST['email'] ?? ''); ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 mt-6">
                Login
            </button>
        </form>

        <p class="text-center mt-4 text-gray-600">
            Don't have an account? <a href="<?php echo BASE_URL; ?>pages/register.php" class="text-blue-600 font-bold">Register</a>
        </p>

        <!-- Demo Credentials -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6 text-sm">
            <p class="font-semibold mb-2">Demo Credentials:</p>
            <p>Email: demo@example.com</p>
            <p>Password: Demo1234</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>