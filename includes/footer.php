<footer class="custom-footer pt-5 pb-4">
    <div class="container px-4 px-sm-3">
        <div class="row">

            <div class="col-lg-4 col-md-6 mb-4 pe-lg-5">
                <div class="d-flex align-items-center mb-3">
                    <img
                        src="<?php echo BASE_URL; ?>assets/images/logo.png"
                        alt="The Spot Logo"
                        style="width: 40px; height: 40px; margin-right: 12px; border-radius: 8px;">

                    <h5 class="fw-bold text-white m-0" style="font-size: 1.25rem;">
                        The Spot
                    </h5>
                </div>

                <p class="footer-text mb-4">
                    Your gateway to the best events, nightlife and cultural experiences in cities worldwide.
                </p>

                <div class="d-flex">
                    <a
                        href="https://www.instagram.com/thespot.ro"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="social-btn"
                        title="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>

                    <a
                        href="https://www.tiktok.com/@thespot.ro"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="social-btn"
                        title="TikTok">
                        <i class="fa-brands fa-tiktok"></i>
                    </a>

                    <a
                        href="https://www.youtube.com/watch?v=CBEvfZu4HE4"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="social-btn"
                        title="YouTube">
                        <i class="fa-brands fa-youtube"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="text-uppercase footer-heading">About Us</h6>

                <ul class="list-unstyled">
                    <li>
                        <a href="<?php echo BASE_URL; ?>pages/our-story.php" class="footer-link">
                            Our Story
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>pages/how-it-works.php" class="footer-link">
                            How it works
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>pages/careers.php" class="footer-link">
                            Careers
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>pages/contact.php" class="footer-link">
                            Contact
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase footer-heading">Cities</h6>

                <ul class="list-unstyled" id="footer-cities-list">

                    <?php
                    if (isset($conn)) {

                        $sql_footer_cities = "
                            SELECT DISTINCT cities.id, cities.name
                            FROM cities
                            JOIN events ON cities.id = events.city_id
                            WHERE events.date_time >= NOW()
                            AND cities.status = 'active'
                            ORDER BY cities.name ASC
                        ";

                        $res_footer_cities = $conn->query($sql_footer_cities);

                        if ($res_footer_cities && $res_footer_cities->num_rows > 0) {

                            while ($f_city = $res_footer_cities->fetch_assoc()) {

                                echo '
                                    <li>
                                        <a href="' . BASE_URL . '?switch_city=' . intval($f_city['id']) . '" class="footer-link">
                                            ' . htmlspecialchars($f_city['name'], ENT_QUOTES, 'UTF-8') . '
                                        </a>
                                    </li>
                                ';
                            }
                        } else {

                            echo '
                                <li>
                                    <span class="footer-text small">
                                        No active cities
                                    </span>
                                </li>
                            ';
                        }
                    }
                    ?>

                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="text-uppercase footer-heading">For Organizers</h6>

                <ul class="list-unstyled">
                    <li>
                        <a href="<?php echo BASE_URL; ?>pages/pricing-plans.php" class="footer-link">
                            Pricing Plans
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="footer-link">
                            Organizer Dashboard
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>pages/analytics.php" class="footer-link">
                            Analytics
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo BASE_URL; ?>pages/contact.php" class="footer-link">
                            Support
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <hr class="footer-divider">

        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="footer-text m-0" style="font-size: 0.85rem;">
                    &copy; <?php echo date('Y'); ?> The Spot. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="<?php echo BASE_URL; ?>pages/privacy.php" class="footer-bottom-link">Privacy Policy</a>
                <a href="<?php echo BASE_URL; ?>pages/terms.php" class="footer-bottom-link">Terms of Service</a>
                <a href="#" class="footer-bottom-link" data-bs-toggle="modal" data-bs-target="#cookieModal">Cookie Settings</a>
            </div>
        </div>
    </div>
</footer>

<a href="#" data-bs-toggle="modal" data-bs-target="#addEventModal" id="btn-add-event" title="Adaugă Eveniment">
    <i class="fa-solid fa-plus icon"></i>
    <span class="text">Add Event</span>
</a>

<button onclick="scrollToTop()" id="btn-back-to-top" title="Go to top">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<div class="modal fade custom-event-modal" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal-content border-0 rounded-4">

            <div class="modal-header glass-modal-header border-0 position-relative">
                <h5 class="modal-title w-100 text-center fw-bold text-white" id="addEventModalLabel">Add New Event</h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-3">
                <form method="POST" action="<?php echo BASE_URL; ?>actions/process_event.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-3">
                        <label class="form-label custom-label">Event Title</label>
                        <input type="text" name="title" class="form-control custom-input" placeholder="Enter event title" required>
                    </div>

                    <div class="row gx-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label custom-label">City</label>
                            <input type="text" id="city-input" class="form-control custom-input" name="city_name" placeholder="Choose or type..." required autocomplete="off">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label custom-label">Category</label>

                            <input type="hidden" name="category_name" id="category-hidden-input" required>

                            <div class="dropdown">
                                <button class="form-control custom-input d-flex justify-content-between align-items-center w-100 text-start shadow-none" type="button" id="categoryDropdownBtn" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" style="font-weight: 400; cursor: pointer;"> <span id="category-selected-text" style="color: #64748b;">Choose category...</span>
                                    <i class="fa-solid fa-chevron-down category-arrow" style="font-size: 0.8rem; color: #64748b; transition: transform 0.3s ease;"></i>
                                </button>

                                <ul class="dropdown-menu w-100 glass-dropdown-menu border-0 shadow-lg mt-2" aria-labelledby="categoryDropdownBtn">
                                    <?php
                                    if (isset($conn)) {
                                        $cat_sql = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
                                        $cat_res = $conn->query($cat_sql);
                                        if ($cat_res) {
                                            while ($cat = $cat_res->fetch_assoc()) {
                                                echo '<li><a class="dropdown-item category-item" href="#" data-value="' . htmlspecialchars($cat['name']) . '">' . htmlspecialchars($cat['name']) . '</a></li>';
                                            }
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label custom-label">Exact Location</label>
                        <input type="text" id="location-input" name="location" class="form-control custom-input" placeholder="Enter exact location" required autocomplete="off">
                    </div>

                    <div class="row gx-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label custom-label">Date</label>
                            <input type="text" id="event-date" name="event_date" class="form-control custom-input" placeholder="Select date" required autocomplete="off" readonly style="cursor: pointer; background-color: rgba(255, 255, 255, 0.05);">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label custom-label">Time</label>
                            <input type="time" name="event_time" class="form-control custom-input" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label custom-label">Price (RON)</label>
                            <input type="number" step="0.01" name="price" class="form-control custom-input" placeholder="0 for free" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label custom-label">Image</label>
                        <div class="custom-file-upload">
                            <input type="file" id="event-image" name="image" class="d-none" accept="image/*" required>
                            <label for="event-image" class="form-control custom-input d-flex align-items-center justify-content-between m-0" style="cursor: pointer; padding-right: 0.5rem;">
                                <span id="file-name" style="color: #64748b;">No file chosen...</span>
                                <span class="btn custom-submit-btn btn-sm py-1 px-3 rounded-pill" id="pop-up-screen-buttons" style="font-size: 0.85rem;">Browse</span>
                            </label>
                        </div>

                        <div id="image-preview-container" class="mt-3 text-center d-none">
                            <img id="image-preview" src="" alt="Event Poster Preview" class="img-fluid rounded-3">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label custom-label">Description</label>
                        <textarea name="description" class="form-control custom-input" rows="3" placeholder="Describe the event..." required></textarea>
                    </div>

                    <div
                        class="cf-turnstile mb-4 d-flex justify-content-center"
                        data-sitekey="<?php echo htmlspecialchars($_ENV['TURNSTILE_SITEKEY'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-theme="dark"
                        >
                    </div>

                    <div class="d-grid mb-2">
                        <button type="submit" class="btn custom-submit-btn rounded-pill fw-bold" id="pop-up-screen-buttons">PUBLISH EVENT</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1080;">
    <div id="systemToast" class="toast custom-glass-toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center" id="toastMessage">
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal fade custom-event-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content glass-modal-content border-0 rounded-4">

            <div class="modal-header glass-modal-header border-0 position-relative">
                <h5 class="modal-title w-100 text-center fw-bold text-white" id="loginModalLabel">
                    Welcome Back
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <form id="ajaxLoginForm" method="POST" action="<?php echo BASE_URL; ?>ajax/ajax_login.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-3">
                        <label class="form-label custom-label">Email</label>
                        <div class="input-group-custom">
                            <input type="email" name="email" class="form-control custom-input with-icon" placeholder="Enter your email" required autocomplete="email">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label custom-label">Password</label>
                        <div class="input-group-custom">
                            <input type="password" name="password" class="form-control custom-input with-icon" placeholder="Enter your password" required autocomplete="current-password">
                        </div>
                        <div class="text-start mt-2">
                            <a href="#"
                                data-bs-dismiss="modal"
                                data-bs-toggle="modal"
                                data-bs-target="#forgotPasswordModal"
                                class="text-info text-decoration-none small">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <div
                        class="cf-turnstile mb-4 d-flex justify-content-center"
                        data-sitekey="<?php echo htmlspecialchars($_ENV['TURNSTILE_SITEKEY'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-theme="dark"
                        >
                    </div>

                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill fw-bold py-2 mb-3 text-white">
                        LOGIN
                    </button>

                    <div class="text-center">
                        <span class="text-secondary small">Don't have an account?</span>
                        <a href="<?php echo BASE_URL; ?>register.php" class="text-cyan text-decoration-none fw-bold small ms-1">Register now</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade custom-event-modal" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content glass-modal-content border-0 rounded-4">

            <div class="modal-header glass-modal-header border-0 position-relative">
                <h5 class="modal-title w-100 text-center fw-bold text-white" id="forgotPasswordModalLabel">
                    Reset Password
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <p class="text-secondary text-center small mb-4">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                <form id="ajaxForgotForm" method="POST" action="<?php echo BASE_URL; ?>ajax/ajax_forgot.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-4">
                        <label class="form-label custom-label">Email</label>
                        <div class="input-group-custom">
                            <input type="email" name="email" class="form-control custom-input with-icon" placeholder="Enter your email" required autocomplete="email">
                        </div>
                    </div>

                    <div class="cf-turnstile d-flex justify-content-center" data-sitekey="<?php echo htmlspecialchars($_ENV['TURNSTILE_SITEKEY'], ENT_QUOTES, 'UTF-8'); ?>" data-theme="dark" data-appearance="interaction-only"></div>

                    <button type="submit" class="btn btn-outline-info w-100 rounded-pill fw-bold py-2 mb-3 mt-2 text-white">
                        SEND LINK
                    </button>

                    <div class="text-center">
                        <a href="#"
                            class="text-secondary text-decoration-none small"
                            data-bs-dismiss="modal"
                            data-bs-toggle="modal"
                            data-bs-target="#loginModal">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


<div class="modal fade custom-event-modal" id="cookieModal" tabindex="-1" aria-labelledby="cookieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal-content border-0 rounded-4">

            <div class="modal-header glass-modal-header border-0 position-relative">
                <h5 class="modal-title w-100 text-center fw-bold text-white" id="cookieModalLabel">
                    Cookie Preferences
                </h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <p class="text-secondary mb-4 text-center" style="font-size: 0.9rem;">
                    We use cookies to enhance your browsing experience and analyze our traffic. Please choose your preferences below.
                </p>

                <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded-3" style="background-color: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                    <div>
                        <h6 class="text-white fw-bold mb-1" style="font-size: 0.95rem;">Strictly Necessary</h6>
                        <small class="text-secondary" style="font-size: 0.75rem;">Required for the site to function properly.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" checked disabled style="cursor: not-allowed;">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded-3" style="background-color: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                    <div>
                        <h6 class="text-white fw-bold mb-1" style="font-size: 0.95rem;">Performance & Analytics</h6>
                        <small class="text-secondary" style="font-size: 0.75rem;">Helps us understand how you use the site.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input custom-switch" type="checkbox" role="switch" id="cookieAnalytics" checked>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded-3" style="background-color: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                    <div>
                        <h6 class="text-white fw-bold mb-1" style="font-size: 0.95rem;">Targeting & Advertising</h6>
                        <small class="text-secondary" style="font-size: 0.75rem;">Used to deliver relevant ads to you.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input custom-switch" type="checkbox" role="switch" id="cookieAds">
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold border-0" onclick="saveCookiesAndClose()">Save Preferences</button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold border-0" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/air-datepicker@3.3.2/air-datepicker.min.css">
<script src="https://cdn.jsdelivr.net/npm/air-datepicker@3.3.2/air-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
</script>

<script src="<?php echo BASE_URL; ?>assets/js/ui.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/ajax-forms.js"></script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($_ENV['GOOGLE_MAPS_API_KEY'], ENT_QUOTES, 'UTF-8'); ?>&loading=async&libraries=places&callback=initGoogleAutocomplete"></script>
<script src="<?php echo BASE_URL; ?>assets/js/maps-init.js"></script>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>


<script>
    // Resetăm Turnstile automat când se deschide orice fereastră modală (Login, Add Event, Forgot)
    document.addEventListener('show.bs.modal', function () {
        if (typeof turnstile !== 'undefined') {
            turnstile.reset();
        }
    });
</script>

</body>

</html>