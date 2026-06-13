document.addEventListener('DOMContentLoaded', function () {
    initFormValidation();
});

function previewImages(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert('File too large! Max 5MB.');
        event.target.value = '';
        return;
    }

    const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!allowed.includes(file.type)) {
        alert('Invalid file type. Only JPG, PNG, GIF allowed.');
        event.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('imagePreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function initFormValidation() {
    const form = document.getElementById('productForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const fields = [
            { id: 'productName', msg: 'Please enter a product name.' },
            { id: 'category',    msg: 'Please select a category.' },
            { id: 'price',       msg: 'Please enter a valid price.' },
            { id: 'condition',   msg: 'Please select a condition.' },
            { id: 'description', msg: 'Please enter a description (min 20 chars).' },
            { id: 'location',    msg: 'Please enter your location.' },
        ];

        for (const f of fields) {
            const el = document.getElementById(f.id);
            if (!el) continue;
            const val = el.value.trim();
            if (!val || (f.id === 'description' && val.length < 20) || (f.id === 'price' && parseFloat(val) <= 0)) {
                alert(f.msg);
                e.preventDefault();
                el.focus();
                return;
            }
        }
    });
}