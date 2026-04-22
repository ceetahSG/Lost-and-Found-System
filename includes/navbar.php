<?php
require_once __DIR__ . '/functions.php';
?>
<nav class="bg-blue-600 text-white shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3 md:py-4">
            <!-- Logo -->
            <a href="<?php echo BASE_URL; ?>index.php" class="text-lg md:text-2xl font-bold whitespace-nowrap flex items-center gap-2">
                <i class="fas fa-search"></i>
                <span class="hidden sm:inline">Lost & Found</span>
            </a>
            
            <!-- Mobile Menu Button -->
            <button id="mobileMenuBtn" class="md:hidden bg-blue-700 hover:bg-blue-800 px-3 py-2 rounded text-sm font-bold transition">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Desktop Navigation Menu -->
            <div class="hidden md:flex gap-4 lg:gap-6 items-center flex-wrap justify-end">
                <a href="<?php echo BASE_URL; ?>index.php" class="hover:text-blue-200 transition font-medium">Home</a>
                <a href="<?php echo BASE_URL; ?>search.php" class="hover:text-blue-200 transition font-medium">Search</a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>post-item.php" class="hover:text-blue-200 transition font-medium">Post Item</a>
                    <a href="<?php echo BASE_URL; ?>messages.php" class="hover:text-blue-200 transition font-medium">Messages</a>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="hover:text-blue-200 transition font-medium">Dashboard</a>
                    
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>admin-dashboard.php" class="hover:text-yellow-200 transition font-bold text-yellow-300">Admin</a>
                    <?php endif; ?>
                    
                    <!-- User Dropdown -->
                    <div class="relative group">
                        <button class="hover:text-blue-200 transition font-medium flex items-center gap-2">
                            <i class="fas fa-user"></i>
                            <span><?php echo escape($_SESSION['username']); ?></span>
                        </button>
                        <div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-blue-700 rounded shadow-lg z-50">
                            <a href="<?php echo BASE_URL; ?>profile.php" class="block px-4 py-3 hover:bg-blue-800 transition rounded-t">
                                <i class="fas fa-cog mr-2"></i> Profile
                            </a>
                            <a href="<?php echo BASE_URL; ?>logout.php" class="block px-4 py-3 hover:bg-blue-800 transition border-t border-blue-600 rounded-b">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php" class="hover:text-blue-200 transition font-medium">Login</a>
                    <a href="<?php echo BASE_URL; ?>register.php" class="bg-blue-500 hover:bg-blue-400 transition px-4 py-2 rounded font-medium">Register</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="navMenu" class="hidden md:hidden pb-4 space-y-2 border-t border-blue-500 pt-4">
            <a href="<?php echo BASE_URL; ?>index.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition">Home</a>
            <a href="<?php echo BASE_URL; ?>search.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition">Search</a>
            
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>post-item.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition">Post Item</a>
                <a href="<?php echo BASE_URL; ?>messages.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition">Messages</a>
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition">Dashboard</a>
                
                <?php if (isAdmin()): ?>
                    <a href="<?php echo BASE_URL; ?>admin-dashboard.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition font-bold text-yellow-300">Admin Panel</a>
                <?php endif; ?>
                
                <div class="border-t border-blue-500 pt-2 mt-2">
                    <a href="<?php echo BASE_URL; ?>profile.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition">
                        <i class="fas fa-user mr-2"></i><?php echo escape($_SESSION['username']); ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>logout.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition text-red-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>login.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition">Login</a>
                <a href="<?php echo BASE_URL; ?>register.php" class="block px-4 py-2 hover:bg-blue-700 rounded transition bg-blue-500">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navMenu = document.getElementById('navMenu');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('hidden');
        });
        
        // Close menu when a link is clicked
        document.querySelectorAll('#navMenu a').forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.add('hidden');
            });
        });
    }
});
</script>