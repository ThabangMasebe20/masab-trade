let currentLanguage = localStorage.getItem('language') || 'en';
let translations    = {};

document.addEventListener('DOMContentLoaded', function () {
    loadLanguage(currentLanguage);
    const sel = document.getElementById('languageSelect');
    if (sel) sel.value = currentLanguage;
});

async function loadLanguage(lang) {
    try {
        
        const depth = (window.location.pathname.match(/\//g) || []).length - 2;
        const base  = depth <= 0 ? '' : '../'.repeat(depth);
        const res   = await fetch(base + 'assets/languages/' + lang + '.json');
        if (!res.ok) throw new Error('Not found');

        translations = await res.json();
        translatePage();
        localStorage.setItem('language', lang);
        currentLanguage = lang;
    } catch (err) {
        if (lang !== 'en') loadLanguage('en');
    }
}

function translatePage() {
    document.querySelectorAll('[data-translate]').forEach(el => {
        const val = getTranslation(el.getAttribute('data-translate'));
        if (val && typeof val === 'string') {
            if (el.tagName === 'INPUT' && el.placeholder !== undefined) {
                el.placeholder = val;
            } else {
                el.textContent = val;
            }
        }
    });
}

function getTranslation(key) {
    return key.split('.').reduce((obj, k) => (obj && obj[k] !== undefined ? obj[k] : null), translations);
}

function changeLanguage(lang) { loadLanguage(lang); }