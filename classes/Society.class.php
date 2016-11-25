<?php
/**
 * @package usocrate.exomemory.vostok
 * @author Florent Chanavat
 */
class Society {
	public $id;
	protected $countryNameCode;
	protected $administrativeAreaName;
	protected $subAdministrativeAreaName;
	
	/**
	 * les coordonnées géographiques telles que récupérées par l'API Google
	 */
	protected $longitude;
	protected $latitude;
	protected $altitude;
	protected $parent;
	public function __construct($id = NULL) {
		$this->id = $id;
	}
	/**
	 * Tente d'indentifier la société par son nom.
	 * 
	 * @return boolean
	 * @version 14/01/2006
	 */
	public function identifyFromName() {
		$sql = 'SELECT society_id FROM society WHERE society_name="' . mysql_real_escape_string ( $this->name ) . '"';
		$rowset = mysql_query ( $sql );
		$row = mysql_fetch_assoc ( $rowset );
		if ($row) {
			$this->id = $row ['society_id'];
			return true;
		}
		return false;
	}
	/**
	 * Obtient la valeur d'un attribut.
	 */
	public function getAttribute($name) {
		if (isset ( $this->$name ))
			return $this->$name;
	}
	/**
	 * Fixe la valeur d'un attribut.
	 * 
	 * @since 28/01/2006
	 * @version 04/03/2006
	 */
	public function setAttribute($name, $value) {
		$value = trim ( $value );
		$value = html_entity_decode ( $value, ENT_QUOTES, 'UTF-8' );
		return $this->{$name} = $value;
	}
	/**
	 * Obtient la longitude
	 *
	 * @sicne 23/06/2007
	 */
	public function getLongitude() {
		return $this->getAttribute ( 'longitude' );
	}
	/**
	 * Obtient la latitude
	 *
	 * @since 23/06/2007
	 */
	public function getLatitude() {
		return $this->getAttribute ( 'latitude' );
	}
	/**
	 * Fixe l'identifiant de la société.
	 */
	public function setId($input) {
		return $this->setAttribute ( 'id', $input );
	}
	/**
	 * Obtient l'identifiant de la société.
	 */
	public function getId() {
		return $this->getAttribute ( 'id' );
	}
	/**
	 * Indique si l'identifiant de la société est connu.
	 * 
	 * @since 28/12/2010
	 * @return bool
	 */
	public function hasId() {
		return isset ( $this->id );
	}
	/**
	 * Fixe le nom de la société.
	 */
	public function setName($input) {
		if (! empty ( $input )) {
			$this->name = $input;
		}
	}
	/**
	 * Obtient le nom de la société.
	 */
	public function getName() {
		if (! isset ( $this->name ) && $this->hasId ()) {
			$dataset = $this->getDataFromBase ( array (
					'society_name' 
			) );
			$this->name = $dataset ['society_name'];
		}
		return isset ( $this->name ) ? $this->name : NULL;
	}
	/**
	 * Indique si le nom de la société est connu.
	 *
	 * @return bool
	 */
	public function hasName() {
		return $this->getName () !== null;
	}
	/**
	 *
	 * @since 27/10/2012
	 */
	private static function getKnownNames($substring = NULL) {
	    global $system;
		$sql = 'SELECT society_name FROM society WHERE society_name IS NOT NULL';
		if (isset ( $substring )) {
			$sql .= ' AND society_name LIKE :pattern';
		}
		$statement = $system->getPdo()->prepare($sql);
		if (isset ( $substring )) {
		    $statement->bindValue(':pattern', '%'.$substring.'%', PDO::PARAM_STR);
		}
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_COLUMN);
	}
	/**
	 *
	 * @since 27/10/2012
	 */
	public static function knownNamesToJson($substring = NULL) {
		$output = '{"names":' . json_encode ( self::getKnownNames ( $substring ) ) . '}';
		return $output;
	}
	/**
	 * Obtient de la base de données les valeurs des champs demandés.
	 *
	 * @since 19/01/2009
	 * @version 23/11/2016
	 * @param $fields
	 * @return array
	 */
	private function getDataFromBase($fields = NULL) {
		global $system;
		try {
			if (is_null ( $this->id )) {
				throw new Exception ( __METHOD__ . ' : l\'instance doit être identifiée.' );
			}
			if (is_null ( $fields )) {
				$fields = array (
						'*' 
				);
			}
			$sql = 'SELECT ' . implode ( ',', $fields ) . ' FROM society WHERE society_id=:id';
			$statement = $system->getPdo()->prepare($sql);
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			$statement->execute();
			return $statement->fetch(PDO::FETCH_ASSOC);
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	/**
	 * Obtient le nom affichable de la société.
	 * 
	 * @since 15/01/2006
	 * @version 26/12/2006
	 */
	public function getNameForHtmlDisplay() {
		return $this->hasName () ? ToolBox::toHtml ( $this->name ) : 'XXX';
	}
	/**
	 * Obtient la liste des sociétés portant le même nom
	 *
	 * @since 13/05/2007
	 * @return Array
	 * @todo à écrire
	 */
	public function getHomonyms() {
	}
	/**
	 * Obtient la date d'enregistrement de la société.
	 * 
	 * @version 01/01/2006
	 */
	public function getCreationDate() {
		return $this->getAttribute ( 'creation_date' );
	}
	/**
	 * Obtient le timestamp de l'enregistrement de la société.
	 * 
	 * @since 01/01/2006
	 */
	public function getCreationTimestamp() {
		if ($this->getCreationDate ()) {
			list ( $year, $month, $day ) = explode ( '-', $this->getCreationDate () );
			return mktime ( 0, 0, 0, $month, $day, $year );
		} else {
			return NULL;
		}
	}
	/**
	 * Obtient la date d'enregistrement de la société au format français.
	 * 
	 * @since 01/01/2006
	 */
	public function getCreationDateFr() {
		return date ( 'd/m/Y', $this->getCreationTimestamp () );
	}
	/**
	 * Fixe la description de la société.
	 */
	public function setDescription($input) {
		if (! empty ( $input ))
			$this->description = $input;
	}
	/**
	 * Obtient la description de la société.
	 */
	public function getDescription() {
		return $this->getAttribute ( 'description' );
	}
	/**
	 * Fixe le téléphone de la société.
	 */
	public function setPhone($input) {
		if (! empty ( $input ))
			$this->phone = $input;
	}
	public function getPhone() {
		return $this->getAttribute ( 'phone' );
	}
	/**
	 * Obtient l'adresse de facturation.
	 *
	 * @return string
	 */
	public function getStreet() {
		return $this->getAttribute ( 'street' );
	}
	/**
	 * Obtient la premiére partie de l'adresse physique.
	 * 
	 * @todo Supprimer caractére indésirable notamment double-espace; retour-chariot courants lors de copier-coller.
	 */
	public function setStreet($input) {
		return $this->setAttribute ( 'street', $input );
	}
	public function getPostalCode() {
		return $this->getAttribute ( 'postalcode' );
	}
	public function setPostalCode($input) {
		return $this->setAttribute ( 'postalcode', $input );
	}
	/**
	 * Obtient le nom de la ville où est située la société
	 *
	 * @return string
	 */
	public function getCity() {
		return $this->getAttribute ( 'city' );
	}
	private static function getKnownCities($substring = NULL) {
	    global $system;
		$sql = 'SELECT society_city AS value, COUNT(*) AS count FROM society WHERE society_city IS NOT NULL';
		if (isset ( $substring )) {
			$sql .= ' AND society_city LIKE :pattern';
		}
		$sql .= ' GROUP BY society_city ORDER BY society_city ASC';
		$statement = $system->getPdo()->prepare($sql);
		if (isset ( $substring )) {
		    $statement->bindValue(':pattern', $substring.'%', PDO::PARAM_STR);
		}
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
	public static function knownCitiesToJson($substring = NULL) {
		$output = '{"cities":' . json_encode ( self::getKnownCities ( $substring ) ) . '}';
		return $output;
	}	
	/**
	 * Obtient la chaîne complète de l'adresse de la société
	 *
	 * @since 23/06/2007
	 * @return string
	 */
	public function getAddress() {
		$elements = array ();
		if ($this->getStreet ()) {
			$elements [] = $this->getStreet ();
		}
		if ($this->getPostalCode ()) {
			$elements [] = $this->getPostalCode ();
		}
		if ($this->getCity ()) {
			$elements [] = $this->getCity ();
		}
		if (count ( $elements ) > 0) {
			return implode ( ' ', $elements );
		}
	}
	/**
	 * Fixe la ville.
	 */
	public function setCity($input) {
		return $this->setAttribute ( 'city', $input );
	}
	/**
	 * Fixe les coordonnées géographiques de la société
	 *
	 * @since 23/06/2007
	 */
	public function setCoordinates($longitude, $latitude, $altitude) {
		$this->setAttribute ( 'longitude', $longitude );
		$this->setAttribute ( 'latitude', $latitude );
		$this->setAttribute ( 'altitude', $altitude );
	}
	/**
	 * Obtient les coordonnées géographiques de la société
	 *
	 * @since 23/06/2007
	 * @return string
	 */
	public function getCoordinates() {
		$elements = array ();
		if (isset ( $this->longitude ))
			$elements [] = $this->longitude;
		if (isset ( $this->latitude ))
			$elements [] = $this->latitude;
		if (isset ( $this->altitude ))
			$elements [] = $this->altitude;
		if (count ( $elements ) > 0)
			return implode ( ',', $elements );
	}
	public function getGoogleGeocodeAsJson($input = NULL) {
		global $system;
		$param['address'] = isset ( $input ) ? $input : $this->getAddress ();
		$param['key'] = $system->getGoogleMapsApiKey();
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($param['address']).'&key='.urlencode($param['key']);
		$json = file_get_contents ( $url );
		return $json;
	}
	/**
	 * Obtient les informations de localisation auprès de Google map et complète celles-ci si nécessaire
	 *
	 * @since 23/06/2007
	 * @version 18/11/2016
	 */
	public function getAddressFromGoogle($input = NULL) {
		$json = $this->getGoogleGeocodeAsJson($input);
		$data = json_decode($json);
		$street = array();
		foreach ($data->{'results'}[0]->{'address_components'} as $c) {
			if (in_array('street_number', $c->types)) {
				$street['number'] = $c->long_name;
			}
			if (in_array('route', $c->types)) {
				$street['route'] = $c->long_name;
			}
			if (in_array('locality', $c->types)) {
				$this->city = $c->long_name;
			}
			if (in_array('postal_code', $c->types)) {
				$this->postalcode = $c->long_name;
			}
			if (in_array('administrative_area_level_1', $c->types)) {
				$this->administrativeAreaName = $c->long_name;
			}
			if (in_array('administrative_area_level_2', $c->types)) {
				$this->subAdministrativeAreaName = $c->long_name;
			}
			if (in_array('country', $c->types)) {
				$this->countryNameCode = $c->short_name;
			}			
		}
		$this->street = $street['number'].' '.$street['route'];
		$this->latitude = $data->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
		$this->longitude = $data->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
	}
	public function setType($type) {
		if (! empty ( $type ))
			$this->type = $type;
	}
	/**
	 * Fixe l'Url.
	 */
	public function setUrl($input) {
		$this->url = $this->setAttribute ( 'url', $input );
	}
	/**
	 * Obtient l'Url.
	 */
	public function getUrl() {
		if (! isset ( $this->url ) && $this->hasId ()) {
			$dataset = $this->getDataFromBase ( array (
					'society_url' 
			) );
			$this->url = $dataset ['society_url'];
		}
		return isset ( $this->url ) ? $this->url : null;
	}
	/**
	 * Indique si une url est associée à la société (celle de son site web).
	 *
	 * @return bool
	 */
	public function hasUrl() {
		return $this->getUrl () !== null;
	}
	/**
	 * Obtient un lien HTML vers le site web de la société.
	 * 
	 * @since 24/11/2006
	 * @version 23/11/2016
	 */
	public function getWebHtmlLink() {
		return $this->getUrl () ? '<a href="' . $this->getUrl () . '" title="' . $this->getUrl () . '"><span class="glyphicon glyphicon-link"></span></a>' : NULL;
	}
	/**
	 * Indique si la miniature du site web de la société a déjà été enregistré.
	 *
	 * @return bool
	 * @since 27/12/2010
	 */
	public function hasThumbnail() {
		return is_file ( self::getThumbnailsDirectoryPath () . '/' . $this->getThumbnailFileName () );
	}
	/**
	 * Obtient le fichier image représentant l'interface web de la ressource depuis le site Bluga.net.
	 * 
	 * @return bool
	 * @since 27/12/2010
	 */
	public function getThumbnailFromBluga() {
		try {
			if (! defined ( 'WEBTHUMB_KEY' ) || ! defined ( 'WEBTHUMB_USER_ID' )) {
				throw new Exception ( 'Absence d\'identifiants Bluga' );
			}
			
			ToolBox::addIncludePath ( BLUGA_DIR );
			include_once 'Autoload.php';
			
			$webthumb = new Bluga_Webthumb ();
			$webthumb->setApiKey ( WEBTHUMB_KEY );
			$job = $webthumb->addUrl ( $this->getUrl (), 'medium2', 1024, 768 );
			$webthumb->submitRequests ();
			
			while ( ! $webthumb->readyToDownload () ) {
				sleep ( 2 );
				$webthumb->checkJobStatus ();
			}
			$webthumb->fetchToFile ( $job, $this->getThumbnailFileName (), NULL, self::getThumbnailsDirectoryPath () );
			return $this->hasThumbnail ();
		} catch ( Exception $e ) {
			return false;
		}
	}
	/**
	 * Obtient le nom du fichier où est stockée la miniature du site web.
	 * 
	 * @return string
	 * @since 27/12/2010
	 */
	private function getThumbnailFileName($extension = 'jpg') {
		return isset ( $this->id ) ? $this->id . '.' . $extension : NULL;
	}
	/**
	 * Obtient le chemin vers le répertoire où est stockée la miniature du site web de la société.
	 *
	 * @return string
	 * @since 27/12/2010
	 */
	public static function getThumbnailsDirectoryPath() {
		global $system;
		return $system->getDataDirPath () . DIRECTORY_SEPARATOR . 'thumbnails';
	}
	/**
	 * Obtient un la miniature du site web de la société sous forme de balise Html <img>
	 * 
	 * @param string $size        	
	 * @return string
	 * @since 27/12/2010
	 */
	public function getThumbnailImgTag() {
		if ($this->hasThumbnail () === false) {
			$this->getThumbnailFromBluga ();
		}
		if ($this->hasThumbnail ()) {
			$url = '/thumbnails/' . $this->id . '.jpg';
			return '<img src="' . ToolBox::toHtml ( $url ) . '" />';
		}
	}
	/**
	 * Obtient un lien vers le site web de la société sous forme de miniature.
	 * 
	 * @param string $target        	
	 * @param string $size        	
	 * @return string
	 * @since 27/12/2010
	 */
	public function getHtmlThumbnailLink($target = "_blank") {
		$html = '<div class="thumbnail">';
		$html .= '<a';
		$html .= ' href="' . $this->getUrl () . '"';
		$html .= ' target="' . $target . '"';
		$html .= '>';
		$html .= $this->getThumbnailImgTag ();
		$html .= '</a>';
		$html .= '</div>';
		return $html;
	}
	/**
	 * Obtient les données de la société au format JSON
	 *
	 * @since 24/06/2007
	 * @return string
	 */
	public function getJson() {
		return json_encode ( $this );
	}
	/**
	 * Obtient l'activité de la société.
	 * 
	 * @return string
	 */
	public function getIndustry() {
		return $this->getAttribute ( 'industry' );
	}
	/**
	 * Obtient la liste des activités enregistrées pour la société.
	 * 
	 * @since 16/07/2006
	 * @todo substituer é getIndustry()
	 */
	protected function getIndustriesRowset() {
		if (empty ( $this->id ))
			return NULL;
		$sql = 'SELECT i.*';
		$sql .= ' FROM society_industry AS si INNER JOIN industry AS i ON (i.industry_id=si.industry_id)';
		$sql .= ' WHERE si.society_id=' . $this->id;
		return mysql_query ( $sql );
	}
	/**
	 * Obtient la liste des activités auxquelles est associée la société.
	 * 
	 * @return array
	 * @since 16/07/2006
	 * @todo substituer à getIndustry()
	 */
	public function getIndustries() {
		if (! isset ( $this->industries )) {
			$this->industries = array ();
			$rowset = $this->getIndustriesRowset ();
			while ( $row = mysql_fetch_array ( $rowset ) ) {
				$i = new Industry ();
				$i->feed ( $row );
				array_push ( $this->industries, $i );
			}
			mysql_free_result ( $rowset );
		}
		return $this->industries;
	}
	/**
	 * Obtient le nombre de secteurs d'activité à laquelle est rattachée la société.
	 *
	 * @return int | NULL
	 * @since 26/01/2011
	 */
	public function countIndustries() {
		if (! isset ( $this->industries )) {
			$this->getIndustries ();
		}
		return is_array ( $this->industries ) ? count ( $this->industries ) : NULL;
	}
	/**
	 * Obtient la liste des identifiants des activités de la société.
	 *
	 * @return array | NULL
	 * @since 16/07/2006
	 * @version 26/01/2011
	 */
	public function getIndustriesIds() {
		if ($this->countIndustries () > 0) {
			$output = array ();
			foreach ( $this->getIndustries () as $i ) {
				array_push ( $output, $i->getId () );
			}
			return $output;
		}
	}
	/**
	 * Ajoute une activité é la liste existante.
	 * 
	 * @since 16/07/2006
	 * @todo substituer é setIndustry()
	 */
	public function addIndustry($input) {
		if (! $this->isIndustry ( $input ))
			array_push ( $this->industries, $input );
	}
	/**
	 * Indique si une activité fait partie de la liste des activités de la société.
	 * 
	 * @return boolean
	 * @since 16/07/2006
	 */
	public function isIndustry($input) {
		$this->getIndustries ();
		if (is_a ( $input, 'Industry' )) {
			foreach ( $this->industries as $i ) {
				if (strcmp ( $i->getId (), $input->getId () ) == 0)
					return true;
			}
		}
		return false;
	}
	/**
	 * Vide la liste des activités de la société.
	 * 
	 * @since 16/07/2006
	 */
	public function resetIndustries() {
		$this->industries = array ();
	}
	/**
	 * Enregistre en base de données la liste des activités déclarées de la société.
	 * 
	 * @return boolean
	 * @since 16/07/2006
	 * @version 15/08/2006
	 */
	public function saveIndustries() {
		if (! empty ( $this->id ) && isset ( $this->industries )) {
			if ($this->deleteIndustries ()) {
				$errorless = true;
				foreach ( $this->industries as $i ) {
					$sql = 'INSERT INTO society_industry SET society_id=' . $this->id . ', industry_id=' . $i->getId ();
					$errorless = $errorless && mysql_query ( $sql );
				}
				return $errorless;
			}
		}
		return false;
	}
	/**
	 * Supprime de la base de données la liste des activités déclarées de la société.
	 * 
	 * @since 15/08/2006
	 */
	public function deleteIndustries() {
		if (empty ( $this->id ))
			return false;
		$sql = 'DELETE FROM society_industry WHERE society_id=' . $this->id;
		
		return mysql_query ( $sql );
	}
	/**
	 * Fixe l'activité de la société.
	 */
	public function setIndustry($industry) {
		return $this->setAttribute ( 'industry', $industry );
	}
	/**
	 * Obtient la liste des activités envisageables.
	 * 
	 * @param $valueToSelect La
	 *        	valeur é sélectionner
	 * @version 24/05/2006
	 */
	public function getIndustryOptionsTags($valueToSelect = NULL) {
		$sql = 'SELECT society_industry AS industry, COUNT(*) AS nb';
		$sql .= ' FROM society';
		$sql .= ' GROUP BY industry ASC';
		
		$rowset = mysql_query ( $sql );
		if (is_null ( $valueToSelect ) && isset ( $this->industry )) {
			$valueToSelect = $this->industry;
		}
		$html = '';
		$html .= '<option value="-1">-- indifférent --</option>';
		while ( $row = mysql_fetch_assoc ( $rowset ) ) {
			$html .= '<option value="' . $row ['industry'] . '"';
			if (isset ( $valueToSelect ) && strcasecmp ( $valueToSelect, $row ['industry'] ) == 0) {
				$html .= ' selected="selected"';
			}
			$html .= '>';
			$html .= empty ( $row ['industry'] ) ? '-- é déterminer --' : $row ['industry'];
			$html .= ' (' . $row ['nb'] . ')</option>';
		}
		mysql_free_result ( $rowset );
		return $html;
	}
	/**
	 * Transfére les activités de la société vers une autre société (dans le cadre d'une fusion notamment).
	 * 
	 * @version 16/07/2006
	 */
	public function transferIndustries($society) {
		if (! is_a ( $society, 'Society' ) || ! $society->getId () || empty ( $this->id ))
			return false;
		foreach ( $this->getIndustries () as $i ) {
			$society->addIndustry ( $i );
		}
		$this->resetIndustries ();
		return $society->saveIndustries () && $this->saveIndustries ();
	}
	/**
	 * Obtient les enregistrements des participations associées à la société.
	 * 
	 * @return resource
	 * @since 09/2005
	 */
	public function getMembershipsRowset($criterias = NULL, $sort_key = 'individual_lastName', $sort_order = 'ASC', $offset = 0, $row_count = NULL) {
		$sql = 'SELECT *, DATE_FORMAT(i.individual_creation_date, "%d/%m/%Y")';
		// FROM
		$sql .= ' FROM membership AS ms';
		$sql .= ' INNER JOIN individual AS i';
		$sql .= ' ON ms.individual_id = i.individual_id';
		// WHERE
		if (is_null ( $criterias ))
			$criterias = array ();
		$criterias [] = ' ms.society_id=' . $this->id;
		$sql .= ' WHERE ' . implode ( ' AND ', $criterias );
		// ORDER BY
		$sql .= ' ORDER BY ' . $sort_key . ' ' . $sort_order;
		// LIMIT
		if (isset ( $row_count ))
			$sql .= ' LIMIT ' . $offset . ',' . $row_count;
		
		return mysql_query ( $sql );
	}
	/**
	 * Obtient les participations associées à la société.
	 *
	 * @return array
	 */
	public function getMemberships() {
		if (! isset ( $this->memberships )) {
			$this->memberships = array ();
			$rowset = $this->getMembershipsRowset ();
			while ( $row = mysql_fetch_assoc ( $rowset ) ) {
				$ms = new Membership ();
				$ms->feed ( $row );
				$this->memberships [] = $ms;
			}
		}
		return $this->memberships;
	}
	/**
	 * Transfére les participations de membres au sein de la société vers une autre société (dans le cadre d'une fusion de sociétés notamment).
	 * 
	 * @version 23/10/2005
	 */
	public function transferMemberships($society) {
		if (! is_a ( $society, 'Society' ) || ! $society->getId () || empty ( $this->id ))
			return false;
		$sql = 'UPDATE membership SET society_id=' . $society->getId ();
		$sql .= ' WHERE society_id=' . $this->id;
		
		return mysql_query ( $sql );
	}
	/**
	 * Supprime de la base de données les enregistrements des participations (les personnes associées sont supprimées si elles n'ont pas d'autres participations).
	 * 
	 * @since 15/08/2006
	 */
	public function deleteMemberships() {
		$errorless = true;
		foreach ( $this->getMemberships () as $ms ) {
			$i = $ms->getIndividual ();
			$errorless = $errorless && $ms->delete ();
			if ($i->getMembershipsRowsNb () == 0)
				$i->delete ();
		}
		return $errorless;
	}
	/**
	 * Obtient la liste des membres sous forme de balises Html <option>.
	 * 
	 * @return string
	 * @since 25/06/2006
	 */
	public function getMembersOptionsTags($valueToSelect = NULL) {
		$html = '';
		foreach ( $this->getMemberships () as $ms ) {
			$i = $ms->getIndividual ();
			$html .= '<option value="' . $i->getId () . '"';
			if (isset ( $valueToSelect ) && strcmp ( $valueToSelect, $i->getId () ) == 0) {
				$html .= ' selected="selected"';
			}
			$html .= '>';
			$html .= $i->getWholeName ();
			$html .= '</option>';
		}
		return $html;
	}
	/**
	 * Obtient les enregistrement des éléments en relation avec la société.
	 * 
	 * @return resource
	 * @since 03/2006
	 */
	public function getRelationshipsRowset() {
		$sql = 'SELECT *';
		// FROM
		$sql .= ' FROM relationship AS rs';
		// WHERE
		$criterias = array ();
		$criterias [] = '(rs.item0_id=' . $this->id . ' AND rs.item0_class="' . get_class ( $this ) . '")';
		$criterias [] = '(rs.item1_id=' . $this->id . ' AND rs.item1_class="' . get_class ( $this ) . '")';
		$sql .= ' WHERE ' . implode ( ' OR ', $criterias );
		
		return mysql_query ( $sql );
	}
	/**
	 * Obtient les éléments en relation avec la société.
	 * 
	 * @return array
	 * @since 03/2006
	 */
	public function getRelationships() {
		if (! isset ( $this->relationships )) {
			$this->relationships = array ();
			$rowset = $this->getRelationshipsRowset ();
			while ( $row = mysql_fetch_assoc ( $rowset ) ) {
				$rs = new Relationship ();
				$rs->feed ( $row );
				$this->relationships [] = $rs;
			}
		}
		return $this->relationships;
	}
	/**
	 * Obtient les enregistrements des relations entre la société et une autre société.
	 *
	 * @return resource
	 * @since 25/06/2006
	 * @version 23/06/2007
	 */
	public function getRelationshipsWithSocietyRowset() {
		if (empty ( $this->id )) {
			return NULL;
		}
		$sql = 'SELECT s.*, r.relationship_id, r.item1_role AS relatedsociety_role, r.description, r.init_date, r.end_date';
		$sql .= ' FROM relationship AS r INNER JOIN society AS s ON(r.item1_id=s.society_id)';
		$sql .= ' WHERE item0_class="society" AND item0_id=' . $this->id . ' AND item1_class="society"';
		$sql .= ' UNION';
		$sql .= ' SELECT s.*, r.relationship_id, r.item0_role AS relatedsociety_role, r.description, r.init_date, r.end_date';
		$sql .= ' FROM relationship AS r INNER JOIN society AS s ON(r.item0_id=s.society_id)';
		$sql .= ' WHERE item1_class="society" AND item1_id=' . $this->id . ' AND item0_class="society"';
		$sql .= ' ORDER BY society_name ASC';
		
		return mysql_query ( $sql );
	}
	/**
	 * Obtient la société-mère si elle existe
	 *
	 * @since 23/09/2006
	 * @version 29/07/2007
	 */
	public function getParentSociety() {
		$rowset = $this->getRelationshipsWithSocietyRowset ();
		if ($rowset) {
			while ( $row = mysql_fetch_array ( $rowset ) ) {
				if (isset ( $row ['relatedsociety_role'] ) && strcmp ( $row ['relatedsociety_role'], 'maison-mère' ) == 0) {
					$s = new Society ();
					$s->feed ( $row );
					return $s;
				}
			}
		}
		return NULL;
	}
	/**
	 * Obtient la liste des sociétés en relation.
	 * 
	 * @return array
	 * @since 27/08/2006
	 */
	public function getRelatedSocieties() {
		$output = array ();
		$rowset = $this->getRelationshipsWithSocietyRowset ();
		while ( $row = mysql_fetch_array ( $rowset ) ) {
			$s = new Society ();
			$s->feed ( $row );
			$output [] = $s;
		}
		if ($rowset)
			mysql_free_result ( $rowset );
		return $output;
	}
	/**
	 * Obtient la liste des sociétés ayant en relation au format html (balises <option>).
	 * 
	 * @return string
	 * @since 27/08/2006
	 */
	public function getRelatedSocietiesOptionsTags($valueToSelect = NULL) {
		$html = '';
		foreach ( $this->getRelatedSocieties () as $s ) {
			$html .= '<option value="' . $s->getId () . '"';
			if (isset ( $valueToSelect ) && strcasecmp ( $s->getId (), $valueToSelect ) == 0) {
				$html .= ' selected="selected"';
			}
			$html .= '>';
			$html .= $s->getName ();
			$html .= '</option>';
		}
		return $html;
	}
	/**
	 *
	 * @since 09/04/2006
	 */
	public function transferRelationships($society) {
		if (! is_a ( $society, 'Society' ) || ! $society->getId () || empty ( $this->id ))
			return false;
		$sqlA = 'UPDATE relationship SET item0_id=' . $society->getId ();
		$sqlA .= ' WHERE item0_id=' . $this->id . ' AND item0_class="Society"';
		$sqlB = 'UPDATE relationship SET item1_id=' . $society->getId ();
		$sqlB .= ' WHERE item1_id=' . $this->id . ' AND item1_class="Society"';
		// echo '<p>'.$sqlA.'<br/>'.$sqlB.'</p>';
		return mysql_query ( $sqlA ) && mysql_query ( $sqlB );
	}
	/**
	 * Supprime de la base de données les enregistrements des relations avec des personnes ou d'autres sociétés.
	 * 
	 * @since 15/08/2006
	 */
	public function deleteRelationships() {
		if (empty ( $this->id ))
			return false;
		$sql = 'DELETE FROM relationship';
		$criterias = array ();
		$criterias [] = '(item0_id=' . $this->id . ' AND item0_class="' . get_class ( $this ) . '")';
		$criterias [] = '(item1_id=' . $this->id . ' AND item1_class="' . get_class ( $this ) . '")';
		$sql .= ' WHERE ' . implode ( ' OR ', $criterias );
		
		return mysql_query ( $sql );
	}
	/**
	 * Obtient les enregistrements des pistes associées à la sociétés.
	 * 
	 * @return resource
	 */
	protected function getLeadsRowset($offset = 0, $row_count = NULL) {
		$sql = 'SELECT *, DATE_FORMAT(lead_creation_date, "%d/%m/%Y") as lead_creation_date_fr';
		$sql .= ' FROM lead';
		$sql .= ' WHERE';
		$sql .= ' society_id=' . $this->id;
		if ($row_count)
			$sql .= ' LIMIT ' . $offset . ',' . $row_count;
		$sql .= ' ORDER BY lead_creation_date DESC';
		
		return mysql_query ( $sql );
	}
	/**
	 * Obtient les enregistrements des évènements liés à la société.
	 * 
	 * @param
	 *        	$offset
	 * @param
	 *        	$row_count
	 * @return unknown_type
	 */
	protected function getEventsRowset($offset = 0, $row_count = NULL) {
		$sql = 'SELECT *, DATE_FORMAT(datetime, "%d/%m/%Y") as event_datetime_fr';
		$sql .= ' FROM event';
		$sql .= ' WHERE';
		$sql .= ' society_id=' . $this->id;
		if ($row_count) {
			$sql .= ' LIMIT ' . $offset . ',' . $row_count;
		}
		$sql .= ' ORDER BY datetime DESC';
		
		return mysql_query ( $sql );
	}
	/**
	 * Obtient les pistes associées à la société.
	 * 
	 * @return array
	 */
	public function getLeads() {
		if (! isset ( $this->leads )) {
			$this->leads = array ();
			$rowset = $this->getLeadsRowset ();
			while ( $row = mysql_fetch_assoc ( $rowset ) ) {
				$lead = new Lead ();
				$lead->feed ( $row );
				$this->leads [] = $lead;
			}
		}
		return $this->leads;
	}
	/**
	 * Obtient les évènements associés à la société.
	 *
	 * @return array
	 * @since 2009-01-19
	 */
	public function getEvents() {
		if (! isset ( $this->events )) {
			$this->events = array ();
			$rowset = $this->getEventsRowset ();
			while ( $row = mysql_fetch_assoc ( $rowset ) ) {
				$e = new Event ();
				$e->feed ( $row );
				$this->events [] = $e;
			}
		}
		return $this->events;
	}
	/**
	 * Transfére les pistes liées à la société vers une autre société (dans le cadre d'une fusion de sociétés notamment).
	 * 
	 * @version 24/11/2016
	 */
	public function transferLeads($society) {
		global $system;
		if (! is_a ( $society, 'Society' ) || ! $society->getId () || empty ( $this->id )) return false;
		$statement = $system->getPdo()->prepare('UPDATE lead SET society_id = :target_id WHERE society_id = :id');
		$statement->bindValue(':target_id', $society->getId (), PDO::PARAM_INT);
		$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		return $statement->execute();
	}
	/**
	 * Supprime de la base de données toutes les pistes rattachées à la société.
	 * 
	 * @since 15/08/2006
	 * @version 24/11/2016
	 */
	public function deleteLeads() {
		global $system;
		if (empty ( $this->id )) return false;
		$statement = $system->getPdo()->prepare('DELETE FROM lead WHERE society_id = :id');
		$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		return $statement->execute();
	}
	/**
	 * Fixe les attributs de la société à partir d'un tableau aux clefs normalisées
	 *
	 * @version 09/04/2006
	 */
	public function feed($array = NULL, $prefix = 'society_') {
		if (is_null ( $array )) {
			return $this->initFromDB ();
		} else {
			foreach ( $array as $clé => $valeur ) {
				if (is_null ( $valeur ))
					continue;
				if (isset ( $prefix )) {
					// on ne traite que les clés avec le préfixe spécifié
					if (strcmp ( iconv_substr ( $clé, 0, iconv_strlen ( $prefix ) ), $prefix ) != 0)
						continue;
						// on retire le préfixe
					$clé = iconv_substr ( $clé, iconv_strlen ( $prefix ) );
				}
				// echo $clé.': '.$valeur.'<br />';
				$this->setAttribute ( $clé, $valeur );
			}
			return true;
		}
	}
	/**
	 * Fixe les attributs de la société à partir de son enregistrement en base de données.
	 * 
	 * @version 23/11/2016
	 */
	public function initFromDB() {
		global $system;
		if ($this->getId ()) {
			$statement = $system->getPdo()->prepare('SELECT * FROM society WHERE society_id=:id');
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			$statement->execute();
			return $this->feed ( $statement->fetch(PDO::FETCH_ASSOC) );
		}
		return false;
	}
	/**
	 * Enregistre les données de l'objet en base de données.
	 *
	 * @version 18/11/2016
	 */
	public function toDB() {
		global $system;
		
		$new = ! isset ( $this->id ) || empty ( $this->id );
		
		$settings = array ();
		if (isset ( $this->name )) {
			$settings [] = 'society_name=:name';
		}
		if (isset ( $this->description )) {
			$settings [] = 'society_description=:description';
		}
		if (isset ( $this->phone )) {
			$settings [] = 'society_phone=:phone';
		}
		if (isset ( $this->street )) {
			$settings [] = 'society_street=:street';
		}
		if (isset ( $this->postalcode )) {
			$settings [] = 'society_postalcode=:postalcode';
		}
		if (isset ( $this->city )) {
			$settings [] = 'society_city=:city';
		}
		if (isset ( $this->countryNameCode )) {
			$settings [] = 'society_countryNameCode=:countryNameCode';
		}
		if (isset ( $this->administrativeAreaName )) {
			$settings [] = 'society_administrativeAreaName=:administrativeAreaName';
		}
		if (isset ( $this->subAdministrativeAreaName )) {
			$settings [] = 'society_subAdministrativeAreaName=:subAdministrativeAreaName';
		}
		if (isset ( $this->url )) {
			$settings [] = 'society_url=:url';
		}
		if (isset ( $this->longitude ) && isset ( $this->latitude )) {
			$settings [] = 'society_longitude=:longitude';
			$settings [] = 'society_latitude=:latitude';
		}
		if (isset ( $this->altitude )) {
			$settings [] = 'society_altitude=:altitude';
		}

		if ($new) {
			$settings [] = 'society_creation_date=NOW()';
			if (isset ( $_SESSION ['user_id'] )) {
				$settings [] = 'society_creation_user_id=:user_id';
			}
		} else {
			if (isset ( $_SESSION ['user_id'] )) {
				$settings [] = 'society_lastModification_user_id=:user_id';
			}
		}
		
		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql .= ' society SET ';
		$sql .= implode ( ', ', $settings );
		if (! $new)	{
			$sql .= ' WHERE society_id=:id';
		}
		
		$statement = $system->getPDO()->prepare($sql);
		
		if (isset ( $this->name )) {
			$statement->bindValue(':name', $this->name, PDO::PARAM_STR);
		}
		if (isset ( $this->description )) {
			$statement->bindValue(':description', $this->description, PDO::PARAM_STR);
		}
		if (isset ( $this->phone )) {
			$statement->bindValue(':phone', $this->phone, PDO::PARAM_STR);
		}
		if (isset ( $this->street )) {
			$statement->bindValue(':street', $this->street, PDO::PARAM_STR);
		}
		if (isset ( $this->postalcode )) {
			$statement->bindValue(':postalcode', $this->postalcode, PDO::PARAM_INT);
		}
		if (isset ( $this->city )) {
			$statement->bindValue(':city', $this->city, PDO::PARAM_STR);
		}
		if (isset ( $this->administrativeAreaName )) {
			$statement->bindValue(':administrativeAreaName', $this->administrativeAreaName, PDO::PARAM_STR);
		}
		if (isset ( $this->subAdministrativeAreaName )) {
			$statement->bindValue(':subAdministrativeAreaName', $this->subAdministrativeAreaName, PDO::PARAM_STR);
		}
		if (isset ( $this->countryNameCode )) {
			$statement->bindValue(':countryNameCode', $this->countryNameCode, PDO::PARAM_STR);
		}		
		if (isset ( $this->url )) {
			$statement->bindValue(':url', $this->url, PDO::PARAM_STR);
		}
		if (isset ( $this->longitude ) && isset ( $this->latitude )) {
			$statement->bindValue(':longitude', $this->longitude, PDO::PARAM_STR);
			$statement->bindValue(':latitude', $this->latitude, PDO::PARAM_STR);
		}
		if (isset ( $this->altitude )) {
			$statement->bindValue(':altitude', $this->altitude, PDO::PARAM_STR);
		}

		if (isset ( $_SESSION ['user_id'] )) {
			$statement->bindValue(':user_id', $_SESSION ['user_id'], PDO::PARAM_INT);
		}

		if (! $new)	{
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		}
		
		$result = $statement->execute();
		
		if ($result && ! isset($this->id)) {
            $this->id = $system->getPdo()->lastInsertId();
        }
		
		return $result;
	}
	/**
	 * Efface l'enregistrement de la société en base de données.
	 * 
	 * @return boolean
	 * @version 15/08/2006
	 */
	protected function deleteRow() {
		global $system;
		if (empty ( $this->id )) return false;
		$statement = $system->getPdo()->prepare('DELETE FROM society WHERE society_id = :id');
		$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		return $statement->execute();
	}
	/**
	 * Supprime la société de la base de données ainsi que toutes les données associées (pistes, participations, etc).
	 * 
	 * @return boolean
	 * @since 15/08/2006
	 */
	public function delete() {
		return $this->deleteLeads () && $this->deleteMemberships () && $this->deleteRelationships () && $this->deleteIndustries () && $this->deleteRow ();
	}
}
?>