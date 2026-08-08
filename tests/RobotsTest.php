<?php

use App\Controllers\RobotsController;
use PHPUnit\Framework\TestCase;

final class RobotsTest extends TestCase
{
    /**
     * @return string[]
     */
    private function allowedAgents(): array
    {
        $controller = new RobotsController();
        $method = new ReflectionMethod(RobotsController::class, 'allowedAgents');
        return $method->invoke($controller);
    }

    public function testAnthropicCrawlerTokensAreListed(): void
    {
        $agents = $this->allowedAgents();
        $this->assertContains('ClaudeBot', $agents);
        $this->assertContains('Claude-SearchBot', $agents);
        $this->assertContains('Claude-User', $agents);
        $this->assertContains('anthropic-ai', $agents);
        $this->assertContains('Claude-Web', $agents);
    }

    public function testBytespiderAndOpenAiTokensAreListed(): void
    {
        $agents = $this->allowedAgents();
        $this->assertContains('Bytespider', $agents);
        $this->assertContains('GPTBot', $agents);
        $this->assertContains('OAI-SearchBot', $agents);
        $this->assertContains('ChatGPT-User', $agents);
    }

    public function testNoDuplicateAgentTokens(): void
    {
        $agents = $this->allowedAgents();
        $this->assertCount(count($agents), array_unique($agents));
    }

    public function testSearchEngineBaselineIsListed(): void
    {
        $agents = $this->allowedAgents();
        foreach (['Googlebot', 'Bingbot', 'Applebot', 'Yandex', 'Baiduspider'] as $agent) {
            $this->assertContains($agent, $agents);
        }
    }
}
