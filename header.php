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
        <div class="flex justify-between items-center h-16">
            <!-- Logo section -->
            <div class="flex-shrink-0 flex items-center">
                <a href="<?= $isLoggedIn ? $basePath . 'dashboard.php' : $basePath . 'index.php' ?>" class="text-xl font-bold text-green-600 hover:text-green-700 transition-colors">MealForge</a>
            </div>
            
            <!-- Primary Navigation - Reorganized into categories -->
            <div class="hidden lg:flex items-center">
                <!-- Main navigation categories -->
                <div class="flex space-x-6">
                    <?php if ($isLoggedIn): ?>
                        <!-- Discover Category -->
                        <div class="relative group">
                            <button class="flex items-center text-gray-700 hover:text-green-600 transition-colors px-2 py-1 rounded-md group-hover:bg-gray-50">
                                <i data-lucide="compass" aria-hidden="true" class="w-4 h-4 mr-1"></i>
                                <span class="text-sm">Discover</span>
                                <i data-lucide="chevron-down" aria-hidden="true" class="w-3 h-3 ml-1"></i>
                            </button>
                            <div class="absolute left-0 mt-1 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 hidden group-hover:block z-50">
                                <a href="<?= $basePath ?>map.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'map.php' ? 'text-green-600 font-medium' : '' ?>">
                                    <i data-lucide="map-pin" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                                    Find a Store
                                </a>
                                <a href="<?= $basePath ?>search.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'search.php' ? 'text-green-600 font-medium' : '' ?>">
                                    <i data-lucide="search" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                                    Search Recipes
                                </a>
                                <a href="<?= $basePath ?>match.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'match.php' ? 'text-green-600 font-medium' : '' ?>">
                                    <i data-lucide="heart-handshake" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                                    Recipe Swiper
                                </a>
                            </div>
                        </div>
                        
                        <!-- My Kitchen Category -->
                        <div class="relative group">
                            <button class="flex items-center text-gray-700 hover:text-green-600 transition-colors px-2 py-1 rounded-md group-hover:bg-gray-50">
                                <i data-lucide="utensils" aria-hidden="true" class="w-4 h-4 mr-1"></i>
                                <span class="text-sm">My Kitchen</span>
                                <i data-lucide="chevron-down" aria-hidden="true" class="w-3 h-3 ml-1"></i>
                            </button>
                            <div class="absolute left-0 mt-1 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 hidden group-hover:block z-50">
                                <a href="<?= $basePath ?>ingredients.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'ingredients.php' ? 'text-green-600 font-medium' : '' ?>">
                                    <i data-lucide="cube" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                                    My Ingredients
                                </a>
                                <a href="<?= $basePath ?>dashboard.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'dashboard.php' ? 'text-green-600 font-medium' : '' ?>">
                                    <i data-lucide="utensils" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                                    My Meals
                                </a>
                                <a href="<?= $basePath ?>dashboard/bookmarks.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'bookmarks.php' ? 'text-green-600 font-medium' : '' ?>">
                                    <i data-lucide="bookmark" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                                    Bookmarks
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Community Category (available to all) -->
                    <div class="relative group">
                        <button class="flex items-center text-gray-700 hover:text-green-600 transition-colors px-2 py-1 rounded-md group-hover:bg-gray-50">
                            <i data-lucide="users" aria-hidden="true" class="w-4 h-4 mr-1"></i>
                            <span class="text-sm">Community</span>
                            <i data-lucide="chevron-down" aria-hidden="true" class="w-3 h-3 ml-1"></i>
                        </button>
                        <div class="absolute left-0 mt-1 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 hidden group-hover:block z-50">
                            <a href="<?= $basePath ?>social.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'social.php' ? 'text-green-600 font-medium' : '' ?>">
                                <i data-lucide="users" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                                Social Feed
                            </a>
                            <a href="<?= $basePath ?>AboutLoggedIn.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'AboutLoggedIn.php' ? 'text-green-600 font-medium' : '' ?>">
                                <i data-lucide="info" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                                About Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="lg:hidden ml-auto">
                <button id="mobile-menu-button" class="text-gray-500 hover:text-gray-700 focus:outline-none" aria-label="Toggle mobile navigation menu">
                    <i data-lucide="menu" aria-hidden="true" class="h-6 w-6"></i>
                </button>
            </div>

            <!-- Profile dropdown or Login button -->
            <?php if ($isLoggedIn): ?>
                <div class="ml-4 relative flex items-center">
                    <button id="profile-menu-button" class="flex items-center focus:outline-none" aria-label="User profile menu button">
                        <img class="h-8 w-8 rounded-full object-cover border-2 border-gray-200" 
                             src="<?= htmlspecialchars($basePath . $profilePicture) ?>" 
                             alt="Your profile picture">
                        <i data-lucide="chevron-down" aria-hidden="true" class="w-4 h-4 ml-1 text-gray-600"></i>
                    </button>

                    <div id="profile-menu" class="hidden absolute right-0 mt-2 top-full w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-[9999]">
                        <a href="<?= $basePath ?>profile.php" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                           <i data-lucide="user" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                           Profile
                        </a>
                        <a href="<?= $basePath ?>dashboard/bookmarks.php" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                           <i data-lucide="bookmark" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                           My Bookmarks
                        </a>
                        <a href="<?= $basePath ?>login.php?logout" 
                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                           <i data-lucide="log-out" aria-hidden="true" class="w-4 h-4 mr-2"></i>
                           Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="ml-4 flex items-center">
                    <a href="<?= $basePath ?>login.php" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md text-sm font-medium">
                        Log in
                    </a>
                    <a href="<?= $basePath ?>signup.php" class="ml-2 text-green-600 hover:text-green-700 px-3 py-1.5 rounded-md text-sm font-medium">
                        Sign up
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile menu (redesigned for accordion-style categories) -->
        <div id="mobile-menu" class="lg:hidden hidden pb-3">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <?php if ($isLoggedIn): ?>
                    <!-- Mobile Discover Category -->
                    <div class="mobile-dropdown">
                        <button class="mobile-dropdown-toggle flex justify-between items-center w-full px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                            <span class="flex items-center">
                                <i data-lucide="compass" aria-hidden="true" class="w-5 h-5 mr-2"></i>
                                Discover
                            </span>
                            <i data-lucide="chevron-down" aria-hidden="true" class="w-4 h-4 mobile-dropdown-icon"></i>
                        </button>
                        <div class="mobile-dropdown-content hidden px-4 pt-1 pb-2">
                            <a href="<?= $basePath ?>map.php" class="block py-2 pl-8 pr-3 rounded-md <?= $currentPage === 'map.php' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                <i data-lucide="map-pin" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> Find a Store
                            </a>
                            <a href="<?= $basePath ?>search.php" class="block py-2 pl-8 pr-3 rounded-md <?= $currentPage === 'search.php' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                <i data-lucide="search" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> Search Recipes
                            </a>
                            <a href="<?= $basePath ?>match.php" class="block py-2 pl-8 pr-3 rounded-md <?= $currentPage === 'match.php' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                <i data-lucide="heart-handshake" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> Recipe Swiper
                            </a>
                        </div>
                    </div>
                    
                    <!-- Mobile My Kitchen Category -->
                    <div class="mobile-dropdown">
                        <button class="mobile-dropdown-toggle flex justify-between items-center w-full px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                            <span class="flex items-center">
                                <i data-lucide="utensils" aria-hidden="true" class="w-5 h-5 mr-2"></i>
                                My Kitchen
                            </span>
                            <i data-lucide="chevron-down" aria-hidden="true" class="w-4 h-4 mobile-dropdown-icon"></i>
                        </button>
                        <div class="mobile-dropdown-content hidden px-4 pt-1 pb-2">
                            <a href="<?= $basePath ?>ingredients.php" class="block py-2 pl-8 pr-3 rounded-md <?= $currentPage === 'ingredients.php' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                <i data-lucide="cube" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> My Ingredients
                            </a>
                            <a href="<?= $basePath ?>dashboard.php" class="block py-2 pl-8 pr-3 rounded-md <?= $currentPage === 'dashboard.php' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                <i data-lucide="utensils" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> My Meals
                            </a>
                            <a href="<?= $basePath ?>dashboard/bookmarks.php" class="block py-2 pl-8 pr-3 rounded-md <?= $currentPage === 'bookmarks.php' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                                <i data-lucide="bookmark" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> Bookmarks
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Mobile Community Category -->
                <div class="mobile-dropdown">
                    <button class="mobile-dropdown-toggle flex justify-between items-center w-full px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                        <span class="flex items-center">
                            <i data-lucide="users" aria-hidden="true" class="w-5 h-5 mr-2"></i>
                            Community
                        </span>
                        <i data-lucide="chevron-down" aria-hidden="true" class="w-4 h-4 mobile-dropdown-icon"></i>
                    </button>
                    <div class="mobile-dropdown-content hidden px-4 pt-1 pb-2">
                        <a href="<?= $basePath ?>social.php" class="block py-2 pl-8 pr-3 rounded-md <?= $currentPage === 'social.php' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                            <i data-lucide="users" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> Social Feed
                        </a>
                        <a href="<?= $basePath ?>AboutLoggedIn.php" class="block py-2 pl-8 pr-3 rounded-md <?= $currentPage === 'AboutLoggedIn.php' ? 'text-green-600 font-medium' : 'text-gray-600' ?>">
                            <i data-lucide="info" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> About Us
                        </a>
                    </div>
                </div>
                
                <?php if ($isLoggedIn): ?>
                    <a href="<?= $basePath ?>profile.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                        <i data-lucide="user" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> Profile
                    </a>
                    <a href="<?= $basePath ?>login.php?logout" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                        <i data-lucide="log-out" aria-hidden="true" class="w-4 h-4 inline mr-2"></i> Logout
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
    padding-top: 4rem !important;
}

/* Styling for dropdown hover effects */
.group:hover .group-hover\:block {
    display: block;
}

/* Transition for mobile dropdown icons */
.mobile-dropdown-icon {
    transition: transform 0.2s ease-in-out;
}
.mobile-dropdown.open .mobile-dropdown-icon {
    transform: rotate(180deg);
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
    }
    
    // Mobile menu handling
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Mobile dropdowns handling
    const mobileDropdownToggles = document.querySelectorAll('.mobile-dropdown-toggle');
    
    mobileDropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const dropdown = this.closest('.mobile-dropdown');
            const content = dropdown.querySelector('.mobile-dropdown-content');
            
            // Toggle the dropdown
            dropdown.classList.toggle('open');
            content.classList.toggle('hidden');
        });
    });
});
</script>
<?php
}
?>
