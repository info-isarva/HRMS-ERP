<?php

/**
 * Fallback front controller when the vhost document root is `posh/` instead of `posh/public/`.
 * Preferred vhost path: .../public_html/posh/public
 */
require __DIR__.'/public/index.php';
