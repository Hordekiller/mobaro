<?php

declare(strict_types=1);

$smsTab = $smsTab ?? 'settings';
$smsSettings = $smsSettings ?? [];
$smsStats = $smsStats ?? ['total_sent' => 0, 'today_sent' => 0, 'failed_count' => 0, 'total_credits' => 0];
$smsCredit = $smsCredit ?? ['total_purchased' => 0, 'total_used' => 0];
$lines = $lines ?? [];
$templates = $templates ?? [];
$items = $items ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$search = $search ?? '';
?>

<div class="mb-6 flex justify-between items-center flex-wrap gap-3">
    <div>
        <h2 class="text-2xl font-extrabold">مدیریت پیامک (sms.ir)</h2>
        <p class="text-zinc-400 text-sm">تنظیمات، ارسال و لاگ پیامک‌ها</p>
    </div>
</div>

<?php if ($smsStats['total_sent'] > 0 || $smsCredit['total_purchased'] > 0) : ?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <div class="text-xs text-zinc-400 mb-1">کل ارسال شده</div>
        <div class="text-2xl font-extrabold"><?= faNum($smsStats['total_sent']) ?></div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <div class="text-xs text-zinc-400 mb-1">امروز</div>
        <div class="text-2xl font-extrabold text-green-600"><?= faNum($smsStats['today_sent']) ?></div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <div class="text-xs text-zinc-400 mb-1">مصرف شده</div>
        <div class="text-2xl font-extrabold text-amber-600"><?= faNum($smsStats['total_credits']) ?></div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <div class="text-xs text-zinc-400 mb-1">خریداری شده</div>
        <div class="text-2xl font-extrabold text-blue-600"><?= faNum($smsCredit['total_purchased']) ?></div>
    </div>
</div>
<?php endif; ?>

<div class="flex gap-2 mb-6 flex-wrap">
    <?php
    $tabs = [
        'settings' => ['fa-gear', 'تنظیمات'],
        'send' => ['fa-paper-plane', 'ارسال پیامک'],
        'templates' => ['fa-file-lines', 'قالب‌ها'],
        'logs' => ['fa-clock-rotate-left', 'لاگ ارسال'],
        'credit' => ['fa-coins', 'اعتبار'],
    ];
    foreach ($tabs as $key => $info) :
        ?>
    <a href="/admin/sms?tab=<?= $key ?>" class="px-4 py-2 rounded-full text-xs font-semibold transition-all <?= $smsTab === $key ? 'bg-rose-600 text-white shadow-md shadow-rose-200' : 'bg-white text-zinc-600 hover:bg-rose-50 hover:text-rose-600' ?>">
        <i class="fa-solid <?= $info[0] ?> ml-1"></i><?= $info[1] ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($smsTab === 'settings') : ?>
<div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
    <form action="/admin/sms/save" method="POST" class="space-y-6">
        <?= csrf() ?>
        <div>
            <h3 class="font-bold text-base mb-4 pb-3 border-b border-rose-100" style="border-right:4px solid #e11d48;padding-right:12px;">تنظیمات اتصال</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="sms_enabled" class="block text-sm font-semibold mb-1.5">فعال‌سازی سرویس پیامک</label>
                    <select id="sms_enabled" name="sms_enabled" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                        <option value="1" <?= ($smsSettings['sms_enabled'] ?? '') === '1' ? 'selected' : '' ?>>فعال</option>
                        <option value="0" <?= ($smsSettings['sms_enabled'] ?? '') !== '1' ? 'selected' : '' ?>>غیرفعال</option>
                    </select>
                </div>
                <div>
                    <label for="sms_api_key" class="block text-sm font-semibold mb-1.5">کلید API (X-API-KEY)</label>
                    <input id="sms_api_key" type="text" name="sms_api_key" value="<?= e($smsSettings['sms_api_key'] ?? '') ?>" placeholder="کلید API sms.ir" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" dir="ltr">
                </div>
                <div>
                    <label for="sms_line_number" class="block text-sm font-semibold mb-1.5">شماره خط ارسال</label>
                    <input id="sms_line_number" type="text" name="sms_line_number" value="<?= e($smsSettings['sms_line_number'] ?? '') ?>" placeholder="مثال: 30004505000017" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" dir="ltr">
                </div>
                <div>
                    <label for="sms_template_id" class="block text-sm font-semibold mb-1.5">شناسه قالب OTP (Template ID)</label>
                    <input id="sms_template_id" type="text" name="sms_template_id" value="<?= e($smsSettings['sms_template_id'] ?? '') ?>" placeholder="شناسه قالب در پنل sms.ir" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" dir="ltr">
                </div>
                <div>
                    <label for="sms_otp_ttl" class="block text-sm font-semibold mb-1.5">مدت اعتبار کد تأیید (ثانیه)</label>
                    <input id="sms_otp_ttl" type="number" name="sms_otp_ttl" value="<?= e($smsSettings['sms_otp_ttl'] ?? '180') ?>" min="60" max="600" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                </div>
                <div>
                    <label for="sms_otp_length" class="block text-sm font-semibold mb-1.5">طول کد تأیید (رقم)</label>
                    <input id="sms_otp_length" type="number" name="sms_otp_length" value="<?= e($smsSettings['sms_otp_length'] ?? '5') ?>" min="4" max="6" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                </div>
            </div>
        </div>
        <button type="submit" class="px-8 py-3 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">
            <i class="fa-solid fa-save ml-1"></i>ذخیره تنظیمات
        </button>
    </form>
</div>

<?php elseif ($smsTab === 'send') : ?>
<div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
    <h3 class="font-bold text-base mb-4 pb-3 border-b border-rose-100" style="border-right:4px solid #e11d48;padding-right:12px;">ارسال پیامک گروهی</h3>
    <form action="/admin/sms/send" method="POST" class="space-y-4">
        <?= csrf() ?>
        <div>
            <label for="sms_phones" class="block text-sm font-semibold mb-1.5">شماره تلفن‌ها (هر خط یک شماره)</label>
            <textarea id="sms_phones" name="phones" rows="5" placeholder="09121234567&#10;09191234567&#10;09351234567" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all font-mono text-sm" required></textarea>
        </div>
        <div>
            <label for="sms_message" class="block text-sm font-semibold mb-1.5">متن پیام</label>
            <textarea id="sms_message" name="message" rows="4" placeholder="متن پیامک خود را اینجا بنویسید..." class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" required></textarea>
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" class="px-8 py-3 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all" onclick="return confirm('آیا از ارسال پیامک اطمینان دارید؟')">
                <i class="fa-solid fa-paper-plane ml-1"></i>ارسال پیامک
            </button>
            <span id="sms-char-count" class="text-xs text-zinc-400"></span>
        </div>
    </form>
</div>

<?php elseif ($smsTab === 'templates') : ?>
<div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)] mb-6">
    <h3 class="font-bold text-base mb-4 pb-3 border-b border-rose-100" style="border-right:4px solid #e11d48;padding-right:12px;">ایجاد/ویرایش قالب</h3>
    <form action="/admin/sms/template/save" method="POST" class="space-y-4" id="templateForm">
        <?= csrf() ?>
        <input type="hidden" name="id" id="template-id" value="">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1.5">نام قالب</label>
                <input type="text" name="name" id="template-name" placeholder="مثال: کد تأیید" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">نوع</label>
                <select name="sms_type" id="template-type" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
                    <option value="verify">تأییدیه (OTP)</option>
                    <option value="bulk">انبوه</option>
                    <option value="notification">اعلان</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1.5">متن قالب (از #Variable# برای متغیرها استفاده کنید)</label>
            <textarea name="body" id="template-body" rows="3" placeholder="کد تأیید شما: #Code#" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all" required></textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1.5">متغیرها ( جدا شده با کاما)</label>
            <input type="text" name="variables" id="template-variables" placeholder="Code, Name" class="w-full px-4 py-3 bg-rose-50 border-2 border-transparent rounded-xl focus:border-rose-500 focus:ring-0 outline-none transition-all">
        </div>
        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" id="template-active" checked class="accent-rose-600 w-4 h-4">
                <span class="text-sm font-semibold">فعال</span>
            </label>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="px-6 py-3 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">
                <i class="fa-solid fa-save ml-1"></i>ذخیره قالب
            </button>
            <button type="button" onclick="resetTemplateForm()" class="px-6 py-3 bg-zinc-100 text-zinc-600 rounded-xl font-semibold text-sm hover:bg-zinc-200 transition-all">
                انصراف
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-[18px] shadow-[0_4px_20px_rgba(225,29,72,0.06)] overflow-x-auto">
    <?php if (!empty($templates)) : ?>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-rose-50">
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">نام</th>
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">نوع</th>
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">متن</th>
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">متغیرها</th>
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">وضعیت</th>
                <th class="text-center py-3.5 px-4 text-zinc-400 font-semibold text-sm">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $tpl) : ?>
            <?php
            $tplTypeClass = match ($tpl['sms_type']) {
                'verify' => 'bg-blue-50 text-blue-700',
                'bulk' => 'bg-amber-50 text-amber-700',
                default => 'bg-green-50 text-green-700',
            };
            $tplTypeLabel = match ($tpl['sms_type']) {
                'verify' => 'تأییدیه',
                'bulk' => 'انبوه',
                default => 'اعلان',
            };
            ?>
            <tr class="border-b border-rose-100 hover:bg-rose-50/50 transition-all">
                <td class="py-3 px-4 font-semibold text-sm"><?= e($tpl['name']) ?></td>
                <td class="py-3 px-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $tplTypeClass ?>">
                        <?= $tplTypeLabel ?>
                    </span>
                </td>
                <td class="py-3 px-4 text-sm text-zinc-400 max-w-xs truncate"><?= e($tpl['body']) ?></td>
                <td class="py-3 px-4 text-sm font-mono"><?= e($tpl['variables'] ?? '') ?></td>
                <td class="py-3 px-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $tpl['is_active'] ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-500' ?>">
                        <?= $tpl['is_active'] ? 'فعال' : 'غیرفعال' ?>
                    </span>
                </td>
                <td class="py-3 px-4 text-center">
                    <div class="flex gap-1.5 justify-center">
                        <button onclick='editTemplate(<?= json_encode($tpl) ?>)' class="px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs font-semibold hover:bg-rose-600 hover:text-white transition-all">ویرایش</button>
                        <form action="/admin/sms/template/delete/<?= $tpl['id'] ?>" method="POST" class="inline" onsubmit="return confirm('قالب حذف شود؟')">
                            <?= csrf() ?>
                            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-500 rounded-lg text-xs font-semibold hover:bg-red-500 hover:text-white transition-all">حذف</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
    <div class="text-center py-10 text-zinc-400">هیچ قالبی تعریف نشده است.</div>
    <?php endif; ?>
</div>

<?php elseif ($smsTab === 'logs') : ?>
<div class="mb-4">
    <form method="GET" action="/admin/sms" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="logs">
        <input type="text" name="s" value="<?= e($search) ?>" placeholder="جستجوی شماره یا متن..." aria-label="جستجوی پیامک‌ها" class="px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm focus:border-rose-500 focus:ring-0 outline-none transition-all w-64">
        <button type="submit" class="px-3 py-2.5 bg-zinc-100 text-zinc-600 rounded-xl text-sm hover:bg-rose-50 hover:text-rose-600 transition-all"><i class="fa-solid fa-search"></i></button>
        <?php if (!empty($search)) : ?>
        <a href="/admin/sms?tab=logs" class="px-3 py-2.5 bg-red-50 text-red-500 rounded-xl text-sm hover:bg-red-100 transition-all"><i class="fa-solid fa-xmark"></i></a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-white rounded-[18px] shadow-[0_4px_20px_rgba(225,29,72,0.06)] overflow-x-auto">
    <?php if (!empty($items)) : ?>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-rose-50">
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">شماره</th>
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">متن</th>
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">نوع</th>
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">وضعیت</th>
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">هزینه</th>
                <th class="text-right py-3.5 px-4 text-zinc-400 font-semibold text-sm">تاریخ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item) : ?>
            <?php
            $logTypeClass = match ($item['type']) {
                'verify' => 'bg-blue-50 text-blue-700',
                'bulk' => 'bg-amber-50 text-amber-700',
                default => 'bg-green-50 text-green-700',
            };
            $logTypeLabel = match ($item['type']) {
                'verify' => 'تأییدیه',
                'bulk' => 'انبوه',
                default => 'اعلان',
            };
            $statusClass = match ($item['status']) {
                'sent' => 'bg-green-50 text-green-700',
                'delivered' => 'bg-blue-50 text-blue-700',
                default => 'bg-red-50 text-red-500',
            };
            $statusLabel = match ($item['status']) {
                'sent' => 'ارسال شده',
                'delivered' => 'تحویل شده',
                default => 'ناموفق',
            };
            ?>
            <tr class="border-b border-rose-100 hover:bg-rose-50/50 transition-all">
                <td class="py-3 px-4 font-mono text-sm" dir="ltr"><?= e($item['phone']) ?></td>
                <td class="py-3 px-4 text-sm text-zinc-400 max-w-xs truncate"><?= e($item['message']) ?></td>
                <td class="py-3 px-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $logTypeClass ?>">
                        <?= $logTypeLabel ?>
                    </span>
                </td>
                <td class="py-3 px-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusClass ?>">
                        <?= $statusLabel ?>
                    </span>
                </td>
                <td class="py-3 px-4 text-sm"><?= faNum($item['credits']) ?></td>
                <td class="py-3 px-4 text-sm text-zinc-400" dir="ltr"><?= e($item['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
    <div class="text-center py-10 text-zinc-400">هیچ لاگی یافت نشد.</div>
    <?php endif; ?>
</div>

    <?php if ($totalPages > 1) : ?>
<div class="flex justify-center items-center gap-2 mt-6">
        <?php if ($page > 1) : ?>
    <a href="?tab=logs&page=<?= $page - 1 ?>&s=<?= e($search) ?>" class="w-10 h-10 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all text-sm">
        <i class="fa-solid fa-chevron-right"></i>
    </a>
        <?php endif; ?>
        <?php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        for ($i = $startPage; $i <= $endPage; $i++) : ?>
    <a href="?tab=logs&page=<?= $i ?>&s=<?= e($search) ?>" class="w-10 h-10 rounded-full flex items-center justify-center font-medium text-sm transition-all <?= $i === $page ? 'bg-rose-600 text-white shadow-lg shadow-rose-200' : 'border border-zinc-300 text-zinc-600 hover:bg-rose-600 hover:text-white hover:border-rose-600' ?>">
            <?= faNum($i) ?>
    </a>
        <?php endfor; ?>
        <?php if ($page < $totalPages) : ?>
    <a href="?tab=logs&page=<?= $page + 1 ?>&s=<?= e($search) ?>" class="w-10 h-10 rounded-full border border-zinc-300 flex items-center justify-center text-zinc-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all text-sm">
        <i class="fa-solid fa-chevron-left"></i>
    </a>
        <?php endif; ?>
</div>
    <?php endif; ?>

<?php elseif ($smsTab === 'credit') : ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <h3 class="font-bold text-base mb-4 pb-3 border-b border-rose-100" style="border-right:4px solid #e11d48;padding-right:12px;">موجودی فعلی</h3>
        <div class="text-center py-8">
            <div class="text-5xl font-extrabold text-rose-600 mb-2"><?= faNum($smsCredit['total_purchased'] - $smsCredit['total_used']) ?></div>
            <p class="text-zinc-400 text-sm">پیامک باقی‌مانده</p>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div class="bg-green-50 rounded-xl p-4 text-center">
                <div class="text-2xl font-extrabold text-green-600"><?= faNum($smsCredit['total_purchased']) ?></div>
                <p class="text-xs text-green-600 mt-1">خریداری شده</p>
            </div>
            <div class="bg-amber-50 rounded-xl p-4 text-center">
                <div class="text-2xl font-extrabold text-amber-600"><?= faNum($smsCredit['total_used']) ?></div>
                <p class="text-xs text-amber-600 mt-1">مصرف شده</p>
            </div>
        </div>
        <form action="/admin/sms/credit/refresh" method="POST" class="mt-6">
            <?= csrf() ?>
            <button type="submit" class="w-full px-6 py-3 bg-zinc-100 text-zinc-600 rounded-xl font-semibold text-sm hover:bg-zinc-200 transition-all">
                <i class="fa-solid fa-arrows-rotate ml-1"></i>بروزرسانی موجودی از سرویس
            </button>
        </form>
    </div>

    <div class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <h3 class="font-bold text-base mb-4 pb-3 border-b border-rose-100" style="border-right:4px solid #e11d48;padding-right:12px;">خطوط فعال</h3>
        <?php if (!empty($lines)) : ?>
        <div class="space-y-3">
            <?php foreach ($lines as $line) : ?>
            <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-xl">
                <span class="font-mono text-sm" dir="ltr"><?= e($line) ?></span>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">فعال</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <div class="text-center py-8 text-zinc-400">
            <p>خطی یافت نشد یا سرویس تنظیم نشده است.</p>
        </div>
        <?php endif; ?>

        <div class="mt-6 p-4 bg-amber-50 rounded-xl">
            <h4 class="font-semibold text-sm text-amber-800 mb-2"><i class="fa-solid fa-info-circle ml-1"></i>راهنمای خرید اعتبار</h4>
            <ul class="text-xs text-amber-700 space-y-1">
                <li>- برای خرید اعتبار به <a href="https://app.sms.ir" target="_blank" class="underline">پنل sms.ir</a> مراجعه کنید</li>
                <li>- شماره خط را از بخش "برنامه‌نویسان > لیست خطوط" دریافت کنید</li>
                <li>- قالب OTP را از بخش "ارسال سریع" ایجاد کنید</li>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function editTemplate(tpl) {
    document.getElementById('template-id').value = tpl.id;
    document.getElementById('template-name').value = tpl.name;
    document.getElementById('template-body').value = tpl.body;
    document.getElementById('template-type').value = tpl.sms_type;
    document.getElementById('template-variables').value = tpl.variables || '';
    document.getElementById('template-active').checked = Number.parseInt(tpl.is_active, 10) === 1;
    document.getElementById('templateForm').scrollIntoView({ behavior: 'smooth' });
}
function resetTemplateForm() {
    document.getElementById('template-id').value = '';
    document.getElementById('template-name').value = '';
    document.getElementById('template-body').value = '';
    document.getElementById('template-type').value = 'bulk';
    document.getElementById('template-variables').value = '';
    document.getElementById('template-active').checked = true;
}
</script>
