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
            // Not sure what this next line does
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

class RedbeanPostDataProvider implements PostDataProvider {

    public function __construct(array $config = [])
    {
        $dbConnection = DatabaseConnection::getInstance();
        $dbConnection->setup($config);
    }

    public function getAllPosts(): array {
        $posts = \R::findAll('post', 'ORDER BY post_time DESC');
        return \R::exportAll($posts);
    }

    public function getCommentsForPost($postId): array {
        $comments = \R::findAll('comment', 'WHERE post_id = ? ORDER BY comment_time ASC', [$postId]);
        return \R::exportAll($comments);
    }

}
