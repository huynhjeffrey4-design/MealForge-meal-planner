<?php
session_start();
require_once __DIR__ . '/controllers/user.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'] ?? 'info';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
} else {
    $message = '';
    $messageType = '';
}

$userId = $_SESSION['user']['id'];
$userController = getUserController();
$user = $userController->getUserById($userId);

if (!$user) {
    header('Location: login.php');
    exit;
}

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

$firstName = $user['first_name'] ?? '';
$lastName = $user['last_name'] ?? '';
$dob = $user['date_of_birth'] ?? '';
$gender = $user['gender'] ?? '';
$email = $user['email'] ?? '';
$phone = $user['phone_number'] ?? '';
$dietaryRestrictions = $user['dietary_restrictions'] ?? '';
$dietaryPreferences = $user['dietary_preferences'] ?? '';
$profilePicture = $user['profile_picture'] ?? '';
$defaultProfilePic = 'assets/default-profile.png';

$restrictionsArray = !empty($dietaryRestrictions) ? explode(',', $dietaryRestrictions) : [];
$preferencesArray = !empty($dietaryPreferences) ? explode(',', $dietaryPreferences) : [];
$randomTip = $healthTips[array_rand($healthTips)];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_all'])) {
    $userData = [
        'first_name' => $_POST['first_name'] ?? $firstName,
        'last_name' => $_POST['last_name'] ?? $lastName,
        'email' => $_POST['email'] ?? $email,
        'date_of_birth' => $_POST['dob'] ?? $dob,
        'gender' => $_POST['gender'] ?? $gender,
        'phone_number' => $_POST['phone'] ?? $phone
    ];
    
    $userData['dietary_restrictions'] = isset($_POST['dietary_restrictions']) 
        ? implode(',', $_POST['dietary_restrictions']) 
        : '';
    
    $userData['dietary_preferences'] = isset($_POST['dietary_preferences']) 
        ? implode(',', $_POST['dietary_preferences']) 
        : '';
    
    $userUpdateSuccess = $userController->updateUser($userId, $userData);
    
    if ($userUpdateSuccess) {
        $_SESSION['message'] = 'Profile updated successfully';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to update profile';
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: profile.php');
    exit;
}

// REMOVED: require_once __DIR__ . '/header.php'; -- This was causing the double inclusion
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
    <style>
    body {
        padding-top: 4rem; /* Matches header height */
    }
    .main-container {
        margin-top: 0 !important; /* Remove existing margin-top */
    }
    .header-content {
        height: 4rem;
    }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>


    <div class="main-content p-5 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8 pb-2 border-b border-gray-200">
            <h1 class="text-2xl text-gray-800 font-bold"><?= htmlspecialchars($firstName) ?>'s Profile</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="p-3 mb-4 rounded text-center <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="w-44 h-44 rounded-full bg-gray-100 mx-auto mb-5 overflow-hidden relative">
        <img src="<?= !empty($profilePicture) ? htmlspecialchars($profilePicture) : $defaultProfilePic ?>" 
         alt="Profile Picture" 
         class="w-full h-full object-cover">
        <label for="profile_picture" class="absolute inset-x-0 bottom-0 bg-black bg-opacity-50 text-white text-center py-1 cursor-pointer opacity-0 hover:opacity-100 transition-opacity duration-300">
        <i class="fa fa-camera"></i> Change
        </label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden">
        </div>


        <div class="grid md:grid-cols-2 gap-5 mb-8 relative">
        <form>
            <button id="edit-toggle" class="absolute top-0 right-0 text-primary font-bold">Edit</button>
        </form>            
                <div class="mb-5 profile-info-section">
                <div class="text-base font-bold text-gray-800 mb-1">First name</div>
                <div class="text-lg text-gray-600"><?= htmlspecialchars($firstName) ?></div>
            </div>
            
            <div class="mb-5 profile-info-section">
                <div class="text-base font-bold text-gray-800 mb-1">Last name</div>
                <div class="text-lg text-gray-600"><?= htmlspecialchars($lastName) ?></div>
            </div>
            
            <div class="mb-5 profile-info-section">
                <div class="text-base font-bold text-gray-800 mb-1">Date of Birth</div>
                <div class="text-lg text-gray-600"><?= htmlspecialchars($dob) ?></div>
            </div>
            
            <div class="mb-5 profile-info-section">
                <div class="text-base font-bold text-gray-800 mb-1">Gender</div>
                <div class="text-lg text-gray-600"><?= htmlspecialchars($gender) ?></div>
            </div>
            
            <div class="mb-5 profile-info-section">
                <div class="text-base font-bold text-gray-800 mb-1">Email</div>
                <div class="text-lg text-gray-600"><?= htmlspecialchars($email) ?></div>
            </div>
            
            <div class="mb-5 profile-info-section">
                <div class="text-base font-bold text-gray-800 mb-1">Phone</div>
                <div class="text-lg text-gray-600"><?= htmlspecialchars($phone) ?></div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-5 mb-8">
            <div class="mb-5 profile-info-section">
                <div class="bg-primary text-white font-bold p-3 rounded text-center mb-2">Dietary Restrictions</div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($restrictionsArray as $restriction): ?>
                        <span class="bg-green-50 text-primary px-3 py-1 rounded-full text-sm"><?= htmlspecialchars(trim($restriction)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="mb-5 profile-info-section">
                <div class="bg-primary text-white font-bold p-3 rounded text-center mb-2">Dietary Preferences</div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($preferencesArray as $preference): ?>
                        <span class="bg-green-50 text-primary px-3 py-1 rounded-full text-sm"><?= htmlspecialchars(trim($preference)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-gray-50 rounded-lg p-5 border-l-4 border-primary profile-info-section">
            <div class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                <i class="fa fa-lightbulb text-primary mr-2"></i> Daily Health Tips
            </div>
            <div class="flex items-start p-4 mb-4 bg-white rounded-lg shadow-sm" id="current-tip">
                <div class="text-2xl mr-4 text-primary">💡</div>
                <div class="flex-1 leading-relaxed text-gray-600 tip-content"><?= htmlspecialchars($randomTip) ?></div>
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
                        <div class="w-2 h-2 rounded-full tip-indicator <?= ($index === array_search($randomTip, $healthTips)) ? 'bg-primary' : 'bg-gray-300' ?>" data-index="<?= $index ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="hidden" id="edit-individual-form">
            <form method="POST" action="">
                <div class="grid md:grid-cols-2 gap-5 mb-8">
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">First name</div>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                    
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Last name</div>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                    
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Date of Birth</div>
                        <input type="date" name="dob" value="<?= htmlspecialchars($dob) ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                    
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Gender</div>
                        <select name="gender" class="w-full p-2 border border-gray-300 rounded text-base">
                            <option value="">Select gender</option>
                            <option value="M" <?= $gender === 'M' ? 'selected' : '' ?>>M</option>
                            <option value="F" <?= $gender === 'F' ? 'selected' : '' ?>>F</option>
                            <option value="Other" <?= $gender === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Email</div>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                    
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Phone</div>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($phone) ?>" class="w-full p-2 border border-gray-300 rounded text-base">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-5 mb-8">
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Dietary Restrictions</div>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <?php foreach ($dietaryRestrictionOptions as $option): ?>
                                <div class="flex items-center mb-1">
                                    <input type="checkbox" id="restriction-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $option))) ?>" 
                                           name="dietary_restrictions[]" 
                                           value="<?= htmlspecialchars($option) ?>"
                                           <?= in_array($option, $restrictionsArray) ? 'checked' : '' ?>
                                           class="mr-2">
                                    <label for="restriction-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $option))) ?>">
                                        <?= htmlspecialchars($option) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <div class="text-base font-bold text-gray-800 mb-1">Dietary Preferences</div>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <?php foreach ($dietaryPreferenceOptions as $option): ?>
                                <div class="flex items-center mb-1">
                                    <input type="checkbox" id="preference-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $option))) ?>" 
                                           name="dietary_preferences[]" 
                                           value="<?= htmlspecialchars($option) ?>"
                                           <?= in_array($option, $preferencesArray) ? 'checked' : '' ?>
                                           class="mr-2">
                                    <label for="preference-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $option))) ?>">
                                        <?= htmlspecialchars($option) ?>
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

    <form id="profile-picture-form" method="POST" action="upload_profile_picture.php" enctype="multipart/form-data" class="hidden">
        <input type="file" name="profile_picture" id="profile_picture_hidden">
    </form>

    <!-- Load Lucide first -->
    <script src="https://unpkg.com/lucide@latest"></script>
    

    <script>
    document.getElementById('profile_picture').addEventListener('change', function() {
        if (this.files[0]) {
            // Copy the selected file to the hidden form input
            const hiddenInput = document.getElementById('profile_picture_hidden');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(this.files[0]);
            hiddenInput.files = dataTransfer.files;
            
            // Submit the form
            document.getElementById('profile-picture-form').submit();
        }
    });
</script>
    <!-- Health tips script -->
    <script>
        const healthTips = <?= json_encode($healthTips) ?>;
        let currentTipIndex = <?= array_search($randomTip, $healthTips) ?>;
        const editToggle = document.getElementById('edit-toggle');
        const cancelEdit = document.getElementById('cancel-edit');

        document.getElementById('profile_picture').addEventListener('change', function() {
            if (this.files[0]) {
                document.getElementById('profile-picture-form').submit();
            }
        });

        if (editToggle) {
            editToggle.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.profile-info-section').forEach(el => el.classList.add('hidden'));
                document.getElementById('edit-individual-form').classList.remove('hidden');
                editToggle.classList.add('hidden');
            });
        }

        if (cancelEdit) {
            cancelEdit.addEventListener('click', () => {
                document.querySelectorAll('.profile-info-section').forEach(el => el.classList.remove('hidden'));
                document.getElementById('edit-individual-form').classList.add('hidden');
                editToggle.classList.remove('hidden');
            });
        }

        document.getElementById('prev-tip')?.addEventListener('click', () => {
            currentTipIndex = (currentTipIndex - 1 + healthTips.length) % healthTips.length;
            updateTip();
        });

        document.getElementById('next-tip')?.addEventListener('click', () => {
            currentTipIndex = (currentTipIndex + 1) % healthTips.length;
            updateTip();
        });

        document.querySelectorAll('.tip-indicator').forEach(indicator => {
            indicator.addEventListener('click', function() {
                currentTipIndex = parseInt(this.dataset.index);
                updateTip();
            });
        });

        function updateTip() {
            document.querySelector('.tip-content').textContent = healthTips[currentTipIndex];
            document.querySelectorAll('.tip-indicator').forEach((indicator, index) => {
                indicator.classList.toggle('bg-primary', index === currentTipIndex);
                indicator.classList.toggle('bg-gray-300', index !== currentTipIndex);
            });
        }
        
        updateTip();
    </script>
</body>
</html>