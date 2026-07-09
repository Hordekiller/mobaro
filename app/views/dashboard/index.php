<?php
$title = 'پنل کاربری | موبارو';
$currentTab = isset($tab) ? $tab : 'dashboard';
?>
<div class="min-h-screen bg-[#FDF6F0]" style="background-color: var(--cream, #FDF6F0)">
    <div class="max-w-[1400px] mx-auto px-8 py-8" style="padding: 30px auto;">
        <div class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-8">
            <!-- Sidebar -->
            <div class="bg-white rounded-[20px] p-6 shadow-[0_4px_30px_rgba(183,110,121,0.08)] h-fit lg:sticky lg:top-[100px]">
                <div class="text-center pb-5 border-b border-[#efe5dc]">
                    <div class="relative w-[100px] h-[100px] mx-auto mb-3">
                        <?php if ($user['avatar']): ?>
                            <img src="/<?= e($user['avatar']) ?>" class="w-full h-full rounded-full object-cover border-4 border-[#FDF6F0] shadow-[0_4px_20px_rgba(183,110,121,0.25)]">
                        <?php else: ?>
                            <div class="w-full h-full rounded-full bg-gradient-to-br from-rose-300 to-rose-500 flex items-center justify-center text-white text-3xl font-bold border-4 border-[#FDF6F0] shadow-[0_4px_20px_rgba(183,110,121,0.25)]">
                                <?= e(mb_substr($user['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="font-bold text-lg"><?= e($user['name'] . ' ' . $user['family']) ?></div>
                    <div class="text-[#9e9e9e] text-sm"><?= e($user['phone']) ?></div>
                    <div class="inline-flex items-center gap-1.5 mt-3 px-3.5 py-1 bg-gradient-to-l from-[#D4AF37] to-[#e8c86a] text-white rounded-full text-xs font-semibold">
                        <i class="fa-solid fa-crown"></i>
                        <?= e($user['level']) ?>
                    </div>
                    <div class="mt-4">
                        <div class="flex justify-between text-xs text-[#9e9e9e] mb-1.5">
                            <span>امتیاز تا سطح بعدی</span>
                            <span><?= e($user['points']) ?> / ۱۰۰۰</span>
                        </div>
                        <div class="h-2 bg-[#FDF6F0] rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-l from-[#B76E79] to-[#D4AF37] rounded-full transition-all duration-700" style="width: <?= min(100, ($user['points'] / 1000) * 100) ?>%"></div>
                        </div>
                    </div>
                </div>

                <ul class="mt-5 space-y-1">
                    <?php
                    $menuItems = [
                        'dashboard' => ['fa-solid fa-chart-pie', 'پیشخوان'],
                        'appointments' => ['fa-solid fa-calendar-check', 'نوبت‌های من'],
                        'courses' => ['fa-solid fa-graduation-cap', 'دوره‌های من'],
                        'orders' => ['fa-solid fa-truck', 'سفارش‌های من'],
                        'wishlist' => ['fa-solid fa-heart', 'علاقه‌مندی‌ها'],
                        'wallet' => ['fa-solid fa-wallet', 'کیف پول'],
                        'addresses' => ['fa-solid fa-location-dot', 'آدرس‌ها'],
                        'account' => ['fa-solid fa-user-gear', 'اطلاعات حساب'],
                        'password' => ['fa-solid fa-lock', 'تغییر رمز'],
                    ];
                    ?>
                    <?php foreach ($menuItems as $key => $item): ?>
                    <li>
                        <a href="/dashboard/<?= $key ?>"
                           class="flex items-center gap-3 px-3.5 py-3 rounded-xl transition-all duration-300 text-sm font-medium
                                  <?= $currentTab === $key ? 'bg-gradient-to-l from-[#B76E79] to-[#d18d97] text-white' : 'text-zinc-700 hover:bg-[#FDF6F0] hover:translate-x-[-4px]' ?>">
                            <i class="<?= $item[0] ?> w-5 text-center <?= $currentTab === $key ? 'text-white' : 'text-[#9e9e9e]' ?>"></i>
                            <span><?= $item[1] ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <li class="mt-4 pt-4 border-t border-[#efe5dc]">
                        <a href="/logout" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 transition-all">
                            <i class="fa-solid fa-sign-out-alt w-5 text-center"></i>
                            <span>خروج از حساب</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Content -->
            <div class="min-w-0">
                <?php if ($currentTab === 'dashboard'): ?>
                    <?php require __DIR__ . '/tabs/dashboard.php'; ?>
                <?php elseif ($currentTab === 'appointments'): ?>
                    <?php require __DIR__ . '/tabs/appointments.php'; ?>
                <?php elseif ($currentTab === 'courses'): ?>
                    <?php require __DIR__ . '/tabs/courses.php'; ?>
                <?php elseif ($currentTab === 'orders'): ?>
                    <?php require __DIR__ . '/tabs/orders.php'; ?>
                <?php elseif ($currentTab === 'wishlist'): ?>
                    <?php require __DIR__ . '/tabs/wishlist.php'; ?>
                <?php elseif ($currentTab === 'wallet'): ?>
                    <?php require __DIR__ . '/tabs/wallet.php'; ?>
                <?php elseif ($currentTab === 'addresses'): ?>
                    <?php require __DIR__ . '/tabs/addresses.php'; ?>
                <?php elseif ($currentTab === 'account'): ?>
                    <?php require __DIR__ . '/tabs/account.php'; ?>
                <?php elseif ($currentTab === 'password'): ?>
                    <?php require __DIR__ . '/tabs/password.php'; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
