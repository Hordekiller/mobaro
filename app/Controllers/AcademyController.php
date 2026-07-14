<?php

class AcademyController extends BaseController
{
    private function getSidebar(): array
    {
        return Cache::remember('academy_sidebar', Config::get('cache.ttl.page', 600), function () {
            $categories = Database::fetchAll(
                "SELECT category, COUNT(*) as cnt FROM courses WHERE is_active = 1 GROUP BY category ORDER BY cnt DESC"
            );
            $totalStudents = Database::fetch("SELECT COALESCE(SUM(students), 0) as total FROM courses")['total'] ?? 0;
            return compact('categories', 'totalStudents');
        }, 'academy');
    }

    public function index(): void
    {
        $tab = sanitize($_GET['tab'] ?? 'newest');
        $category = sanitize($_GET['category'] ?? 'all');

        $where = "WHERE c.is_active = 1";
        $params = [];

        if ($category !== 'all') {
            $where .= " AND c.category = ?";
            $params[] = $category;
        }

        $orderBy = match ($tab) {
            'popular' => 'c.students DESC',
            'free' => 'c.is_free DESC, c.price ASC',
            default => 'c.id DESC',
        };

        if ($tab === 'free') {
            $where .= " AND c.is_free = 1";
        }

        $cacheKey = 'academy_list_' . md5(serialize([$tab, $category]));
        $courses = Cache::remember($cacheKey, Config::get('cache.ttl.page', 600), function () use ($where, $params, $orderBy) {
            return Database::fetchAll(
                "SELECT c.* FROM courses c {$where} ORDER BY {$orderBy}",
                $params
            );
        }, 'academy');

        $featuredCourse = Cache::remember('academy_featured', Config::get('cache.ttl.page', 600), function () {
            return Database::fetch(
                "SELECT * FROM courses WHERE is_active = 1 ORDER BY RAND() LIMIT 1"
            );
        }, 'academy');

        $sidebar = $this->getSidebar();
        $settings = Settings::all();

        $this->view('academy/index', [
            'courses' => $courses, 'tab' => $tab, 'category' => $category,
            'settings' => $settings, 'featuredCourse' => $featuredCourse,
        ] + $sidebar);
    }

    public function show(string $slug): void
    {
        $course = Cache::remember('course_' . $slug, 600, function () use ($slug) {
            $c = Database::fetch(
                "SELECT c.* FROM courses c WHERE (c.slug = ? OR c.id = ?) AND c.is_active = 1",
                [$slug, (int) $slug]
            );
            if ($c) {
                Cache::tag('academy', 'course_' . $slug);
            }
            return $c;
        });

        if (!$course) {
            http_response_code(404);
            require __DIR__ . '/../views/layouts/header.php';
            require __DIR__ . '/../views/errors/404.php';
            require __DIR__ . '/../views/layouts/footer.php';
            return;
        }

        $related = Database::fetchAll(
            "SELECT * FROM courses WHERE category = ? AND id != ? AND is_active = 1 LIMIT 3",
            [$course['category'], $course['id']]
        );

        $settings = Settings::all();

        $this->view('academy/detail', compact('course', 'related', 'settings'));
    }

    public function enroll(string $slug): void
    {
        if (empty($_SESSION['user'])) {
            redirect('/login');
            return;
        }

        if (!verifyCsrf($_POST['_csrf'] ?? '')) {
            back();
            return;
        }

        $course = Database::fetch(
            "SELECT id, is_free FROM courses WHERE (slug = ? OR id = ?) AND is_active = 1",
            [$slug, (int) $slug]
        );

        if (!$course) {
            redirect('/academy');
            return;
        }

        if (!$course['is_free']) {
            redirect('/course/' . $slug);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $existing = Database::fetch(
            "SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?",
            [$userId, $course['id']]
        );

        if (!$existing) {
            Database::insert('course_enrollments', [
                'user_id' => $userId,
                'course_id' => $course['id'],
                'progress' => 0,
            ]);

            Database::query("UPDATE courses SET students = students + 1 WHERE id = ?", [$course['id']]);
            Cache::forget('course_' . $slug);
            Cache::forget('academy_sidebar');
        }

        redirect('/dashboard/courses');
    }

    public function watch(string $slug): void
    {
        if (empty($_SESSION['user'])) {
            redirect('/login?redirect=/course/' . $slug . '/watch');
            return;
        }

        $course = Cache::remember('course_' . $slug, Config::get('cache.ttl.page', 600), function () use ($slug) {
            $c = Database::fetch(
                "SELECT c.* FROM courses c WHERE (c.slug = ? OR c.id = ?) AND c.is_active = 1",
                [$slug, (int) $slug]
            );
            if ($c) {
                Cache::tag('academy', 'course_' . $slug);
            }
            return $c;
        });

        if (!$course) {
            http_response_code(404);
            require __DIR__ . '/../views/layouts/header.php';
            require __DIR__ . '/../views/errors/404.php';
            require __DIR__ . '/../views/layouts/footer.php';
            return;
        }

        $userId = $_SESSION['user']['id'];
        $enrollment = Database::fetch(
            "SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?",
            [$userId, $course['id']]
        );

        if (!$enrollment) {
            redirect('/course/' . $slug);
            return;
        }

        $completedLessons = Database::fetchAll(
            "SELECT lesson_index FROM course_lessons_completed WHERE user_id = ? AND course_id = ?",
            [$userId, $course['id']]
        );
        $completedIndexes = array_column($completedLessons, 'lesson_index');

        $curriculum = json_decode($course['curriculum'] ?? '[]', true) ?: [];
        $totalLessons = 0;
        foreach ($curriculum as $module) {
            $totalLessons += count($module['lessons'] ?? []);
        }

        $activeModule = (int) ($_GET['module'] ?? 0);
        $activeLesson = (int) ($_GET['lesson'] ?? 0);

        $activeModule = max(0, min($activeModule, max(0, count($curriculum) - 1)));
        if (!empty($curriculum[$activeModule]['lessons'])) {
            $activeLesson = max(0, min($activeLesson, count($curriculum[$activeModule]['lessons']) - 1));
        }

        $settings = Settings::all();

        $courseMedia = null;
        if (!empty($course['video_url']) && $course['video_type'] === 'upload') {
            $courseMedia = Cache::remember('course_media_' . $course['id'], Config::get('cache.ttl.page', 600), function () use ($course) {
                $row = Database::fetch("SELECT id FROM media WHERE filepath = ?", [ltrim($course['video_url'], '/')]);
                Cache::tag('academy', 'course_media_' . $course['id']);
                return $row;
            });
        }

        $this->view('academy/watch', compact('course', 'enrollment', 'curriculum', 'completedIndexes', 'totalLessons', 'activeModule', 'activeLesson', 'settings', 'courseMedia'));
    }

    public function completeLesson(): void
    {
        if (empty($_SESSION['user'])) {
            $this->json(['error' => 'لطفاً ابتدا وارد شوید'], 401);
            return;
        }
        $this->verifyCsrf();

        $courseId = (int) ($_POST['course_id'] ?? 0);
        $lessonIndex = (int) ($_POST['lesson_index'] ?? 0);
        $moduleIndex = (int) ($_POST['module_index'] ?? 0);

        if (!$courseId || $lessonIndex < 0) {
            $this->json(['error' => 'اطلاعات نامعتبر'], 400);
            return;
        }

        $userId = $_SESSION['user']['id'];

        $enrollment = Database::fetch(
            "SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?",
            [$userId, $courseId]
        );
        if (!$enrollment) {
            $this->json(['error' => 'ثبت‌نام نشده‌اید'], 403);
            return;
        }

        $existing = Database::fetch(
            "SELECT id FROM course_lessons_completed WHERE user_id = ? AND course_id = ? AND lesson_index = ?",
            [$userId, $courseId, $lessonIndex]
        );

        if (!$existing) {
            Database::insert('course_lessons_completed', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'module_index' => $moduleIndex,
                'lesson_index' => $lessonIndex,
            ]);
        }

        $course = Database::fetch("SELECT curriculum FROM courses WHERE id = ?", [$courseId]);
        $curriculum = json_decode($course['curriculum'] ?? '[]', true) ?: [];
        $totalLessons = 0;
        foreach ($curriculum as $module) {
            $totalLessons += count($module['lessons'] ?? []);
        }

        $completedCount = Database::fetch(
            "SELECT COUNT(*) as cnt FROM course_lessons_completed WHERE user_id = ? AND course_id = ?",
            [$userId, $courseId]
        )['cnt'] ?? 0;

        $progress = $totalLessons > 0 ? min(100, round(($completedCount / $totalLessons) * 100)) : 0;

        Database::update('course_enrollments', ['progress' => $progress], 'user_id = :uid AND course_id = :cid', ['uid' => $userId, 'cid' => $courseId]);

        $this->json([
            'success' => true,
            'progress' => $progress,
            'completed' => $completedCount,
            'total' => $totalLessons,
            'message' => $progress >= 100 ? 'تبریک! دوره را تکمیل کردید.' : 'درس تکمیل شد.',
        ]);
    }

    public function certificate(string $slug): void
    {
        if (empty($_SESSION['user'])) {
            redirect('/login');
            return;
        }

        $course = Database::fetch(
            "SELECT c.* FROM courses c WHERE (c.slug = ? OR c.id = ?) AND c.is_active = 1",
            [$slug, (int) $slug]
        );

        if (!$course) {
            redirect('/academy');
            return;
        }

        $userId = $_SESSION['user']['id'];
        $enrollment = Database::fetch(
            "SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?",
            [$userId, $course['id']]
        );

        if (!$enrollment || $enrollment['progress'] < 100) {
            redirect('/course/' . $slug . '/watch');
            return;
        }

        $user = $_SESSION['user'];
        $certificateDate = jdate('Y/m/d', strtotime($enrollment['created_at']));

        $this->view('academy/certificate', compact('course', 'user', 'enrollment', 'certificateDate'));
    }
}
