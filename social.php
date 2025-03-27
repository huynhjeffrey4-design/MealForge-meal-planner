<?php
session_start();
require_once __DIR__ . '/controllers/user.php'; // Assuming a UserController for user management
require_once __DIR__ . '/controllers/post.php'; // Assuming a PostController for social posts

// Initialize PostController with RedbeanPostDataProvider (no configuration override)
$postController = new PostController(new RedbeanPostDataProvider());

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']['id']);

// Handle Logout directly in social.php
if (isset($_GET['logout'])) {
    // Clear all session variables related to the user
    session_unset();
    session_destroy();

    // Redirect back to the same social page (logging out and staying on the page)
    header('Location: social.php');
    exit;
}

// Handle like/unlike action for logged-in users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && $isLoggedIn) {
    $postId = $_POST['id'];
    $userEmail = $_SESSION['user']['email'];  // Get the logged-in user's email

    // Find the post in the database
    $post = \R::load('post', $postId);

    // Ensure the post exists
    if ($post->id) {
        // Get the current liked_by value (a comma-separated list of emails)
        $likedBy = explode(',', $post->liked_by);  // Convert the string to an array

        if (in_array($userEmail, $likedBy)) {
            // If the user has already liked the post, remove their email and decrement the likes count
            $likedBy = array_diff($likedBy, [$userEmail]);  // Remove user's email
            $post->likes--;  // Decrement like count
        } else {
            // If the user hasn't liked the post, add their email and increment the likes count
            $likedBy[] = $userEmail;  // Add user's email to the array
            $post->likes++;  // Increment like count
        }

        // Update the liked_by field with the new comma-separated list of emails
        $post->liked_by = implode(',', $likedBy);  // Convert the array back to a string

        // Save the updated post back to the database
        \R::store($post);

        // Return the updated like count and liked status
        echo json_encode([
            'likes' => $post->likes,
            'liked' => in_array($userEmail, $likedBy) // True if the user has liked the post
        ]);
        exit;
    }
}

// Get the user's information if logged in
if ($isLoggedIn) {
    // Check if the user_id exists in the session and assign it
    $userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

    if ($userId) {
        $userController = getUserController();
        $user = $userController->getUserById($userId);
    } else {
        // If user ID is not available in session, redirect to login
        header('Location: login.php');
        exit;
    }
}

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description']) && isset($_FILES['image'])) {
    if ($isLoggedIn) {
        // Handle file upload
        $description = $_POST['description'];
        $image = $_FILES['image'];

        // Validate the file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($image['type'], $allowedTypes)) {
            // Ensure the postimages directory exists
            $uploadDir = 'postimgs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
            }

            // Generate a temporary path for the image before we store it
            $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
            $temporaryImagePath = $uploadDir . uniqid('image') . '.' . $extension;

            // Move the uploaded image to the server with the temporary name
            if (move_uploaded_file($image['tmp_name'], $temporaryImagePath)) {
                // Generate a new post using RedBeanPHP (using 'posts' table explicitly)
                $post = \R::dispense('post');

                // Populate the post object
                $post->user_id = $userId; // Use user_id from session
                $post->first_name = $user['first_name'];
                $post->last_name = $user['last_name'];
                $post->post_time = date('Y-m-d H:i:s');
                $post->profile_picture = $user['profile_picture'];
                $post->description = $description;
                $post->likes = 0; // Initial like count
                $post->image_url = $temporaryImagePath;
                $post->liked_by = "";

                // Store the post in the database and get the generated post ID
                $postId = \R::store($post);

                // After storing the post, we can use the post id to rename the image
                $finalImagePath = $uploadDir . 'image' . $postId . '.' . $extension;

                // Rename the uploaded image to match the post id
                rename($temporaryImagePath, $finalImagePath);

                // Update the post in the database with the final image path
                $post->image_url = $finalImagePath;
                \R::store($post); // Re-store the post with the correct image URL

                // Set a session message and redirect to the social page
                $_SESSION['message'] = 'Post uploaded successfully';
                header('Location: social.php');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to upload the image. Please try again.';
                header('Location: social.php');
                exit;
            }
        } else {
            $_SESSION['error'] = 'Invalid image type. Please upload a JPG, PNG, or GIF image.';
            header('Location: social.php');
            exit;
        }
    }
}

// Fetch posts from the database using PostController
$posts = $postController->getAllPosts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Social Page - Recipe Sharing</title>
    <!-- Tailwind CSS (New CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons (New CDN) -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-50">
<?php include 'header.php'; ?>

<div class="container mx-auto py-8 px-4 md:px-12">
    <!-- Header -->
    <header class="mb-8 pb-2 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-semibold">Recipe Social Feed</h1>
        </div>
    </header>

    <!-- Upload Section (Only Visible to Logged-In Users) -->
    <?php if ($isLoggedIn): ?>
        <div class="mb-8 bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Upload a New Post</h2>
            <form action="social.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Post Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full p-2 border border-gray-300 rounded-md" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="image" class="block text-sm font-medium text-gray-700">Upload Image</label>
                    <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif" class="w-full p-2 border border-gray-300 rounded-md" required>
                </div>
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Upload Post</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Social Feed Section -->
    <div id="social-feed" class="w-full">
        <p class="text-sm text-gray-600 mb-6">Showing the most recent posts</p>

        <div class="space-y-8">
            <!-- Loop through the posts and display each one -->
            <?php foreach ($posts as $post): ?>
                <div class="bg-green-50 rounded-lg shadow p-8 mb-8 border border-green-200 max-w-4xl mx-auto">
                    <div class="flex flex-col md:flex-row">
                        <!-- Left section: Profile Info, Description, and Likes (1/3 of the width) -->
                        <div class="w-full md:w-1/3 md:pr-8 mb-6 md:mb-0">
                            <div class="flex items-center mb-6">
                                <img src="<?= htmlspecialchars($post['profile_picture']) ?>" alt="Profile Picture"
                                     class="w-20 h-20 rounded-full object-cover mr-6">
                                <div>
                                    <h3 class="font-semibold text-lg text-green-700"><?= htmlspecialchars($post['first_name']) . ' ' . htmlspecialchars($post['last_name']) ?></h3>
                                    <p class="text-sm text-gray-600"><?= date('F j, Y', strtotime($post['post_time'])) ?></p>
                                </div>
                            </div>

                            <!-- Post Description (Bigger Text) -->
                            <div class="text-lg text-gray-700 mb-6">
                                <p class="font-bold"><?= htmlspecialchars($post['description']) ?></p>
                            </div>

                            <!-- Like Button -->
                            <div class="flex items-center space-x-2">
                                <!-- Like button with dynamic class based on whether user has liked or not -->
                                <?php if ($isLoggedIn): ?>
                                    <form action="social.php" method="POST" class="like-form" data-post-id="<?= $post['id'] ?>">
                                        <?php
                                        // Get the logged-in user's email
                                        $userEmail = $_SESSION['user']['email'];

                                        // Get the liked_by field and convert it to an array
                                        $likedByArray = explode(',', $post['liked_by']);

                                        // Check if the user's email is in the liked_by array
                                        $isLiked = in_array($userEmail, $likedByArray);
                                        ?>
                                        <button type="submit" class="like-button <?= $isLiked ? 'liked' : '' ?>">
                                            <i data-lucide="thumbs-up" class="h-5 w-5 <?= $isLiked ? 'text-red-500' : 'text-black' ?>"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="text-primary font-bold no-underline">
                                        <i data-lucide="thumbs-up" class="h-5 w-5 text-black"></i>
                                    </a>
                                <?php endif; ?>
                                <span class="like-count text-black font-bold <?= $isLiked ? 'liked' : '' ?>"><?= $post['likes'] ?></span>
                            </div>
                        </div>

                        <!-- Right section: Recipe Image (2/3 of the width) -->
                        <div class="w-full md:w-2/3">
                            <div class="relative h-0" style="padding-bottom: 66.67%;"> <!-- Maintain Aspect Ratio -->
                                <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Recipe Image"
                                     class="absolute top-0 left-0 w-full h-full object-contain rounded-lg">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Initialize Lucide icons -->
<script>
    lucide.createIcons();
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const likeButtons = document.querySelectorAll('.like-button');

        likeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                // Only proceed if the user is logged in
                if (<?= json_encode($isLoggedIn) ?>) {
                    const postId = button.closest('form').dataset.postId;
                    const likeCountElement = button.closest('.flex').querySelector('.like-count');
                    const icon = button.querySelector('svg'); // Select the SVG element (thumbs-up icon)

                    // Make the AJAX call to like/unlike the post
                    fetch('social.php', {
                        method: 'POST',
                        body: new URLSearchParams({
                            'id': postId
                        }),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                        .then(response => response.json())
                        .then(response => { // Using response here instead of data
                            // Update the like count
                            likeCountElement.textContent = response.likes;

                            // Dynamically toggle the color based on the current like state
                            if (response.liked) { // Now we check the 'liked' status correctly
                                // change like icon to red
                                icon.classList.add('text-red-500');
                                icon.classList.remove('text-black');

                            } else {
                                // change like icon to black
                                icon.classList.add('text-black');
                                icon.classList.remove('text-red-500');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                } else {
                    window.location.href = 'login.php';  // Redirect to login page if not logged in
                }
            });
        });
    });
</script>

</body>
</html>