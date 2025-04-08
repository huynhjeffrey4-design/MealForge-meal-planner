<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

final class SessionSecurityCest
{
    public function _before(AcceptanceTester $I): void
    {
        // Start a session if not already started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function _after(AcceptanceTester $I): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Test session ID regeneration
     */
    public function testSessionIdRegeneration(AcceptanceTester $I): void
    {
        // Arrange
        $oldSessionId = session_id();
        $_SESSION['test_data'] = 'This should persist';

        // Create a function to simulate session regeneration
        $regenerateSession = function () {
            $oldSessionData = $_SESSION;
            session_regenerate_id(true);
            $_SESSION = $oldSessionData;
            return session_id();
        };

        // Act
        $newSessionId = $regenerateSession();

        // Assert
        $I->assertNotEquals($oldSessionId, $newSessionId);
        $I->assertEquals('This should persist', $_SESSION['test_data']);
    }

    /**
     * Test session fixation protection
     */
    public function testSessionFixationProtection(AcceptanceTester $I): void
    {
        // Arrange
        $initialSessionId = session_id();

        // Create a function to simulate login with session fixation protection
        $loginWithProtection = function () {
            // Store important session data
            $oldSessionData = $_SESSION;

            // Regenerate session ID
            session_regenerate_id(true);

            // Restore session data
            $_SESSION = $oldSessionData;

            // Set login status
            $_SESSION['user_id'] = 123;
            $_SESSION['logged_in'] = true;

            return session_id();
        };

        // Act
        $afterLoginSessionId = $loginWithProtection();

        // Assert
        $I->assertNotEquals($initialSessionId, $afterLoginSessionId);
        $I->assertTrue($_SESSION['logged_in']);
        $I->assertEquals(123, $_SESSION['user_id']);
    }

    /**
     * Test session timeout functionality
     */
    public function testSessionTimeout(AcceptanceTester $I): void
    {
        // Arrange
        $_SESSION['last_activity'] = time() - 1800; // 30 minutes ago
        $_SESSION['user_id'] = 123;
        $_SESSION['logged_in'] = true;

        // Create a function to check session timeout
        $checkSessionTimeout = function ($maxLifetime = 1200) { // 20 minutes default
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxLifetime)) {
                // Session has expired
                session_unset();
                session_destroy();
                return true; // Session expired
            }

            // Update last activity time
            $_SESSION['last_activity'] = time();
            return false; // Session still valid
        };

        // Act
        $isExpired = $checkSessionTimeout();

        // Assert
        $I->assertTrue($isExpired);
        $I->assertFalse(isset($_SESSION['user_id']));
        $I->assertFalse(isset($_SESSION['logged_in']));

        // Start a new session
        session_start();

        // Set a recent activity time
        $_SESSION['last_activity'] = time() - 600; // 10 minutes ago
        $_SESSION['user_id'] = 123;

        // Check again with the same timeout
        $isExpired = $checkSessionTimeout();

        // Session should not be expired
        $I->assertFalse($isExpired);
        $I->assertEquals(123, $_SESSION['user_id']);
    }

    /**
     * Test session hijacking protection
     */
    public function testSessionHijackingProtection(AcceptanceTester $I): void
    {
        // Arrange
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $ipAddress = '192.168.1.1';

        // Store user agent and IP in session
        $_SESSION['user_agent'] = $userAgent;
        $_SESSION['ip_address'] = $ipAddress;
        $_SESSION['user_id'] = 123;

        // Create a function to check for session hijacking
        $checkSessionHijacking = function ($currentUserAgent, $currentIp) {
            if (!isset($_SESSION['user_agent']) || !isset($_SESSION['ip_address'])) {
                return false; // New session, not hijacked
            }

            // Check if user agent or IP has changed
            if ($_SESSION['user_agent'] !== $currentUserAgent || $_SESSION['ip_address'] !== $currentIp) {
                // Potential session hijacking
                session_unset();
                session_destroy();
                return true; // Session hijacked
            }

            return false; // Session not hijacked
        };

        // Act & Assert

        // Same user agent and IP should not trigger hijacking detection
        $isHijacked = $checkSessionHijacking($userAgent, $ipAddress);
        $I->assertFalse($isHijacked);
        $I->assertEquals(123, $_SESSION['user_id']);

        // Different user agent should trigger hijacking detection
        $isHijacked = $checkSessionHijacking('Different User Agent', $ipAddress);
        $I->assertTrue($isHijacked);

        // Session should be destroyed
        $I->assertFalse(isset($_SESSION['user_id']));

        // Start a new session
        session_start();

        // Set up session again
        $_SESSION['user_agent'] = $userAgent;
        $_SESSION['ip_address'] = $ipAddress;
        $_SESSION['user_id'] = 123;

        // Different IP should trigger hijacking detection
        $isHijacked = $checkSessionHijacking($userAgent, '10.0.0.1');
        $I->assertTrue($isHijacked);
    }
}
