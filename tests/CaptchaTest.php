<?php

use PHPUnit\Framework\TestCase;

class CaptchaTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        Captcha::clearCache();
    }

    public function testGenerateReturnsQuestionAndAnswer(): void
    {
        $result = Captcha::generate();
        $this->assertArrayHasKey('question', $result);
        $this->assertArrayHasKey('answer', $result);
        $this->assertIsInt($result['answer']);
        $this->assertGreaterThan(0, strlen($result['question']));
    }

    public function testStoreReturnsQuestionAndSetsSession(): void
    {
        $question = Captcha::store();
        $this->assertNotEmpty($question);
        $this->assertSame($question, $_SESSION['captcha_question']);
        $this->assertIsInt($_SESSION['captcha_answer']);
        $this->assertIsInt($_SESSION['captcha_time']);
    }

    public function testVerifyCorrectAnswer(): void
    {
        $_SESSION['captcha_answer'] = 42;
        $_SESSION['captcha_time'] = time();
        $this->assertTrue(Captcha::verify(42));
    }

    public function testVerifyWrongAnswer(): void
    {
        $_SESSION['captcha_answer'] = 42;
        $_SESSION['captcha_time'] = time();
        $this->assertFalse(Captcha::verify(99));
    }

    public function testVerifyClearsSessionAfterVerification(): void
    {
        $_SESSION['captcha_answer'] = 42;
        $_SESSION['captcha_time'] = time();
        Captcha::verify(42);
        $this->assertArrayNotHasKey('captcha_answer', $_SESSION);
        $this->assertArrayNotHasKey('captcha_time', $_SESSION);
    }

    public function testVerifyRejectsExpiredCaptcha(): void
    {
        $_SESSION['captcha_answer'] = 42;
        $_SESSION['captcha_time'] = time() - 400;
        $this->assertFalse(Captcha::verify(42));
    }

    public function testVerifyRejectsNoCaptcha(): void
    {
        $this->assertFalse(Captcha::verify(42));
    }

    public function testVerifyAcceptsPersianDigits(): void
    {
        $_SESSION['captcha_answer'] = 42;
        $_SESSION['captcha_time'] = time();
        $this->assertTrue(Captcha::verify('۴۲'));
    }

    public function testVerifyAndRegenerateReturnsNewQuestion(): void
    {
        $_SESSION['captcha_answer'] = 42;
        $_SESSION['captcha_time'] = time();
        $newQuestion = Captcha::verifyAndRegenerate(42);
        $this->assertNotEmpty($newQuestion);
        $this->assertIsInt($_SESSION['captcha_answer']);
    }

    public function testVerifyAndRegenerateReturnsNullOnWrong(): void
    {
        $_SESSION['captcha_answer'] = 42;
        $_SESSION['captcha_time'] = time();
        $result = Captcha::verifyAndRegenerate(99);
        $this->assertNull($result);
    }
}
