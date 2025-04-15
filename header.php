<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page to check if it's the social page
$currentPage = basename($_SERVER['PHP_SELF']);
$allowPublicAccess = ($currentPage === 'social.php');

// Check if the user is logged in, but allow public access to social.php
if (!isset($_SESSION['user']) && !$allowPublicAccess) {
    // Only redirect if this is a direct page access, not an AJAX call
    if (!headers_sent() && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Location: login.php');
        exit;
    } elseif (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        // If headers are already sent but it's not an AJAX call
        die('Session expired. Please <a href="login.php">login again</a>');
    }
    // For AJAX calls, just continue without redirecting
}

// Set a flag to track if the header script has run
if (!defined('HEADER_SCRIPT_RUN')) {
    define('HEADER_SCRIPT_RUN', true);
    $profilePicture = isset($_SESSION['user']) ? ($_SESSION['user']['profile_picture'] ?? 'assets/default-profile.jpg') : 'assets/default-profile.jpg';

    // Get current page to highlight active nav item
    $currentPage = basename($_SERVER['PHP_SELF']);

    // Determine if user is logged in
    $isLoggedIn = isset($_SESSION['user']);

    // Determine base path based on whether we're in a subdirectory
    $basePath = '';
    if (defined('IN_SUBDIRECTORY') && IN_SUBDIRECTORY) {
        $basePath = '../';
    }
    ?>
<header class="bg-white shadow-sm fixed w-full top-0 z-50">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Added pt-4 pb-4 for more vertical padding -->
        <div class="flex justify-between items-center h-20 pt-4 pb-4">
            <!-- Logo section - adjusted alignment -->
            <div class="flex-shrink-0 flex items-center">
                <a href="<?= $isLoggedIn ? $basePath . 'dashboard.php' : $basePath . 'index.php' ?>" class="text-2xl font-bold text-green-600 hover:text-green-700 transition-colors">MealForge</a>
            </div>
            
            <!-- Navigation links -->
            <div class="hidden xl:flex space-x-8 items-center">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= $basePath ?>map.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors <?= $currentPage === 'map.php' ? 'text-green-600 font-semibold' : '' ?>">
                        <i data-lucide="map-pin" class="w-5 h-5 mr-1"></i>
                        Find a Store
                    </a>
                    <a href="<?= $basePath ?>search.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors <?= $currentPage === 'search.php' ? 'text-green-600 font-semibold' : '' ?>">
                        <i data-lucide="search" class="w-5 h-5 mr-1"></i>
                        Search Recipes
                    </a>
                    <a href="<?= $basePath ?>ingredients.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors <?= $currentPage === 'ingredients.php' ? 'text-green-600 font-semibold' : '' ?>">
    <i data-lucide="cube" class="w-5 h-5 mr-1"></i>
    My Ingredients
</a>
                    <a href="<?= $basePath ?>match.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors <?= $currentPage === 'match.php' ? 'text-green-600 font-semibold' : '' ?>">
                        <i data-lucide="heart-handshake" class="w-5 h-5 mr-1"></i>
                        Recipe Swiper
                    </a>
                    <a href="<?= $basePath ?>dashboard.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors <?= $currentPage === 'dashboard.php' ? 'text-green-600 font-semibold' : '' ?>">
                        <i data-lucide="utensils" class="w-5 h-5 mr-1"></i>
                        My Meals
                    </a>
                    <a href="<?= $basePath ?>dashboard/bookmarks.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors <?= $currentPage === 'bookmarks.php' ? 'text-green-600 font-semibold' : '' ?>">
                        <i data-lucide="bookmark" class="w-5 h-5 mr-1"></i>
                        Bookmarks
                    </a>
                <?php endif; ?>
                <a href="<?= $basePath ?>social.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors <?= $currentPage === 'social.php' ? 'text-green-600 font-semibold' : '' ?>">
                    <i data-lucide="users" class="w-5 h-5 mr-1"></i>
                    Social Feed
                </a>
                <a href="<?= $basePath ?>AboutLoggedIn.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors <?= $currentPage === 'AboutLoggedIn.php' ? 'text-green-600 font-semibold' : '' ?>">
                    <i data-lucide="info" class="w-5 h-5 mr-1"></i>
                    About Us
                </a>
            </div>

            <!-- Mobile menu button -->
            <div class="xl:hidden ml-auto">
                <button id="mobile-menu-button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i data-lucide="menu" class="h-6 w-6"></i>
                </button>
            </div>

            <!-- Profile dropdown or Login button -->
            <?php if ($isLoggedIn): ?>
                <div class="ml-4 relative flex items-center">
                    <button id="profile-menu-button" class="flex items-center focus:outline-none">
                        <!-- Increased image size -->
                        <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200" 
                             src="<?= htmlspecialchars($basePath . $profilePicture) ?>" 
                             alt="Profile picture">
                        <i data-lucide="chevron-down" class="w-4 h-4 ml-2 text-gray-600"></i>
                    </button>

                    <div id="profile-menu" class="hidden absolute right-0 mt-2 top-full w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-[9999]">
                        <a href="<?= $basePath ?>profile.php" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                           <i data-lucide="user" class="w-4 h-4 mr-2"></i>
                           Profile
                        </a>
                        <a href="<?= $basePath ?>dashboard/bookmarks.php" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                           <i data-lucide="bookmark" class="w-4 h-4 mr-2"></i>
                           My Bookmarks
                        </a>
                        <a href="<?= $basePath ?>login.php?logout" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                           <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                           Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="ml-4 flex items-center">
                    <a href="<?= $basePath ?>login.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Log in
                    </a>
                    <a href="<?= $basePath ?>signup.php" class="ml-2 text-green-600 hover:text-green-700 px-4 py-2 rounded-md text-sm font-medium">
                        Sign up
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="xl:hidden hidden pb-3">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= $basePath ?>map.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'map.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                        Find a Store
                    </a>
                    <a href="<?= $basePath ?>search.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'search.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                        Search Recipes
                    </a>
                    <a href="<?= $basePath ?>ingredients.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors <?= $currentPage === 'ingredients.php' ? 'text-green-600 font-semibold' : '' ?>">
    <i data-lucide="cube" class="w-5 h-5 mr-1"></i>
    My Ingredients
</a>
                    <a href="<?= $basePath ?>match.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'match.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                        Recipe Swiper
                    </a>
                    <a href="<?= $basePath ?>dashboard.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'dashboard.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                        My Meals
                    </a>
                    <a href="<?= $basePath ?>dashboard/bookmarks.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'bookmarks.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                        Bookmarks
                    </a>
                <?php endif; ?>
                <a href="<?= $basePath ?>social.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'social.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                    Social Feed
                </a>
                <a href="<?= $basePath ?>AboutLoggedIn.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'AboutLoggedIn.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                    About Us
                </a>
                <?php if ($isLoggedIn): ?>
                    <a href="<?= $basePath ?>profile.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                        Profile
                    </a>
                    <a href="<?= $basePath ?>login.php?logout" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="<?= $basePath ?>login.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                        Log in
                    </a>
                    <a href="<?= $basePath ?>signup.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                        Sign up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<style>
#profile-menu {
    z-index: 9999 !important;
}

#profile-menu.hidden {
    display: none !important;
}

#profile-menu:not(.hidden) {
    display: block !important;
}

/* Adjust body padding to match new header height */
body {
    padding-top: 5rem !important;
}
</style>

<script>
// Only add this script once
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Profile menu handling
    const profileButton = document.getElementById('profile-menu-button');
    const profileMenu = document.getElementById('profile-menu');
    
    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(e) {
            if (!profileMenu.contains(e.target) && !profileButton.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        });
    } else {
        console.error('Profile menu elements not found');
    }
    
    // Mobile menu handling
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
<?php
}
?>
