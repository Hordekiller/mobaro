<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Settings;

class RobotsController extends BaseController
{
    public function index(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        $body = Cache::remember('robots.txt', 86400, function () {
            $custom = Settings::get('robots_txt', '');
            if ($custom !== '') {
                return $custom;
            }
            return $this->defaultRules();
        });

        echo $body;
        exit;
    }

    /**
     * Security rules applied to every crawler group.
     * Per RFC 9309 a more specific user-agent group overrides "User-agent: *",
     * so these paths must be repeated in each group or AI/search bots could
     * reach them even though the wildcard group blocks them.
     *
     * @return string[]
     */
    private function securityRules(): array
    {
        return [
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /logout',
            'Disallow: /verify-otp',
            'Disallow: /auth/',
            'Disallow: /dashboard/',
            'Disallow: /api/',
            'Disallow: /cart',
            'Disallow: /cart/summary',
            'Disallow: /wishlist',
            'Disallow: /shop/cart',
            'Disallow: /shop/coupon/',
            'Disallow: /shop/wishlist/',
            'Disallow: /shop/payment/callback',
            'Disallow: /dashboard/wallet/payment/callback',
            'Disallow: /course/*/watch',
            'Disallow: /course/*/certificate',
            'Disallow: /course/*/enroll',
            'Disallow: /media/',
            'Disallow: /avatar/',
            'Disallow: /booking/captcha',
        ];
    }

    /**
     * @return string[] user-agent tokens that may index the whole site
     */
    private function allowedAgents(): array
    {
        return [
            // Search engines
            'Googlebot',
            'Bingbot',
            'Applebot',
            'Yandex',
            'Baiduspider',
            // AI search / citation agents
            'GPTBot',
            'OAI-SearchBot',
            'ChatGPT-User',
            'ClaudeBot',
            'Claude-SearchBot',
            'Claude-User',
            'anthropic-ai',
            'Claude-Web',
            'PerplexityBot',
            'Perplexity-User',
            'Google-Extended',
            'Applebot-Extended',
            'CCBot',
            'Bytespider',
            'meta-externalagent',
            'meta-externalfetcher',
            'cohere-ai',
            'Amazonbot',
            'DuckAssistBot',
        ];
    }

    private function defaultRules(): string
    {
        $lines = [
            '# robots.txt — ' . (Settings::get('brand_name', '') ?: 'Mobaro'),
            '# Generated automatically. Custom content saved in the admin panel',
            '# (SEO settings) overrides this default file.',
            '',
        ];

        $allow = ['Allow: /'];
        $security = $this->securityRules();

        foreach ($this->allowedAgents() as $agent) {
            $lines[] = 'User-agent: ' . $agent;
            foreach (array_merge($allow, $security) as $rule) {
                $lines[] = $rule;
            }
            $lines[] = '';
        }

        // Default group — everything else.
        $lines[] = 'User-agent: *';
        foreach (array_merge($allow, $security) as $rule) {
            $lines[] = $rule;
        }
        $lines[] = '';
        $lines[] = 'Sitemap: ' . url('/sitemap.xml');
        $lines[] = '# AI-friendly content index: ' . url('/llms.txt');
        $lines[] = '';

        return implode("\n", $lines);
    }
}
