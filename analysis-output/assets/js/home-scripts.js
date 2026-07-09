    <script>
        // Tailwind script run
        function initializeTailwind() {
            tailwind.config = {
                content: [],
                theme: {
                    extend: {}
                }
            }
        }
        
        let cart = []
        let currentFilter = 'all'
        let currentAuthTab = 0
        let currentBookingStep = 0
        let selectedServiceIndex = null
        
        const services = [
            { title: "رنگ و لایت", price: 380000, duration: "120 دقیقه" },
            { title: "آرایش عروس", price: 1200000, duration: "180 دقیقه" },
            { title: "کوتاهی و شینیون", price: 220000, duration: "45 دقیقه" },
            { title: "فیشیال و پاکسازی", price: 450000, duration: "90 دقیقه" }
        ]
        
        const hairModels = [
            {
                id: 1,
                title: "بابلیز بلند",
                category: "مو",
                img: "https://picsum.photos/id/1011/400/520"
            },
            {
                id: 2,
                title: "شینیون عروس",
                category: "عروس",
                img: "https://picsum.photos/id/29/400/520"
            },
            {
                id: 3,
                title: "لایت طلایی",
                category: "رنگ",
                img: "https://picsum.photos/id/160/400/520"
            },
            {
                id: 4,
                title: "چتری کوتاه",
                category: "مو",
                img: "https://picsum.photos/id/201/400/520"
            },
            {
                id: 5,
                title: "آرایش نود",
                category: "آرایش",
                img: "https://picsum.photos/id/251/400/520"
            }
        ]
        
        const products = [
            {
                id: 1,
                name: "شامپو تقویت کننده",
                price: 245000,
                category: "مو",
                img: "https://picsum.photos/id/180/280/280"
            },
            {
                id: 2,
                name: "ماسک مو کراتینه",
                price: 315000,
                category: "مو",
                img: "https://picsum.photos/id/201/280/280"
            },
            {
                id: 3,
                name: "رژ لب مات",
                price: 89000,
                category: "آرایش",
                img: "https://picsum.photos/id/29/280/280"
            },
            {
                id: 4,
                name: "کرم مرطوب کننده",
                price: 175000,
                category: "پوست",
                img: "https://picsum.photos/id/316/280/280"
            },
            {
                id: 5,
                name: "سشوار حرفه‌ای",
                price: 1250000,
                category: "ابزار",
                img: "https://picsum.photos/id/160/280/280"
            }
        ]
        
        let testimonials = [
            {
                text: "از لحظه ورود تا پایان کار، همه چیز عالی بود. آرایشم برای عروسی فوق‌العاده شد و همه از آن تعریف کردند.",
                name: "لیلا کریمی",
                role: "مشتری ثابت از ۱۴۰۱"
            },
            {
                text: "رنگ موی من را به بهترین شکل ممکن انجام دادند. واقعاً از کیفیت کار و رفتار کارکنان راضی هستم.",
                name: "زهرا محمدی",
                role: "دانشجوی دانشگاه"
            },
            {
                text: "آموزش‌های آنلاینشان بسیار کاربردی بود. توانستم در خانه آرایش چشمم را بهبود ببخشم.",
                name: "مریم حسینی",
                role: "خانه‌دار"
            }
        ]
        let currentTestimonial = 0
        
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container')
            container.classList.remove('hidden')
            
            const toastHTML = `
                <div class="toast flex items-center gap-x-3 bg-white shadow-2xl border border-zinc-100 text-zinc-700 px-5 py-4 rounded-3xl mb-3">
                    <div class="${type === 'success' ? 'text-emerald-500' : 'text-rose-500'}">
                        ${type === 'success' ? 
                            '<i class="fa-solid fa-circle-check"></i>' : 
                            '<i class="fa-solid fa-circle-exclamation"></i>'}
                    </div>
                    <div class="text-sm font-medium">${message}</div>
                </div>
            `
            container.innerHTML = toastHTML
            
            setTimeout(() => {
                container.classList.add('hidden')
                container.innerHTML = ''
            }, 3000)
        }
        
        function navigateToSection(section) {
            document.getElementById('mobile-menu').classList.add('hidden')
            
            if (section === 'booking') {
                document.getElementById('booking').scrollIntoView({ behavior: "smooth" })
                setTimeout(() => {
                    renderBookingStep()
                }, 800)
                return
            }
            
            const el = document.getElementById(section)
            if (el) {
                el.scrollIntoView({ behavior: "smooth" })
            }
        }
        
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu')
            menu.classList.toggle('hidden')
        }
        
        function showLoginModal() {
            const modal = document.getElementById('login-modal')
            modal.classList.remove('hidden')
            modal.classList.add('flex')
            switchAuthTab(0)
        }
        
        function hideLoginModal() {
            const modal = document.getElementById('login-modal')
            modal.classList.add('hidden')
            modal.classList.remove('flex')
        }
        
        function switchAuthTab(tab) {
            currentAuthTab = tab
            document.getElementById('login-form').classList.toggle('hidden', tab === 1)
            document.getElementById('register-form').classList.toggle('hidden', tab === 0)
            
            const loginTab = document.getElementById('tab-login')
            const registerTab = document.getElementById('tab-register')
            
            if (tab === 0) {
                loginTab.classList.add('border-b-2', 'border-rose-500', 'text-rose-500')
                registerTab.classList.remove('border-b-2', 'border-rose-500', 'text-rose-500')
                registerTab.classList.add('text-zinc-400')
            } else {
                registerTab.classList.add('border-b-2', 'border-rose-500', 'text-rose-500')
                loginTab.classList.remove('border-b-2', 'border-rose-500', 'text-rose-500')
                loginTab.classList.add('text-zinc-400')
            }
            
            document.getElementById('forgot-view').classList.add('hidden')
        }
        
        function performLogin() {
            const phone = document.getElementById('phone-input').value
            if (!phone) {
                showToast('لطفا شماره تلفن وارد کنید', 'error')
                return
            }
            hideLoginModal()
            setTimeout(() => {
                showToast('با موفقیت وارد شدید. خوش آمدید!', 'success')
            }, 600)
        }
        
        function performRegister() {
            hideLoginModal()
            setTimeout(() => {
                showToast('ثبت نام با موفقیت انجام شد!', 'success')
            }, 600)
        }
        
        function showForgotPassword() {
            document.getElementById('login-form').classList.add('hidden')
            document.getElementById('register-form').classList.add('hidden')
            document.getElementById('forgot-view').classList.remove('hidden')
        }
        
        function sendResetCode() {
            const phoneField = document.getElementById('forgot-phone')
            if (phoneField.value.trim() === '') {
                showToast('شماره تلفن را وارد کنید', 'error')
                return
            }
            showToast('کد تأیید به شماره شما ارسال شد', 'success')
            setTimeout(() => {
                hideLoginModal()
            }, 1600)
        }
        
        function toggleCart() {
            const drawer = document.getElementById('cart-drawer')
            drawer.classList.toggle('hidden')
            
            if (!drawer.classList.contains('hidden')) {
                renderCart()
            }
        }
        
        function addToCart(product) {
            cart.push({
                ...product,
                qty: 1
            })
            updateCartCount()
            showToast(`${product.name} به سبد خرید اضافه شد`)
        }
        
        function updateCartCount() {
            document.getElementById('cart-count').innerText = cart.length
        }
        
        function renderCart() {
            const container = document.getElementById('cart-items')
            container.innerHTML = ''
            
            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="h-full flex flex-col justify-center items-center text-center py-12">
                        <i class="fa-solid fa-bag-shopping text-6xl text-zinc-200"></i>
                        <p class="text-zinc-300 mt-6">سبد خرید خالی است</p>
                    </div>
                `
                document.getElementById('cart-total').innerText = `۰ تومان`
                return
            }
            
            let total = 0
            
            cart.forEach((item, index) => {
                total += item.price * item.qty
                
                const itemHTML = `
                <div class="flex gap-4">
                    <img src="${item.img}" class="w-16 h-16 object-cover rounded-2xl" alt="">
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <div class="font-medium text-sm">${item.name}</div>
                            <div onclick="removeFromCart(${index}); event.stopImmediatePropagation()" 
                                 class="text-xs text-rose-400 cursor-pointer">حذف</div>
                        </div>
                        <div class="text-xs text-zinc-400">${item.category}</div>
                        <div class="flex items-center justify-between mt-6">
                            <div class="flex border rounded-3xl">
                                <button onclick="changeCartQty(${index}, -1); event.stopImmediatePropagation()" 
                                        class="w-7 h-7 flex items-center justify-center text-xs">-</button>
                                <div class="w-7 h-7 flex items-center justify-center text-xs font-medium">${item.qty}</div>
                                <button onclick="changeCartQty(${index}, 1); event.stopImmediatePropagation()" 
                                        class="w-7 h-7 flex items-center justify-center text-xs">+</button>
                            </div>
                            <div class="font-semibold">${(item.price * item.qty).toLocaleString('fa-IR')} تومان</div>
                        </div>
                    </div>
                </div>`
                container.innerHTML += itemHTML
            })
            
            document.getElementById('cart-total').innerHTML = `${total.toLocaleString('fa-IR')} <span class="text-base align-super font-normal">تومان</span>`
        }
        
        function changeCartQty(index, change) {
            if (!cart[index]) return
            cart[index].qty = Math.max(1, cart[index].qty + change)
            renderCart()
            updateCartCount()
        }
        
        function removeFromCart(index) {
            cart.splice(index, 1)
            renderCart()
            updateCartCount()
        }
        
        function checkout() {
            if (cart.length === 0) return
            toggleCart()
            setTimeout(() => {
                showToast('سفارش شما با موفقیت ثبت شد. کد پیگیری: LN-9382', 'success')
                cart = []
                updateCartCount()
            }, 700)
        }
        
        function renderModels() {
            const container = document.getElementById('models-grid')
            container.innerHTML = ''
            
            hairModels.forEach(model => {
                const div = document.createElement('div')
                div.className = `model-card bg-white border border-zinc-100 rounded-3xl overflow-hidden cursor-pointer`
                div.innerHTML = `
                    <div class="relative">
                        <img src="${model.img}" class="w-full h-72 object-cover hair-model">
                        <div class="absolute top-4 right-4 text-[10px] bg-white/90 backdrop-blur px-4 py-1 rounded-3xl font-medium">${model.category}</div>
                    </div>
                    <div class="px-5 py-6">
                        <div class="font-semibold">${model.title}</div>
                        <div onclick="event.stopImmediatePropagation(); likeModel(${model.id});" 
                             class="absolute bottom-6 left-6 text-rose-400 text-xl">
                            <i class="fa-solid fa-heart"></i>
                        </div>
                    </div>
                `
                container.append(div)
            })
        }
        
        function likeModel(id) {
            event.stopImmediatePropagation()
            showToast('به علاقه‌مندی‌ها اضافه شد ❤️')
        }
        
        function showAllModels() {
            showToast('در نسخه کامل، تمام ۳۲ مدل نمایش داده خواهد شد')
            navigateToSection('models')
        }
        
        function renderShop() {
            const container = document.getElementById('shop-grid')
            container.innerHTML = ''
            
            const filtered = currentFilter === 'all' ? products : products.filter(p => p.category === currentFilter)
            
            filtered.forEach(product => {
                const card = document.createElement('div')
                card.className = `product-card bg-white rounded-3xl overflow-hidden border border-transparent hover:border-zinc-200`
                card.innerHTML = `
                    <div class="relative">
                        <img src="${product.img}" class="w-full aspect-square object-cover">
                        <div onclick="event.stopImmediatePropagation(); quickAddToCart(${product.id});" 
                             class="absolute top-4 left-4 bg-white h-8 w-8 rounded-2xl flex items-center justify-center shadow text-rose-500 text-lg leading-none pt-px">🛒</div>
                    </div>
                    <div class="p-5">
                        <div class="text-xs text-zinc-400">${product.category}</div>
                        <div class="font-medium text-base mt-1 line-clamp-2">${product.name}</div>
                        <div class="flex justify-between items-baseline mt-6">
                            <div class="font-bold text-rose-500">${product.price.toLocaleString('fa-IR')} تومان</div>
                            <button onclick="event.stopImmediatePropagation(); addToCartFromShop(${product.id});" 
                                    class="text-xs border border-zinc-300 hover:bg-zinc-50 px-5 py-3 rounded-3xl">اضافه به سبد</button>
                        </div>
                    </div>
                `
                container.append(card)
            })
        }
        
        function addToCartFromShop(id) {
            const product = products.find(p => p.id === id)
            if (product) {
                addToCart(product)
            }
        }
        
        function quickAddToCart(id) {
            const product = products.find(p => p.id === id)
            if (product) addToCart(product)
        }
        
        function filterShop(cat) {
            currentFilter = cat
            document.getElementById('current-filter').innerText = cat === 'all' ? 'همه محصولات' : cat
            renderShop()
        }
        
        function showAllProducts() {
            showToast('در نسخه کامل، ۴۸ محصول نمایش داده می‌شود')
        }
        
        function renderBookingStep() {
            const container = document.getElementById('booking-form-content')
            
            if (currentBookingStep === 0) {
                container.innerHTML = `
                    <div class="text-xs font-medium text-zinc-400 mb-4">انتخاب خدمت</div>
                    <div class="grid grid-cols-2 gap-3 text-sm" id="service-select-grid">
                    </div>
                    
                    <button onclick="nextBookingStep()" 
                            class="mt-10 w-full py-6 text-lg font-semibold border border-white/30 hover:bg-white/10 transition-colors rounded-3xl">ادامه →</button>
                `
                
                const grid = document.getElementById('service-select-grid')
                
                services.forEach((service, i) => {
                    const serviceEl = document.createElement('div')
                    serviceEl.className = `px-5 py-6 border ${selectedServiceIndex === i ? 'border-rose-400 bg-rose-50' : 'border-white/10'} rounded-3xl cursor-pointer transition-all`
                    serviceEl.innerHTML = `
                        <div class="font-medium">${service.title}</div>
                        <div class="text-xs text-zinc-400">${service.duration}</div>
                        <div class="text-rose-400 font-semibold text-xl mt-5">${service.price.toLocaleString('fa-IR')}</div>
                    `
                    serviceEl.onclick = () => {
                        selectedServiceIndex = i
                        renderBookingStep()
                    }
                    grid.append(serviceEl)
                })
            } 
            else if (currentBookingStep === 1) {
                container.innerHTML = `
                    <div>
                        <div class="text-xs font-medium text-zinc-400 mb-3">تاریخ و ساعت</div>
                        <div class="flex gap-x-2 overflow-auto pb-6">
                            <div onclick="this.classList.toggle('ring-2');this.classList.toggle('ring-rose-400')" 
                                 class="cursor-pointer text-center min-w-[70px] bg-white/5 border border-white/10 hover:border-white/40 transition-colors rounded-3xl py-4">
                                <div class="text-xs text-white/60">امروز</div>
                                <div class="font-semibold text-xl text-white">۲۹</div>
                                <div class="text-rose-400 text-xs">اردیبهشت</div>
                            </div>
                            <div onclick="this.classList.toggle('ring-2');this.classList.toggle('ring-rose-400')" 
                                 class="cursor-pointer text-center min-w-[70px] bg-white/5 border border-white/10 hover:border-white/40 transition-colors rounded-3xl py-4 ring-2 ring-rose-400">
                                <div class="text-xs text-white/60">فردا</div>
                                <div class="font-semibold text-xl text-white">۳۰</div>
                                <div class="text-rose-400 text-xs">اردیبهشت</div>
                            </div>
                            <div onclick="this.classList.toggle('ring-2');this.classList.toggle('ring-rose-400')" 
                                 class="cursor-pointer text-center min-w-[70px] bg-white/5 border border-white/10 hover:border-white/40 transition-colors rounded-3xl py-4">
                                <div class="text-xs text-white/60">شنبه</div>
                                <div class="font-semibold text-xl text-white">۳۱</div>
                                <div class="text-rose-400 text-xs">اردیبهشت</div>
                            </div>
                        </div>
                        
                        <div class="mt-6 grid grid-cols-3 gap-3 text-xs">
                            <div onclick="selectTimeSlot(this)" class="bg-white/5 border border-white/10 hover:border-emerald-400 transition-all text-center py-5 rounded-3xl cursor-pointer">۱۱:۰۰</div>
                            <div onclick="selectTimeSlot(this)" class="bg-white/5 border border-white/10 hover:border-emerald-400 transition-all text-center py-5 rounded-3xl cursor-pointer">۱۲:۳۰</div>
                            <div onclick="selectTimeSlot(this)" class="bg-emerald-900 text-emerald-400 border border-emerald-400 text-center py-5 rounded-3xl cursor-pointer">۱۴:۴۵</div>
                            <div onclick="selectTimeSlot(this)" class="bg-white/5 border border-white/10 hover:border-emerald-400 transition-all text-center py-5 rounded-3xl cursor-pointer">۱۶:۰۰</div>
                            <div onclick="selectTimeSlot(this)" class="bg-white/5 border border-white/10 hover:border-emerald-400 transition-all text-center py-5 rounded-3xl cursor-pointer">۱۷:۳۰</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 mt-14">
                        <button onclick="prevBookingStep()" 
                                class="flex-1 py-5 border border-white/30 text-white rounded-3xl">قبلی</button>
                        <button onclick="nextBookingStep()" 
                                class="flex-1 py-5 bg-white text-zinc-900 font-semibold rounded-3xl">بعدی</button>
                    </div>
                `
            } 
            else if (currentBookingStep === 2) {
                container.innerHTML = `
                    <div class="bg-white/5 border border-dashed border-white/30 rounded-3xl p-7 text-center">
                        <div class="mx-auto w-20 h-20 bg-white/10 text-white rounded-3xl flex items-center justify-center text-4xl mb-5">💇🏻‍♀️</div>
                        <div class="font-medium text-white">نوبت شما ثبت شد!</div>
                        <div class="text-xs text-emerald-400 mt-1">پنجشنبه ۳۰ اردیبهشت - ساعت ۱۴:۴۵</div>
                        
                        <div class="my-10 border-t border-white/10"></div>
                        
                        <div class="flex justify-between text-xs">
                            <div class="text-left">
                                <div class="text-white/60">خدمت</div>
                                <div class="font-medium text-white">${selectedServiceIndex !== null ? services[selectedServiceIndex].title : 'رنگ مو'}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-white/60">هزینه</div>
                                <div class="font-medium text-rose-400">${selectedServiceIndex !== null ? services[selectedServiceIndex].price.toLocaleString('fa-IR') : '۳۸۰,۰۰۰'} تومان</div>
                            </div>
                        </div>
                    </div>
                    
                    <button onclick="finishBooking()" 
                            class="mt-10 w-full py-7 bg-emerald-500 text-white font-bold rounded-3xl">تأیید و ذخیره نوبت</button>
                    
                    <div onclick="prevBookingStep()" class="text-center text-xs text-white/60 mt-6 cursor-pointer">ویرایش نوبت</div>
                `
            }
        }
        
        function setBookingStep(step) {
            currentBookingStep = step
            renderBookingStep()
        }
        
        function nextBookingStep() {
            if (currentBookingStep < 2) {
                currentBookingStep++
                renderBookingStep()
            }
        }
        
        function prevBookingStep() {
            if (currentBookingStep > 0) {
                currentBookingStep--
                renderBookingStep()
            }
        }
        
        function selectTimeSlot(el) {
            document.querySelectorAll('#booking-form-content .grid.grid-cols-3 > div').forEach(item => {
                item.classList.remove('bg-emerald-900', 'text-emerald-400', 'border-emerald-400')
            })
            el.classList.add('bg-emerald-900', 'text-emerald-400', 'border-emerald-400')
        }
        
        function selectService(i) {
            selectedServiceIndex = i
            navigateToSection('booking')
            currentBookingStep = 0
            setTimeout(() => {
                renderBookingStep()
            }, 900)
        }
        
        function completeBookingDemo() {
            currentBookingStep = 2
            renderBookingStep()
            showToast('نوبت شما ثبت گردید!', 'success')
        }
        
        function quickBookExample() {
            showToast('نوبت آزمایشی رزرو شد', 'success')
        }
        
        function finishBooking() {
            hideLoginModal()
            showToast('نوبت شما با موفقیت ثبت شد. جزئیات به واتساپ شما ارسال گردید.', 'success')
            currentBookingStep = 0
        }
        
        function watchFeaturedVideo() {
            showToast('در حال پخش ویدیو آموزشی...', 'success')
        }
        
        function openTutorial(n) {
            const messages = [
                "در حال پخش ویدیو «چگونه موهای خود را لایت کنیم»",
                "در حال پخش ویدیو «آرایش روزانه در ۱۰ دقیقه»"
            ]
            showToast(messages[n])
        }
        
        function prevTestimonial() {
            currentTestimonial = (currentTestimonial - 1 + testimonials.length) % testimonials.length
            updateTestimonial()
        }
        
        function nextTestimonial() {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length
            updateTestimonial()
        }
        
        function updateTestimonial() {
            const t = testimonials[currentTestimonial]
            document.getElementById('testimonial-text').innerText = `“${t.text}”`
            document.getElementById('testimonial-name').innerText = t.name
            document.getElementById('testimonial-role').innerText = t.role
        }
        
        function fakeSocialClick() {
            showToast('به اینستاگرام لونا هدایت می‌شوید')
        }
        
        function subscribeNewsletter() {
            const input = document.getElementById('newsletter-input')
            if (input.value.trim() !== '') {
                showToast('به خبرنامه ما خوش آمدید!')
                input.value = ''
            }
        }
        
        // Progress bar
        function setupProgressBar() {
            const progressBar = document.getElementById('progress-bar')
            
            window.addEventListener('scroll', () => {
                const scrollTop = window.scrollY
                const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight
                const scrollPercent = scrollTop / docHeight * 100
                progressBar.style.width = `${scrollPercent}%`
            })
        }
        
        // Keyboard shortcuts
        function handleKeyboard(e) {
            if (e.metaKey && e.key === "k") {
                e.preventDefault()
                showLoginModal()
            }
        }
        
        // Main initialization
        function initialize() {
            initializeTailwind()
            
            // Render dynamic components
            renderModels()
            renderShop()
            updateTestimonial()
            renderBookingStep()
            
            // Progress bar
            setupProgressBar()
            
            // Keyboard
            document.addEventListener('keydown', handleKeyboard)
            
            // Make phone input auto format
            const phoneInput = document.getElementById('phone-input')
            if (phoneInput) {
                phoneInput.addEventListener('keyup', function() {
                    let val = this.value.replace(/\D/g, '')
                    if (val.length > 10) val = val.substring(0, 10)
                    this.value = val
                })
            }
            
            // Demo add some products to cart
            setTimeout(() => {
                cart = [products[0], products[2]]
                updateCartCount()
            }, 2100)
            
            // Click anywhere on hero to scroll to services
            const hero = document.querySelector('header')
            if (hero) {
                hero.style.cursor = 'pointer'
                hero.addEventListener('click', function(e) {
                    if (e.target.tagName === "BUTTON" || e.target.closest('button')) return
                    navigateToSection('services')
                })
            }
            
            // Show welcome toast
            setTimeout(() => {
                showToast('به آرایشگاه لونا خوش آمدید 👋')
            }, 2100)
            
            console.log('%cآرایشگاه لونا آماده است 🚀', 'color:#e11d48; font-family:monospace')
        }
        
        // Boot app
        window.onload = initialize
    </script>
