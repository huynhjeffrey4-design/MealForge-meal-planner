<?php

require_once __DIR__ . '/../setup.php';
require_once __DIR__ . '/../SetupRedbean.php';

/**
 * post Controller with parameter-based filtering
 */
class PostController {
    private $postProvider;

    public function __construct(?PostDataProvider $postProvider) {
        if ($postProvider !== null) {
            $this->postProvider = $postProvider;
        } else {
            $env = env('PROVIDER_POST', '');
            $this->postProvider = $env == 'mock' ? new MockPostDataProvider() : new RedbeanPostDataProvider();
        }
    }

    /**
     * Get all posts without filtering
     *
     * @return array All available posts
     */
    public function getAllPosts(): array {
        $posts =  $this->postProvider->getAllPosts();

        return $posts;
    }

    public function getCommentsForPost($postId): array {
        $comments = $this->postProvider->getCommentsForPost($postId);

        return $comments;
    }

    /**
     * Get a post by ID
     *
     * @param int $postId ID of the post to retrieve
     * @return array|null post data or null if not found
     */
    public function getPostById($postId): array|null {
        $posts = $this->postProvider->getAllPosts();

        foreach ($posts as $post) {
            if ($post['post_id'] == $postId) {
                return $post;
            }
        }

        return null;
    }

    // Add a new post
    public function addPost($userId, $firstName, $lastName, $description, $base64Image, $profile_pic): void {
        $this->postProvider->addPost($userId, $firstName, $lastName, $description, $base64Image, $profile_pic);
    }

    // Add a comment to a post
    public function addComment($postId, $userId, $commentBody): void {
        $this->postProvider->addComment($postId, $userId, $commentBody);
    }

    // Toggle like status on a post
    public function toggleLike($postId, $userEmail): void {
        $this->postProvider->toggleLike($postId, $userEmail);
    }
}

/**
 * Interface for post data providers
 */
interface PostDataProvider
{
    /**
     * Get all available posts
     *
     * NOTE: Assumed format:
     * [
     * 'first_name' => 'John',
     * 'last_name' => 'Doe',
     * 'description' => 'This is a mock post. Just testing the layout!',
     * 'post_time' => '2025-03-24 10:00 AM',
     * 'profile_picture' => 'prof_pics/default_avatar.png',
     * 'description' => 'A delicious something recipe',
     * 'image_url' => 'postimgs/image1.jpg',
     * 'likes' => 15,
     * 'id' => 1,
     * 'liked_by' => ',dsgulvin@buffalo.edu,foodman@food.com'
     * ]
     *
     *
     * @return array of post data
     */
    public function getAllPosts(): array;
}

/**
 * Mock implementation of post data provider
 */
class MockPostDataProvider implements PostDataProvider {
    private $posts = [];

    public function __construct() {
        $this->initializePosts();
    }

    public function getAllPosts(): array {
        return $this->posts;
    }

    /**
     * Initialize mock post data
     */
    private function initializePosts(): void {
        $this->posts = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'message' => 'This is a mock post. Just testing the layout!',
                'post_time' => '2025-03-24 10:00 AM',
                'profile_picture' => 'prof_pics/default_avatar.png',
                'description' => 'A delicious something recipe',
                'imageURL' => 'postimgs/image1.jpg',
                'likes' => 15,
                'post_id' => 1
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'message' => 'Another mock post to check the feed layout.',
                'post_time' => '2025-03-23 5:30 PM',
                'profile_picture' => 'prof_pics/default_avatar.png',
                'description' => 'Best burger recipe!',
                'imageURL' => 'postimgs/image2.jpg',
                'likes' => 30,
                'post_id' => 2
            ],
            [
                'first_name' => 'Mark',
                'last_name' => 'Spencer',
                'message' => 'Loving this new social feature, hope it works great!',
                'post_time' => '2025-03-22 8:00 PM',
                'profile_picture' => 'prof_pics/default_avatar.png',
                'description' => 'A healthy pancake recipe',
                'imageURL' => 'postimgs/image3.jpg',
                'likes' => 5,
                'post_id' => 3
            ],
        ];
    }
}

class RedbeanPostDataProvider implements PostDataProvider
{
    public function __construct(array $config = [])
    {
        $dbConnection = DatabaseConnection::getInstance();
        $dbConnection->setup($config);
    }

    public function getAllPosts(): array
    {
        $posts = \R::findAll('post', 'ORDER BY post_time DESC');
        return \R::exportAll($posts);
    }

    public function getCommentsForPost($postId): array
    {
        $comments = \R::findAll('comment', 'WHERE post_id = ? ORDER BY comment_time ASC', [$postId]);

        $commentsWithUserData = [];
        foreach ($comments as $comment) {
            // Load the user associated with the comment (avoid \R::load in view)
            $commentUser = \R::load('user', $comment['user_id']);
            $commentsWithUserData[] = [
                'comment' => $comment,
                'user' => $commentUser
            ];
        }
        return $commentsWithUserData;
    }

    // Add a new post to the database
    public function addPost($userId, $firstName, $lastName, $description, $base64Image, $profile_pic): void
    {
        $post = \R::dispense('post');
        $post->user_id = $userId;
        $post->first_name = $firstName;
        $post->last_name = $lastName;
        $post->post_time = date('Y-m-d H:i:s');
        $post->description = $description;
        $post->image_url = $base64Image;
        $post->likes = 0;
        $post->liked_by = "";
        $post->profile_picture = $profile_pic;

        \R::store($post);
    }

    // Store a comment for a specific post
    public function addComment($postId, $userId, $commentBody): void
    {
        $comment = \R::dispense('comment');
        $comment->post_id = $postId;
        $comment->user_id = $userId;
        $comment->comment_body = $commentBody;
        $comment->comment_time = date('Y-m-d H:i:s');

        \R::store($comment);
    }

    // Toggle like status for a post
    public function toggleLike($postId, $userEmail): void {
        $post = \R::load('post', $postId);
        if ($post->id) {
            // Get the list of users who liked the post
            $likedBy = explode(',', $post->liked_by);

            // Toggle like status
            if (in_array($userEmail, $likedBy)) {
                // If the user has already liked, remove the like
                $likedBy = array_diff($likedBy, [$userEmail]);
                $post->likes--;
            } else {
                // If the user hasn't liked, add the like
                $likedBy[] = $userEmail;
                $post->likes++;
            }

            // Update the 'liked_by' field with the new list of users
            $post->liked_by = implode(',', $likedBy);

            // Store the updated post object back to the database
            \R::store($post);

            // Return the updated likes count and whether the user has liked the post
            echo json_encode([
                'likes' => $post->likes,
                'liked' => in_array($userEmail, $likedBy)
            ]);
        }
    }
}