a<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
include BASE_PATH . 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/our-story.css">

<div class="story-page-wrapper">
    <div class="story-overlay"></div>

    <div class="container position-relative z-3 pb-5 story-content" style="padding-top: 6rem; min-height: 80vh;">
        
        <div class="text-center pb-4">
            <span class="custom-badge-category mb-3 d-inline-block text-uppercase text-danger border-danger">Our Story</span>
            <h1 class="display-3 fw-bold text-white mb-3" id="story-h1">It started with a Friday night.</h1>
            <p class="text-secondary fs-5 mx-auto" id="story-p" style="max-width: 700px;">I built The Spot because I was tired of missing out on the best events just because I didn't check the right app at the right time.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="dark-card p-4 p-md-5 mb-4 shadow-lg story-block">
                    <div class="d-flex align-items-center mb-3">
                        <div class="story-icon text-warning me-3">
                            <i class="fa-solid fa-triangle-exclamation fa-xl"></i>
                        </div>
                        <h3 class="text-white fw-bold mb-0">The Chaos</h3>
                    </div>
                    <p class="text-secondary fs-6 lh-lg mb-0">
                        Think about how you used to find events. Scrolling endlessly on Facebook, checking ten different Instagram pages, or relying on word-of-mouth. The best underground gigs were hidden in private groups and the big festivals were scattered across the web. Information was everywhere, which meant it was practically nowhere. I knew there had to be a better way.
                    </p>
                </div>

                <div class="dark-card p-4 p-md-5 mb-4 shadow-lg story-block">
                    <div class="d-flex align-items-center mb-3">
                        <div class="story-icon text-info me-3">
                            <i class="fa-solid fa-lightbulb fa-xl"></i>
                        </div>
                        <h3 class="text-white fw-bold mb-0">The Blueprint</h3>
                    </div>
                    <p class="text-secondary fs-6 lh-lg mb-0">
                        I didn't want another social network. I wanted a tool. One place. One spot. A platform built purely for connection-connecting explorers who want to experience the pulse of the city, with the organizers who work hard to create those experiences. I wanted a clean, fast and secure hub where finding your next night out takes seconds, not hours.
                    </p>
                </div>

                <div class="dark-card p-4 p-md-5 mb-3 shadow-lg story-block">
                    <div class="d-flex align-items-center mb-3">
                        <div class="story-icon text-success me-3">
                            <i class="fa-solid fa-rocket fa-xl"></i>
                        </div>
                        <h3 class="text-white fw-bold mb-0">The Spot Today</h3>
                    </div>
                    <p class="text-secondary fs-6 lh-lg mb-0">
                        Today, The Spot is exactly what I envisioned: the ultimate gateway to nightlife, culture and entertainment. No noise. No algorithms hiding the good stuff. Just you, your friends, and a curated list of the best events happening around you. Whether you're here to dance, to listen, or to host, you're in the right place.
                    </p>
                </div>

            </div>
        </div>

        <div class="text-center mt-3">
            <h4 class="text-white fw-bold mb-4">Ready to make memories?</h4>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-info rounded-pill px-5 py-3 fw-bold" style="border-width: 2px;">
                    EXPLORE EVENTS
                </a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-danger rounded-pill px-5 py-3 fw-bold">
                        JOIN THE COMMUNITY
                    </a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>