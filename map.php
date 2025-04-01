<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MealForge - Find Grocery Stores</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        DEFAULT: '#00A651',
                        dark: '#008c44'
                    }
                }
            }
        }
    }
  </script>
</head>
<body class="bg-gray-50">
  <?php include 'header.php'; ?>
  
  <main class="max-w-6xl mx-auto px-4 py-8 mt-16">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Find a Grocery Store Near You</h2>

    <!-- Search Container -->
    <div class="flex flex-col md:flex-row gap-3 mb-6">
      <div class="relative flex-grow">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
          <i data-lucide="search" class="w-5 h-5"></i>
        </span>
        <input 
          type="text" 
          id="searchBox" 
          placeholder="Enter your address, city or zip" 
          class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary transition"
        />
      </div>
      <button 
        id="searchButton" 
        class="bg-primary hover:bg-primary-dark text-white py-3 px-6 rounded-lg transition flex items-center justify-center"
      >
        <i data-lucide="arrow-right" class="w-5 h-5"></i>
      </button>
      <button 
        id="currentLocationButton" 
        class="bg-primary hover:bg-primary-dark text-white py-3 px-6 rounded-lg transition flex items-center justify-center whitespace-nowrap gap-2"
      >
        <i data-lucide="map-pin" class="w-5 h-5"></i>
        <span>Use my location</span>
      </button>
    </div>

    <!-- Range slider to filter stores by distance -->
    <div class="flex items-center gap-4 mb-6">
      <label for="distanceRange" class="font-medium text-gray-700">Distance Range:</label>
      <input
        type="range"
        id="distanceRange"
        min="0"
        max="10"
        step="0.1"
        value="5"
        class="w-full h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
      />
      <span id="rangeValue" class="font-medium text-primary min-w-[4rem] text-center">5 km</span>
    </div>
    
    <!-- Open Now filter -->
    <div class="flex items-center mb-6">
      <label class="flex items-center cursor-pointer">
        <div class="relative inline-block w-10 mr-2 align-middle select-none">
          <input 
            type="checkbox" 
            id="openNowFilter" 
            class="checkbox opacity-0 absolute h-6 w-10 cursor-pointer z-10"
          />
          <div class="toggle-bg bg-gray-300 h-6 w-10 rounded-full"></div>
          <style>
            .toggle-bg:after {
              content: '';
              position: absolute;
              top: 0.125rem;
              left: 0.125rem;
              width: 1.25rem;
              height: 1.25rem;
              background-color: white;
              border-radius: 50%;
              transition: transform 0.3s ease;
            }
            .checkbox:checked + .toggle-bg {
              background-color: #00A651;
            }
            .checkbox:checked + .toggle-bg:after {
              transform: translateX(1rem);
            }
          </style>
        </div>
        <span class="text-gray-700">Only show stores that are open now</span>
      </label>
    </div>

    <!-- Map Container -->
    <div id="map" class="w-full h-96 rounded-xl overflow-hidden shadow-md mb-8 border border-gray-200"></div>

    <!-- Stores List -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
      <h3 class="text-xl font-semibold p-4 border-b border-gray-200 bg-gray-50">Nearby Grocery Stores</h3>
      <ul id="stores-list" class="divide-y divide-gray-200"></ul>
    </div>
  </main>

  <script>
    // Initialize Lucide icons
    lucide.createIcons();
  </script>
  <script src="map.js"></script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCy_Mlx0IaBsrSlxmJw8f6luCc09nNcLYs&callback=initMap&libraries=places,geometry"></script>
</body>
</html>
