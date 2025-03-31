let map;
let service;
let currentLocation; // Store the user's current location
let globalResults = []; // Store the search results
let markers = []; // Store the map markers
let arrowMarker = null; // User location marker
let currentInfoWindow = null; // Track the currently open InfoWindow

function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: 42.976, lng: -78.744 }, // Default map center
    zoom: 12,
    styles: [
      {
        featureType: "poi.business",
        elementType: "labels",
        stylers: [{ visibility: "off" }],
      },
    ],
  });

  service = new google.maps.places.PlacesService(map);
  
  // Close InfoWindow when clicking on the map
  google.maps.event.addListener(map, 'click', function() {
    if (currentInfoWindow) {
      currentInfoWindow.close();
    }
  });
}

// Click search button
document.getElementById("searchButton").addEventListener("click", () => {
  const query = document.getElementById("searchBox").value;
  if (query) {
    searchNearbyStores(query);
  }
});

// Click "Use my location" button
document.getElementById("currentLocationButton").addEventListener("click", () => {
  if (navigator.geolocation) {
    // Show loading indicator
    document.getElementById("currentLocationButton").innerHTML = `
      <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    `;
    
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const userLocation = {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
        };

        currentLocation = userLocation;
        map.setCenter(userLocation);

        // Create or update user location marker
        if (arrowMarker) {
          arrowMarker.setPosition(userLocation);
        } else {
          arrowMarker = new google.maps.Marker({
            position: userLocation,
            map: map,
            title: "Your Location",
            icon: {
              path: google.maps.SymbolPath.CIRCLE,
              fillColor: "#00A651",
              fillOpacity: 1,
              strokeColor: "#FFFFFF",
              strokeWeight: 2,
              scale: 10,
            },
            zIndex: 999,
          });
        }

        // Search for nearby stores
        searchNearbyStores(userLocation);
        
        // Restore button text
        document.getElementById("currentLocationButton").innerHTML = `
          <i data-lucide="map-pin" class="w-5 h-5"></i>
          <span>Use my location</span>
        `;
        lucide.createIcons();
      },
      (error) => {
        // Restore button text
        document.getElementById("currentLocationButton").innerHTML = `
          <i data-lucide="map-pin" class="w-5 h-5"></i>
          <span>Use my location</span>
        `;
        lucide.createIcons();
        
        alert("Error getting your location: " + error.message);
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  } else {
    alert("Geolocation is not supported by this browser.");
  }
});

/**
 * Main search function
 * location parameter can be a string address or LatLng object
 */
function searchNearbyStores(location) {
  // Show loading state
  document.getElementById("stores-list").innerHTML = `
    <li class="p-6 flex justify-center">
      <div class="flex items-center">
        <svg class="animate-spin h-6 w-6 text-primary mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-600">Searching for grocery stores...</span>
      </div>
    </li>
  `;

  const request = {
    location: typeof location === "string" ? null : location,
    rankBy: google.maps.places.RankBy.DISTANCE,
    keyword: "grocery store",
  };

  if (typeof location === "string") {
    // If user entered an address, geocode it first
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: location }, (results, status) => {
      if (status === "OK") {
        request.location = results[0].geometry.location;
        currentLocation = results[0].geometry.location;

        // Center the map on the geocoded address
        map.setCenter(currentLocation);

        // Create or update location marker
        if (arrowMarker) {
          arrowMarker.setPosition(currentLocation);
        } else {
          arrowMarker = new google.maps.Marker({
            position: currentLocation,
            map: map,
            title: "Your location",
            icon: {
              path: google.maps.SymbolPath.CIRCLE,
              fillColor: "#00A651",
              fillOpacity: 1,
              strokeColor: "#FFFFFF",
              strokeWeight: 2,
              scale: 10,
            },
            zIndex: 999,
          });
        }

        // Search for nearby grocery stores
        service.nearbySearch(request, handleResults);
      } else {
        document.getElementById("stores-list").innerHTML = `
          <li class="p-6 text-center text-red-600">
            Could not find this location. Please try again with a different address.
          </li>
        `;
        console.error("Geocode was not successful: " + status);
      }
    });
  } else {
    // If already a LatLng object, search directly
    service.nearbySearch(request, handleResults);
  }
}

function handleResults(results, status) {
  if (status !== "OK" || !results) {
    document.getElementById("stores-list").innerHTML = `
      <li class="p-6 text-center text-red-600">
        No grocery stores found in this area. Try expanding your search range.
      </li>
    `;
    return;
  }
  
  // Store global results, then filter by distance
  globalResults = results;
  filterAndDisplayResults();
}

function filterAndDisplayResults() {
  const rangeValue = parseFloat(document.getElementById("distanceRange").value);
  const openNowOnly = document.getElementById("openNowFilter").checked;

  // Clear old markers and list
  markers.forEach((marker) => marker.setMap(null));
  markers = [];
  document.getElementById("stores-list").innerHTML = "";

  // Filter results by range slider and open status
  const filteredResults = globalResults.filter((store) => {
    if (!store.geometry || !store.geometry.location || !currentLocation)
      return false;
    
    // Distance filter
    const distance =
      google.maps.geometry.spherical.computeDistanceBetween(
        currentLocation,
        store.geometry.location
      ) / 1000;
    
    // Open now filter
    const openNowCondition = openNowOnly ? 
      (store.opening_hours && store.opening_hours.open_now) : 
      true;
    
    return distance <= rangeValue && openNowCondition;
  });

  // Display filtered results
  if (filteredResults.length === 0) {
    document.getElementById("stores-list").innerHTML = `
      <li class="p-6 text-center text-gray-600">
        No stores found within ${rangeValue} km${openNowOnly ? " that are currently open" : ""}.
        Try increasing your search distance or adjusting filters.
      </li>
    `;
  } else {
    filteredResults.forEach((store, index) => {
      addStoreMarker(store, index + 1);
      addStoreToList(store, index + 1);
    });
  }
}

// Listen for range slider changes and update displayed value
document.getElementById("distanceRange").addEventListener("input", function () {
  const value = this.value;
  document.getElementById("rangeValue").innerText = value + " km";
  if (globalResults.length > 0) {
    filterAndDisplayResults();
  }
});

// Listen for "Open Now" checkbox changes and update list
document.getElementById("openNowFilter").addEventListener("change", function() {
  if (globalResults.length > 0) {
    filterAndDisplayResults();
  }
});

/**
 * Convert Google Places price_level (0-4) to custom spend range
 */
function getSpendRange(priceLevel) {
  switch (priceLevel) {
    case 0:
      return "Free or N/A";
    case 1:
      return "$0 - $50";
    case 2:
      return "$50 - $100";
    case 3:
      return "$100 - $200";
    case 4:
      return "$200+";
    default:
      return "N/A";
  }
}

/**
 * Add map marker for each store
 */
function addStoreMarker(store, number) {
  if (!store.geometry || !store.geometry.location) return;

  // Calculate distance in km
  let distanceText = "N/A";
  if (currentLocation) {
    const distance =
      google.maps.geometry.spherical.computeDistanceBetween(
        currentLocation,
        store.geometry.location
      ) / 1000;
    distanceText = distance.toFixed(2) + " km";
  }

  // Get spend range
  let perCapita =
    typeof store.price_level === "number"
      ? getSpendRange(store.price_level)
      : "N/A";

  const lat = store.geometry.location.lat();
  const lng = store.geometry.location.lng();
  // Create Google Maps directions link
  const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;

  // Opening hours status
  let openingStatusHtml = "N/A";
  if (store.opening_hours) {
    if (store.opening_hours.open_now) {
      openingStatusHtml = `<span style="color: #00A651; font-weight: bold;">Open</span>`;
    } else {
      openingStatusHtml = `<span style="color: #EF4444; font-weight: bold;">Closed</span>`;
    }
  }

  // InfoWindow content - simplified version
  let infoContent = `
    <div style="padding: 12px; max-width: 300px; font-family: Arial, sans-serif;">
      <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">${number}. ${store.name}</h3>
      <p style="margin: 5px 0; color: #4B5563; font-size: 14px;">${store.vicinity}</p>
      <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
        <div style="display: flex; align-items: center; gap: 4px;">
          <span style="color: #F59E0B;">★</span>
          <span style="font-size: 14px;">${store.rating || "N/A"}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 4px;">
          <span style="color: #10B981;">💰</span>
          <span style="font-size: 14px;">${perCapita}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 4px;">
          <span style="color: #6366F1;">📏</span>
          <span style="font-size: 14px;">${distanceText}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 4px;">
          <span style="color: #F97316;">🕒</span>
          <span style="font-size: 14px;">${openingStatusHtml}</span>
        </div>
      </div>
      <div style="margin-top: 12px;">
        <a href="${directionsUrl}" target="_blank" style="display: inline-block; background-color: #00A651; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 14px;">
          Get Directions
        </a>
      </div>
    </div>
  `;

  // Create marker on map
  const marker = new google.maps.Marker({
    position: store.geometry.location,
    map: map,
    label: {
      text: String(number),
      color: "#FFFFFF",
      fontWeight: "bold"
    },
    title: store.name,
    icon: {
      url: "https://maps.gstatic.com/mapfiles/place_api/icons/v1/png_71/shopping-71.png",
      scaledSize: new google.maps.Size(32, 32),
      labelOrigin: new google.maps.Point(16, 16)
    },
    animation: google.maps.Animation.DROP
  });
  markers.push(marker);

  marker.addListener("click", () => {
    // Close any existing InfoWindow
    if (currentInfoWindow) {
      currentInfoWindow.close();
    }
    
    // Create a new InfoWindow
    const markerInfoWindow = new google.maps.InfoWindow({
      content: infoContent,
      maxWidth: 320
    });
    
    // Set as current and open
    currentInfoWindow = markerInfoWindow;
    markerInfoWindow.open(map, marker);
  });
}

/**
 * Add store information to the list below the map
 */
function addStoreToList(store, number) {
  const storesList = document.getElementById("stores-list");

  // Calculate distance
  let distanceText = "N/A";
  let distanceValue = null;
  if (currentLocation) {
    distanceValue = google.maps.geometry.spherical.computeDistanceBetween(
      currentLocation,
      store.geometry.location
    ) / 1000;
    distanceText = distanceValue.toFixed(2) + " km";
  }

  // Spend range
  let perCapita =
    typeof store.price_level === "number"
      ? getSpendRange(store.price_level)
      : "N/A";

  // Opening status
  let openingStatusHtml = "";
  if (store.opening_hours) {
    if (store.opening_hours.open_now) {
      openingStatusHtml = `<span class="text-green-600 font-medium">Open</span>`;
    } else {
      openingStatusHtml = `<span class="text-red-600 font-medium">Closed</span>`;
    }
  }

  // Directions link
  const lat = store.geometry.location.lat();
  const lng = store.geometry.location.lng();
  const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;

  // Create list item
  const listItem = document.createElement("li");
  listItem.className = "p-4 hover:bg-gray-50 transition cursor-pointer";

  // Create click handler for the list item
  listItem.addEventListener("click", () => {
    // Close any existing infowindow
    if (currentInfoWindow) {
      currentInfoWindow.close();
    }
    
    // Pan to this marker and trigger click
    map.panTo(store.geometry.location);
    google.maps.event.trigger(markers[number - 1], "click");
  });

  listItem.innerHTML = `
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex-grow">
        <div class="flex items-center gap-2">
          <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary text-white text-sm font-bold">${number}</span>
          <h3 class="font-semibold text-gray-900">${store.name}</h3>
          ${store.rating ? 
            `<div class="flex items-center text-yellow-500 ml-2">
              <i data-lucide="star" class="w-4 h-4 fill-current"></i>
              <span class="ml-1 text-sm font-medium">${store.rating}</span>
            </div>` : ''}
        </div>
        <p class="text-gray-600 text-sm mt-1">${store.vicinity}</p>
        <div class="flex flex-wrap gap-4 mt-2">
          <div class="flex items-center text-sm text-gray-600">
            <i data-lucide="wallet" class="w-4 h-4 mr-1"></i> ${perCapita}
          </div>
          <div class="flex items-center text-sm text-gray-600">
            <i data-lucide="map-pin" class="w-4 h-4 mr-1"></i> ${distanceText}
          </div>
          <div class="flex items-center text-sm">
            <i data-lucide="clock" class="w-4 h-4 mr-1"></i> ${openingStatusHtml}
          </div>
        </div>
      </div>
      <div class="mt-3 md:mt-0">
        <a href="${directionsUrl}" target="_blank" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark transition">
          <i data-lucide="navigation" class="w-4 h-4 mr-1"></i>
          Directions
        </a>
      </div>
    </div>
  `;
  storesList.appendChild(listItem);
  
  // Re-initialize Lucide icons in the new content
  lucide.createIcons();
}
