document.addEventListener('DOMContentLoaded', function () {
    initSmoothScrolling();
    initSessionTimeout();
});

function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
            const id = this.getAttribute('href');
            if (id !== '#') {
                const el = document.querySelector(id);
                if (el) {
                    e.preventDefault();
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
}


let inactivityTimer, warningTimer;
const TIMEOUT = 5 * 60 * 1000;
const WARNING = 4 * 60 * 1000;   

function resetTimer() {
    clearTimeout(inactivityTimer);
    clearTimeout(warningTimer);
    const w = document.getElementById('sessionWarning');
    if (w) w.style.display = 'none';

    warningTimer    = setTimeout(showSessionWarning, WARNING);
    inactivityTimer = setTimeout(function () {
        window.location.href = '/backend/auth/logout.php';
    }, TIMEOUT);
}

function showSessionWarning() {
    let w = document.getElementById('sessionWarning');
    if (!w) {
        w = document.createElement('div');
        w.id = 'sessionWarning';
        w.innerHTML = `
            <div class="session-warning">
                <i class="fas fa-clock"></i>
                <span>Your session expires in 1 minute due to inactivity.</span>
                <button onclick="resetTimer()">Stay Logged In</button>
            </div>`;
        document.body.appendChild(w);
    }
    w.style.display = 'block';
}

function initSessionTimeout() {
    // Only activate if user appears to be logged in
    const hasLogout = document.querySelector('.nav-logout, .logout-link');
    if (!hasLogout) return;

    ['mousemove', 'keypress', 'click', 'scroll', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, resetTimer, { passive: true });
    });
    resetTimer();
}


function changeLanguage(lang) {
    if (typeof loadLanguage === 'function') loadLanguage(lang);
}


function formatCurrency(amount) {
    return 'R ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}