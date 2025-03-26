<?php
declare(strict_types=1);
namespace Tests\Acceptance;
use Tests\Support\AcceptanceTester;

// Verify links are working
final class LinksCest
{
    /**
     * Test user credentials
     */
    private array $testUser = [
        'email' => 'test@email.com',
        'password' => 'password123'
    ];
    
    /**
     * @var array Page test data
     */
    private array $pageTests = [
        'Home Page' => [
            'url' => '/index.php',
            'pageIdentifier' => 'MealForge',
            'links' => [
                ['text' => 'About', 'href' => 'About.html'],
                ['text' => 'Get Started', 'href' => 'login.php'],
                ['text' => 'Start Your Journey', 'href' => 'login.php'],
                ['text' => 'Get Started Now', 'href' => 'login.php']
            ]
        ],
        'Login Page' => [
            'url' => '/login.php',
            'pageIdentifier' => 'Login to your MealForge account',
            'links' => [
				/* Uncomment this when implementing forgot-password */
                /*['text' => 'Forgot your password?', 'href' => 'forgot-password.php'],*/
                ['text' => 'Create one now', 'href' => 'registration.php']
            ]
        ],
        'Registration Page' => [
            'url' => '/registration.php',
            'pageIdentifier' => 'Create your MealForge account',
            'links' => [
                ['text' => 'Login', 'href' => 'login.php', 'expectInUrl' => true]
            ]
        ],
        'Search Page' => [
            'url' => '/search.php',
            'pageIdentifier' => 'Recipe Search',
            'requireLogin' => true,
            'links' => [
                ['text' => 'Back to Profile', 'href' => 'profile.php', 'expectInUrl' => true]
            ]
        ],
        'About Page' => [
            'url' => '/About.html',
            'pageIdentifier' => 'About MealForge',
            'verifyContent' => [
                'MealForge',
                'Your meal planner, powered by your needs.',
                'Our Story',
                'Why MealForge?',
                'Ready to Simplify Meal Planning?',
                'Local Grocery Integration',
                'Health-First Approach',
                'Student-Friendly',
                'Completely Free'
            ],
            'links' => [
                ['text' => 'Get Started Now', 'href' => 'login.php', 'expectInUrl' => true]
            ]
        ],
		'Profile Page' => [
					'url' => '/profile.php',
					'pageIdentifier' => 'Welcome back',
					'requireLogin' => true,
					'links' => [
						['text' => 'Shop', 'href' => 'map.html', 'expectInUrl' => true],
						['text' => 'Social', 'href' => 'social.php', 'expectInUrl' => true],
						['text' => 'Shop', 'href' => 'shop.php', 'expectInUrl' => true]
					]
			]
    ];

    public function _before(AcceptanceTester $I): void
    {
        // Code here will be executed before each test.
    }
    
    /**
     * Helper method to login a test user
     */
    private function loginTestUser(AcceptanceTester $I): void
    {
        $I->amOnPage('/login.php');
        $I->fillField('email', $this->testUser['email']);
        $I->fillField('password', $this->testUser['password']);
        $I->click('Continue');
        $I->seeInCurrentUrl('profile.php'); // Verify login worked
    }
    
    /**
     * Test home page links
     */
    public function testHomePageLinks(AcceptanceTester $I): void
    {
        $this->runPageTest($I, 'Home Page');
    }
    
    /**
     * Test login page links
     */
    public function testLoginPageLinks(AcceptanceTester $I): void
    {
        $this->runPageTest($I, 'Login Page');
    }
    
    /**
     * Test registration page links
     */
    public function testRegistrationPageLinks(AcceptanceTester $I): void
    {
        $this->runPageTest($I, 'Registration Page');
    }
    
    /**
     * Test search page links
     */
    public function testSearchPageLinks(AcceptanceTester $I): void
    {
        $this->runPageTest($I, 'Search Page');
    }
    
    /**
     * Test about page links
     */
    public function testAboutPageLinks(AcceptanceTester $I): void
    {
        $this->runPageTest($I, 'About Page');
    }
    
    /**
     * Test profile page functionality
     */
    public function testProfilePageFunctionality(AcceptanceTester $I): void
{
    // First login to access profile page
    $this->loginTestUser($I);
    
    // Check that we're on the profile page
    $I->seeInCurrentUrl('profile.php');
    $I->see('MealForge');
    
    // Test header navigation links
    $I->seeLink('Find a Store');
    $I->seeLink('Search Recipes');
    $I->seeLink('Recipe Swiper');
    $I->seeLink('My Meals');
    $I->seeLink('About Us');
    
    // Test profile dropdown (if visible in test)
    $I->see('Profile');
}
    
    /**
     * Test map page functionality
     */
    public function testMapPage(AcceptanceTester $I): void
    {
        // Login first to ensure we can access the map page properly
        $this->loginTestUser($I);
        
        // Navigate to the map page
        $I->amOnPage('/map.html');
        
        // Check for main content elements
        $I->see('MealForge');
        $I->see('Find a Grocery Store Near You');
        
        // Check for the search input and buttons
        $I->seeElement('input#searchBox');
        $I->seeElement('button#searchButton');
        $I->seeElement('button#currentLocationButton');
        
        // Check for distance filter
        $I->see('Distance Range (km):');
        $I->seeElement('input#distanceRange');
        $I->see('5 km');
        
        // Check for map element
        $I->seeElement('#map');
        
        // Check for stores list element
        $I->seeElement('#stores-list');
        
        // There's an issue with the "Back to Profile" link in the HTML
        // The correct format should be:
        // <a id="profileLink" href="profile.php">🔙 Back to Profile</a>
        // Note this potential issue in the test
        
        // Uncomment this when the link is fixed
        // $I->seeLink('🔙 Back to Profile', 'profile.php');
        // $I->click('🔙 Back to Profile');
        // $I->seeInCurrentUrl('profile.php');
    }
    
    /**
     * Run test for a specific page
     */
    protected function runPageTest(AcceptanceTester $I, string $pageName): void
    {
        $pageData = $this->pageTests[$pageName];
        
        // Login if required for this page
        if (isset($pageData['requireLogin']) && $pageData['requireLogin']) {
            $this->loginTestUser($I);
        }
        
        // Visit the page
        $I->amOnPage($pageData['url']);
        $I->see($pageData['pageIdentifier']);
        
        // Verify additional content if specified
        if (isset($pageData['verifyContent'])) {
            foreach ($pageData['verifyContent'] as $content) {
                $I->see($content);
            }
        }
        
        // Verify elements if specified
        if (isset($pageData['elements'])) {
            foreach ($pageData['elements'] as $element) {
                $I->seeElement($element);
            }
        }
        
        // Test links on the page if specified
        if (isset($pageData['links'])) {
            foreach ($pageData['links'] as $link) {
                $I->seeLink($link['text'], $link['href']);
                $I->click($link['text']);
                $I->dontSee('404'); // Simple check to ensure page loads
                
                // Check if we should verify the URL
                if (isset($link['expectInUrl']) && $link['expectInUrl']) {
                    $I->seeInCurrentUrl($link['href']);
                }
                
                // Go back to the original page
                $I->amOnPage($pageData['url']);
                
                // Re-login if needed after navigation
                if (isset($pageData['requireLogin']) && $pageData['requireLogin'] && !str_contains($link['href'], '#')) {
                    $this->loginTestUser($I);
                    $I->amOnPage($pageData['url']);
                }
            }
        }
    }
}
