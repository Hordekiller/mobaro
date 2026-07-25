<?php
$title = e($course['title']) . ' | مشاهده دوره';
$currentGlobalIndex = 0;
$lessonMap = [];
foreach ($curriculum as $mi => $module) {
    foreach (($module['lessons'] ?? []) as $li => $lesson) {
        $lessonMap[] = ['module' => $mi, 'lesson' => $li, 'title' => $lesson['title'], 'duration' => $lesson['duration'] ?? ''];
    }
}
$activeIdx = 0;
foreach ($lessonMap as $idx => $lm) {
    if ($lm['module'] === $activeModule && $lm['lesson'] === $activeLesson) {
        $activeIdx = $idx;
        break;
    }
}
$activeLessonData = $lessonMap[$activeIdx] ?? ($lessonMap[0] ?? null);
$activeGlobalKey = $activeModule . '-' . $activeLesson;
?>

<div class="max-w-screen-2xl mx-auto px-4 lg:px-8 py-6">
    <nav class="flex items-center gap-2 text-sm text-zinc-500 mb-4">
        <a href="/" class="hover:text-rose-600 transition-colors">خانه</a>
        <i class="fa-solid fa-chevron-left text-[10px]"></i>
        <a href="/academy" class="hover:text-rose-600 transition-colors">آکادمی</a>
        <i class="fa-solid fa-chevron-left text-[10px]"></i>
        <a href="/course/<?= e($course['slug'] ?: $course['id']) ?>" class="hover:text-rose-600 transition-colors"><?= e($course['title']) ?></a>
        <i class="fa-solid fa-chevron-left text-[10px]"></i>
        <span class="text-zinc-800 font-medium">مشاهده دوره</span>
    </nav>

    <div class="grid lg:grid-cols-[1fr_380px] gap-6">
        <!-- Main Content -->
        <div class="space-y-5">
            <!-- Video Player -->
            <div class="bg-zinc-900 rounded-2xl overflow-hidden">
                <?php if (!empty($course['video_url'])) : ?>
                <div class="aspect-video">
                    <?php $videoType = $course['video_type'] ?? 'upload'; ?>
                    <?php if ($videoType === 'youtube') : ?>
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/<?= e(getYoutubeId($course['video_url'])) ?>?autoplay=1" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" id="course-video"></iframe>
                    <?php elseif ($videoType === 'aparat') : ?>
                    <iframe class="w-full h-full" src="https://www.aparat.com/video/video/embed/videohash/<?= e(getAparatHash($course['video_url'])) ?>/vt/frame" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" id="course-video"></iframe>
                    <?php elseif (!empty($courseMedia)) : ?>
                    <video controls autoplay id="course-video" class="w-full h-full object-contain"
                           data-course-id="<?= $course['id'] ?>"
                           data-module="<?= $activeModule ?>"
                           data-lesson="<?= $activeLesson ?>"
                           data-index="<?= $activeIdx ?>"
                           ontimeupdate="onVideoTimeUpdate(this)">
                        <source src="/media/stream/<?= $courseMedia['id'] ?>" type="video/mp4">
                        مرورگر شما پخش ویدیو را پشتیبانی نمی‌کند.
                    </video>
                    <?php else : ?>
                    <div class="aspect-video flex items-center justify-center text-white/60">
                        <div class="text-center">
                            <i class="fa-solid fa-play-circle text-6xl mb-4"></i>
                            <p class="text-lg">ویدیوی این درس هنوز بارگذاری نشده است</p>
                            <p class="text-sm text-white/40 mt-2">می‌توانید محتوای متنی زیر را مطالعه کنید</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="aspect-video flex items-center justify-center text-white/60">
                    <div class="text-center">
                        <i class="fa-solid fa-play-circle text-6xl mb-4"></i>
                        <p class="text-lg">ویدیوی این درس هنوز بارگذاری نشده است</p>
                        <p class="text-sm text-white/40 mt-2">می‌توانید محتوای متنی زیر را مطالعه کنید</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Lesson Info -->
            <div class="bg-white rounded-2xl border border-zinc-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <span class="text-xs text-zinc-400">درس <?= faNum($activeIdx + 1) ?> از <?= faNum($totalLessons) ?></span>
                        <h2 class="text-lg font-bold mt-1"><?= e($activeLessonData['title'] ?? '') ?></h2>
                    </div>
                    <button onclick="markComplete()" id="complete-btn"
                        class="px-5 py-2.5 rounded-xl font-semibold text-sm transition-all <?= in_array($activeIdx, $completedIndexes) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-600 text-white hover:bg-rose-700' ?>"
                        data-completed="<?= in_array($activeIdx, $completedIndexes) ? '1' : '0' ?>">
                        <?php if (in_array($activeIdx, $completedIndexes)) : ?>
                        <i class="fa-solid fa-check-circle ml-1"></i>تکمیل شده
                        <?php else : ?>
                        <i class="fa-solid fa-circle-check ml-1"></i>تکمیل درس
                        <?php endif; ?>
                    </button>
                </div>
                <?php if (!empty($activeLessonData['duration'])) : ?>
                <div class="text-sm text-zinc-400"><i class="fa-regular fa-clock ml-1"></i><?= e($activeLessonData['duration']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Progress Bar -->
            <div class="bg-white rounded-2xl border border-zinc-100 p-5">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium">پیشرفت دوره</span>
                    <span class="text-sm font-bold text-rose-600" id="progress-pct"><?= $enrollment['progress'] ?>%</span>
                </div>
                <div class="h-3 bg-zinc-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-l from-rose-500 to-amber-400 rounded-full transition-all duration-500" id="progress-bar" style="width: <?= $enrollment['progress'] ?>%"></div>
                </div>
                <div class="flex justify-between text-xs text-zinc-400 mt-2">
                    <span id="completed-count"><?= faNum(count($completedIndexes)) ?> درس تکمیل شده</span>
                    <span><?= faNum($totalLessons) ?> درس کل</span>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between">
                <?php if ($activeIdx > 0) : ?>
                    <?php $prev = $lessonMap[$activeIdx - 1]; ?>
                <a href="/course/<?= e($course['slug'] ?: $course['id']) ?>/watch?module=<?= $prev['module'] ?>&lesson=<?= $prev['lesson'] ?>" class="px-5 py-3 bg-white border border-zinc-200 rounded-xl font-semibold text-sm hover:bg-zinc-50 transition-all">
                    <i class="fa-solid fa-arrow-right ml-1"></i>درس قبلی
                </a>
                <?php else : ?>
                <div></div>
                <?php endif; ?>
                <?php if ($activeIdx < count($lessonMap) - 1) : ?>
                    <?php $next = $lessonMap[$activeIdx + 1]; ?>
                <a href="/course/<?= e($course['slug'] ?: $course['id']) ?>/watch?module=<?= $next['module'] ?>&lesson=<?= $next['lesson'] ?>" class="px-5 py-3 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:bg-rose-700 transition-all">
                    درس بعدی<i class="fa-solid fa-arrow-left mr-1"></i>
                </a>
                <?php else : ?>
                <a href="/course/<?= e($course['slug'] ?: $course['id']) ?>/certificate" class="px-5 py-3 bg-emerald-600 text-white rounded-xl font-semibold text-sm hover:bg-emerald-700 transition-all">
                    <i class="fa-solid fa-certificate mr-1"></i>دریافت گواهی
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar - Curriculum -->
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-zinc-100 overflow-hidden lg:sticky lg:top-24">
                <div class="p-4 border-b border-zinc-100">
                    <h3 class="font-bold">برنامه درسی</h3>
                    <p class="text-xs text-zinc-400 mt-1"><?= faNum(count($curriculum)) ?> ماژول • <?= faNum($totalLessons) ?> درس</p>
                </div>
                <div class="max-h-[60vh] overflow-y-auto">
                    <?php foreach ($curriculum as $mi => $module) : ?>
                    <div class="border-b border-zinc-50 last:border-0">
                        <div class="px-4 py-3 bg-zinc-50 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center text-xs font-bold"><?= faNum($mi + 1) ?></span>
                                <span class="font-medium text-sm"><?= e($module['title'] ?? '') ?></span>
                            </div>
                            <span class="text-xs text-zinc-400"><?= e($module['duration'] ?? '') ?></span>
                        </div>
                        <div class="px-2 py-1">
                            <?php foreach (($module['lessons'] ?? []) as $li => $lesson) :
                                $globIdx = 0;
                                for ($k = 0; $k < $mi; $k++) {
                                    $globIdx += count($curriculum[$k]['lessons'] ?? []);
                                }
                                $globIdx += $li;
                                $isActive = ($mi === $activeModule && $li === $activeLesson);
                                $isDone = in_array($globIdx, $completedIndexes);
                                ?>
                            <a href="/course/<?= e($course['slug'] ?: $course['id']) ?>/watch?module=<?= $mi ?>&lesson=<?= $li ?>"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all <?= $isActive ? 'bg-rose-50 text-rose-700 font-semibold' : ($isDone ? 'bg-emerald-50/50 text-emerald-700' : 'hover:bg-zinc-50 text-zinc-600') ?>">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs <?= $isDone ? 'bg-emerald-500 text-white' : ($isActive ? 'bg-rose-500 text-white' : 'bg-zinc-100 text-zinc-400') ?>">
                                    <?php if ($isDone) :
                                        ?><i class="fa-solid fa-check"></i><?php
                                    else :
                                        ?><?= faNum($li + 1) ?><?php
                                    endif; ?>
                                </span>
                                <span class="flex-1 min-w-0 line-clamp-1"><?= e($lesson['title'] ?? '') ?></span>
                                <?php if (!empty($lesson['duration'])) : ?>
                                <span class="text-xs text-zinc-400 flex-shrink-0"><?= e($lesson['duration']) ?></span>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markComplete() {
    var btn = document.getElementById('complete-btn');
    if (btn.dataset.completed === '1') return;

    var courseId = <?= $course['id'] ?>;
    var moduleIdx = <?= $activeModule ?>;
    var lessonIdx = <?= $activeLesson ?>;
    var globalIdx = <?= $activeIdx ?>;

    fetch('/course/lesson/complete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'course_id=' + courseId + '&module_index=' + moduleIdx + '&lesson_index=' + globalIdx + '&' + csrfParam()
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.success) {
            btn.dataset.completed = '1';
            btn.className = 'px-5 py-2.5 rounded-xl font-semibold text-sm transition-all bg-emerald-100 text-emerald-700';
            btn.innerHTML = '<i class="fa-solid fa-check-circle ml-1"></i>تکمیل شده';
            document.getElementById('progress-pct').textContent = d.progress + '%';
            document.getElementById('progress-bar').style.width = d.progress + '%';
            document.getElementById('completed-count').textContent = d.completed + ' درس تکمیل شده';
            showToast(d.message, d.progress >= 100 ? 'success' : 'success');
            var sidebarLesson = document.querySelector('a[href*="module=' + moduleIdx + '"][href*="lesson=' + lessonIdx + '"] .w-6');
            if (sidebarLesson) {
                sidebarLesson.className = 'w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs bg-emerald-500 text-white';
                sidebarLesson.innerHTML = '<i class="fa-solid fa-check"></i>';
            }
        } else {
            showToast(d.error || 'خطا', 'error');
        }
    }).catch(function() { showToast('خطا در ارتباط با سرور', 'error'); });
}

function onVideoTimeUpdate(video) {
    if (video.duration && video.currentTime >= video.duration * 0.9) {
        var btn = document.getElementById('complete-btn');
        if (btn && btn.dataset.completed !== '1') {
            markComplete();
        }
    }
}

<?php $videoType = $course['video_type'] ?? 'upload'; ?>
<?php if ($videoType === 'youtube') : ?>
var tag = document.createElement('script');
tag.src = 'https://www.youtube.com/iframe_api';
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
var ytPlayer;
function onYouTubeIframeAPIReady() {
    ytPlayer = new YT.Player('course-video', {
        events: {
            'onStateChange': function(e) {
                if (e.data === YT.PlayerState.ENDED) {
                    var btn = document.getElementById('complete-btn');
                    if (btn && btn.dataset.completed !== '1') markComplete();
                }
            }
        }
    });
}
<?php endif; ?>
</script>
