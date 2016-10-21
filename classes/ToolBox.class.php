<?php
class ToolBox {
	public function _construct() {
	}
	/**
	 * Transforme les charactères spéciaux en entités HTML
	 *
	 * @return string
	 */
	public static function xhtmlEntities($input) {
		return htmlentities ( $input, ENT_QUOTES, 'UTF-8' );
	}
	/**
	 *
	 * @version 01/08/2014
	 */
	public static function toHtml($input) {
		return htmlentities ( $input, ENT_HTML5 );
	}
	public static function getDBAccess() {
		global $system;
		define ( 'DB_HOST', $system->getDbHost () );
		define ( 'DB_NAME', $system->getDbName () );
		define ( 'DB_USER', $system->getDbUser () );
		define ( 'DB_PASSWORD', $system->getDbPassword () );
		print_r($system);
		$connexion = mysql_connect ( DB_HOST, DB_USER, DB_PASSWORD );
		mysql_select_db ( DB_NAME, $connexion );
		mysql_query ( 'SET NAMES "utf8"' );
	}
	/**
	 *
	 * @version 01/08/2014
	 */
	public static function getHtmlPagesNav($page_index = 1, $pages_nb, $param, $page_index_param_name = 'page_index') {
		// contruction de l'url de base des liens
		$url_param = is_array ( $param ) ? self::arrayToUrlParam ( $param ) : $param;
		if (iconv_strlen ( $url_param ) > 0)
			$url_param .= '&';
		$url_base = $_SERVER ['PHP_SELF'] . '?' . $url_param;
		
		$empan = 3;
		$nav_items = array ();
		
		/*
		 * Première page
		 */
		if ($page_index > 2) {
			$nav_items [] = '<li><a class="prev" href="' . $url_base . $page_index_param_name . '=1">&#171;</a></li>';
		} else {
			$nav_items [] = '<li><a class="prev">&#171;&#171;</a></li>';
		}
		/*
		 * Page précédente
		 */
		if ($page_index > 1) {
			$nav_items [] = '<li><a class="prev" href="' . $url_base . $page_index_param_name . '=' . ($page_index - 1) . '">&#171;</a></li>';
		} else {
			$nav_items [] = '<li><a class="prev">&#171;</a></li>';
		}
		/*
		 * Autres pages
		 */
		for($i = ($page_index - $empan); $i <= ($page_index + $empan); $i ++) {
			if ($i < 1 || $i > $pages_nb)
				continue;
			if ($i == $page_index) {
				$nav_items [] = '<li><a class="active" href="#">' . $i . '</a></li>';
			} else {
				$nav_items [] = '<li><a href="' . $url_base . $page_index_param_name . '=' . $i . '">' . $i . '</a></li>';
			}
		}
		/*
		 * Page suivante
		 */
		if ($page_index < $pages_nb) {
			$nav_items [] = '<li><a class="next" href="' . $url_base . $page_index_param_name . '=' . ($page_index + 1) . '">&#187;</a></li>';
		} else {
			$nav_items [] = '<li><a class="next">&#187;</a></li>';
		}
		/*
		 * Dernière page
		 */
		if ($page_index < ($pages_nb - 1)) {
			$nav_items [] = '<li><a class="next" href="' . $url_base . $page_index_param_name . '=' . $pages_nb . '">&#187;&#187;</a></li>';
		} else {
			$nav_items [] = '<li><a class="next">&#187;&#187;</a></li>';
		}
		return '<ul class="pagination">' . implode ( '', $nav_items ) . '</ul>';
	}
	/**
	 * Transforme un tableau en chaîne de paramètres à intégrer dans une url.
	 *
	 * @param
	 *        	$array
	 * @return string
	 * @version 2009-04-17
	 */
	public static function arrayToUrlParam($array) {
		if (is_array ( $array )) {
			$params = array ();
			foreach ( $array as $clé => $valeur ) {
				if (isset ( $valeur ))
					$params [] = $clé . '=' . url_encode ( $valeur );
			}
			return implode ( '&', $params );
		}
		return false;
	}
	/**
	 * Remplace tous les caractères accentués d'une chaîne.
	 *
	 * @param string $input        	
	 * @return string
	 */
	public static function sans_accent($input) {
		$input = utf8_decode ( $input );
		$accent = utf8_decode ( "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ" );
		$noaccent = "aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby";
		return strtr ( trim ( $input ), $accent, $noaccent );
	}
	/**
	 * Elimine les caractères indésirables pour qu'une chaîne de caractère devienne utilisable comme nom de fichier.
	 *
	 * @return string
	 */
	public static function formatForFileName($input) {
		$input = self::sans_accent ( $input );
		$input = strtolower ( $input );
		$input = str_replace ( ' ', '-', $input );
		return $input;
	}
	public static function formatUserPost(&$data) {
		if (is_array ( $data )) {
			foreach ( $data as &$item ) {
				self::formatUserPost ( $item );
			}
		} else {
			$data = strip_tags ( $data );
			$data = html_entity_decode ( $data, ENT_QUOTES, 'UTF-8' );
			$data = trim ( $data );
		}
	}
	/**
	 * Obtient le timestamp Unix correspondant à un datetime (format Mysql)
	 *
	 * @param string $input        	
	 */
	public static function mktimeFromMySqlDatetime($input) {
		list ( $date, $time ) = explode ( ' ', $input );
		list ( $year, $month, $day ) = explode ( '-', $date );
		list ( $hour, $min, $sec ) = explode ( ':', $time );
		return mktime ( intval ( $hour ), intval ( $min ), intval ( $sec ), intval ( $month ), intval ( $day ), intval ( $year ) );
	}
	/**
	 * Ajoute un répertoire dans la liste des répertoires utilisés dans la recherche de fichiers à inclure.
	 *
	 * @since 27/12/2010
	 */
	public static function addIncludePath($input) {
		return ini_set ( 'include_path', $input . PATH_SEPARATOR . ini_get ( 'include_path' ) );
	}
}
?>