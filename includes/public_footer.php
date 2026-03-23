</main>
<footer>
    <div class="container">
        <div class="footer-section">
            <i class="fas fa-heart-pulse" style="font-size:2rem;color:#7c3aed;margin-bottom:.75rem;display:block"></i>
            <h3><?php echo APP_NAME; ?></h3>
            <p>Person-centred care management for social care providers. Maintain complete profiles, track care needs, and manage key worker relationships — all in one secure place.</p>
        </div>
        <div class="footer-section">
            <h3>Service</h3>
            <ul>
                <li><a href="<?php echo url('landing.php'); ?>">About</a></li>
                <li><a href="<?php echo url('contact.php'); ?>">Contact</a></li>
                <li><a href="<?php echo url('login.php'); ?>">Sign In</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Integration</h3>
            <ul>
                <li><a href="<?php echo url('api/people-data.php'); ?>">People API</a></li>
                <li><a href="<?php echo url('api/keyworkers.php'); ?>">Keyworkers API</a></li>
                <li><a href="<?php echo url('contact.php'); ?>">Support</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
