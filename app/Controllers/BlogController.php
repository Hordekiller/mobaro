<?php

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
                    if (!empty($tag)) $allTags[$tag] = true;
                }
            }
            $allTags = array_keys($allTags);

            $latestCourse = Database::fetch(
                "SELECT id, title, teacher, image, slug, duration, rating, students, is_free, price, category FROM courses WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1"
            );

            return compact('featured', 'categories', 'popularPosts', 'allTags', 'latestCourse');
        }, 'blog');
    }

    public function index(): void
    {
        $category = sanitize($_GET['category'] ?? '');
        $search = sanitize($_GET['s'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $perPage = (int) Settings::get('blog_posts_per_page', 6);
        if ($perPage < 1) $perPage = 6;
        $offset = ($page - 1) * $perPage;

        $where = "AND is_published = 1";
        $params = [];

        if (!empty($category)) {
            $where .= " AND category = ?";
            $params[] = $category;
        }
        if (!empty($search)) {
            $where .= " AND (title LIKE ? OR content LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

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

        $sidebar = $this->getSidebar();
        $settings = Settings::all();

        $this->view('blog/index', [
            'posts' => $posts, 'category' => $category, 'search' => $search,
            'page' => $page, 'totalPages' => $totalPages, 'settings' => $settings,
        ] + $sidebar);
    }

    public function postComment(string $slug): void
    {
        header('Content-Type: application/json');
        $this->verifyCsrf();

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

        if ($name) $data['name'] = $name;
        if ($email) $data['email'] = $email;

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
        header('Content-Type: application/json');
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

    public function show(string $slug): void
    {
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
            require __DIR__ . '/../views/layouts/header.php';
            require __DIR__ . '/../views/errors/404.php';
            require __DIR__ . '/../views/layouts/footer.php';
            return;
        }

        Database::query(
            "UPDATE blog_posts SET views = views + 1 WHERE id = ?",
            [$post['id']]
        );
        $post['views']++;

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

        $this->view('blog/show', [
            'post' => $post, 'relatedPosts' => $relatedPosts, 'settings' => $settings, 'tags' => $tags,
            'comments' => $comments, 'commentCount' => $commentCount,
        ] + $sidebar);
    }
}
