<?php
$filter = $filter ?? '';
$search = $search ?? '';
$items = $items ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
?>
<div class="mb-6 flex justify-between items-center flex-wrap gap-3">
    <div>
        <h2 class="text-2xl font-extrabold">گالری رسانه</h2>
        <p class="text-zinc-400 text-sm"><?= faNum($total) ?> فایل</p>
    </div>
    <div class="flex items-center gap-3">
        <form method="GET" action="/admin/gallery" class="flex items-center gap-2">
            <select name="filter" onchange="this.form.submit()" class="px-3 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm focus:border-rose-500 focus:ring-0 outline-none transition-all">
                <option value="">همه</option>
                <option value="image" <?= $filter === 'image' ? 'selected' : '' ?>>تصاویر</option>
                <option value="video" <?= $filter === 'video' ? 'selected' : '' ?>>ویدیوها</option>
            </select>
            <input type="text" name="s" value="<?= e($search) ?>" placeholder="جستجو..." class="px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm focus:border-rose-500 focus:ring-0 outline-none transition-all w-44">
            <button type="submit" class="px-3 py-2.5 bg-zinc-100 text-zinc-600 rounded-xl text-sm hover:bg-rose-50 hover:text-rose-600 transition-all"><i class="fa-solid fa-search"></i></button>
        </form>
        <button onclick="showUploadModal()" class="px-4 py-2.5 bg-rose-600 text-white rounded-xl text-sm font-semibold hover:bg-rose-700 transition-all shadow-lg shadow-rose-600/20">
            <i class="fa-solid fa-upload ml-1"></i>آپلود رسانه
        </button>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-lg p-6">
    <?php if (empty($items)) : ?>
    <div class="text-center py-16 text-zinc-400">
        <i class="fa-solid fa-photo-film text-5xl mb-4 opacity-30"></i>
        <p class="text-lg">هیچ فایل رسانه‌ای یافت نشد</p>
        <button onclick="showUploadModal()" class="mt-4 px-5 py-2.5 bg-rose-600 text-white rounded-xl text-sm font-semibold hover:bg-rose-700 transition-all">
            اولین فایل را آپلود کنید
        </button>
    </div>
    <?php else : ?>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <?php foreach ($items as $item) : ?>
            <?php
            $isImage = $item['type'] === 'image';
            $src = '/media/stream/' . $item['id'];
            $icon = $isImage ? 'fa-image' : 'fa-video';
            $iconColor = $isImage ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600';
            $sourceLabels = [
            'product_image' => 'محصول',
            'product_gallery' => 'گالری محصول',
            'product_video' => 'ویدیو محصول',
            'course_video' => 'ویدیو دوره',
            'tutorial_video' => 'ویدیو آموزش',
            'direct' => 'آپلود مستقیم',
            ];
            $sourceLabel = $sourceLabels[$item['source_type']] ?? $item['source_type'];
            ?>
        <div class="group relative bg-zinc-50 rounded-xl overflow-hidden border border-zinc-100 hover:shadow-lg hover:border-rose-200 transition-all">
            <div class="aspect-square bg-zinc-100 flex items-center justify-center overflow-hidden">
                <?php if ($isImage) : ?>
                <img src="<?= e($src) ?>" alt="<?= e($item['alt_text'] ?: $item['original_name']) ?>" class="w-full h-full object-cover" loading="lazy" onerror="this.parentElement.innerHTML='<i class=\'fa-solid fa-image text-3xl text-zinc-300\'></i>'">
                <?php else : ?>
                <div class="flex flex-col items-center gap-2 text-zinc-400">
                    <i class="fa-solid fa-video text-4xl"></i>
                    <span class="text-xs">ویدیو</span>
                </div>
                <?php endif; ?>
            </div>
            <div class="p-2.5">
                <p class="text-xs text-zinc-700 truncate font-medium" title="<?= e($item['alt_text'] ?: $item['original_name']) ?>">
                    <?= e(mb_substr($item['alt_text'] ?: $item['original_name'], 0, 30)) ?>
                </p>
                <div class="flex items-center justify-between mt-1.5">
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full <?= $iconColor ?>"><?= e($sourceLabel) ?></span>
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-all">
                        <button type="button" onclick="copyMediaLink(<?= $item['id'] ?>)" class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 text-xs flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all" title="کپی لینک">
                            <i class="fa-solid fa-link"></i>
                        </button>
                        <button type="button" onclick="deleteMedia(<?= $item['id'] ?>)" class="w-6 h-6 rounded-full bg-red-100 text-red-600 text-xs flex items-center justify-center hover:bg-red-600 hover:text-white transition-all" title="حذف">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

        <?php if ($totalPages > 1) : ?>
    <div class="flex justify-center gap-2 mt-8">
            <?php if ($page > 1) : ?>
        <a href="/admin/gallery?page=<?= $page - 1 ?>&filter=<?= e($filter) ?>&s=<?= e($search) ?>" class="px-4 py-2 bg-zinc-100 rounded-xl text-sm hover:bg-rose-100 hover:text-rose-600 transition-all">قبلی</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
        <a href="/admin/gallery?page=<?= $i ?>&filter=<?= e($filter) ?>&s=<?= e($search) ?>" class="px-4 py-2 rounded-xl text-sm transition-all <?= $i === $page ? 'bg-rose-600 text-white' : 'bg-zinc-100 hover:bg-rose-100 hover:text-rose-600' ?>"><?= faNum($i) ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages) : ?>
        <a href="/admin/gallery?page=<?= $page + 1 ?>&filter=<?= e($filter) ?>&s=<?= e($search) ?>" class="px-4 py-2 bg-zinc-100 rounded-xl text-sm hover:bg-rose-100 hover:text-rose-600 transition-all">بعدی</a>
            <?php endif; ?>
    </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div id="uploadMediaModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden" onclick="if(event.target===this)closeUploadModal()">
    <div class="bg-white rounded-[20px] p-6 w-full max-w-lg mx-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold">آپلود رسانه جدید</h3>
            <button onclick="closeUploadModal()" class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-500 hover:bg-zinc-200 transition-all text-sm"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="/admin/gallery/save" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf() ?>
            <div>
                <label class="block text-sm font-semibold mb-1.5">انتخاب فایل</label>
                <input type="file" name="file" required
                    accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/ogg,video/quicktime"
                    class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-rose-600 file:text-white hover:file:bg-rose-700">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">عنوان (اختیاری)</label>
                <input type="text" name="alt_text" placeholder="نام نمایشی فایل" class="form-input w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
            </div>
            <button type="submit" class="w-full py-3.5 bg-gradient-to-l from-rose-600 to-rose-700 text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all">
                <i class="fa-solid fa-upload ml-1"></i>آپلود
            </button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteMediaModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden" onclick="if(event.target===this)closeDeleteModal()">
    <div class="bg-white rounded-[20px] p-6 w-full max-w-sm mx-4 shadow-2xl" onclick="event.stopPropagation()">
        <div class="text-center">
            <i class="fa-solid fa-triangle-exclamation text-4xl text-red-500 mb-4"></i>
            <h3 class="text-xl font-bold mb-2">حذف فایل</h3>
            <p class="text-zinc-500 text-sm mb-6">آیا از حذف این فایل اطمینان دارید؟ این عمل قابل بازگشت نیست.</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 py-3 bg-zinc-100 rounded-xl text-sm font-semibold hover:bg-zinc-200 transition-all">انصراف</button>
                <form id="deleteMediaForm" method="POST" class="flex-1">
                    <?= csrf() ?>
                    <button type="submit" class="w-full py-3 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition-all">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Copy Toast -->
<div id="copyToast" class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-zinc-800 text-white px-6 py-3 rounded-xl text-sm shadow-2xl z-50 hidden transition-all duration-300 opacity-0">
    <i class="fa-solid fa-check ml-1 text-emerald-400"></i>
    <span>لینک کپی شد</span>
</div>

<script>
function showUploadModal() {
    document.getElementById('uploadMediaModal').classList.remove('hidden');
}
function closeUploadModal() {
    document.getElementById('uploadMediaModal').classList.add('hidden');
}
function deleteMedia(id) {
    var form = document.getElementById('deleteMediaForm');
    form.action = '/admin/gallery/delete/' + id;
    document.getElementById('deleteMediaModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteMediaModal').classList.add('hidden');
}
function copyMediaLink(id) {
    var url = window.location.origin + '/media/stream/' + id;
    navigator.clipboard.writeText(url).then(function() {
        var toast = document.getElementById('copyToast');
        toast.classList.remove('hidden', 'opacity-0');
        toast.classList.add('opacity-100');
        setTimeout(function() {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
            setTimeout(function() { toast.classList.add('hidden'); }, 300);
        }, 2000);
    }).catch(function() {
        alert('خطا در کپی لینک');
    });
}
</script>
