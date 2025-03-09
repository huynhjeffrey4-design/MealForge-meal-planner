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

$userId = $_SESSION['user_id']['id'];

$userController = getUserController();
$user = $userController->getUserById($userId);
$userProfile = $userController->getProfileByUserId($userId);

if (!$user || !$userProfile) {
		$_SESSION['message'] = 'User not found';
		$_SESSION['message_type'] = 'error';
		header('Location: login.php');
		exit;
		session_destroy();
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

$dob = $userProfile['date_of_birth'] ?? '';
$gender = $userProfile['gender'] ?? '';
$email = $userProfile['email'] ?? '';
$phone = $userProfile['phone_number'] ?? '';
$dietaryRestrictions = $userProfile['dietary_restrictions'] ?? '';
$dietaryPreferences = $userProfile['dietary_preferences'] ?? '';
$profilePicture = $userProfile['profile_picture'] ?? '';

$defaultProfilePic = 'assets/default-profile.png';

// Format dietary restrictions and preferences as arrays for display
$restrictionsArray = !empty($dietaryRestrictions) ? explode(',', $dietaryRestrictions) : [];
$preferencesArray = !empty($dietaryPreferences) ? explode(',', $dietaryPreferences) : [];

// Get a random health tip
$randomTip = $healthTips[array_rand($healthTips)];

// Handle form submission (updated for new structure)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_all'])) {
    $userData = [
        'first_name' => $_POST['first_name'] ?? $firstName,
        'last_name' => $_POST['last_name'] ?? $lastName,
        'email' => $_POST['email'] ?? $email
    ];
    
    $profileData = [
        'date_of_birth' => $_POST['dob'] ?? $dob,
        'gender' => $_POST['gender'] ?? $gender,
        'phone_number' => $_POST['phone'] ?? $phone
    ];
    
    // Handle dietary restrictions
    if (isset($_POST['dietary_restrictions'])) {
        $profileData['dietary_restrictions'] = implode(',', $_POST['dietary_restrictions']);
    } else {
        $profileData['dietary_restrictions'] = '';
    }
    
    // Handle dietary preferences
    if (isset($_POST['dietary_preferences'])) {
        $profileData['dietary_preferences'] = implode(',', $_POST['dietary_preferences']);
    } else {
        $profileData['dietary_preferences'] = '';
    }
    
    // Update user data
    $userUpdateSuccess = $userController->updateUser($userId, $userData);
    
    // Update profile data
    $profileUpdateSuccess = $userController->updateProfile($userId, $profileData);
    
    if ($userUpdateSuccess && $profileUpdateSuccess) {
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }
        
        body {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #00A651;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        
        .profile-image-container {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background-color: #f0f0f0;
            margin: 0 auto 20px;
            overflow: hidden;
            position: relative;
        }
        
        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .edit-profile-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0,0,0,0.5);
            color: white;
            text-align: center;
            padding: 5px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .profile-image-container:hover .edit-profile-overlay {
            opacity: 1;
        }
        
        .welcome-text {
            text-align: center;
            margin-bottom: 40px;
            font-size: 18px;
        }
        
        .highlight {
            font-weight: bold;
        }
        
        .nav-links {
            list-style: none;
            margin-top: 20px;
        }
        
        .nav-links li {
            margin-bottom: 15px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            display: block;
            padding: 5px 0;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #fff;
            overflow-y: auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .header h1 {
            font-size: 24px;
            color: #333;
        }
        
        .logout-link {
            color: #00A651;
            text-decoration: none;
            font-weight: bold;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .edit-link {
            position: absolute;
            top: 0;
            right: 0;
            color: #00A651;
            text-decoration: none;
            font-weight: bold;
        }
        
        .field {
            margin-bottom: 20px;
        }
        
        .field-label {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .field-value {
            font-size: 18px;
            color: #555;
        }
        
        .dietary-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dietary-container {
            margin-bottom: 20px;
        }
        
        .dietary-title {
            background-color: #00A651;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 10px;
            width: 100%;
            text-align: center;
        }
        
        .dietary-list {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .dietary-tag {
            background-color: #e8f5e9;
            color: #00A651;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            display: inline-block;
        }
        
        .edit-form {
            display: none;
        }
        
        .edit-form.active {
            display: block;
        }
        
        .edit-form input, 
        .edit-form select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .dietary-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .option-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .option-item input[type="checkbox"] {
            margin-right: 8px;
            width: auto;
        }
        
        .buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .save-btn, .cancel-btn {
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .save-btn {
            background-color: #00A651;
            color: white;
            border: none;
        }
        
        .cancel-btn {
            background-color: white;
            color: #00A651;
            border: 2px solid #00A651;
        }
        
        .hidden-file-input {
            display: none;
        }
        
        /* Health Tips Section */
        .health-tips-section {
            margin-top: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border-left: 5px solid #00A651;
        }
        
        .health-tips-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .health-tips-title i {
            color: #00A651;
            margin-right: 10px;
        }
        
        .health-tip {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: relative;
        }
        
        .tip-icon {
            font-size: 24px;
            margin-right: 15px;
            color: #00A651;
        }
        
        .tip-content {
            flex: 1;
            line-height: 1.5;
            color: #555;
        }
        
        .tip-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        
        .tip-navigation {
            display: flex;
            gap: 10px;
        }
        
        .tip-nav-button {
            background-color: #00A651;
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .tip-nav-button:hover {
            background-color: #008c44;
        }
        
        .tip-indicators {
            display: flex;
            gap: 6px;
            margin-top: 10px;
            justify-content: center;
        }
        
        .tip-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #ddd;
            transition: background-color 0.3s;
        }
        
        .tip-indicator.active {
            background-color: #00A651;
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding-bottom: 20px;
            }
            
            .profile-grid, 
            .dietary-section {
                grid-template-columns: 1fr;
            }
            
            .dietary-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">MealForge</div>
        <div class="profile-image-container">
            <img src="<?php 
                if (!empty($profilePicture)) {
                    echo $profilePicture;
                } else {
                    echo $defaultProfilePic;
                }
            ?>" alt="Profile Picture" class="profile-image">
            <label for="profile_picture" class="edit-profile-overlay">
                <i class="fa fa-camera"></i> Change
            </label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden-file-input">
        </div>
        <div class="welcome-text">
            Welcome back, <span class="highlight"><?php echo htmlspecialchars($firstName); ?>!</span>
        </div>
        <ul class="nav-links">
            <li><a href="#"><i class="fa fa-home fa-fw"></i> Dashboard</a></li>
            <li><a href="#"><i class="fa fa-users fa-fw"></i> Social</a></li>
            <li><a href="#"><i class="fa fa-shopping-cart fa-fw"></i> Shop</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1><?php echo htmlspecialchars($firstName); ?>'s profile</h1>
            <a href="logout.php" class="logout-link">Log out</a>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Profile Information Grid -->
        <div class="profile-grid">
            <a href="#" class="edit-link" id="edit-toggle">Edit</a>
            
            <!-- First Name -->
            <div class="field">
                <div class="field-label">First name</div>
                <div class="field-value"><?php echo htmlspecialchars($firstName); ?></div>
            </div>
            
            <!-- Last Name -->
            <div class="field">
                <div class="field-label">Last name</div>
                <div class="field-value"><?php echo htmlspecialchars($lastName); ?></div>
            </div>
            
            <!-- Date of Birth -->
            <div class="field">
                <div class="field-label">Date of Birth</div>
                <div class="field-value"><?php echo htmlspecialchars($dob); ?></div>
            </div>
            
            <!-- Gender -->
            <div class="field">
                <div class="field-label">Gender</div>
                <div class="field-value"><?php echo htmlspecialchars($gender); ?></div>
            </div>
            
            <!-- Email -->
            <div class="field">
                <div class="field-label">Email (username)</div>
                <div class="field-value"><?php echo htmlspecialchars($email); ?></div>
            </div>
            
            <!-- Phone Number -->
            <div class="field">
                <div class="field-label">Phone number</div>
                <div class="field-value"><?php echo htmlspecialchars($phone); ?></div>
            </div>
        </div>

        <!-- Dietary Sections -->
        <div class="dietary-section">
            <!-- Dietary Restrictions -->
            <div class="dietary-container">
                <div class="dietary-title">Dietary Restrictions</div>
                <div class="dietary-list">
                    <?php if (!empty($restrictionsArray)): ?>
                        <?php foreach ($restrictionsArray as $restriction): ?>
                            <span class="dietary-tag"><?php echo htmlspecialchars(trim($restriction)); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="dietary-tag">None specified</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Dietary Preferences -->
            <div class="dietary-container">
                <div class="dietary-title">Dietary Preferences</div>
                <div class="dietary-list">
                    <?php if (!empty($preferencesArray)): ?>
                        <?php foreach ($preferencesArray as $preference): ?>
                            <span class="dietary-tag"><?php echo htmlspecialchars(trim($preference)); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="dietary-tag">None specified</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Health Tips Section -->
        <div class="health-tips-section">
            <div class="health-tips-title">
                <i class="fa fa-lightbulb"></i> Daily Health Tips
            </div>
            <div class="health-tip" id="current-tip">
                <div class="tip-icon">💡</div>
                <div class="tip-content"><?php echo htmlspecialchars($randomTip); ?></div>
            </div>
            <div class="tip-controls">
                <div class="tip-navigation">
                    <button class="tip-nav-button" id="prev-tip"><i class="fa fa-chevron-left"></i></button>
                    <button class="tip-nav-button" id="next-tip"><i class="fa fa-chevron-right"></i></button>
                </div>
                <div class="tip-indicators">
                    <?php foreach ($healthTips as $index => $tip): ?>
                        <div class="tip-indicator <?php echo ($index === array_search($randomTip, $healthTips)) ? 'active' : ''; ?>" data-index="<?php echo $index; ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="edit-form" id="edit-individual-form">
            <form method="POST" action="update_field.php">
                <div class="profile-grid">
                    <!-- First Name Edit -->
                    <div class="field">
                        <div class="field-label">First name</div>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>">
                    </div>
                    
                    <!-- Last Name Edit -->
                    <div class="field">
                        <div class="field-label">Last name</div>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>">
                    </div>
                    
                    <!-- Date of Birth Edit -->
                    <div class="field">
                        <div class="field-label">Date of Birth</div>
                        <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
                    </div>
                    
                    <!-- Gender Edit -->
                    <div class="field">
                        <div class="field-label">Gender</div>
                        <select name="gender">
                            <option value="">Select gender</option>
                            <option value="M" <?php echo $gender === 'M' ? 'selected' : ''; ?>>M</option>
                            <option value="F" <?php echo $gender === 'F' ? 'selected' : ''; ?>>F</option>
                            <option value="Other" <?php echo $gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <!-- Email Edit -->
                    <div class="field">
                        <div class="field-label">Email (username)</div>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    
                    <!-- Phone Number Edit -->
                    <div class="field">
                        <div class="field-label">Phone number</div>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                    </div>
                </div>

                <div class="dietary-section">
                    <!-- Dietary Restrictions Edit -->
                    <div class="dietary-container">
                        <div class="field-label">Dietary Restrictions</div>
                        <div class="dietary-options">
                            <?php foreach ($dietaryRestrictionOptions as $option): ?>
                                <div class="option-item">
                                    <input type="checkbox" id="restriction-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $option))); ?>" 
                                           name="dietary_restrictions[]" 
                                           value="<?php echo htmlspecialchars($option); ?>"
                                           <?php echo in_array($option, $restrictionsArray) ? 'checked' : ''; ?>>
                                    <label for="restriction-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $option))); ?>">
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Dietary Preferences Edit -->
                    <div class="dietary-container">
                        <div class="field-label">Dietary Preferences</div>
                        <div class="dietary-options">
                            <?php foreach ($dietaryPreferenceOptions as $option): ?>
                                <div class="option-item">
                                    <input type="checkbox" id="preference-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $option))); ?>" 
                                           name="dietary_preferences[]" 
                                           value="<?php echo htmlspecialchars($option); ?>"
                                           <?php echo in_array($option, $preferencesArray) ? 'checked' : ''; ?>>
                                    <label for="preference-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $option))); ?>">
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="edit_all" value="1">
                
                <div class="buttons">
                    <button type="submit" class="save-btn">Save</button>
                    <button type="button" class="cancel-btn" id="cancel-edit">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form for profile picture upload -->
    <form id="profile-picture-form" method="POST" action="upload_profile.php" enctype="multipart/form-data" style="display:none;">
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
            document.querySelector('.profile-grid').style.display = 'none';
            document.querySelector('.dietary-section').style.display = 'none';
            document.getElementById('edit-individual-form').classList.add('active');
            document.getElementById('edit-toggle').style.display = 'none';
        });

        // Cancel edit functionality
        document.getElementById('cancel-edit').addEventListener('click', function() {
            document.querySelector('.profile-grid').style.display = 'grid';
            document.querySelector('.dietary-section').style.display = 'grid';
            document.getElementById('edit-individual-form').classList.remove('active');
            document.getElementById('edit-toggle').style.display = 'block';
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
            document.querySelector('.tip-content').textContent = healthTips[currentTipIndex];
            
            // Update indicators
            document.querySelectorAll('.tip-indicator').forEach((indicator, index) => {
                if (index === currentTipIndex) {
                    indicator.classList.add('active');
                } else {
                    indicator.classList.remove('active');
                }
            });
        }
        
        // Allow clicking on indicators to jump to specific tips
        document.querySelectorAll('.tip-indicator').forEach((indicator) => {
            indicator.addEventListener('click', function() {
                currentTipIndex = parseInt(this.getAttribute('data-index'));
                updateTip();
            });
        });
    </script>
</body>
</html>
