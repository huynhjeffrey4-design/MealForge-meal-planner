<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    // Only redirect if this is a direct page access, not an AJAX call
    if (!headers_sent() && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Location: login.php');
        exit;
    } else if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        // If headers are already sent but it's not an AJAX call
        die('Session expired. Please <a href="login.php">login again</a>');
    }
    // For AJAX calls, just continue without redirecting
}

// Set a flag to track if the header script has run
if (!defined('HEADER_SCRIPT_RUN')) {
    define('HEADER_SCRIPT_RUN', true);
    $profilePicture = isset($_SESSION['user']['profile_picture']) ? $_SESSION['user']['profile_picture'] : 'assets/default-profile.jpg';
    
    // Get current page to highlight active nav item
    $currentPage = basename($_SERVER['PHP_SELF']);
?>
<header class="bg-white shadow-sm fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center h-16">
            <!-- Logo on left -->
            <div class="flex-shrink-0 mr-10">
                <a href="dashboard.php" class="text-2xl font-bold text-green-600 hover:text-green-700 transition-colors">MealForge</a>
            </div>
            
            <!-- Navigation links in center -->
            <div class="hidden md:flex flex-grow justify-center space-x-8">
                <a href="map.php" class="inline-flex items-center text-sm <?= $currentPage === 'map.php' ? 'text-green-600 font-semibold' : 'text-gray-700 hover:text-green-600' ?> transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Find a Store
                </a>
                <a href="search.php" class="inline-flex items-center text-sm <?= $currentPage === 'search.php' ? 'text-green-600 font-semibold' : 'text-gray-700 hover:text-green-600' ?> transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                    Search Recipes
                </a>
                <a href="match.php" class="inline-flex items-center text-sm <?= $currentPage === 'match.php' ? 'text-green-600 font-semibold' : 'text-gray-700 hover:text-green-600' ?> transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>
                    Recipe Swiper
                </a>
                <a href="dashboard.php" class="inline-flex items-center text-sm <?= $currentPage === 'dashboard.php' ? 'text-green-600 font-semibold' : 'text-gray-700 hover:text-green-600' ?> transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path><path d="M7 2v20"></path><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path></svg>
                    My Meals
                </a>
                <a href="social.php" class="inline-flex items-center text-sm <?= $currentPage === 'social.php' ? 'text-green-600 font-semibold' : 'text-gray-700 hover:text-green-600' ?> transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    Social Feed
                </a>
                <a href="AboutLoggedIn.php" class="inline-flex items-center text-sm <?= $currentPage === 'AboutLoggedIn.php' ? 'text-green-600 font-semibold' : 'text-gray-700 hover:text-green-600' ?> transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    About Us
                </a>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden ml-auto">
                <button id="mobile-menu-button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>

            <!-- Profile dropdown on right -->
            <div class="hidden md:flex items-center ml-10">
                <div class="relative">
                    <button id="profile-menu-button" class="flex items-center focus:outline-none">
                        <img class="h-8 w-8 rounded-full object-cover border-2 border-gray-200" 
                             src="<?= htmlspecialchars($profilePicture) ?>" 
                             alt="Profile picture">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>

                    <div id="profile-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-50">
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Profile
                        </a>
                        <a href="login.php?logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="md:hidden hidden pb-3">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="map.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'map.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                    Find a Store
                </a>
                <a href="search.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'search.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                    Search Recipes
                </a>
                <a href="match.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'match.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                    Recipe Swiper
                </a>
                <a href="dashboard.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'dashboard.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                    My Meals
                </a>
                <a href="social.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'social.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                    Social Feed
                </a>
                <a href="AboutLoggedIn.php" class="block px-3 py-2 rounded-md <?= $currentPage === 'AboutLoggedIn.php' ? 'bg-green-100 text-green-600 font-medium' : 'text-gray-700 hover:bg-gray-100' ?>">
                    About Us
                </a>
                <a href="profile.php" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                    Profile
                </a>
                <a href="login.php?logout" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>

<style>
/* Adjust body padding to match header height */
body {
    padding-top: 4rem !important;
}
</style>

<script>
// Only add this script once
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
<?php
}
?>