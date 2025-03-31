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
  <script src="https://unpkg.com/lucide@latest"></script>
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
  <style>
    body {
      padding-top: 5rem; /* Match the padding in header.php */
      font-family: Arial, sans-serif;
    }
    
    main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    
    h2 {
      font-size: 24px;
      margin-bottom: 20px;
      text-align: center;
      color: #333;
    }
    
    .search-container {
      display: flex;
      margin-bottom: 20px;
      gap: 10px;
    }
    
    #searchBox {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    
    #searchButton, #currentLocationButton {
      padding: 10px 15px;
      background-color: #00A651;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    
    #currentLocationButton {
      white-space: nowrap;
    }
    
    .range-container {
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    #distanceRange {
      flex: 1;
    }
    
    #map {
      height: 400px;
      width: 100%;
      margin-bottom: 20px;
      border-radius: 8px;
      border: 1px solid #ddd;
    }
    
    #stores-list {
      list-style: none;
      padding: 0;
    }
    
    #stores-list li {
      padding: 15px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    #stores-list li:hover {
      background-color: #f9f9f9;
    }
    
    .store-name {
      font-weight: bold;
      color: #333;
    }
    
    .store-address {
      color: #666;
      font-size: 14px;
    }
    
    .store-distance {
      color: #00A651;
      font-weight: bold;
    }
    
    @media (max-width: 768px) {
      .search-container {
        flex-direction: column;
      }
      
      #currentLocationButton {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>
  
  <main>
    <h2>Find a Grocery Store Near You</h2>

    <div class="search-container">
      <input type="text" id="searchBox" placeholder="Enter your address, city or zip" />
      <button id="searchButton">⬅</button>
      <button id="currentLocationButton">📍 Use my current location</button>
    </div>

    <!-- Range slider to filter stores by distance -->
    <div class="range-container">
      <label for="distanceRange">Distance Range (km):</label>
      <input
        type="range"
        id="distanceRange"
        min="0"
        max="10"
        step="0.1"
        value="5"
      />
      <span id="rangeValue">5 km</span>
    </div>
    
    <!-- Open Now filter -->
    <div class="filter-container" style="margin-bottom: 20px; display: flex; align-items: center;">
      <input type="checkbox" id="openNowFilter" style="margin-right: 5px;">
      <label for="openNowFilter">Show only stores that are open now</label>
    </div>

    <div id="map"></div>

    <ul id="stores-list"></ul>
  </main>

  <script src="map.js"></script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCy_Mlx0IaBsrSlxmJw8f6luCc09nNcLYs&callback=initMap&libraries=places,geometry"></script>
</body>
</html>
