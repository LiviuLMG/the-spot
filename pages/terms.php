<?php 
// Folosim BASE_PATH pentru a accesa corect header-ul și baza de date
require_once __DIR__ . '/../includes/db.php'; 
include BASE_PATH . 'includes/header.php'; 
?>

<div class="container" style="padding-top: 6rem; padding-bottom: 5rem; min-height: 75vh;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="mb-5 text-center text-md-start">
                <h1 class="fw-bold text-white mb-2" style="letter-spacing: -1px;">Terms of Service</h1>
                <p class="text-secondary">Effective Date: <?php echo date('F d, Y'); ?></p>
            </div>

            <div class="dark-card p-4 p-md-5" style="background-color: #0f172a; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px;">
                <div class="text-secondary" style="line-height: 1.8; font-size: 0.95rem;">
                    
                    <p>Welcome to <strong>The Spot</strong>. These Terms of Service outline the rules and regulations for the use of our platform.</p>

                    <h5 class="text-white mt-5 mb-3 fw-bold">1. Acceptance of Terms</h5>
                    <p class="mb-4">By accessing this website, we assume you accept these terms and conditions. Do not continue to use The Spot if you do not agree to take all of the terms and conditions stated on this page.</p>

                    <h5 class="text-white mt-5 mb-3 fw-bold">2. User Accounts</h5>
                    <p>To use certain features of the platform, you must register for an account. You agree to:</p>
                    <ul class="mb-4">
                        <li>Provide accurate, current, and complete information during the registration process.</li>
                        <li>Maintain and promptly update your account information.</li>
                        <li>Maintain the security of your password and accept all risks of unauthorized access to your account.</li>
                        <li>Notify us immediately if you discover or suspect any security breaches related to the platform.</li>
                    </ul>

                    <h5 class="text-white mt-5 mb-3 fw-bold">3. Event Creation and Ticketing</h5>
                    <p class="mb-4">If you create an event on The Spot, you are solely responsible for ensuring the accuracy of the event details, including the time, location, and entry price. You must possess all necessary rights and permits to host the event. The Spot is not responsible for event cancellations, changes, or any disputes between organizers and attendees.</p>

                    <h5 class="text-white mt-5 mb-3 fw-bold">4. Prohibited Conduct</h5>
                    <p>You agree not to engage in any of the following activities:</p>
                    <ul class="mb-4">
                        <li>Using the platform for any illegal purpose or in violation of any local, state, national, or international law.</li>
                        <li>Harassing, threatening, demeaning, or promoting violence against others.</li>
                        <li>Uploading or transmitting viruses, malware, or any other type of malicious code.</li>
                    </ul>

                    <h5 class="text-white mt-5 mb-3 fw-bold">5. Termination</h5>
                    <p class="mb-4">We may terminate or suspend your account and bar access to the service immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever and without limitation, including but not limited to a breach of the Terms.</p>

                    <h5 class="text-white mt-5 mb-3 fw-bold">6. Contact Information</h5>
                    <p>If you have any questions regarding these terms, contact us at:</p>
                    <p class="text-cyan fw-bold" style="color: #00ccff;">legal@thespot.com</p>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>