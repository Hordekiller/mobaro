<script>
// Tab switching
const menuItems = document.querySelectorAll('.menu-item[data-tab]');
const tabs = document.querySelectorAll('.tab-content');

function switchTab(tabId) {
    menuItems.forEach(m => m.classList.remove('active'));
    tabs.forEach(t => t.classList.remove('active'));
    document.querySelector(`.menu-item[data-tab="${tabId}"]`).classList.add('active');
    document.getElementById(tabId).classList.add('active');
    window.scrollTo({top:0, behavior:'smooth'});
}

menuItems.forEach(item => {
    item.addEventListener('click', () => {
        switchTab(item.dataset.tab);
    });
});

// Apt filter
function filterApts(btn, type) {
    document.querySelectorAll('#appointments .tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// Toggle password
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Password strength
function checkStrength(pwd) {
    const bars = [document.getElementById('bar1'), document.getElementById('bar2'), document.getElementById('bar3'), document.getElementById('bar4')];
    const label = document.getElementById('strengthLabel');
    let strength = 0;
    if (pwd.length >= 8) strength++;
    if (/[A-Z]/.test(pwd) || /[آ-ی]/.test(pwd)) strength++;
    if (/[0-9]/.test(pwd)) strength++;
    if (/[^A-Za-z0-9آ-ی]/.test(pwd)) strength++;

    bars.forEach(b => b.style.background = 'var(--border)');
    const colors = ['#E57373', '#FFA726', '#FFC107', '#4CAF50'];
    const labels = ['ضعیف', 'متوسط', 'خوب', 'قوی'];
    for (let i = 0; i < strength; i++) {
        bars[i].style.background = colors[strength-1];
    }
    label.textContent = pwd ? `قدرت رمز عبور: ${labels[strength-1] || 'ضعیف'}` : 'قدرت رمز عبور: -';
    label.style.color = pwd ? colors[strength-1] || '#E57373' : 'var(--gray)';
}

// Copy code
function copyCode(btn, code) {
    navigator.clipboard.writeText(code);
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> کپی شد';
    btn.style.background = '#4CAF50';
    setTimeout(() => {
        btn.innerHTML = original;
        btn.style.background = '';
    }, 1500);
}
</script>
