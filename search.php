<?php
// Assume these are retrieved from a RecipeController
$recipes = [
    [
        'id' => 1,
        'title' => 'Mediterranean Quinoa Bowl',
        'tags' => ['Vegetarian', 'High Protein', 'Mediterranean'],
        'prep_time' => 35,
        'price' => 3.50,
        'difficulty' => 'Medium',
        'image' => 'quinoa_bowl.jpg'
    ],
    [
        'id' => 2,
        'title' => 'Mediterranean Quinoa Bowl',
        'tags' => ['Vegetarian', 'High Protein', 'Mediterranean'],
        'prep_time' => 35,
        'price' => 3.50,
        'difficulty' => 'Medium',
        'image' => null // No image for second recipe to match design
    ]
];

// Sample filter options
$dietaryOptions = ['Vegetarian', 'Vegan', 'Gluten-Free', 'Dairy-Free', 'Keto'];

// Get filter values from URL parameters if present
$selectedDietary = $_GET['dietary'] ?? [];
$maxPrepTime = $_GET['max_prep_time'] ?? 60;
$mealType = $_GET['meal_type'] ?? 'Any';

// Calculate recipes count
$recipesCount = count($recipes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <!-- Back to Dashboard Button -->
        <a href="dashboard.php" class="flex items-center text-gray-600 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>

        <div class="flex flex-col md:flex-row gap-6">
            <!-- Left Column: Filters -->
            <div class="w-full md:w-1/3 bg-white rounded-lg shadow p-6">
                <!-- Search Bar -->
                <div class="relative mb-6">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <form action="" method="GET">
                        <input type="text" name="search" class="bg-gray-100 text-gray-700 border-0 rounded-lg pl-10 p-2.5 w-full" placeholder="Search recipes...">
                    </form>
                </div>

                <!-- Filters Section -->
                <div class="mb-4 flex justify-between items-center">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <h2 class="text-lg font-semibold">Filters</h2>
                    </div>
                    <a href="?reset=1" class="text-sm text-red-500">Reset All</a>
                </div>

                <form action="" method="GET" class="space-y-6">
                    <!-- Dietary Preferences -->
                    <div class="border-b pb-4">
                        <div class="flex justify-between items-center mb-2 cursor-pointer" onclick="toggleSection('dietary')">
                            <h3 class="font-medium">Dietary Preferences</h3>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" id="dietary-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div id="dietary-options" class="mt-2 space-y-2">
                            <?php foreach ($dietaryOptions as $option): ?>
                            <div class="flex items-center">
                                <input type="checkbox" name="dietary[]" value="<?= htmlspecialchars($option) ?>" id="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $option))) ?>" class="h-4 w-4 text-red-500 rounded"
                                <?= in_array($option, $selectedDietary) ? 'checked' : '' ?>>
                                <label for="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $option))) ?>" class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($option) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Maximum Preparation Time -->
                    <div class="border-b pb-4">
                        <div class="flex justify-between items-center mb-2 cursor-pointer" onclick="toggleSection('prep-time')">
                            <h3 class="font-medium">Maximum Preparation Time</h3>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" id="prep-time-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div id="prep-time-options" class="mt-2">
                            <input type="range" name="max_prep_time" min="5" max="120" step="5" value="<?= $maxPrepTime ?>" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" oninput="updatePrepTimeValue(this.value)">
                            <p class="text-sm text-gray-600 mt-1"><span id="prep-time-value"><?= $maxPrepTime ?></span> minutes</p>
                        </div>
                    </div>

                    <!-- Meal Type -->
                    <div class="border-b pb-4">
                        <div class="flex justify-between items-center mb-2 cursor-pointer" onclick="toggleSection('meal-type')">
                            <h3 class="font-medium">Meal Type</h3>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform transition-transform" id="meal-type-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div id="meal-type-options" class="mt-2">
                            <select name="meal_type" class="w-full p-2 bg-gray-100 rounded-lg text-gray-700">
                                <option value="Any" <?= $mealType === 'Any' ? 'selected' : '' ?>>Any</option>
                                <option value="Breakfast" <?= $mealType === 'Breakfast' ? 'selected' : '' ?>>Breakfast</option>
                                <option value="Lunch" <?= $mealType === 'Lunch' ? 'selected' : '' ?>>Lunch</option>
                                <option value="Dinner" <?= $mealType === 'Dinner' ? 'selected' : '' ?>>Dinner</option>
                                <option value="Snack" <?= $mealType === 'Snack' ? 'selected' : '' ?>>Snack</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button - Hidden but included for form submission without JS -->
                    <button type="submit" class="hidden">Apply Filters</button>
                </form>
            </div>

            <!-- Right Column: Recipe Results -->
            <div class="w-full md:w-2/3">
                <p class="text-sm text-gray-600 mb-4">Showing <?= $recipesCount ?> recipes that match your preferences</p>
                
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ($recipes as $recipe): ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            <?php if ($recipe['image']): ?>
                            <div class="w-full md:w-2/5">
                                <img src="images/<?= htmlspecialchars($recipe['image']) ?>" alt="<?= htmlspecialchars($recipe['title']) ?>" class="w-full h-48 object-cover">
                            </div>
                            <?php endif; ?>
                            <div class="p-4 flex-1">
                                <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($recipe['title']) ?></h3>
                                
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <?php foreach ($recipe['tags'] as $tag): ?>
                                    <span class="inline-block px-2 py-1 text-xs rounded
                                        <?php if ($tag === 'Vegetarian'): ?>
                                            bg-green-100 text-green-800
                                        <?php elseif ($tag === 'High Protein'): ?>
                                            bg-blue-100 text-blue-800
                                        <?php elseif ($tag === 'Mediterranean'): ?>
                                            bg-purple-100 text-purple-800
                                        <?php else: ?>
                                            bg-gray-100 text-gray-800
                                        <?php endif; ?>
                                    "><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm text-gray-600"><?= $recipe['prep_time'] ?> mins</span>
                                    </div>
                                    
                                    <div class="flex items-center mx-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm text-gray-600">$<?= number_format($recipe['price'], 2) ?></span>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span class="text-sm text-gray-600"><?= htmlspecialchars($recipe['difficulty']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Minimal JavaScript for interactive elements
        function toggleSection(id) {
            const element = document.getElementById(`${id}-options`);
            const icon = document.getElementById(`${id}-icon`);
            
            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
                icon.classList.remove('rotate-180');
            } else {
                element.classList.add('hidden');
                icon.classList.add('rotate-180');
            }
        }
        
        function updatePrepTimeValue(value) {
            document.getElementById('prep-time-value').textContent = value;
        }
        
        // Auto-submit form when filters change (to minimize JavaScript)
        document.querySelectorAll('input[type=checkbox], input[type=range], select').forEach(element => {
            element.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    </script>
</body>
</html>
