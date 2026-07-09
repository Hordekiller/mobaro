<div class="page-header mb-6">
    <h1 class="text-2xl font-extrabold">اطلاعات حساب</h1>
    <p class="text-[#9e9e9e] text-sm">ویرایش اطلاعات شخصی</p>
</div>

<form action="/dashboard/profile/update" method="POST" enctype="multipart/form-data" class="bg-white rounded-[18px] p-6 shadow-[0_4px_20px_rgba(183,110,121,0.06)] mb-5">
    <?= csrf() ?>
    <div class="flex flex-col items-center mb-8">
        <div class="relative w-[100px] h-[100px] rounded-full overflow-hidden mb-3 border-4 border-[#FDF6F0] shadow">
            <img src="<?= $user['avatar'] ? '/assets/images/'.e($user['avatar']) : 'https://ui-avatars.com/api/?name='.urlencode($user['name'].' '.$user['family']).'&background=B76E79&color=fff&size=128' ?>"
                 alt="avatar" class="w-full h-full object-cover" id="avatar-preview">
            <label for="avatar-upload" class="absolute inset-0 bg-black/40 flex items-center justify-center text-white opacity-0 hover:opacity-100 transition-opacity cursor-pointer">
                <i class="fa-solid fa-camera text-xl"></i>
            </label>
            <input type="file" name="avatar" id="avatar-upload" accept="image/*" class="hidden" onchange="document.getElementById('avatar-preview').src = URL.createObjectURL(this.files[0])">
        </div>
        <p class="text-sm text-[#9e9e9e]">برای تغییر عکس کلیک کنید</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold mb-1.5">نام</label>
            <input type="text" name="name" value="<?= e($user['name']) ?>" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" required>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1.5">نام خانوادگی</label>
            <input type="text" name="family" value="<?= e($user['family']) ?>" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" required>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1.5">شماره تلفن</label>
            <input type="text" value="<?= e($user['phone']) ?>" disabled class="w-full px-4 py-3 bg-gray-100 border-2 border-transparent rounded-xl text-gray-500 outline-none">
            <p class="text-xs text-[#9e9e9e] mt-1">تغییر شماره تلفن امکان‌پذیر نیست</p>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1.5">ایمیل</label>
            <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all" placeholder="example@mail.com">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1.5">تاریخ تولد</label>
            <input type="date" name="birth_date" value="<?= e($user['birth_date'] ?? '') ?>" class="w-full px-4 py-3 bg-[#FDF6F0] border-2 border-transparent rounded-xl focus:border-[#B76E79] focus:ring-0 outline-none transition-all">
        </div>
    </div>

    <div class="mt-6 text-sm text-[#9e9e9e] leading-relaxed bg-[#FDF6F0] rounded-xl p-4">
        <i class="fa-regular fa-circle-info text-[#B76E79] ml-1"></i>
        عضویت شما از <strong><?= e($user['created_at']) ?></strong>
    </div>

    <button type="submit" class="w-full mt-5 py-3.5 bg-gradient-to-l from-[#B76E79] to-[#9c5761] text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all">ذخیره تغییرات</button>
</form>
