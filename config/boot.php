<?php
iconv_set_encoding ( 'internal_encoding', 'UTF-8' );
iconv_set_encoding ( 'input_encoding', 'UTF-8' );
iconv_set_encoding ( 'output_encoding', 'UTF-8' );

// Bootstrap
define ( 'BOOTSTRAP_CSS_URI', $system->getSkinUrl().'bootstrap/css/bootstrap.min.css');
define ( 'BOOTSTRAP_CSS_THEME_URI', $system->getSkinUrl().'bootstrap/css/bootstrap-theme.min.css');
define ( 'BOOTSTRAP_JS_URI', $system->getSkinUrl().'bootstrap/js/bootstrap.min.js');

// JQuery
define ( 'JQUERY_URI', 'https://code.jquery.com/jquery-1.12.4.min.js' );
define ( 'JQUERY_UI_URI', 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js' );

// Masonry
define ('MASONRY_URI', 'https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js');
define ('IMAGESLOADED_URI', 'https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js');