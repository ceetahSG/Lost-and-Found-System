<?php
require_once __DIR__ . '/functions.php';
?>
<nav class="bg-blue-600 text-white shadow-lg">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <a href="<?php echo BASE_URL; ?>pages/index.php" class="text-2xl font-bold">
            🔍 Lost & Found
        </a>
        
        <div class="flex space-x-6">
            <a href="<?php echo BASE_URL; ?>pages/index.php" class="hover:text-blue-200">Home</a>
            <a href="<?php echo BASE_URL; ?>pages/search.php" class="hover:text-blue-200">Search</a>
            
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>pages/post-item.php" class="hover:text-blue-200">Post Item</a>
                <a href="<?php echo BASE_URL; ?>pages/messages.php" class="hover:text-blue-200">Messages</a>
                <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="hover:text-blue-200">Dashboard</a>
                
                <?php if (isAdmin()): ?>
                    <a href="<?php echo BASE_URL; ?>pages/admin-dashboard.php" class="hover:text-blue-200 font-bold">Admin</a>
                <?php endif; ?>
                
                <div class="relative group">
                    <button class="hover:text-blue-200"><?php echo escape($_SESSION['username']); ?> ▼</button>
                    <div class="hidden group-hover:block bg-blue-700 absolute right-0 mt-2 w-40 rounded shadow-lg">
                        <a href="<?php echo BASE_URL; ?>pages/profile.php" class="block px-4 py-2 hover:bg-blue-800">Profile</a>
                        <a href="<?php echo BASE_URL; ?>pages/logout.php" class="block px-4 py-2 hover:bg-blue-800 border-t">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>pages/login.php" class="hover:text-blue-200">Login</a>
                <a href="<?php echo BASE_URL; ?>pages/register.php" class="hover:text-blue-200">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>