<?php

declare(strict_types=1);

$paymentSettings = $paymentSettings ?? [];
$paymentConfigured = $paymentConfigured ?? false;
$paymentSandbox = $paymentSandbox ?? false;
$callbackUrl = $callbackUrl ?? '';
$paymentLogs = $paymentLogs ?? [];
$paymentStats = $paymentStats ?? ['total' => 0, 'success' => 0, 'failed' => 0];
?>

<div class="mb-6">
    <h2 class="text-2xl font-extrabold">تنظیمات پرداخت (زرین‌پال)</h2>
    <p class="text-zinc-400 text-sm">تنظیمات درگاه پرداخت، وضعیت اتصال و لاگ تراکنش‌ها</p>
</div>

<?php if ($paymentStats['total'] > 0) : ?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <div class="text-xs text-zinc-400 mb-1">کل تراکنش‌ها</div>
        <div class="text-2xl font-extrabold"><?= faNum($paymentStats['total']) ?></div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <div class="text-xs text-zinc-400 mb-1">موفق</div>
        <div class="text-2xl font-extrabold text-green-600"><?= faNum($paymentStats['success']) ?></div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <div class="text-xs text-zinc-400 mb-1">ناموفق</div>
        <div class="text-2xl font-extrabold text-red-600"><?= faNum($paymentStats['failed']) ?></div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <div class="text-xs text-zinc-400 mb-1">وضعیت اتصال</div>
        <div class="text-2xl font-extrabold <?= $paymentConfigured ? 'text-green-600' : 'text-amber-600' ?>"><?= $paymentConfigured ? 'فعال' : 'غیرفعال' ?></div>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <h3 class="text-lg font-bold mb-4"><i class="fa-solid fa-credit-card text-rose-600 ml-2"></i>پیکربندی درگاه</h3>
        <form action="/admin/payment/save" method="POST" class="space-y-4">
            <?= csrf() ?>
            <div>
                <label class="block text-sm font-semibold mb-1.5">کد مرچنت زرین‌پال</label>
                <input type="text" name="zarinpal_merchant_id" value="<?= e($paymentSettings['zarinpal_merchant_id'] ?? '') ?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                    class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500 outline-none transition-all" dir="ltr">
                <p class="text-xs text-zinc-400 mt-1">از پنل زرین‌پال → تنظیمات → درگاه پرداخت</p>
            </div>
            <div>
                <label class="flex items-center justify-between cursor-pointer">
                    <span class="text-sm font-semibold">حالت تست (Sandbox)</span>
                    <input type="checkbox" name="zarinpal_sandbox" value="1" class="w-4 h-4 accent-rose-600" <?= ($paymentSettings['zarinpal_sandbox'] ?? '') === '1' ? 'checked' : '' ?>>
                </label>
                <p class="text-xs text-zinc-400 mt-1">در حالت تست، پرداخت واقعی انجام نمی‌شود (کد مرچنت تست: 10000000-0000-0000-0000-000000000000).</p>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:shadow-lg transition-all">ذخیره تنظیمات</button>
        </form>
    </div>

    <div class="bg-white rounded-xl p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
        <h3 class="text-lg font-bold mb-4"><i class="fa-solid fa-circle-info text-sky-600 ml-2"></i>وضعیت اتصال</h3>
        <ul class="space-y-3 text-sm">
            <li class="flex items-center justify-between">
                <span class="text-zinc-500">مرچنت کد</span>
                <span class="font-mono text-xs <?= $paymentConfigured ? 'text-green-600' : 'text-red-600' ?>"><?= $paymentConfigured ? 'تنظیم شده' : 'تنظیم نشده' ?></span>
            </li>
            <li class="flex items-center justify-between">
                <span class="text-zinc-500">حالت فعلی</span>
                <span class="<?= $paymentSandbox ? 'text-amber-600' : 'text-green-600' ?> font-semibold"><?= $paymentSandbox ? 'تست (Sandbox)' : 'واقعی (Production)' ?></span>
            </li>
            <li class="flex items-center justify-between">
                <span class="text-zinc-500">مبلغ درگاه</span>
                <span class="text-zinc-700">ریال</span>
            </li>
            <li>
                <div class="text-zinc-500 mb-1">آدرس بازگشت (Callback)</div>
                <div class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-2 font-mono text-xs text-zinc-600 break-all" dir="ltr"><?= e($callbackUrl) ?></div>
                <p class="text-xs text-zinc-400 mt-1">این آدرس را بدون هیچ پارامتر query در پنل زرین‌پال ثبت کنید (شناسهٔ سفارش به صورت داخلی بازیابی می‌شود). دامنه باید با دامنه‌ای که در زرین‌پال تأیید شده یکسان باشد.</p>
            </li>
        </ul>
    </div>
</div>

<div class="bg-white rounded-xl p-6 shadow-[0_4px_20px_rgba(225,29,72,0.06)]">
    <h3 class="text-lg font-bold mb-4"><i class="fa-solid fa-list text-rose-600 ml-2"></i>لاگ تراکنش‌های پرداخت</h3>
    <?php if (empty($paymentLogs)) : ?>
        <p class="text-zinc-400 text-sm py-6 text-center">هنوز تراکنشی ثبت نشده است.</p>
    <?php else : ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-right text-zinc-400 border-b border-zinc-100">
                        <th class="py-2 px-2">شناسه</th>
                        <th class="py-2 px-2">عملیات</th>
                        <th class="py-2 px-2">مبلغ (تومان)</th>
                        <th class="py-2 px-2">وضعیت</th>
                        <th class="py-2 px-2">Authority</th>
                        <th class="py-2 px-2">Ref ID</th>
                        <th class="py-2 px-2">تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paymentLogs as $log) : ?>
                    <tr class="border-b border-zinc-50 hover:bg-zinc-50/50">
                        <td class="py-2 px-2 text-zinc-500"><?= e((string) ($log['id'] ?? '')) ?></td>
                        <td class="py-2 px-2">
                            <?php
                            $actionLabels = ['request' => 'درخواست پرداخت', 'verify' => 'تأیید پرداخت', 'callback' => 'بازگشت درگاه'];
                            $label = $actionLabels[$log['action'] ?? ''] ?? e($log['action'] ?? '');
                            ?>
                            <span class="px-2 py-1 rounded-lg text-xs bg-zinc-100 text-zinc-600"><?= $label ?></span>
                        </td>
                        <td class="py-2 px-2"><?= faNum(number_format((int) ($log['amount'] ?? 0))) ?></td>
                        <td class="py-2 px-2">
                            <?php
                            $statusLabels = ['sent' => 'ارسال شد', 'verified' => 'تأیید شد', 'failed' => 'ناموفق', 'cancelled' => 'لغو شد', 'invalid' => 'نامعتبر'];
                            $statusColor = in_array($log['status'] ?? '', ['verified', 'sent']) ? 'text-green-600' : 'text-red-600';
                            ?>
                            <span class="<?= $statusColor ?> font-semibold text-xs"><?= $statusLabels[$log['status'] ?? ''] ?? e($log['status'] ?? '') ?></span>
                        </td>
                        <td class="py-2 px-2 font-mono text-[10px] text-zinc-500" dir="ltr"><?= e(mb_substr((string) ($log['authority'] ?? ''), 0, 20)) ?></td>
                        <td class="py-2 px-2 font-mono text-[10px] text-zinc-500" dir="ltr"><?= e((string) ($log['ref_id'] ?? '')) ?></td>
                        <td class="py-2 px-2 text-xs text-zinc-400"><?= e((string) ($log['created_at'] ?? '')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
