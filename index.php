<?php
// Root redirect for Hostinger deployment (web root is public_html/, app is in public/)
header('Location: /public/index.php', true, 301);
exit;
