<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testHashCreatesBcryptOrArgonHash(): void
    {
        $hash = Auth::hash('testpassword');
        $this->assertNotEmpty($hash);
        $this->assertNotSame('testpassword', $hash);

        if (defined('PASSWORD_ARGON2ID')) {
            $this->assertStringStartsWith('$argon2id$', $hash);
        } else {
            $this->assertStringStartsWith('$2y$', $hash);
        }
    }

    public function testVerifyCorrectPassword(): void
    {
        $hash = Auth::hash('mypassword');
        $this->assertTrue(Auth::verify('mypassword', $hash));
    }

    public function testVerifyIncorrectPassword(): void
    {
        $hash = Auth::hash('mypassword');
        $this->assertFalse(Auth::verify('wrongpassword', $hash));
    }

    public function testHashDifferentEachTime(): void
    {
        $hash1 = Auth::hash('samepassword');
        $hash2 = Auth::hash('samepassword');
        $this->assertNotSame($hash1, $hash2);
    }

    public function testVerifyWithBcryptHash(): void
    {
        $hash = password_hash('test', PASSWORD_BCRYPT);
        $this->assertTrue(Auth::verify('test', $hash));
        $this->assertFalse(Auth::verify('wrong', $hash));
    }

    public function testVerifyWithArgonHash(): void
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            $this->markTestSkipped('Argon2id not available');
        }
        $hash = password_hash('test', PASSWORD_ARGON2ID);
        $this->assertTrue(Auth::verify('test', $hash));
    }
}
