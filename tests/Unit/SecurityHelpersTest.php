<?php
/**
 * Security Helpers Unit Tests
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SecurityHelpersTest extends TestCase
{
    public function testSanitizeFilename(): void
    {
        // Test basic sanitization
        $this->assertEquals('test_file.txt', sanitizeFilename('test file.txt'));

        // Test directory traversal prevention
        $this->assertEquals('file.txt', sanitizeFilename('../../../etc/passwd'));
        $this->assertEquals('file.txt', sanitizeFilename('../../file.txt'));

        // Test special characters removal
        $this->assertEquals('test_file.txt', sanitizeFilename('test<script>file.txt'));

        // Test multiple underscores collapsed
        $this->assertEquals('test_file.txt', sanitizeFilename('test___file.txt'));

        // Test leading/trailing cleanup
        $this->assertEquals('file.txt', sanitizeFilename('...file.txt...'));
    }

    public function testGenerateSecureFilename(): void
    {
        $filename = generateSecureFilename('test.jpg');

        // Should contain timestamp and random component
        $this->assertMatchesRegularExpression('/^\d+_[a-f0-9]+\.jpg$/', $filename);

        // With prefix
        $filename = generateSecureFilename('test.png', 'game');
        $this->assertMatchesRegularExpression('/^game_\d+_[a-f0-9]+\.png$/', $filename);
    }

    public function testCleanInput(): void
    {
        // Test null byte removal
        $this->assertEquals('test', cleanInput("te\0st"));

        // Test HTML stripping
        $this->assertEquals('alert', cleanInput('<script>alert</script>'));

        // Test whitespace trimming
        $this->assertEquals('test', cleanInput('  test  '));

        // Test array handling
        $result = cleanInput(['<b>test</b>', '  trim  ']);
        $this->assertEquals(['test', 'trim'], $result);
    }

    public function testCleanHtml(): void
    {
        // Test allowed tags preserved
        $this->assertEquals('<p>test</p>', cleanHtml('<p>test</p>'));
        $this->assertEquals('<strong>bold</strong>', cleanHtml('<strong>bold</strong>'));

        // Test dangerous tags removed
        $this->assertEquals('alert', cleanHtml('<script>alert</script>'));
        $this->assertEquals('content', cleanHtml('<iframe>content</iframe>'));

        // Test event handlers removed
        $cleaned = cleanHtml('<p onclick="alert()">test</p>');
        $this->assertStringNotContainsString('onclick', $cleaned);

        // Test javascript: URLs removed
        $cleaned = cleanHtml('<a href="javascript:alert()">link</a>');
        $this->assertStringNotContainsString('javascript:', $cleaned);
    }

    public function testSecureHash(): void
    {
        $hash = secureHash('password123');

        // Should be SHA-256 (64 characters hex)
        $this->assertEquals(64, strlen($hash));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);

        // Same input should produce same hash
        $this->assertEquals($hash, secureHash('password123'));
    }

    public function testSecureCompare(): void
    {
        $this->assertTrue(secureCompare('test', 'test'));
        $this->assertFalse(secureCompare('test', 'TEST'));
        $this->assertFalse(secureCompare('test', 'test2'));
    }

    public function testGenerateToken(): void
    {
        $token = generateToken(32);

        // Should be 64 hex characters (32 bytes)
        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);

        // Should be unique
        $token2 = generateToken(32);
        $this->assertNotEquals($token, $token2);
    }

    public function testCheckRateLimit(): void
    {
        $key = 'test_rate_limit_' . uniqid();

        // First 3 attempts should pass
        $this->assertTrue(checkRateLimit($key, 3, 60));
        $this->assertTrue(checkRateLimit($key, 3, 60));
        $this->assertTrue(checkRateLimit($key, 3, 60));

        // 4th attempt should fail
        $this->assertFalse(checkRateLimit($key, 3, 60));

        // Clean up
        $cacheFile = STORAGE_PATH . '/cache/rate_' . md5($key) . '.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
}
