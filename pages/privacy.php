<?php 
// Folosim BASE_PATH pentru a accesa corect header-ul din folderul superior
require_once __DIR__ . '/../includes/db.php'; // Chemăm db.php ca să avem acces la constante dacă e nevoie
include BASE_PATH . 'includes/header.php'; 
?>

<div class="container" style="padding-top: 6rem; padding-bottom: 5rem; min-height: 75vh;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="mb-5 text-center text-md-start">
                <h1 class="fw-bold text-white mb-2" style="letter-spacing: -1px;">Privacy Policy</h1>
                <p class="text-secondary">Last updated: <?php echo date('F d, Y'); ?></p>
            </div>

            <div class="dark-card p-4 p-md-5" style="background-color: #0f172a; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px;">
                <div class="text-secondary" style="line-height: 1.8; font-size: 0.95rem;">
                    
                    <p>At <strong>The Spot</strong>, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.</p>

                    <h5 class="text-white mt-5 mb-3 fw-bold">1. Information We Collect</h5>
                    <p>We may collect information about you in a variety of ways. The information we may collect includes:</p>
                    <ul class="mb-4">
                        <li><strong>Personal Data:</strong> Personally identifiable information, such as your name, shipping address, email address, and telephone number, that you voluntarily give to us when you register with the site or when you choose to participate in various activities related to the site.</li>
                        <li><strong>Derivative Data:</strong> Information our servers automatically collect when you access the site, such as your IP address, your browser type, your operating system, your access times, and the pages you have viewed directly before and after accessing the site.</li>
                    </ul>

                    <h5 class="text-white mt-5 mb-3 fw-bold">2. Use of Your Information</h5>
                    <p>Having accurate information about you permits us to provide you with a smooth, efficient, and customized experience. Specifically, we may use information collected about you via the site to:</p>
                    <ul class="mb-4">
                        <li>Create and manage your account.</li>
                        <li>Process your event registrations and ticket purchases.</li>
                        <li>Email you regarding your account or order.</li>
                        <li>Fulfill and manage purchases, orders, payments, and other transactions related to the site.</li>
                    </ul>

                    <h5 class="text-white mt-5 mb-3 fw-bold">3. Disclosure of Your Information</h5>
                    <p>We may share information we have collected about you in certain situations. Your information may be disclosed as follows:</p>
                    <p class="mb-4"><strong>By Law or to Protect Rights:</strong> If we believe the release of information about you is necessary to respond to legal process, to investigate or remedy potential violations of our policies, or to protect the rights, property, and safety of others.</p>

                    <h5 class="text-white mt-5 mb-3 fw-bold">4. Security of Your Information</h5>
                    <p class="mb-4">We use administrative, technical, and physical security measures to help protect your personal information. While we have taken reasonable steps to secure the personal information you provide to us, please be aware that despite our efforts, no security measures are perfect or impenetrable.</p>

                    <h5 class="text-white mt-5 mb-3 fw-bold">5. Contact Us</h5>
                    <p>If you have questions or comments about this Privacy Policy, please contact us at:</p>
                    <p class="text-cyan fw-bold" style="color: #00ccff;">contact@thespot.com</p>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>