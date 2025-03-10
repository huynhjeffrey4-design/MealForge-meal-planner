<?php
// Start session and require config
session_start();
require_once __DIR__ . '/controllers/control.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

// Retrieve session messages if present
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'] ?? 'info';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
} else {
    $message = '';
    $messageType = '';
}

$userId = $_SESSION['user']['id']; // Fixed user ID access

$userController = getUserController();
$user = $userController->getUserById($userId);

if (!$user) {
    header('Location: login.php');
    exit;
}

// Define dietary options
$dietaryRestrictionOptions = [
    'Vegetarian',
    'Vegan',
    'Gluten-Free',
    'Dairy-Free',
    'Nut-Free',
    'Shellfish-Free',
    'Kosher',
    'Halal',
    'Low-FODMAP',
    'Pescatarian'
];

$dietaryPreferenceOptions = [
    'Low-Carb',
    'High-Protein',
    'Keto',
    'Paleo',
    'Mediterranean',
    'Low-Fat',
    'Low-Sodium',
    'Low-Sugar',
    'Organic',
    'Whole30'
];

// Health tips
$healthTips = [
    "Get Enough Sleep! 7-8 hours per night improves focus and overall health.",
    "Stay Hydrated! Drink 8 glasses of water daily for better energy and digestion.",
    "Eat a rainbow of vegetables for diverse nutrients and antioxidants.",
    "At least 30 minutes of exercise daily keeps your heart healthy and mood elevated.",
    "Practice mindfulness to reduce stress and improve mental clarity.",
    "Include lean protein with every meal to maintain muscle mass and feel satisfied longer.",
    "Limit processed foods to reduce intake of artificial ingredients and excess sodium.",
    "Add fermented foods like yogurt or kimchi to support gut health.",
    "Take short breaks during work to rest your eyes and stretch your body.",
    "Meal prep on weekends to ensure healthy eating throughout the week."
];

// Initialize variables for form values
$firstName = $user['first_name'] ?? '';
$lastName = $user['last_name'] ?? '';

// Profile info now comes directly from user record, not a separate profile
$dob = $user['date_of_birth'] ?? '';
$gender = $user['gender'] ?? '';
$email = $user['email'] ?? '';
$phone = $user['phone_number'] ?? '';
$dietaryRestrictions = $user['dietary_restrictions'] ?? '';
$dietaryPreferences = $user['dietary_preferences'] ?? '';
$profilePicture = $user['profile_picture'] ?? '';

$defaultProfilePic = 'assets/default-profile.png';

// Format dietary restrictions and preferences as arrays for display
$restrictionsArray = !empty($dietaryRestrictions) ? explode(',', $dietaryRestrictions) : [];
$preferencesArray = !empty($dietaryPreferences) ? explode(',', $dietaryPreferences) : [];

// Get a random health tip
$randomTip = $healthTips[array_rand($healthTips)];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_all'])) {
    $userData = [
        'first_name' => $_POST['first_name'] ?? $firstName,
        'last_name' => $_POST['last_name'] ?? $lastName,
        'email' => $_POST['email'] ?? $email,
        'date_of_birth' => $_POST['dob'] ?? $dob,
        'gender' => $_POST['gender'] ?? $gender,
        'phone_number' => $_POST['phone'] ?? $phone
    ];
    
    // Handle dietary restrictions
    if (isset($_POST['dietary_restrictions'])) {
        $userData['dietary_restrictions'] = implode(',', $_POST['dietary_restrictions']);
    } else {
        $userData['dietary_restrictions'] = '';
    }
    
    // Handle dietary preferences
    if (isset($_POST['dietary_preferences'])) {
        $userData['dietary_preferences'] = implode(',', $_POST['dietary_preferences']);
    } else {
        $userData['dietary_preferences'] = '';
    }
    
    // Update user data
    $userUpdateSuccess = $userController->updateUser($userId, $userData);
    
    if ($userUpdateSuccess) {
        $_SESSION['message'] = 'Profile updated successfully';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to update profile';
        $_SESSION['message_type'] = 'error';
    }
    
    // Refresh page to show updated data
    header('Location: profile.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealForge - Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="flex flex-col md:flex-row min-h-screen">
    <!-- Sidebar -->
    <div class="w-full md:w-64 bg-primary text-white p-5 flex flex-col">
        <div class="text-2xl font-bold mb-8">MealForge</div>
        <div class="w-44 h-44 rounded-full bg-gray-100 mx-auto mb-5 overflow-hidden relative">
            <img src="<?php 
                if (!empty($profilePicture)) {
                    echo $profilePicture;
                } else {
                    echo $defaultProfilePic;
                }
            ?>" alt="Profile Picture" class="w-full h-full object-cover">
            <label for="profile_picture" class="absolute inset-x-0 bottom-0 bg-black bg-opacity-50 text-white text-center py-1 cursor-pointer opacity-0 hover:opacity-100 transition-opacity duration-300">
                <i class="fa fa-camera"></i> Change
            </label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden">
        </div>
        <div class="text-center mb-10 text-lg">
            Welcome back, <span class="font-bold"><?php echo htmlspecialchars($firstName); ?>!</span>
        </div>
        <ul class="space-y-4">
            <li><a href="#" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-home fa-fw mr-2"></i> Dashboard</a></li>
            <li><a href="#" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-users fa-fw mr-2"></i> Social</a></li>
            <li><a href="#" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-shopping-cart fa-fw mr-2"></i> Shop</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-5 overflow-y-auto bg-white">
        <div class="flex justify-between items-center mb-8 pb-2 border-b border-gray-200">
            <h1 class="text-2xl text-gray-800"><?php echo htmlspecialchars($firstName); ?>'s profile</h1>
            <a href="logout.php" class="text-primary font-bold no-underline">Log out</a>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="p-3 mb-4 rounded text-center <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Profile Information Grid -->
        <div class="grid md:grid-cols-2 gap-5 mb-8 relative">
            <a href="#" class="absolute top-0 right-0 text-primary font-bold" id="edit-toggle">Edit</a>
            
            <!-- First Name -->
            <div class="mb-5">
                <div class="text-base font-bold text-gray-800 mb-1">First name</div>
                <div class="text-lg text-gray-600"><?php echo htmlspecialchars($firstName); ?></div>
            </div>
            
            <!-- Last Name -->
            <div class="mb-5">
                <div class="text-base font-bold text-gray-800 mb-1">Last name</div>
                <div class="text-lg text-gray-600"><?php echo htmlspecialchars($lastName); ?></div>
            </div>
            
            <!-- Date of Birth -->
            <div class="mb-5">
                <div class="text-base font-bold text-gray-800 mb-1">Date of Birth</div>
                <div class="text-lg text-gray-600"><?php echo htmlspecialchars($dob); ?></div>
            </div>
            
            <!-- Gender -->
            <div class="mb-5">
                <div class="text-base font-bold text-gray-800 mb-1">Gender</div>
                <div class="text-lg text-gray-600"><?php echo htmlspecialchars($gender); ?></div>
            </div>
            
            <!-- Email -->
            <div class="mb-5">
                <div class="text-base font-bold text-gray-800 mb-1">Email (username)</div>
                <div class="text-lg text-gray-600"><?php echo htmlspecialchars($email); ?></div>
            </div>
            
            <!-- Phone Number -->
            <div class="mb-5">
                <div class="text-base font-bold text-gray-800 mb-1">Phone number</div>
                <div class="text-lg text-gray-600"><?php echo htmlspecialchars($phone); ?></div>
            </div>
        </div>

        <!-- Dietary Sections -->
        <div class="grid md:grid-cols-2 gap-5 mb-8">
            <!-- Dietary Restrictions -->
            <div class="mb-5">
                <div class="bg-primary text-white font-bold p-3 rounded text-center mb-2">Dietary Restrictions</div>
                <div class="flex flex-wrap gap-2">
                    <?php if (!empty($restrictionsArray)): ?>
                        <?php foreach ($restrictionsArray as $restriction): ?>
                            <span class="bg-green-50 text-primary px-3 py-1 rounded-full text-sm"><?php echo htmlspecialchars(trim($restriction)); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="bg-green-50 text-primary px-3 py-1 rounded-full text-sm">None specified</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Dietary Preferences -->
            <div class="mb-5">
                <div class="bg-primary text-white font-bold p-3 rounded text-center mb-2">Dietary Preferences</div>
                <div class="flex flex-wrap gap-2">
                    <?php if (!empty($preferencesArray)): ?>
                        <?php foreach ($preferencesArray as $preference): ?>
                            <span class="bg-green-50 text-primary px-3 py-1 rounded-full text-sm"><?php echo htmlspecialchars(trim($preference)); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="bg-green-50 text-primary px-3 py-1 rounded-full text-sm">None specified</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Health Tips Section -->
        <div class="mt-8 bg-gray-50 rounded-lg p-5 border-l-4 border-primary">
            <div class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                <i class="fa fa-lightbulb text-primary mr-2"></i> Daily Health Tips
            </div>
            <div class="flex items-start p-4 mb-4 bg-white rounded-lg shadow-sm" id="current-tip">
                <div class="text-2xl mr-4 text-primary">💡</div>
                <div class="flex-1 leading-relaxed text-gray-600"><?php echo htmlspecialchars($randomTip); ?></div>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex gap-2">
                    <button class="bg-primary text-white w-9 h-9 rounded-full flex items-center justify-center cursor-pointer transition hover:bg-primary-dark" id="prev-tip">
                        <i class="fa fa-chevron-left"></i>
                    </button>
                    <button class="bg-primary text-white w-9 h-9 rounded-full flex items-center justify-center cursor-pointer transition hover:bg-primary-dark" id="next-tip">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                </div>
                <div class="flex gap-1 mt-2 justify-center">
                    <?php foreach ($healthTips as $index => $tip): ?>
                        <div class="w-2 h-2 rounded-full <?php echo ($index === array_search($randomTip, $healthTips)) ? 'bg-primary' : 'bg-gray-300'; ?> transition-colors" data-index="<?php echo $index; ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="hidden" id="edit-individual-form">
            <form method="POST" action="">
                <div class="grid md:grid-cols-2 gap-5 mb-8">
                    <!-- First Name Edit -->
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">First name</div>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                    
                    <!-- Last Name Edit -->
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Last name</div>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                    
                    <!-- Date of Birth Edit -->
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Date of Birth</div>
                        <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                    
                    <!-- Gender Edit -->
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Gender</div>
                        <select name="gender" class="w-full p-2 border border-gray-300 rounded text-base">
                            <option value="">Select gender</option>
                            <option value="M" <?php echo $gender === 'M' ? 'selected' : ''; ?>>M</option>
                            <option value="F" <?php echo $gender === 'F' ? 'selected' : ''; ?>>F</option>
                            <option value="Other" <?php echo $gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <!-- Email Edit -->
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Email (username)</div>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                    
                    <!-- Phone Number Edit -->
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Phone number</div>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-5 mb-8">
                    <!-- Dietary Restrictions Edit -->
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Dietary Restrictions</div>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <?php foreach ($dietaryRestrictionOptions as $option): ?>
                                <div class="flex items-center mb-1">
                                    <input type="checkbox" id="restriction-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $option))); ?>" 
                                           name="dietary_restrictions[]" 
                                           value="<?php echo htmlspecialchars($option); ?>"
                                           <?php echo in_array($option, $restrictionsArray) ? 'checked' : ''; ?>
                                           class="mr-2">
                                    <label for="restriction-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $option))); ?>">
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Dietary Preferences Edit -->
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Dietary Preferences</div>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <?php foreach ($dietaryPreferenceOptions as $option): ?>
                                <div class="flex items-center mb-1">
                                    <input type="checkbox" id="preference-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $option))); ?>" 
                                           name="dietary_preferences[]" 
                                           value="<?php echo htmlspecialchars($option); ?>"
                                           <?php echo in_array($option, $preferencesArray) ? 'checked' : ''; ?>
                                           class="mr-2">
                                    <label for="preference-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $option))); ?>">
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="edit_all" value="1">
                
                <div class="flex justify-end gap-2 mt-5">
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded font-bold">Save</button>
                    <button type="button" class="bg-white text-primary px-6 py-2 rounded font-bold border-2 border-primary" id="cancel-edit">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form for profile picture upload -->
    <form id="profile-picture-form" method="POST" action="upload_profile.php" enctype="multipart/form-data" class="hidden">
        <input type="file" name="profile_picture" id="profile_picture_hidden">
    </form>

    <script>
        // Store all health tips in a JavaScript array
        const healthTips = <?php echo json_encode($healthTips); ?>;
        let currentTipIndex = <?php echo array_search($randomTip, $healthTips); ?>;
        
        // Profile picture upload handling
        document.getElementById('profile_picture').addEventListener('change', function() {
            // Get the file from the input
            const file = this.files[0];
            if (file) {
                // Set the file in the hidden form
                document.getElementById('profile_picture_hidden').files = this.files;
                // Submit the form
                document.getElementById('profile-picture-form').submit();
            }
        });

        // Edit toggle functionality
        document.getElementById('edit-toggle').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.grid.md\\:grid-cols-2.gap-5.mb-8.relative').classList.add('hidden');
            document.querySelector('.grid.md\\:grid-cols-2.gap-5.mb-8:not(.relative)').classList.add('hidden');
            document.getElementById('edit-individual-form').classList.remove('hidden');
            document.getElementById('edit-toggle').classList.add('hidden');
        });

        // Cancel edit functionality
        document.getElementById('cancel-edit').addEventListener('click', function() {
            document.querySelector('.grid.md\\:grid-cols-2.gap-5.mb-8.relative').classList.remove('hidden');
            document.querySelector('.grid.md\\:grid-cols-2.gap-5.mb-8:not(.relative)').classList.remove('hidden');
            document.getElementById('edit-individual-form').classList.add('hidden');
            document.getElementById('edit-toggle').classList.remove('hidden');
        });
        
        // Health tip navigation
        document.getElementById('next-tip').addEventListener('click', function() {
            currentTipIndex = (currentTipIndex + 1) % healthTips.length;
            updateTip();
        });
        
        document.getElementById('prev-tip').addEventListener('click', function() {
            currentTipIndex = (currentTipIndex - 1 + healthTips.length) % healthTips.length;
            updateTip();
        });
        
        // Update the displayed health tip
        function updateTip() {
            document.querySelector('#current-tip .flex-1').textContent = healthTips[currentTipIndex];
            
            // Update indicators
            document.querySelectorAll('.flex.gap-1.mt-2.justify-center div').forEach((indicator, index) => {
                if (index === currentTipIndex) {
                    indicator.classList.remove('bg-gray-300');
                    indicator.classList.add('bg-primary');
                } else {
                    indicator.classList.remove('bg-primary');
                    indicator.classList.add('bg-gray-300');
                }
            });
        }
        
        // Allow clicking on indicators to jump to specific tips
        document.querySelectorAll('.flex.gap-1.mt-2.justify-center div').forEach((indicator) => {
            indicator.addEventListener('click', function() {
                currentTipIndex = parseInt(this.getAttribute('data-index'));
                updateTip();
            });
        });
    </script>
</body>
</html>
