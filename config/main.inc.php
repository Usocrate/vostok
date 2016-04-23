<?php
iconv_set_encoding ( 'internal_encoding', 'UTF-8' );
iconv_set_encoding ( 'input_encoding', 'UTF-8' );
iconv_set_encoding ( 'output_encoding', 'UTF-8' );

switch ($_SERVER ['HTTP_HOST']) {
	case 'vostok.chosta' :
		require 'hosts/vostok.chosta.php';
		break;
	case 'vostok.usocrate.traktor' :
		require 'hosts/vostok.usocrate.traktor.php';
		break;
	case 'vostok.cgdlptl01153' :
		require 'hosts/vostok.cgdlptl01153.php';
		break;
	default : // sur vist
		require 'hosts/vostok.usocrate.fr.php';
}
// commun
define ( 'APPLI_VERSION', '2.00' );
define ( 'COOKIES_LIFETIME', 60 * 60 * 24 * 7 ); // 7 jours;
define ( 'CRYPT_SALT', 'uf' ); // la clef permettant de crypter le mot de passe;
define ( 'MOSTUSED_PERIOD', 100 ); // la période prise en compte pour le calcul des ressources les plus fréquemment utilisées (en jours);

// Bootstrap
define ( 'BOOTSTRAP_CSS_URI', SKIN_URL.'bootstrap/css/bootstrap.min.css');
define ( 'BOOTSTRAP_CSS_THEME_URI', SKIN_URL.'bootstrap/css/bootstrap-theme.min.css');
define ( 'BOOTSTRAP_JS_URI', SKIN_URL.'bootstrap/js/bootstrap.min.js');

// JQuery
define ( 'JQUERY_URI', 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js' );
define ( 'JQUERY_UI_URI', 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js' );
         
// google maps
if (defined ( 'GOOGLE_MAPS_API_KEY' )) {
	define ( 'GOOGLE_MAPS_API_URL', 'http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . GOOGLE_MAPS_API_KEY );
}
function __autoload($class_name) {
	if (is_file ( CLASS_DIR . '/' . $class_name . '.class.php' )) {
		include_once CLASS_DIR . '/' . $class_name . '.class.php';
	} else {
		if (strcmp ( substr ( $class_name, 0, 5 ), 'Bluga' ) == 0) {
			if (is_file ( BLUGA_DIR . '/' . str_replace ( '_', '/', $class_name ) . '.php' )) {
				include_once BLUGA_DIR . '/' . str_replace ( '_', '/', $class_name ) . '.php';
			}
		}
	}
}
$system = new System ( DB_HOST, DB_NAME, DB_USER, DB_PASSWORD );