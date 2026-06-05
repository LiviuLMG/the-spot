// --- LIVE SEARCH LOGIC (Optimized with Debounce) ---
document.addEventListener('DOMContentLoaded', function () {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        const form = input.closest('form');
        const resultsBox = document.createElement('div');
        resultsBox.id = 'live-search-results';
        form.appendChild(resultsBox);

        let debounceTimer;

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            
            let query = this.value.trim();

            if (query.length >= 2) {
                debounceTimer = setTimeout(() => {
                    fetch(BASE_URL + 'ajax/ajax_search.php?q=' + encodeURIComponent(query))
                        .then(response => response.text())
                        .then(data => {
                            resultsBox.innerHTML = data;
                            resultsBox.style.display = 'block';
                        });
                }, 300);
            } else {
                resultsBox.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!form.contains(e.target)) resultsBox.style.display = 'none';
        });
    });
});

// Funcție generică pentru a rula formularele AJAX (DRY applied)
function handleAjaxForm(formId, modalId, loadingText) {
    const form = document.querySelector(formId);
    if (!form) return;

    const toastElement = document.getElementById('systemToast');
    const toastMessage = document.getElementById('toastMessage');
    const bsToast = new bootstrap.Toast(toastElement, { delay: 4000 });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${loadingText}`;
        submitBtn.disabled = true;

        const formData = new FormData(this);

        fetch(form.action, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                toastElement.classList.remove('toast-success', 'toast-error');
                if (data.status === 'success') {
                    toastElement.classList.add('toast-success');
                    toastMessage.innerHTML = `<i class="fa-solid fa-circle-check toast-icon text-info"></i> ${data.message}`;
                    
                    if (modalId) {
                        const modalInstance = bootstrap.Modal.getInstance(document.getElementById(modalId));
                        if(modalInstance) modalInstance.hide();
                    }
                    
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    toastElement.classList.add('toast-error');
                    toastMessage.innerHTML = `<i class="fa-solid fa-triangle-exclamation toast-icon text-danger"></i> ${data.message}`;
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;

                    // --- RESET TURNSTILE LA EROARE ---
                    // Dacă datele sunt greșite, resetăm widget-ul ca să ceară un jeton nou pentru următoarea apăsare de buton
                    if (typeof turnstile !== 'undefined') {
                        turnstile.reset();
                    }
                }
                bsToast.show();
            })
            .catch(error => {
                console.error('Error:', error);
                toastElement.classList.add('toast-error');
                toastMessage.innerHTML = `<i class="fa-solid fa-triangle-exclamation toast-icon text-danger"></i> Eroare de server.`;
                bsToast.show();
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

                // --- RESET TURNSTILE LA EROARE DE REȚEA ---
                if (typeof turnstile !== 'undefined') {
                    turnstile.reset();
                }
            });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    handleAjaxForm('#addEventModal form', 'addEventModal', 'Publishing...');
    handleAjaxForm('#editEventForm', 'editEventModal', 'Saving...');
    handleAjaxForm('#ajaxLoginForm', 'loginModal', 'Logging in...');
    handleAjaxForm('#ajaxForgotForm', 'forgotPasswordModal', 'Sending link...');
    handleAjaxForm('#contactForm', null, 'Sending...');
    handleAjaxForm('#careerForm', null, 'Sending application...');
});

// --- AJAX PENTRU JOIN / LEAVE EVENT ---
document.addEventListener('DOMContentLoaded', function () {
    const joinForm = document.getElementById('joinEventForm');
    const participantCountSpan = document.getElementById('participantCount');

    if (joinForm) {
        joinForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const btn = document.getElementById('joinEventBtn');
            const originalText = btn.innerHTML;

            // Arătăm spinner-ul
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btn.disabled = true;

            const formData = new FormData(this);
            const toastElement = document.getElementById('systemToast');
            const toastMessage = document.getElementById('toastMessage');
            const bsToast = new bootstrap.Toast(toastElement, { delay: 3000 });

            fetch(joinForm.action, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    toastElement.classList.remove('toast-success', 'toast-error');

                    if (data.status === 'joined') {
                        toastElement.classList.add('toast-success');
                        toastMessage.innerHTML = `<i class="fa-solid fa-circle-check toast-icon text-info"></i> ${data.message}`;

                        btn.className = 'btn btn-outline-danger w-100 rounded-pill fw-bold py-2 mb-2';
                        btn.style.borderWidth = '2px';
                        btn.innerHTML = '<i class="fa-solid fa-xmark me-2"></i> Leave Event';

                        if (participantCountSpan) {
                            let currentCount = parseInt(participantCountSpan.innerText) || 0;
                            participantCountSpan.innerText = currentCount + 1;
                        }
                    } else if (data.status === 'left') {
                        toastElement.classList.add('toast-success');
                        toastMessage.innerHTML = `<i class="fa-solid fa-circle-check toast-icon text-info"></i> ${data.message}`;

                        btn.className = 'btn btn-dark-map w-100 rounded-pill py-2 text-white fw-medium d-flex justify-content-center align-items-center mb-2';
                        btn.style.borderWidth = '';
                        btn.innerHTML = 'Join Event';

                        if (participantCountSpan) {
                            let currentCount = parseInt(participantCountSpan.innerText) || 0;
                            participantCountSpan.innerText = Math.max(0, currentCount - 1);
                        }
                    } else {
                        toastElement.classList.add('toast-error');
                        toastMessage.innerHTML = `<i class="fa-solid fa-triangle-exclamation toast-icon text-danger"></i> ${data.message}`;
                        btn.innerHTML = originalText;
                    }
                    bsToast.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastElement.classList.add('toast-error');
                    toastMessage.innerHTML = `<i class="fa-solid fa-triangle-exclamation toast-icon text-danger"></i> Eroare de server.`;
                    bsToast.show();
                    btn.innerHTML = originalText;
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });
    }
});