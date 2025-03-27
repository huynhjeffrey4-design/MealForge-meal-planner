<?php
session_start();
require_once __DIR__ . '/controllers/user.php';
require_once __DIR__ . '/controllers/post.php';

// Initialize PostController with Redbean
$postController = new PostController(new RedbeanPostDataProvider());

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']['id']);

// Handle Logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: social.php');
    exit;
}

// Helper function to handle form submissions
function handlePostSubmission($isLoggedIn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'], $_FILES['image']) && $isLoggedIn) {
        $description = $_POST['description'];
        $image = $_FILES['image'];

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowedTypes)) {
            $_SESSION['error'] = 'Invalid image type. Please upload a JPG, PNG, or GIF image.';
            header('Location: social.php');
            exit;
        }

        // Handle file upload
        $uploadDir = 'postimgs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $temporaryImagePath = $uploadDir . uniqid('image') . '.' . $extension;

        if (!move_uploaded_file($image['tmp_name'], $temporaryImagePath)) {
            $_SESSION['error'] = 'Failed to upload the image. Please try again.';
            header('Location: social.php');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $user = getUserController()->getUserById($userId);

        // Store the post
        $post = \R::dispense('post');
        $post->user_id = $userId;
        $post->first_name = $user['first_name'];
        $post->last_name = $user['last_name'];
        $post->post_time = date('Y-m-d H:i:s');
        $post->profile_picture = $user['profile_picture'];
        $post->description = $description;
        $post->likes = 0;
        $post->image_url = $temporaryImagePath;
        $post->liked_by = "";

        $postId = \R::store($post);

        // Rename the uploaded image
        $finalImagePath = $uploadDir . 'image' . $postId . '.' . $extension;
        rename($temporaryImagePath, $finalImagePath);

        $post->image_url = $finalImagePath;
        \R::store($post);

        $_SESSION['message'] = 'Post uploaded successfully';
        header('Location: social.php');
        exit;
    }
}

// Helper function to handle comment submissions
function handleCommentSubmission($isLoggedIn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_body'], $_POST['post_id']) && $isLoggedIn) {
        $commentBody = $_POST['comment_body'];
        $postId = $_POST['post_id'];
        $userId = $_SESSION['user']['id'];

        $comment = \R::dispense('comment');
        $comment->post_id = $postId;
        $comment->user_id = $userId;
        $comment->comment_body = $commentBody;
        $comment->comment_time = date('Y-m-d H:i:s');
        \R::store($comment);

        header('Location: social.php');
        exit;
    }
}

// Helper function to handle like submissions
function handleLikeAction($isLoggedIn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && $isLoggedIn) {
        $postId = $_POST['id'];
        $userEmail = $_SESSION['user']['email'];

        $post = \R::load('post', $postId);
        if ($post->id) {
            $likedBy = explode(',', $post->liked_by);

            if (in_array($userEmail, $likedBy)) {
                $likedBy = array_diff($likedBy, [$userEmail]);
                $post->likes--;
            } else {
                $likedBy[] = $userEmail;
                $post->likes++;
            }

            $post->liked_by = implode(',', $likedBy);
            \R::store($post);

            echo json_encode([
                'likes' => $post->likes,
                'liked' => in_array($userEmail, $likedBy)
            ]);
            exit;
        }
    }
}

handlePostSubmission($isLoggedIn);
handleCommentSubmission($isLoggedIn);
handleLikeAction($isLoggedIn);

// Fetch posts from the database
$posts = $postController->getAllPosts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Recipe Social</title>
    <!-- Tailwind CSS (New CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons (New CDN) -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-50">
<div class="container mx-auto pt-8 px-12">
    <!-- Back to Profile Button -->
    <a href="profile.php" class="flex items-center text-gray-600 mb-6">
        <i data-lucide="arrow-left" class="h-5 w-5 mr-1"></i>
        Back to Profile
    </a>
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
        <div class="mb-8 bg-white p-4 rounded-lg shadow"> <!-- Reduced top padding -->
            <form action="social.php" method="POST" enctype="multipart/form-data">
                <div class="flex items-center space-x-2 mb-4">
                    <h2 class="text-xl font-semibold">Share your meal:</h2>
                    <!-- Upload Post Button -->
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Upload Post</button>
                </div>
                <div class="flex space-x-4">
                    <!-- Post Description (Takes 2/3 of the width) -->
                    <div class="flex-2 w-2/3"> <!-- 2/3 width for description -->
                        <label for="description" class="block text-sm font-medium text-gray-700">Post Description</label>
                        <textarea id="description" name="description" rows="2" class="w-full p-3 border border-gray-300 rounded-md" required></textarea> <!-- Doubled height -->
                    </div>
                    <!-- Image File Upload (Takes 1/3 of the width) -->
                    <div class="flex-1 w-1/3"> <!-- 1/3 width for image file input -->
                        <label for="image" class="block text-sm font-medium text-gray-700">Upload Image</label>
                        <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif" class="w-full p-2 border border-gray-300 rounded-md" required>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Social Feed Section -->
    <div id="social-feed" class="w-full">
        <div class="space-y-8">
            <!-- Loop through the posts and display each one -->
            <?php foreach ($posts as $post): ?>
                <div class="bg-green-50 rounded-lg shadow p-8 mb-8 border border-green-200 max-w-4xl mx-auto">
                    <div class="flex">
                        <!-- Left section: Profile Info, Description, and Likes -->
                        <div class="w-1/3 pr-8">
                            <div class="flex items-center mb-6">
                                <img src="<?= htmlspecialchars($post['profile_picture']) ?>" alt="Profile Picture"
                                     class="w-20 h-20 rounded-full object-cover mr-6">
                                <div>
                                    <h3 class="font-semibold text-lg text-green-700"><?= htmlspecialchars($post['first_name']) . ' ' . htmlspecialchars($post['last_name']) ?></h3>
                                    <p class="text-sm text-gray-600"><?= date('F j, Y', strtotime($post['post_time'])) ?></p>
                                </div>
                            </div>

                            <!-- Post Description -->
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

                        <!-- Right section: Recipe Image -->
                        <div class="w-2/3">
                            <div class="relative h-0" style="padding-bottom: 66.67%;">
                                <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="Recipe Image"
                                     class="absolute top-0 left-0 w-full h-full object-contain rounded-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Display Comments -->
                    <div class="comments mt-6">
                        <h3 class="text-3xl font-bold underline mb-3">Comments</h3>
                        <?php
                        $comments = $postController->getCommentsForPost($post['id']);
                        if ($comments):
                            foreach ($comments as $comment):
                                $commentUser = \R::load('user', $comment['user_id']);
                                $formattedDate = date('m/d/Y', strtotime($comment['comment_time']));
                                ?>
                                <div class="comment mb-4 flex items-start">
                                    <div class="flex items-center space-x-4">
                                        <!-- Profile Picture of the Commenter -->
                                        <img src="<?= htmlspecialchars($commentUser->profile_picture) ?>" alt="Profile Picture" class="w-9 h-9 rounded-full object-cover">

                                        <div class="flex-1">
                                            <p><strong><?= htmlspecialchars($commentUser->first_name) . ' ' . htmlspecialchars($commentUser->last_name) ?>:</strong> <?= htmlspecialchars($comment['comment_body']) ?></p>
                                            <p class="text-sm text-gray-400"><?= $formattedDate ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>


                    <!-- Comment Form (Only for logged-in users) -->
                    <?php if ($isLoggedIn): ?>
                        <div class="comment-form mt-4">
                            <form action="social.php" method="POST">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <textarea name="comment_body" rows="3" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Add a comment..." required></textarea>
                                <button type="submit" class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Submit Comment</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p class="mt-4 text-sm text-gray-600">Please <a href="login.php" class="text-primary">log in</a> to comment.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Initialize Lucide icons -->
<script>
    lucide.createIcons();
</script>

<!-- Dynamic liking -->
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