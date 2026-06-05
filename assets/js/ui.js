// Luăm ambele butoane
let mybutton = document.getElementById("btn-back-to-top");
let addEventBtn = document.getElementById("btn-add-event");

window.onscroll = function () { scrollFunction(); };

function scrollFunction() {
    // 1. Arată sau ascunde butoanele după 300px
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        if (mybutton) mybutton.classList.add("show");
        if (addEventBtn) addEventBtn.classList.add("show");
    } else {
        if (mybutton) mybutton.classList.remove("show");
        if (addEventBtn) addEventBtn.classList.remove("show");
    }

    // 2. Ridică butoanele când ajungi la footer
    let isAtBottom = Math.ceil(window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight - 100;

    if (isAtBottom) {
        if (mybutton) mybutton.classList.add("lift-up");
        if (addEventBtn) addEventBtn.classList.add("lift-up");
    } else {
        if (mybutton) mybutton.classList.remove("lift-up");
        if (addEventBtn) addEventBtn.classList.remove("lift-up");
    }
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function saveCookiesAndClose() {
    const toastElement = document.getElementById('systemToast');
    const toastMessage = document.getElementById('toastMessage');
    const bsToast = new bootstrap.Toast(toastElement, { delay: 3000 });

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('cookieModal'));
    modalInstance.hide();

    toastElement.classList.remove('toast-error');
    toastElement.classList.add('toast-success');
    toastMessage.innerHTML = `<i class="fa-solid fa-circle-check toast-icon text-info"></i> Cookie preferences saved successfully!`;
    bsToast.show();
}

// --- CUSTOM FILE INPUT DISPLAY & IMAGE PREVIEW ---
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('event-image');
    const fileNameDisplay = document.getElementById('file-name');
    const previewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');

    if (fileInput && fileNameDisplay && previewContainer && imagePreview) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                fileNameDisplay.textContent = file.name;
                fileNameDisplay.style.color = '#e2e8f0';

                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                fileNameDisplay.textContent = 'No file chosen...';
                fileNameDisplay.style.color = '#64748b';
                imagePreview.src = '';
                previewContainer.classList.add('d-none');
            }
        });
    }
});

// --- AIR DATEPICKER INIT ---
document.addEventListener('DOMContentLoaded', function () {
    
    // Definim limba engleză o singură dată ca să păstrăm codul curat (DRY)
    const englishLocale = {
        days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        today: 'Today',
        clear: 'Clear',
        dateFormat: 'yyyy-MM-dd',
        firstDay: 1 // Lunea e prima zi din săptămână
    };

    const dateInput = document.getElementById('event-date');
    if (dateInput) {
        new AirDatepicker(dateInput, {
            locale: englishLocale,
            minDate: new Date(),
            container: '#addEventModal',
            autoClose: true
        });
    }

    const editDateInput = document.getElementById('edit-event-date');
    if (editDateInput) {
        new AirDatepicker(editDateInput, {
            locale: englishLocale,
            minDate: new Date(),
            container: '#editEventModal',
            autoClose: true
        });
    }
});

// --- PREVIEW IMAGINE NOUĂ EDIT ---
document.addEventListener('DOMContentLoaded', function () {
    const editFileInput = document.getElementById('edit-event-image');
    const editFileNameDisplay = document.getElementById('edit-file-name');
    const editImagePreview = document.getElementById('edit-image-preview');

    if (editFileInput && editFileNameDisplay && editImagePreview) {
        const originalImageSrc = editImagePreview.src;
        editFileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                editFileNameDisplay.textContent = file.name;
                editFileNameDisplay.style.color = '#e2e8f0';
                const reader = new FileReader();
                reader.onload = function (e) {
                    editImagePreview.src = e.target.result;
                    editImagePreview.nextElementSibling.textContent = "New Image Preview";
                };
                reader.readAsDataURL(file);
            } else {
                editFileNameDisplay.textContent = 'No new file chosen...';
                editFileNameDisplay.style.color = '#64748b';
                editImagePreview.src = originalImageSrc;
                editImagePreview.nextElementSibling.textContent = "Current Image";
            }
        });
    }
});


// --- CUSTOM CATEGORY DROPDOWN LOGIC ---
document.addEventListener('DOMContentLoaded', function () {
    function setupCategoryDropdown(itemSelector, inputId, textId) {
        const items = document.querySelectorAll(itemSelector);
        const hiddenInput = document.getElementById(inputId);
        const selectedText = document.getElementById(textId);

        if (items.length > 0) {
            items.forEach(item => {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    const selectedValue = this.getAttribute('data-value');
                    
                    if (hiddenInput) hiddenInput.value = selectedValue;
                    
                    if (selectedText) {
                        selectedText.textContent = selectedValue;
                        selectedText.style.color = '#ffffff'; // Devine alb după selecție
                    }
                });
            });
        }
    }
    
    // Inițializările vechi
    setupCategoryDropdown('.category-item', 'category-hidden-input', 'category-selected-text');
    setupCategoryDropdown('.edit-category-item', 'edit-category-hidden-input', 'edit-category-selected-text');
    
    // NOU: Inițializarea pentru subiectul din Contact
    setupCategoryDropdown('.subject-item', 'subject-hidden-input', 'subject-selected-text');
    
    // Dropdown pentru pagina de Careers
    setupCategoryDropdown('.career-item', 'career-hidden-input', 'career-selected-text');
});










// --- DUAL PRICE SLIDER LOGIC ---
document.addEventListener('DOMContentLoaded', function () {
    const minSlider = document.getElementById('minPrice');
    const maxSlider = document.getElementById('maxPrice');
    const minVal = document.getElementById('minPriceVal');
    const maxVal = document.getElementById('maxPriceVal');
    const rangeTrack = document.getElementById('rangeTrack');

    if (minSlider && maxSlider) {
        function updateSliderUI() {
            let min = parseInt(minSlider.value);
            let max = parseInt(maxSlider.value);

            // Prevenim ca minimul să treacă de maxim
            if (min > max) {
                let tmp = max;
                minSlider.value = tmp;
                min = tmp;
            }

            // Actualizăm textul
            minVal.textContent = min + ' RON';
            maxVal.textContent = max >= 500 ? 'Any' : max + ' RON';

            // Calculăm poziția și lățimea barei colorate
            let percent1 = (min / minSlider.max) * 100;
            let percent2 = (max / maxSlider.max) * 100;
            
            rangeTrack.style.left = percent1 + '%';
            rangeTrack.style.width = (percent2 - percent1) + '%';
        }

        function applyFilters() {
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('min_price', minSlider.value);
            currentUrl.searchParams.set('max_price', maxSlider.value);
            currentUrl.searchParams.delete('page'); // Resetăm pagina
            currentUrl.hash = 'events-section';
            
            window.location.href = currentUrl.toString();
        }

        // Evenimente pentru tragere (update vizual instant)
        minSlider.addEventListener('input', updateSliderUI);
        maxSlider.addEventListener('input', updateSliderUI);

        // Evenimente pentru când eliberezi click-ul (apucă filtrul)
        minSlider.addEventListener('change', applyFilters);
        maxSlider.addEventListener('change', applyFilters);

        // Inițializare la încărcarea paginii
        updateSliderUI();
    }
});