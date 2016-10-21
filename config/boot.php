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

// Yahoo! User Interface
define ('YUI_SEEDFILE_URI', '/outsourcing/yui_3.17.2/yui/yui-min.js');

// Bluga.net WebThumb snapping the web
define('BLUGA_DIR', 'C:/Users/Florent/www/usocrate/outsourcing/Bluga.net-Webthumb-API-for-PHP');