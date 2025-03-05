let map;
let service;
let infowindow;
let currentLocation; // Store the user's current location
let globalResults = []; // Store the search results
let markers = []; // Store the map markers
let arrowMarker = null; // 用于表示地址/当前位置的箭头，确保永远只有一个

function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: 42.976, lng: -78.744 }, // Default map center
    zoom: 12,
  });

  service = new google.maps.places.PlacesService(map);
  infowindow = new google.maps.InfoWindow();
}

// 点击搜索按钮
document.getElementById("searchButton").addEventListener("click", () => {
  const query = document.getElementById("searchBox").value;
  if (query) {
    searchNearbyStores(query);
  }
});

// 点击“Use my current location”按钮
document.getElementById("currentLocationButton").addEventListener("click", () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const userLocation = {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
        };

        currentLocation = userLocation; // 保存用户位置

        // 将地图中心定位到用户位置
        map.setCenter(userLocation);

        // 使用 arrowMarker 显示当前位置（确保只有一个）
        if (arrowMarker) {
          arrowMarker.setPosition(userLocation);
        } else {
          arrowMarker = new google.maps.Marker({
            position: userLocation,
            map: map,
            title: "Your Location",
            icon: {
              url: "https://upload.wikimedia.org/wikipedia/commons/8/88/Map_marker.svg",
              scaledSize: new google.maps.Size(40, 40),
            },
          });
        }

        // 搜索附近商店
        searchNearbyStores(userLocation);
      },
      () => alert("Geolocation failed."),
      { enableHighAccuracy: true, timeout: 10000 }
    );
  } else {
    alert("Geolocation is not supported by this browser.");
  }
});

/**
 * 搜索商店的主要逻辑
 * location 参数可以是字符串地址或 LatLng 对象
 */
function searchNearbyStores(location) {
  const request = {
    location: typeof location === "string" ? null : location,
    rankBy: google.maps.places.RankBy.DISTANCE,
    keyword: "grocery store",
  };

  if (typeof location === "string") {
    // 如果用户输入地址，先进行地理编码
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: location }, (results, status) => {
      if (status === "OK") {
        request.location = results[0].geometry.location;
        currentLocation = results[0].geometry.location;

        // 将地图中心定位到搜索地址
        map.setCenter(currentLocation);

        // 使用 arrowMarker 显示搜索地址位置
        if (arrowMarker) {
          arrowMarker.setPosition(currentLocation);
        } else {
          arrowMarker = new google.maps.Marker({
            position: currentLocation,
            map: map,
            title: "Your location",
            icon: {
              url: "https://upload.wikimedia.org/wikipedia/commons/8/88/Map_marker.svg",
              scaledSize: new google.maps.Size(40, 40),
            },
          });
        }

        // 搜索附近的杂货店
        service.nearbySearch(request, handleResults);
      } else {
        alert("Geocode was not successful: " + status);
      }
    });
  } else {
    // 如果已经是 LatLng 对象，直接调用 nearbySearch
    service.nearbySearch(request, handleResults);
  }
}

function handleResults(results, status) {
  if (status !== "OK" || !results) return;
  // 存储全局结果，然后过滤距离
  globalResults = results;
  filterAndDisplayResults();
}

function filterAndDisplayResults() {
  const rangeValue = parseFloat(document.getElementById("distanceRange").value);

  // 清除旧的标记和列表
  markers.forEach((marker) => marker.setMap(null));
  markers = [];
  document.getElementById("stores-list").innerHTML = "";

  // 根据滑块范围过滤结果
  const filteredResults = globalResults.filter((store) => {
    if (!store.geometry || !store.geometry.location || !currentLocation)
      return false;
    const distance =
      google.maps.geometry.spherical.computeDistanceBetween(
        currentLocation,
        store.geometry.location
      ) / 1000;
    return distance <= rangeValue;
  });

  // 显示过滤后的结果
  filteredResults.forEach((store, index) => {
    addStoreMarker(store, index + 1);
    addStoreToList(store, index + 1);
  });
}

// 监听滑块变化并更新列表
document.getElementById("distanceRange").addEventListener("input", function () {
  const value = this.value;
  document.getElementById("rangeValue").innerText = value + " km";
  if (globalResults.length > 0) {
    filterAndDisplayResults();
  }
});

/**
 * 将 Google Places 的 price_level (0-4) 转换为自定义的消费范围
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
 * 为每个商店添加标记，并在 InfoWindow 中创建信息：
 * - 路径链接
 * - 简化的营业状态（Open/Closed）
 * - 查看详情（显示详细营业时间）
 * - 访问网站
 */
function addStoreMarker(store, number) {
  if (!store.geometry || !store.geometry.location) return;

  // 计算距离（单位：km）
  let distanceText = "N/A";
  if (currentLocation) {
    const distance =
      google.maps.geometry.spherical.computeDistanceBetween(
        currentLocation,
        store.geometry.location
      ) / 1000;
    distanceText = distance.toFixed(2) + " km";
  }

  // 获取消费范围
  let perCapita =
    typeof store.price_level === "number"
      ? getSpendRange(store.price_level)
      : "N/A";

  const lat = store.geometry.location.lat();
  const lng = store.geometry.location.lng();
  // 构造 Google Maps 路径链接
  const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;

  // 营业状态（仅 nearbySearch 返回 open_now）
  let openingStatusHtml = "N/A";
  if (store.opening_hours) {
    if (store.opening_hours.open_now) {
      openingStatusHtml = `<span class="open-status-open">Open</span>`;
    } else {
      openingStatusHtml = `<span class="open-status-closed">Closed</span>`;
    }
  }

  // InfoWindow 内容
  let infoContent = `
    <b>${number}. ${store.name}</b><br>
    ${store.vicinity}<br>
    ⭐ Rating: ${store.rating || "N/A"}<br>
    💰 Average Spend: ${perCapita}<br>
    📏 Distance: ${distanceText}<br>
    🕒 Opening Hours: ${openingStatusHtml}<br>
    🚗 <a href="${directionsUrl}" target="_blank">Get directions</a><br><br>
    <button onclick="showPlaceDetails('${store.place_id}')">Merchant Information</button>
    <button onclick="visitWebsite('${store.place_id}')">Visit Website</button>
  `;

  // 在地图上创建标记
  const marker = new google.maps.Marker({
    position: store.geometry.location,
    map: map,
    label: String(number),
    title: store.name,
    icon: {
      url: "https://maps.gstatic.com/mapfiles/place_api/icons/v1/png_71/shopping-71.png",
      scaledSize: new google.maps.Size(40, 40),
    },
  });
  markers.push(marker);

  const infoWindow = new google.maps.InfoWindow({
    content: infoContent,
  });

  marker.addListener("click", () => {
    infoWindow.open(map, marker);
  });
}

/**
 * 将商店信息添加到页面下方的列表中
 */
function addStoreToList(store, number) {
  const storesList = document.getElementById("stores-list");

  // 计算距离
  let distanceText = "N/A";
  if (currentLocation) {
    const distance =
      google.maps.geometry.spherical.computeDistanceBetween(
        currentLocation,
        store.geometry.location
      ) / 1000;
    distanceText = distance.toFixed(2) + " km";
  }

  // 消费范围
  let perCapita =
    typeof store.price_level === "number"
      ? getSpendRange(store.price_level)
      : "N/A";

  // 营业状态（简化）
  let openingStatusHtml = "N/A";
  if (store.opening_hours) {
    if (store.opening_hours.open_now) {
      openingStatusHtml = `<span class="open-status-open">Open</span>`;
    } else {
      openingStatusHtml = `<span class="open-status-closed">Closed</span>`;
    }
  }

  // 路径链接
  const lat = store.geometry.location.lat();
  const lng = store.geometry.location.lng();
  const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;

  // 创建列表项内容
  const listItem = document.createElement("li");
  listItem.innerHTML = `
    <b>${number}. ${store.name}</b> - ${store.vicinity}<br>
    ⭐ ${store.rating || "N/A"} |
    💰 Average Spend: ${perCapita} |
    📏 Distance: ${distanceText} |
    🕒 ${openingStatusHtml}<br>
    📍 <a class="get-directions" href="${directionsUrl}" target="_blank">Get directions</a><br><br>
    <button onclick="showPlaceDetails('${store.place_id}')">Merchant Information</button>
    <button onclick="visitWebsite('${store.place_id}')">Visit Website</button>
  `;
  storesList.appendChild(listItem);
}

/**
 * 显示商店的详细信息，包括更完整的营业时间
 */
function showPlaceDetails(placeId) {
  service.getDetails({ placeId: placeId }, (place, status) => {
    if (status === google.maps.places.PlacesServiceStatus.OK) {
      // 获取详细营业状态信息
      const openingStatus = getDetailedOpeningStatus(place);

      let detailsContent = `
        <div>
          <h3>${place.name}</h3>
          <p><strong>Address:</strong> ${place.formatted_address || "N/A"}</p>
          <p><strong>Phone:</strong> ${place.formatted_phone_number || "N/A"}</p>
          <p><strong>Hours:</strong> ${openingStatus}</p>
        </div>
      `;

      infowindow.setContent(detailsContent);
      infowindow.setPosition(place.geometry.location);
      infowindow.open(map);
    } else {
      alert("Details not available.");
    }
  });
}

/**
 * 打开商店的官网（如果有的话）
 * 为了兼容 Safari，先立即打开一个空白窗口，再在回调中更新窗口 URL
 */
function visitWebsite(placeId) {
  // 立即打开空白窗口（必须是用户点击的直接结果）
  const newWindow = window.open('', '_blank');
  service.getDetails(
    { placeId: placeId, fields: ["website", "name"] },
    (place, status) => {
      console.log("getDetails status:", status, "place:", place);
      if (status === google.maps.places.PlacesServiceStatus.OK) {
        if (place.website) {
          newWindow.location.href = place.website;
        } else {
          newWindow.close();
          alert("Website not available for " + (place.name || "this place") + ".");
        }
      } else {
        newWindow.close();
        alert("Details not available. Status: " + status);
      }
    }
  );
}

/**
 * 返回详细营业状态，例如 "Open · Closes 12:00 AM" 或 "Closed · Opens 9:00 AM"
 */
function getDetailedOpeningStatus(place) {
  if (!place.opening_hours) {
    return "No opening hours info.";
  }

  const isOpen = place.opening_hours.isOpen();
  const periods = place.opening_hours.periods;

  if (!periods) {
    return isOpen
      ? '<span style="color: green;">Open</span>'
      : '<span style="color: red;">Closed</span>';
  }

  const today = new Date().getDay();
  const todayPeriod = periods.find((p) => p.open.day === today);

  if (!todayPeriod) {
    return isOpen
      ? '<span style="color: green;">Open</span>'
      : '<span style="color: red;">Closed</span>';
  }

  if (isOpen) {
    if (todayPeriod.close) {
      const closeTime = formatTimeString(todayPeriod.close.time);
      return `<span style="color: green;">Open</span> · Closes ${closeTime}`;
    } else {
      return `<span style="color: green;">Open</span>`;
    }
  } else {
    return `<span style="color: red;">Closed</span>`;
  }
}

/**
 * 将 "HHmm" 格式（例如 "2200"）转换为 12 小时格式（例如 "10:00 PM"）
 */
function formatTimeString(timeStr) {
  if (timeStr.length < 3) return timeStr;
  let hh = parseInt(timeStr.slice(0, 2), 10);
  let mm = parseInt(timeStr.slice(2), 10);

  const suffix = hh >= 12 ? "PM" : "AM";
  hh = hh % 12;
  if (hh === 0) hh = 12;

  const mmStr = mm.toString().padStart(2, "0");
  return `${hh}:${mmStr} ${suffix}`;
}
