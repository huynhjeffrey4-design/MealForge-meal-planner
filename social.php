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

// Handle like button click for logged-in users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && $isLoggedIn) {
    $postId = $_POST['id'];

    // Find the post in the database
    $post = \R::load('post', $postId);

    // Ensure the post exists
    if ($post->id) {
        // Increment the likes count
        $post->likes++;
        \R::store($post);  // Save the updated post back to the database

        // Return the updated like count and liked status
        echo json_encode([
            'likes' => $post->likes,
            'liked' => true // Indicating the user has liked the post
        ]);
        exit;  // Terminate the script to avoid further output
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
                $post->image_url = $temporaryImagePath; // Use 'image_url' as per your specification

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
    <style>
        .liked {
            color: red; /* Change color to red when liked */
            font-weight: bold; /* Make the like count bolder */
        }
    </style>
</head>

<body class="bg-gray-50">
<div class="container mx-auto py-16 px-12">
    <!-- Header -->
    <header class="mb-8 pb-2 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-semibold">Recipe Social Feed</h1>
            <!-- Updated Logout Button -->
            <?php if ($isLoggedIn): ?>
                <a href="?logout=true" class="text-primary font-bold no-underline">Log out</a>
            <?php else: ?>
                <a href="login.php" class="text-primary font-bold no-underline">Log in</a>
            <?php endif; ?>
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
                    <div class="flex">
                        <!-- Left section: Profile Info, Description, and Likes (1/3 of the width) -->
                        <div class="w-1/3 pr-8">
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
                                        <button type="submit" class="like-button <?= isset($post['liked_by_user']) && $post['liked_by_user'] ? 'liked' : '' ?>">
                                            <i data-lucide="thumbs-up" class="h-5 w-5 <?= isset($post['liked_by_user']) && $post['liked_by_user'] ? 'text-red-500' : 'text-black' ?>"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="text-primary font-bold no-underline">
                                        <i data-lucide="thumbs-up" class="h-5 w-5 text-black"></i>
                                    </a>
                                <?php endif; ?>
                                <span class="like-count text-black font-bold <?= isset($post['liked_by_user']) && $post['liked_by_user'] ? 'liked' : '' ?>"><?= $post['likes'] ?></span>
                            </div>
                        </div>

                        <!-- Right section: Recipe Image (2/3 of the width) -->
                        <div class="w-2/3">
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
    // Handle like button color change (Only for logged-in users)
    document.addEventListener('DOMContentLoaded', function() {
        const likeButtons = document.querySelectorAll('.like-button');

        likeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Prevent form submission to prevent page reload (for this effect)
                e.preventDefault();

                // Check if the user is logged in (only allow liking if logged in)
                if (<?= json_encode($isLoggedIn) ?>) {
                    const postId = button.closest('form').dataset.postId;
                    const likeCountElement = button.closest('.flex').querySelector('.like-count'); // Get the like count element
                    button.classList.toggle('liked'); // Toggle the "liked" class for the button

                    // Make the AJAX call to update the likes
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
                        .then(data => {
                            // Update the displayed like count with the returned likes value
                            likeCountElement.textContent = data.likes;

                            // Change button color (red if liked, black if not)
                            if (data.liked) {
                                button.querySelector('i').classList.add('text-red-500');
                                button.querySelector('i').classList.remove('text-black');
                            } else {
                                button.querySelector('i').classList.remove('text-red-500');
                                button.querySelector('i').classList.add('text-black');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                } else {
                    // Redirect to login page if not logged in
                    window.location.href = 'login.php';
                }
            });
        });
    });

</script>

</body>
</html>
