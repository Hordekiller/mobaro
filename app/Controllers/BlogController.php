<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Config;
use App\Database;
use App\Settings;
use App\SEOService;
use App\RateLimiter;
use App\Auth;
use App\StructuredData;
use App\FileUploader;

class BlogController extends BaseController
{
    private function getSidebar(): array
    {
        return Cache::remember('blog_sidebar', Config::get('cache.ttl.page', 600), function () {
            $featured = Database::fetch(
                "SELECT * FROM blog_posts WHERE is_featured = 1 AND is_published = 1 ORDER BY published_at DESC LIMIT 1"
            );

            $categories = Database::fetchAll(
                "SELECT category, COUNT(*) as cnt FROM blog_posts WHERE is_published = 1 AND category IS NOT NULL GROUP BY category ORDER BY cnt DESC"
            );

            $popularPosts = Database::fetchAll(
                "SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY views DESC LIMIT 3"
            );

            $tags = Database::fetchAll(
                "SELECT tags FROM blog_posts WHERE is_published = 1 AND tags IS NOT NULL"
            );
            $allTags = [];
            foreach ($tags as $t) {
                $parts = explode(',', $t['tags']);
                foreach ($parts as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                        $allTags[$tag] = true;
                    }
                }
            }
            $allTags = array_keys($allTags);

            $latestCourseRow = Database::fetch(
                "SELECT id, title, teacher, image, slug, duration, rating, students, is_free, price, category FROM courses WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
            );
            $latestCourse = $latestCourseRow ? normalizeCourse($latestCourseRow) : null;

            return compact('featured', 'categories', 'popularPosts', 'allTags', 'latestCourse');
        }, 'blog');
    }

    public function index(): void
    {
        $category = sanitize($_GET['category'] ?? '');
        $search = sanitize($_GET['s'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $perPage = (int) Settings::get('blog_posts_per_page', 6);
        if ($perPage < 1) {
            $perPage = 6;
        }
        $offset = ($page - 1) * $perPage;

        $where = "AND is_published = 1";
        $params = [];

        if (!empty($category)) {
            $where .= " AND category = ?";
            $params[] = $category;
        }
        if (!empty($search)) {
            $where .= " AND (title LIKE ? OR content LIKE ?)";
            $params[] = likePattern($search);
            $params[] = likePattern($search);
        }

        $cacheKey = 'blog_list_' . hash('sha256', serialize([$category, $search, $page]));
        $cached = Cache::remember($cacheKey, Config::get('cache.ttl.page', 600), function () use ($where, $params, $perPage, $offset) {
            $countResult = Database::fetch(
                "SELECT COUNT(*) as cnt FROM blog_posts WHERE 1=1 {$where}",
                $params
            );
            $totalPosts = (int) ($countResult['cnt'] ?? 0);
            $totalPages = max(1, (int) ceil($totalPosts / $perPage));
            $allParams = array_merge($params, [$perPage, $offset]);
            $posts = Database::fetchAll(
                "SELECT * FROM blog_posts WHERE 1=1 {$where} ORDER BY published_at DESC LIMIT ? OFFSET ?",
                $allParams
            );
            return compact('posts', 'totalPosts', 'totalPages');
        }, 'blog');

        $posts = $cached['posts'];
        $totalPages = $cached['totalPages'];

        $sidebar = $this->getSidebar();
        $settings = Settings::all();
        $seo = SEOService::forPage('blog');

        $this->view('blog/index', [
            'posts' => $posts, 'category' => $category, 'search' => $search,
            'page' => $page, 'totalPages' => $totalPages, 'settings' => $settings,
            'seo' => $seo,
        ] + $sidebar);
    }

    public function postComment(string $slug): void
    {
        header(self::CONTENT_TYPE_JSON);
        $this->verifyCsrf();

        // Keep in sync with show(): map URL slug variations to the stored slug.
        $slug = slugify($slug);

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (RateLimiter::isLocked('comment:' . $ip, 5, 15)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'درخواست‌های شما بیش از حد مجاز است. لطفاً چند دقیقه صبر کنید.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        RateLimiter::recordAttempt('comment:' . $ip);

        $post = Database::fetch("SELECT id FROM blog_posts WHERE slug = ?", [$slug]);
        if (!$post) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'پست مورد نظر یافت نشد.']);
            exit;
        }

        $text = trim($_POST['text'] ?? '');
        if (empty($text)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'متن نظر را وارد کنید.']);
            exit;
        }

        $userId = Auth::check() ? Auth::id() : null;
        $name = sanitize(trim($_POST['name'] ?? ''));
        $email = sanitize(trim($_POST['email'] ?? ''));

        if (!$userId && empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'نام خود را وارد کنید.']);
            exit;
        }

        $data = [
            'post_id' => $post['id'],
            'user_id' => $userId,
            'text' => sanitize($text),
            'is_approved' => 0,
        ];

        if ($name) {
            $data['name'] = $name;
        }
        if ($email) {
            $data['email'] = $email;
        }

        if ($userId) {
            $user = Database::fetch("SELECT name, email FROM users WHERE id = ?", [$userId]);
            if ($user) {
                $data['name'] = $user['name'];
                $data['email'] = $user['email'];
            }
        }

        Database::insert('blog_comments', $data);

        echo json_encode(['success' => true, 'message' => 'نظر شما با موفقیت ثبت شد و پس از تأیید نمایش داده می‌شود.']);
        exit;
    }

    public function likeComment(): void
    {
        header(self::CONTENT_TYPE_JSON);
        $this->verifyCsrf();

        $commentId = (int) ($_POST['comment_id'] ?? 0);
        if (!$commentId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'نظر نامعتبر است.']);
            exit;
        }

        Database::query(
            "UPDATE blog_comments SET likes = likes + 1 WHERE id = ?",
            [$commentId]
        );
        $comment = Database::fetch("SELECT likes FROM blog_comments WHERE id = ?", [$commentId]);

        echo json_encode(['success' => true, 'likes' => $comment['likes'] ?? 0]);
        exit;
    }

    public function uploadImage(): void
    {
        header(self::CONTENT_TYPE_JSON);
        $this->requireAdmin();

        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!verifyCsrf($token)) {
            http_response_code(419);
            echo json_encode(['error' => ['message' => 'درخواست نامعتبر (CSRF).']]);
            exit;
        }

        if (empty($_FILES['file']['name']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => ['message' => 'فایل ارسال نشد.']]);
            exit;
        }

        $uploaded = FileUploader::upload($_FILES['file'], 'blog');
        if (!$uploaded) {
            http_response_code(400);
            echo json_encode(['error' => ['message' => 'آپلود ناموفق بود.']]);
            exit;
        }

        $this->registerInMedia($uploaded, $_FILES['file'], 'blog');

        echo json_encode(['location' => '/assets/images/' . $uploaded]);
        exit;
    }

    /**
     * Register an uploaded image in the shared media library so every image
     * inserted through TinyMCE is also visible/selectable in the site gallery.
     */
    private function registerInMedia(string $filename, array $file, string $sourceType): void
    {
        $filepath = 'assets/images/' . $filename;
        $publicDir = realpath(__DIR__ . '/../../public');
        $fullPath = realpath($publicDir . '/' . $filepath);
        if ($fullPath === false || !str_starts_with($fullPath, $publicDir . '/')) {
            return;
        }

        $existing = Database::fetch("SELECT id FROM media WHERE filepath = ?", [$filepath]);
        if ($existing) {
            return;
        }

        $mime = file_exists($fullPath) ? mime_content_type($fullPath) : ($file['type'] ?? '');
        $size = file_exists($fullPath) ? filesize($fullPath) : 0;

        Database::insert('media', [
            'filepath' => $filepath,
            'original_name' => $file['name'] ?? $filename,
            'type' => 'image',
            'mime_type' => $mime,
            'size' => $size,
            'source_type' => $sourceType,
            'uploaded_by' => Auth::id(),
        ]);
    }

    /**
     * JSON list of gallery images for the TinyMCE media-picker modal.
     * Only authenticated admins may call it; returns public-absolute URLs.
     */
    public function galleryImages(): void
    {
        header(self::CONTENT_TYPE_JSON);
        $this->requireAdmin();
        echo json_encode(
            ['items' => $this->collectGalleryEntries()],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    /**
     * Build the gallery picker entries (id, name, public url, stream url) for
     * the current media library. Kept free of output/exit so it is testable.
     */
    private function collectGalleryEntries(): array
    {
        $rows = Database::fetchAll(
            "SELECT id, filepath, original_name, mime_type, alt_text
             FROM media
             WHERE type = 'image' AND is_active = 1
             ORDER BY id DESC"
        );

        $items = [];
        foreach ($rows as $row) {
            $path = ltrim((string) ($row['filepath'] ?? ''), '/');
            if ($path === '') {
                continue;
            }
            $items[] = [
                'id'       => (int) $row['id'],
                'name'     => ($row['alt_text'] ?? '') !== '' ? $row['alt_text'] : ($row['original_name'] ?? ''),
                'url'      => url('/' . $path),
                'original' => url('/media/stream/' . (int) $row['id']),
            ];
        }

        return $items;
    }

    public function show(string $slug): void
    {
        // Normalize the incoming slug so URL variations — a stray space, a
        // percent-encoded space (%20) already decoded by the Router, or
        // duplicated/collapsed separators (e.g. "bleach -touch-up") — all map
        // to the canonical stored slug (e.g. "bleach-touch-up"). Without this,
        // a manually-entered slug that accidentally contained a space would
        // produce /blog/bleach%20-touch-up... and 404 on the published post.
        $slug = slugify($slug);

        $post = Cache::remember('blog_post_' . $slug, Config::get('cache.ttl.page', 600), function () use ($slug) {
            $p = Database::fetch(
                "SELECT * FROM blog_posts WHERE slug = ? AND is_published = 1",
                [$slug]
            );
            if ($p) {
                Cache::tag('blog', 'blog_post_' . $slug);
            }
            return $p;
        });

        if (!$post) {
            http_response_code(404);
            $settings = Settings::all();
            require_once __DIR__ . '/../views/layouts/header.php';
            require_once __DIR__ . '/../views/errors/404.php';
            require_once __DIR__ . '/../views/layouts/footer.php';
            return;
        }

        Database::query(
            "UPDATE blog_posts SET views = views + 1 WHERE id = ?",
            [$post['id']]
        );
        $post['views']++;
        Cache::set('blog_post_' . $slug, $post, Config::get('cache.ttl.page', 600));

        $relatedPosts = Database::fetchAll(
            "SELECT * FROM blog_posts WHERE is_published = 1 AND category = ? AND id != ? ORDER BY published_at DESC LIMIT 3",
            [$post['category'], $post['id']]
        );
        if (empty($relatedPosts)) {
            $relatedPosts = Database::fetchAll(
                "SELECT * FROM blog_posts WHERE is_published = 1 AND id != ? ORDER BY published_at DESC LIMIT 3",
                [$post['id']]
            );
        }

        $sidebar = $this->getSidebar();
        $settings = Settings::all();

        $tags = !empty($post['tags']) ? explode(',', $post['tags']) : [];

        $comments = Database::fetchAll(
            "SELECT * FROM blog_comments WHERE post_id = ? AND is_approved = 1 ORDER BY created_at DESC",
            [$post['id']]
        );
        $commentCount = count($comments);

        $seo = SEOService::forBlogPost($post);

        $jsonLd = StructuredData::render(
            StructuredData::organization(),
            StructuredData::breadcrumb([
                ['name' => 'خانه', 'url' => url('/')],
                ['name' => 'وبلاگ', 'url' => url('/blog')],
                ['name' => (string) $post['title'], 'url' => url('/blog/' . $slug)],
            ]),
            StructuredData::blogPosting($post)
        );

        $this->view('blog/show', [
            'post' => $post, 'relatedPosts' => $relatedPosts, 'settings' => $settings, 'tags' => $tags,
            'comments' => $comments, 'commentCount' => $commentCount, 'seo' => $seo, 'jsonLd' => $jsonLd,
        ] + $sidebar);
    }
}
