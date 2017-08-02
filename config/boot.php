<?php
iconv_set_encoding ( 'internal_encoding', 'UTF-8' );
iconv_set_encoding ( 'input_encoding', 'UTF-8' );
iconv_set_encoding ( 'output_encoding', 'UTF-8' );

// Bootstrap
define ( 'BOOTSTRAP_CSS_URI', $system->getSkinUrl().'bootstrap/css/bootstrap.min.css');
define ( 'BOOTSTRAP_CSS_THEME_URI', $system->getSkinUrl().'bootstrap/css/bootstrap-theme.min.css');
define ( 'BOOTSTRAP_JS_URI', $system->getSkinUrl().'bootstrap/js/bootstrap.min.js');

// JQuery
define ( 'JQUERY_URI', 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js' );
define ( 'JQUERY_UI_URI', 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js' );

// Masonry
define ('MASONRY_URI', 'https://unpkg.com/masonry-layout@4.2.0/dist/masonry.pkgd.min.js');

// Bluga.net WebThumb snapping the web
define('BLUGA_DIR', '/home/ubuntu/workspace/outsourcing/Bluga.net-Webthumb-API-for-PHP');