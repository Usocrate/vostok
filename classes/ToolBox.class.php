<?php
class ToolBox {
	public function _construct() {
	}
	/**
	 *
	 * @version 02/2022
	 */
	public static function toHtml($input, bool $nl2br = true) {
		if (is_string ( $input )) {
			return $nl2br ? nl2br (htmlentities ( $input, ENT_QUOTES | ENT_HTML5, 'UTF-8' )) : htmlentities ( $input, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}
		return '';
	}
	/**
	 *
	 * @version 08/2018
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
			$nav_items [] = '<li class="page-item"><a class="page-link" href="' . $url_base . $page_index_param_name . '=1">&#171;</a></li>';
		} else {
			$nav_items [] = '<li class="page-item"><a class="page-link">&#171;&#171;</a></li>';
		}
		/*
		 * Page précédente
		 */
		if ($page_index > 1) {
			$nav_items [] = '<li class="page-item"><a class="page-link" href="' . $url_base . $page_index_param_name . '=' . ($page_index - 1) . '">&#171;</a></li>';
		} else {
			$nav_items [] = '<li class="page-item"><a class="page-link">&#171;</a></li>';
		}
		/*
		 * Autres pages
		 */
		for($i = ($page_index - $empan); $i <= ($page_index + $empan); $i ++) {
			if ($i < 1 || $i > $pages_nb)
				continue;
			if ($i == $page_index) {
				$nav_items [] = '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
			} else {
				$nav_items [] = '<li class="page-item"><a class="page-link" href="' . $url_base . $page_index_param_name . '=' . $i . '">' . $i . '</a></li>';
			}
		}
		/*
		 * Page suivante
		 */
		if ($page_index < $pages_nb) {
			$nav_items [] = '<li class="page-item"><a class="page-link" href="' . $url_base . $page_index_param_name . '=' . ($page_index + 1) . '">&#187;</a></li>';
		} else {
			$nav_items [] = '<li class="page-item"><a class="page-link">&#187;</a></li>';
		}
		/*
		 * Dernière page
		 */
		if ($page_index < ($pages_nb - 1)) {
			$nav_items [] = '<li class="page-item"><a class="page-link" href="' . $url_base . $page_index_param_name . '=' . $pages_nb . '">&#187;&#187;</a></li>';
		} else {
			$nav_items [] = '<li class="page-item"><a class="page-link">&#187;&#187;</a></li>';
		}
		return '<ul class="pagination">' . implode ( '', $nav_items ) . '</ul>';
	}
	/**
	 * Transforme un tableau en chaîne de paramètres à intégrer dans une url.
	 *
	 * @version 12/2018
	 */
	public static function arrayToUrlParam(Array $in) {
		$count = 0;
		do {
			$out = $count > 0 ? $out . '&' . key ( $in ) . '=' . urlencode ( current ( $in ) ) : key ( $in ) . '=' . urlencode ( current ( $in ) );
			$count ++;
		} while ( next ( $in ) );
		return $out;
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
	/**
	 * @version 02/2022
	 */
	public static function formatUserPost(&$data) {
		if (is_array ( $data )) {
			foreach ( $data as &$item ) {
				self::formatUserPost ( $item );
			}
		} else {
			$data = urldecode($data);
			$data = html_entity_decode ( $data, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			$data = str_replace(array("<br />", "<br>", "</p>", "</div>", "</li>"), "\n", $data);
			$data = strip_tags ( $data );
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
	 * Convertir une date au format local (fr) au format date mySQL
	 *
	 * @since 01/2019
	 */
	public static function convertDateFromLocalToMySqlFormat($input) {
		list ( $day, $month, $year ) = explode ( '/', $input );
		return $year . '-' . $month . '-' . $day;
	}
	/**
	 * Ajoute un répertoire dans la liste des répertoires utilisés dans la recherche de fichiers à inclure.
	 *
	 * @since 12/2010
	 */
	public static function addIncludePath($input) {
		return ini_set ( 'include_path', $input . PATH_SEPARATOR . ini_get ( 'include_path' ) );
	}
	/**
	 *
	 * @since 12/2018
	 */
	public static function getGoogleQueryUrl($query, $type = null) {
		$params = array ();

		$url = 'https://www.google.com/search?';

		switch ($type) {
			case 'images' :
				$params ['tbm'] = 'isch';
				break;
			case 'vidéos' :
				$params ['tbm'] = 'vid';
				break;
			case 'actualités' :
				$params ['tbm'] = 'nws';
				break;
		}

		$params ['q'] = $query;

		return $url . self::arrayToUrlParam ( $params );
	}
}
?>