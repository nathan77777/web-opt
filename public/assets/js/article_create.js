const imagesInput = document.getElementById('images');
const mainImageIndexInput = document.getElementById('main_image_index');
const mainImageChoices = document.getElementById('main-image-choices');

function renderMainImageChoices() {
    const files = imagesInput.files;

    if (!files || files.length === 0) {
        mainImageChoices.innerHTML = '<em>Aucune image sélectionnée.</em>';
        mainImageIndexInput.value = '0';
        return;
    }

    const selectedIndex = Number.parseInt(mainImageIndexInput.value, 10);
    const safeIndex = Number.isInteger(selectedIndex) && selectedIndex >= 0 && selectedIndex < files.length
        ? selectedIndex
        : 0;

    let html = '';
    for (let i = 0; i < files.length; i++) {
        const checked = i === safeIndex ? 'checked' : '';
        const fileName = files[i].name
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        html += `<label style="display:block;margin-bottom:4px;"><input type="radio" name="main_image_choice" value="${i}" ${checked}> ${fileName}</label>`;
    }

    mainImageChoices.innerHTML = html;

    const selected = mainImageChoices.querySelector('input[name="main_image_choice"]:checked');
    mainImageIndexInput.value = selected ? selected.value : '0';
}

imagesInput.addEventListener('change', renderMainImageChoices);
mainImageChoices.addEventListener('change', function (event) {
    if (event.target && event.target.name === 'main_image_choice') {
        mainImageIndexInput.value = event.target.value;
    }
});

renderMainImageChoices();

// Auto-generate slug from title (client-side convenience)
document.getElementById('title').addEventListener('input', function () {
    const slugField = document.getElementById('slug');
    if (slugField.dataset.touched) return; // Don't overwrite manual edits
    slugField.value = this.value
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // strip accents
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
});
document.getElementById('slug').addEventListener('input', function () {
    this.dataset.touched = '1';
});