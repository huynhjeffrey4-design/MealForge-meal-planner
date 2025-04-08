<?php

require_once __DIR__ . '/../setup.php';
require_once __DIR__ . '/../SetupRedbean.php';

require_once __DIR__ . '/valdiation.php';

class BookmarkController
{
    private $userId;

    public function __construct(string $userID)
    {
        $this->userId = $userID;
    }

    /**
     * Toggle bookmark status for a recipe
     *
     * @param int|null $userId The user ID
     * @param int|null $recipeId The recipe ID
     * @return array Response with success status, errors, and data
     */
    public function toggleBookmark(int $recipeId): array
    {
        $bookmarkProvider = new BookmarkProvider($this->userId);
        $result = $bookmarkProvider->toggleBookmark($recipeId);

        return [
            'success' => $result,
        ];
    }

    public function isBookmarked(int $recipeId): bool
    {
        $bookmarkProvider = new BookmarkProvider($this->userId);
        return $bookmarkProvider->isBookmarked($recipeId);
    }

    /**
     * Get bookmarks for a user
     *
     * @param int|null $userId The user ID
     * @return array Response with success status, errors, and data
     */
    public function getUserBookmarks(): array
    {
        $bookmarkProvider = new BookmarkProvider($this->userId);

        $bookmarks = $bookmarkProvider->getUserBookmarks();

        return [
            'success' => true,
            'bookmarks' => $bookmarks
        ];
    }
}

class BookmarkProvider
{
    private $user;

    public function __construct(string $userID, array $config = [])
    {
        $dbConnection = DatabaseConnection::getInstance();
        $dbConnection->setup($config);
        $this->user = R::load('user', $userID);
    }

    public function toggleBookmark(string $recipeId): bool
    {
        $recipe = \R::load('recipes', $recipeId);

        // Get the current shared list
        $bookmarkedRecipes = $this->user->sharedRecipesList;

        // Check if this recipe is already in the list
        $found = false;
        foreach ($bookmarkedRecipes as $key => $bookmarkedRecipe) {
            if ($bookmarkedRecipe->id == $recipe->id) {
                // If found, remove it
                unset($this->user->sharedRecipesList[$key]);
                $found = true;
                break;
            }
        }

        // If not found, add it
        if (!$found) {
            $this->user->sharedRecipesList[] = $recipe;
        }

        \R::store($this->user);
        return true;
    }

    public function isBookmarked(string $recipeId): bool
    {
        $recipe = \R::load('recipes', $recipeId);

        foreach ($this->user->sharedRecipesList as $bookmarkedRecipe) {
            if ($bookmarkedRecipe->id == $recipeId) {
                return true;
            }
        }

        return false;
    }

    public function getUserBookmarks(): array
    {
        return $this->user->sharedRecipesList;
    }
}
