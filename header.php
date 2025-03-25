<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    if (!headers_sent()) {
        header('Location: login.php');
        exit;
    } else {
        die('Session expired. Please <a href="login.php">login again</a>');
    }
}

// Set a flag to track if the header script has run
if (!defined('HEADER_SCRIPT_RUN')) {
    define('HEADER_SCRIPT_RUN', true);
    $profilePicture = $_SESSION['user']['profile_picture'] ?? 'assets/default-profile.jpg';
?>
<header class="bg-white shadow-sm fixed w-full top-0 z-50">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Added pt-4 pb-4 for more vertical padding -->
        <div class="flex justify-between items-center h-20 pt-4 pb-4">
            <!-- Logo section - adjusted alignment -->
            <div class="flex-shrink-0 flex items-center">
                <a href="dashboard.php" class="text-2xl font-bold text-green-600 hover:text-green-700 transition-colors">MealForge</a>
            </div>
            
            <!-- Navigation links -->
            <div class="hidden md:flex space-x-8 items-center">
                <a href="map.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                    <i data-lucide="map-pin" class="w-5 h-5 mr-1"></i>
                    Find a Store
                </a>
                <a href="search.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                    <i data-lucide="search" class="w-5 h-5 mr-1"></i>
                    Search Recipes
                </a>
                <a href="match.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                    <i data-lucide="heart-handshake" class="w-5 h-5 mr-1"></i>
                    Recipe Swiper
                </a>
                <a href="dashboard.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                    <i data-lucide="utensils" class="w-5 h-5 mr-1"></i>
                    My Meals
                </a>
                <a href="AboutLoggedIn.php" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                    <i data-lucide="users" class="w-5 h-5 mr-1"></i>
                    About Us
                </a>
            </div>

            <!-- Profile dropdown - adjusted alignment -->
            <div class="ml-4 relative flex items-center">
                <button id="profile-menu-button" class="flex items-center focus:outline-none">
                    <!-- Increased image size -->
                    <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200" 
                         src="<?= htmlspecialchars($profilePicture) ?>" 
                         alt="Profile picture">
                    <i data-lucide="chevron-down" class="w-4 h-4 ml-2 text-gray-600"></i>
                </button>

                <div id="profile-menu" class="hidden absolute right-0 mt-2 top-full w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-[9999]">
                    <a href="profile.php" 
                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                       <i data-lucide="user" class="w-4 h-4 mr-2"></i>
                       Profile
                    </a>
                    <a href="login.php?logout" 
                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                       <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                       Logout
                    </a>
                </div>
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
            console.log('Profile button clicked');
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
});
</script>
<?php
}
?>