<?php
/**
 * @package usocrate.vostok
 * @author Florent Chanavat
 */
class Society {
	public $id;
	protected $industries;
	protected $street;
	protected $city;
	protected $postalcode;
	protected $subAdministrativeAreaName;
	protected $administrativeAreaName;
	protected $countryNameCode;

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
	 * @version 12/2018
	 */
	public function identifyFromName() {
		global $system;
		$statement = $system->getPdo ()->prepare ( 'SELECT society_id FROM society WHERE society_name=:name' );
		$statement->bindValue ( ':name', $this->name, PDO::PARAM_STR );
		$statement->execute ();
		$this->id = $statement->fetch ( PDO::FETCH_COLUMN );
		return $this->id !== false;
	}
	
	/**
	 * Obtient la valeur d'un attribut.
	 */
	public function getAttribute($name) {
		if (isset ( $this->$name ))	return $this->$name;
	}
	
	/**
	 * Fixe la valeur d'un attribut.
	 *
	 * @since 01/2006
	 * @version 03/2006
	 */
	public function setAttribute($name, $value) {
		$value = trim ( $value );
		$value = html_entity_decode ( $value, ENT_QUOTES, 'UTF-8' );
		return $this->{$name} = $value;
	}
	/**
	 * Obtient la longitude
	 *
	 * @since 06/2007
	 */
	public function getLongitude() {
		return $this->getAttribute ( 'longitude' );
	}
	/**
	 * Obtient la latitude
	 *
	 * @since 06/2007
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
	 * @since 12/2010
	 * @return bool
	 */
	public function hasId() {
		return isset ( $this->id );
	}
	/**
	 * Fixe le nom de la société.
	 * @version 02/2022
	 */
	public function setName(string $input) {
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
	 * @since 10/2012
	 */
	private static function getKnownNames($substring = NULL) {
		global $system;
		$sql = 'SELECT society_name FROM society WHERE society_name IS NOT NULL';
		if (isset ( $substring )) {
			$sql .= ' AND society_name LIKE :pattern';
		}
		$sql .= ' ORDER BY society_name ASC';
		$statement = $system->getPdo ()->prepare ( $sql );
		if (isset ( $substring )) {
			$statement->bindValue ( ':pattern', '%' . $substring . '%', PDO::PARAM_STR );
		}
		$statement->execute ();
		return $statement->fetchAll ( PDO::FETCH_COLUMN );
	}
	/**
	 *
	 * @since 10/2012
	 */
	public static function knownNamesToJson($substring = NULL) {
		$output = '{"names":' . json_encode ( self::getKnownNames ( $substring ) ) . '}';
		return $output;
	}
	/**
	 * Obtient de la base de données les valeurs des champs demandés.
	 *
	 * @since 01/2009
	 * @version 11/2016
	 * @param
	 *        	$fields
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
			$statement = $system->getPdo ()->prepare ( $sql );
			$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
			$statement->execute ();
			return $statement->fetch ( PDO::FETCH_ASSOC );
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
		$sql = 'SELECT DISTINCT society_city FROM `society` WHERE society_city IS NOT NULL';
		if (isset ( $substring )) {
			$sql .= ' AND society_city LIKE :pattern';
		}
		$sql .= ' ORDER BY society_city ASC';
		$statement = $system->getPdo ()->prepare ( $sql );
		if (isset ( $substring )) {
			$statement->bindValue ( ':pattern', $substring . '%', PDO::PARAM_STR );
		}
		$statement->execute ();
		return $statement->fetchAll ( PDO::FETCH_COLUMN );
	}
	public static function knownCitiesToJson($substring = NULL) {
		$output = '{"cities":' . json_encode ( self::getKnownCities ( $substring ) ) . '}';
		return $output;
	}
	/**
	 * Obtient la chaîne complète de l'adresse de la société
	 *
	 * @version 02/2022
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
		return implode ( ' ', $elements );
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
	 * @since 06/2007
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
	/**
	 * Obtient l'URL permettant de googliser la société.
	 *
	 * @return string
	 * @since 03/2019
	 */
	public function getGoogleQueryUrl($type = null) {
		$var = $this->getName ();
		return empty ( $var ) ? null : Toolbox::getGoogleQueryUrl ( '"' . $var . '"', $type );
	}
	/**
	 * Obtient les informations de localisation auprès de Google map et complète celles-ci si nécessaire
	 *
	 * @since 06/2007
	 * @version 08/2018
	 */
	public function getAddressFromGoogle($input = NULL) {
		try {
			global $system;
			if (empty ( $input ))
				$input = $this->getAddress ();
			$json = $system->getGoogleGeocodeAsJson ( $input );
			$data = json_decode ( $json );
			if (empty ( $data->{'results'} [0] )) {
				throw new Exception ( 'Pas de résultat GoogleGeocode : ' . $json );
			}
			$street = array ();
			foreach ( $data->{'results'} [0]->{'address_components'} as $c ) {
				if (in_array ( 'street_number', $c->types )) {
					$street ['number'] = $c->long_name;
				}
				if (in_array ( 'route', $c->types )) {
					$street ['route'] = $c->long_name;
				}
				if (in_array ( 'locality', $c->types )) {
					$this->city = $c->long_name;
				}
				if (in_array ( 'postal_code', $c->types )) {
					$this->postalcode = $c->long_name;
				}
				if (in_array ( 'administrative_area_level_1', $c->types )) {
					$this->administrativeAreaName = $c->long_name;
				}
				if (in_array ( 'administrative_area_level_2', $c->types )) {
					$this->subAdministrativeAreaName = $c->long_name;
				}
				if (in_array ( 'country', $c->types )) {
					$this->countryNameCode = $c->short_name;
				}
			}
			if (isset ( $street ['number'] ) && isset ( $street ['route'] )) {
				$this->street = $street ['number'] . ' ' . $street ['route'];
			}
			$this->latitude = $data->{'results'} [0]->{'geometry'}->{'location'}->{'lat'};
			$this->longitude = $data->{'results'} [0]->{'geometry'}->{'location'}->{'lng'};
		} catch ( Exception $e ) {
			$system->reportException ( $e );
			return false;
		}
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
	 * @version 08/2018
	 */
	public function getHtmlLinkToWeb() {
		return $this->getUrl () ? '<a href="' . $this->getUrl () . '" target="_blank" title="' . $this->getUrl () . '"><i class="fas fa-external-link"></i></a>' : NULL;
	}
	/**
	 * Obtenir le lien vers l'écran dédié à la société.
	 *
	 * @return string
	 * @since 12/2016
	 */
	public function getHtmlLinkToSociety($focus = null) {
		return '<a href="' . $this->getDisplayUrl ($focus) . '">' . $this->getNameForHtmlDisplay () . '</a>';
	}
	/**
	 * @since 03/2019
	 * @return string
	 */
	public function getDisplayUrl($focus = null) {
		$href = "society.php?";
		$href .= 'society_id=' . $this->getId ();
		if (isset ( $focus )) {
			$href .= '&focus=' . $focus;
		}
		return $href;
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
	 * Obtient les données de la société au format JSON
	 *
	 * @since 24/06/2007
	 * @return string
	 */
	public function getJson() {
		return json_encode ( $this );
	}
	/**
	 * Obtient la liste des activités auxquelles est associée la société.
	 *
	 * @return array
	 * @since 16/07/2006
	 * @version 20/12/2016
	 */
	public function getIndustries() {
		global $system;
		if (! isset ( $this->industries )) {

			$this->industries = array ();

			if (empty ( $this->id ))
				return NULL;

			$sql = 'SELECT i.* FROM society_industry AS si INNER JOIN industry AS i ON (i.id=si.industry_id)';
			$sql .= ' WHERE si.society_id=:id';
			$statement = $system->getPdo ()->prepare ( $sql );
			$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
			$statement->execute ();
			$data = $statement->fetchAll ( PDO::FETCH_ASSOC );
			foreach ( $data as $row ) {
				$i = new Industry ();
				$i->feed ( $row );
				array_push ( $this->industries, $i );
			}
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
	 * @since 07/2006
	 * @todo substituer à setIndustry()
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
	 * @version 03/06/2017
	 */
	public function saveIndustries() {
		global $system;
		if (! empty ( $this->id ) && isset ( $this->industries )) {
			if ($this->deleteIndustries ()) {
				$errorless = true;
				$statement = $system->getPdo ()->prepare ( 'INSERT INTO society_industry SET society_id=:society_id, industry_id=:industry_id' );
				$statement->bindValue ( ':society_id', $this->id, PDO::PARAM_INT );
				foreach ( $this->industries as $i ) {
					$statement->bindValue ( ':industry_id', $i->getId (), PDO::PARAM_INT );
					$errorless = $errorless && $statement->execute ();
				}
				return $errorless;
			}
		}
		return false;
	}
	/**
	 * Supprime de la base de données la liste des activités déclarées de la société.
	 *
	 * @since 08/2006
	 * @version 12/2016
	 */
	public function deleteIndustries() {
		global $system;
		if (empty ( $this->id ))
			return false;
		$statement = $system->getPdo ()->prepare ( 'DELETE FROM society_industry WHERE society_id=:id' );
		$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
		return $statement->execute ();
	}
	/**
	 * Fixe l'activité de la société.
	 */
	public function setIndustry($industry) {
		return $this->setAttribute ( 'industry', $industry );
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
	 * Obtient les participations associées à la société.
	 *
	 * @return array
	 * @version 06/2017
	 */
	public function getMemberships() {
		global $system;

		if (! isset ( $this->memberships )) {
			$this->memberships = array ();

			$sql = 'SELECT *, DATE_FORMAT(i.individual_creation_date, "%d/%m/%Y")';
			// FROM
			$sql .= ' FROM membership AS ms';
			$sql .= ' INNER JOIN individual AS i';
			$sql .= ' ON ms.individual_id = i.individual_id';
			// WHERE
			$sql .= ' WHERE ms.society_id=:id';
			// ORDER BY
			$sql .= ' ORDER BY ms.weight DESC, i.individual_lastName ASC';

			$statement = $system->getPdo ()->prepare ( $sql );

			$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );

			$statement->execute ();

			foreach ( $statement->fetchAll ( PDO::FETCH_ASSOC ) as $row ) {
				$ms = new Membership ();
				$ms->feed ( $row );
				$this->memberships [] = $ms;
			}
		}
		return $this->memberships;
	}
	/**
	 * Obtient les participations d'un individu dans la société
	 *
	 * @since 12/2020
	 */
	public function getIndividualMemberships(Individual $individual) {
		global $system;
		try {
			if (empty ( $this->id )) {
				throw new Exception ( __METHOD__ . ' : la société doit être identifiée.' );
			}
			if (! $individual->hasId ()) {
				throw new Exception ( __METHOD__ . ' : l\'individu doit être identifié.' );
			}
			$criteria = array (
					'society_id' => $this->getId (),
					'individual_id' => $individual->getId ()
			);
			return $system->getMemberships ( $criteria );
		} catch ( Exception $e ) {
			$system->reportException ( $e );
		}
	}
	/**
	 * Transfére les participations de membres au sein de la société vers une autre société (dans le cadre d'une fusion de sociétés notamment).
	 *
	 * @version 06/2017
	 */
	public function transferMemberships($society) {
		global $system;
		if (! is_a ( $society, 'Society' ) || ! $society->getId () || empty ( $this->id )) {
			return false;
		}
		$statement = $system->getPdo ()->prepare ( 'UPDATE membership SET society_id=:target_id WHERE society_id=:id' );
		$statement->bindValue ( ':target_id', $society->getId (), PDO::PARAM_INT );
		$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
		return $statement->execute ();
	}
	/**
	 * Supprime de la base de données les enregistrements des participations (les personnes associées sont supprimées si elles n'ont pas d'autres participations).
	 *
	 * @since 08/2006
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
	 * @since 06/2006
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
	 * Obtient les éléments en relation avec la société.
	 *
	 * @return array
	 * @since 03/2006
	 * @version 12/2016
	 */
	public function getRelationships() {
		global $system;
		if (! isset ( $this->relationships )) {
			$this->relationships = array ();
			$sql = 'SELECT * FROM relationship AS rs ';
			$sql .= ' WHERE (rs.item0_id=:item0_id AND rs.item0_class=:item0_class) OR (rs.item1_id=:item1_id AND rs.item1_class=:item1_class)';
			$statement = $system->getPdo ()->prepare ( $sql );
			$statement->bindValue ( ':item0_id', $this->id, PDO::PARAM_INT );
			$statement->bindValue ( ':item0_class', get_class ( $this ), PDO::PARAM_STR );
			$statement->bindValue ( ':item1_id', $this->id, PDO::PARAM_INT );
			$statement->bindValue ( ':item1_class', get_class ( $this ), PDO::PARAM_STR );
			$statement->execute ();
			$rowset = $statement->fetchAll ( PDO::FETCH_ASSOC );
			foreach ( $rowset as $row ) {
				$rs = new Relationship ();
				$rs->feed ( $row );
				$this->relationships [] = $rs;
			}
		}
		return $this->relationships;
	}
	/**
	 * Obtient la société-mère si elle existe
	 *
	 * @since 09/2006
	 * @version 12/2016
	 */
	public function getParentSociety() {
		$data = $this->getRelatedSocieties ();
		if (isset ( $data )) {
			foreach ( $data as $item ) {
				$role = $item [2];
				$society = $item [0];
				if (isset ( $role ) && strcasecmp ( $role, 'Maison-mère' ) == 0) {
					return $society;
				}
			}
		}
		return NULL;
	}
	/**
	 * Obtient la liste des sociétés en relation.
	 *
	 * @return array
	 * @since 08/2006
	 * @version 04/2023
	 */
	public function getRelatedSocieties($role=null) {
		global $system;

		if (empty ( $this->id )) {
			return NULL;
		}

		$output = array ();

		$sql = 'SELECT s.*, r.relationship_id, r.item1_role AS relatedsociety_role, r.description, r.init_year, r.end_year';
		$sql .= ' FROM relationship AS r INNER JOIN society AS s ON(r.item1_id=s.society_id)';
		$sql .= ' WHERE item0_class="society" AND item0_id=:item0_id AND item1_class="society"';
		if (isset($role)) {
			$sql .= ' AND item1_role=:item1_role';
		}
		$sql .= ' UNION';
		$sql .= ' SELECT s.*, r.relationship_id, r.item0_role AS relatedsociety_role, r.description, r.init_year, r.end_year';
		$sql .= ' FROM relationship AS r INNER JOIN society AS s ON(r.item0_id=s.society_id)';
		$sql .= ' WHERE item1_class="society" AND item1_id=:item1_id AND item0_class="society"';
		if (isset($role)) {
			$sql .= ' AND item0_role=:item0_role';
		}
		$sql .= ' ORDER BY society_name ASC';

		$statement = $system->getPdo ()->prepare ( $sql );
		$statement->bindValue ( ':item0_id', $this->id, PDO::PARAM_INT );
		$statement->bindValue ( ':item1_id', $this->id, PDO::PARAM_INT );
		if (isset($role)) {
			$statement->bindValue ( ':item0_role', $role, PDO::PARAM_STR );
			$statement->bindValue ( ':item1_role', $role, PDO::PARAM_STR );
		}
		$statement->execute ();
		$data = $statement->fetchAll ( PDO::FETCH_ASSOC );

		foreach ( $data as $row ) {
			$s = new Society ();
			$s->feed ( $row );
			$output [] = array (
					$s,
					$row ['relationship_id'],
					$row ['relatedsociety_role'],
					$row ['description'],
					$row ['init_year'],
					$row ['end_year']
					
			);
		}
		return $output;
	}
	/**
	 * Obtient les sociétés à l'activité proche
	 *
	 * @since 07/2018
	 */
	public function getInSameIndustrySocieties() {
		global $system;
		$sql = 'SELECT s.society_id, s.society_name, COUNT(i2.industry_id) AS similarity_indicator';
		$sql .= ' FROM society_industry AS i INNER JOIN society_industry AS i2 USING (industry_id)';
		$sql .= ' INNER JOIN society AS s ON s.society_id = i2.society_id';
		$sql .= ' WHERE i.society_id = ? AND i2.society_id <> i.society_id';
		$sql .= ' GROUP BY i.society_id, i2.society_id';
		$sql .= ' HAVING similarity_indicator > 1';
		$sql .= ' ORDER BY similarity_indicator DESC';
		$sql .= ' LIMIT 0,7';
		$statement = $system->getPdo ()->prepare ( $sql );
		$statement->execute ( array (
				$this->id
		) );
		$data = $statement->fetchAll ( PDO::FETCH_ASSOC );
		$output = array ();
		foreach ( $data as $row ) {
			$s = new Society ( $row ['society_id'] );
			$s->setName ( $row ['society_name'] );
			$output [$row ['society_id']] = $s;
		}
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
		foreach ( $this->getRelatedSocieties () as $item ) {
			$society = $item [0];
			$html .= '<option value="' . $society->getId () . '"';
			if (isset ( $valueToSelect ) && strcasecmp ( $society->getId (), $valueToSelect ) == 0) {
				$html .= ' selected="selected"';
			}
			$html .= '>';
			$html .= $society->getName ();
			$html .= '</option>';
		}
		return $html;
	}
	/**
	 *
	 * @since 09/04/2006
	 * @version 03/06/2017
	 */
	public function transferRelationships($society) {
		global $system;

		if (! is_a ( $society, 'Society' ) || ! $society->getId () || empty ( $this->id ))
			return false;

		$statementA = $system->getPdo ()->prepare ( 'UPDATE relationship SET item0_id=:target_id WHERE item0_id=:id AND item0_class=:class' );
		$statementB = $system->getPdo ()->prepare ( 'UPDATE relationship SET item1_id=:target_id WHERE item1_id=:id AND item1_class=:class' );

		$statementA->bindValue ( ':target_id', $society->getId (), PDO::PARAM_INT );
		$statementA->bindValue ( ':id', $this->id, PDO::PARAM_INT );
		$statementA->bindValue ( ':class', get_class ( $this ), PDO::PARAM_STR );

		$statementB->bindValue ( ':target_id', $society->getId (), PDO::PARAM_INT );
		$statementB->bindValue ( ':id', $this->id, PDO::PARAM_INT );
		$statementB->bindValue ( ':class', get_class ( $this ), PDO::PARAM_STR );

		return $statementA->execute () && $statementB->execute ();
	}
	/**
	 * Obtient les pistes associées à la société.
	 *
	 * @version 03/2020
	 */
	public function getLeads() {
		global $system;

		if (! isset ( $this->leads )) {
			$this->leads = array ();

			$sql = 'SELECT *, DATE_FORMAT(lead_creation_date, "%d/%m/%Y") as lead_creation_date_fr FROM `lead`';
			$sql .= ' WHERE society_id=:id';
			$sql .= ' ORDER BY lead_creation_date DESC';

			$statement = $system->getPdo ()->prepare ( $sql );
			$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
			$statement->execute ();

			foreach ( $statement->fetchAll ( PDO::FETCH_ASSOC ) as $row ) {
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
	 * @since 01/2009
	 * @version 06/2017
	 */
	public function getEvents() {
		global $system;
		$criteria = array ();
		$criteria ['society_id'] = $this->id;
		return $system->getEvents ( $criteria );
	}
	/**
	 * Transfére les pistes liées à la société vers une autre société (dans le cadre d'une fusion de sociétés notamment).
	 *
	 * @version 11/2016
	 */
	public function transferLeads($society) {
		global $system;
		if (! is_a ( $society, 'Society' ) || ! $society->getId () || empty ( $this->id ))
			return false;
		$statement = $system->getPdo ()->prepare ( 'UPDATE `lead` SET society_id = :target_id WHERE society_id = :id' );
		$statement->bindValue ( ':target_id', $society->getId (), PDO::PARAM_INT );
		$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
		return $statement->execute ();
	}
	/**
	 * Supprime de la base de données toutes les pistes rattachées à la société.
	 *
	 * @since 08/2006
	 * @version 11/2016
	 */
	public function deleteLeads() {
		global $system;
		if (empty ( $this->id ))
			return false;
		$statement = $system->getPdo ()->prepare ( 'DELETE FROM `lead` WHERE society_id = :id' );
		$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
		return $statement->execute ();
	}
	/**
	 * Fixe les attributs de la société à partir d'un tableau aux clefs normalisées
	 *
	 * @version 04/2006
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
			$statement = $system->getPdo ()->prepare ( 'SELECT * FROM society WHERE society_id=:id' );
			$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
			$statement->execute ();
			return $this->feed ( $statement->fetch ( PDO::FETCH_ASSOC ) );
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

		$new = empty ( $this->id );

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
		if (isset ( $this->city )) {
			$settings [] = 'society_city=:city';
		}
		if (isset ( $this->postalcode )) {
			$settings [] = 'society_postalcode=:postalcode';
		}
		if (isset ( $this->subAdministrativeAreaName )) {
			$settings [] = 'society_subAdministrativeAreaName=:subAdministrativeAreaName';
		}
		if (isset ( $this->administrativeAreaName )) {
			$settings [] = 'society_administrativeAreaName=:administrativeAreaName';
		}
		if (isset ( $this->countryNameCode )) {
			$settings [] = 'society_countryNameCode=:countryNameCode';
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
		if (! $new) {
			$sql .= ' WHERE society_id=:id';
		}

		$statement = $system->getPDO ()->prepare ( $sql );

		if (isset ( $this->name )) {
			$statement->bindValue ( ':name', $this->name, PDO::PARAM_STR );
		}
		if (isset ( $this->description )) {
			$statement->bindValue ( ':description', $this->description, PDO::PARAM_STR );
		}
		if (isset ( $this->phone )) {
			$statement->bindValue ( ':phone', $this->phone, PDO::PARAM_STR );
		}
		if (isset ( $this->street )) {
			$statement->bindValue ( ':street', $this->street, PDO::PARAM_STR );
		}
		if (isset ( $this->postalcode )) {
			$statement->bindValue ( ':postalcode', $this->postalcode, PDO::PARAM_INT );
		}
		if (isset ( $this->city )) {
			$statement->bindValue ( ':city', $this->city, PDO::PARAM_STR );
		}
		if (isset ( $this->administrativeAreaName )) {
			$statement->bindValue ( ':administrativeAreaName', $this->administrativeAreaName, PDO::PARAM_STR );
		}
		if (isset ( $this->subAdministrativeAreaName )) {
			$statement->bindValue ( ':subAdministrativeAreaName', $this->subAdministrativeAreaName, PDO::PARAM_STR );
		}
		if (isset ( $this->countryNameCode )) {
			$statement->bindValue ( ':countryNameCode', $this->countryNameCode, PDO::PARAM_STR );
		}
		if (isset ( $this->url )) {
			$statement->bindValue ( ':url', $this->url, PDO::PARAM_STR );
		}
		if (isset ( $this->longitude ) && isset ( $this->latitude )) {
			$statement->bindValue ( ':longitude', $this->longitude, PDO::PARAM_STR );
			$statement->bindValue ( ':latitude', $this->latitude, PDO::PARAM_STR );
		}
		if (isset ( $this->altitude )) {
			$statement->bindValue ( ':altitude', $this->altitude, PDO::PARAM_STR );
		}

		if (isset ( $_SESSION ['user_id'] )) {
			$statement->bindValue ( ':user_id', $_SESSION ['user_id'], PDO::PARAM_INT );
		}

		if (! $new) {
			$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
		}

		$result = $statement->execute ();

		if ($result && $new) {
			$this->id = $system->getPdo ()->lastInsertId ();
		}

		return $result;
	}
	/**
	 * Supprime la société de la base de données ainsi que toutes les données associées (pistes, participations, etc).
	 *
	 * @return boolean
	 * @since 08/2006
	 * @version 03/2022
	 */
	public function delete() {
		global $system;
		return $system->removeSocietyFromDB($this);
	}
}
?>