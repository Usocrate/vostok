<?php
/**
 * @package usocrate.vostok
 * @author Florent Chanavat
 */
class System {
	private $db_host;
	private $db_name;
	private $db_user;
	private $db_password;
	private $appli_name;
	private $appli_description;
	private $appli_url;
	private $googlemaps_api_key;
	private $dir_path;
	private $pdo;
	
	/**
	 *
	 * @version 01/10/2016
	 * @param string $path        	
	 */
	public function __construct($path) {
		$this->config_file_path = $path;
		if ($this->configFileExists ()) {
			$this->parseConfigFile ();
		}
	}
	public function setDbHost($input) {
		$this->db_host = $input;
	}
	public function getDbHost() {
		return $this->db_host;
	}
	public function setDbName($input) {
		$this->db_name = $input;
	}
	public function getDbName() {
		return $this->db_name;
	}
	public function setDbUser($input) {
		$this->db_user = $input;
	}
	public function getDbUser() {
		return $this->db_user;
	}
	public function setDbPassword($input) {
		$this->db_password = $input;
	}
	public function getDbPassword() {
		return $this->db_password;
	}
	public function setAppliName($input) {
		$this->appli_name = $input;
	}
	public function getAppliName() {
		return $this->appli_name;
	}
	public function setAppliDescription($input) {
		$this->appli_description = $input;
	}
	public function getAppliDescription() {
		return $this->appli_description;
	}
	public function setAppliUrl($input) {
		$this->appli_url = $input;
	}
	public function getAppliUrl() {
		return $this->appli_url;
	}
	public function setGoogleMapsApiKey($input) {
		$this->googlemaps_api_key = $input;
	}
	public function getGoogleMapsApiKey() {
		return $this->googlemaps_api_key;
	}
	public function getGoogleGeocodeAsJson($input) {
		$param['address'] = $input;
		$param['key'] = $this->getGoogleMapsApiKey();
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($param['address']).'&key='.urlencode($param['key']);
		$json = file_get_contents ( $url );
		return $json;
	}	
	public function getSkinUrl() {
		return $this->appli_url . 'skin/';
	}
	public function getImagesUrl() {
		return $this->getSkinUrl () . 'images/';
	}
	public function getCvUrl() {
		return $this->appli_url . 'cv/';
	}
	public function getTrombiUrl() {
		return $this->appli_url . 'data/trombinoscope/';
	}
	public function setDirPath($input) {
		$this->dir_path = $input;
	}
	public function getDirPath() {
		return $this->dir_path;
	}
	public function getClassDirPath() {
		return $this->dir_path . DIRECTORY_SEPARATOR . 'classes';
	}
	/**
	 * @version 05/03/2017
	 */
	public function getDataDirPath() {
		$path = $this->dir_path . DIRECTORY_SEPARATOR . 'data';
		if ( !is_dir($path) ) {
			mkdir($path, 770);
		}
		return $path;
	}
	/**
	 * @version 05/03/2017
	 */	
	public function getCvDirPath() {
		$path = $this->getDataDirPath () . DIRECTORY_SEPARATOR . 'cv';
		if ( !is_dir($path) ) {
			mkdir($path, 770);
		}
		return $path;
	}
	/**
	 * @version 05/03/2017
	 */	
	public function getTrombiDirPath() {
		$path = $this->getDataDirPath () . DIRECTORY_SEPARATOR . 'trombinoscope';
		if ( !is_dir($path) ) {
			mkdir($path, 770);
		}
		return $path;
	}
	
	/**
	 *
	 * @since 01/10/2016
	 * @return boolean
	 */
	public function configFileExists() {
		return file_exists ( $this->config_file_path );
	}
	/**
	 *
	 * @since 01/10/2016
	 * @throws Exception
	 * @return boolean
	 */
	public function parseConfigFile() {
		try {
			if (is_readable ( $this->config_file_path )) {
				$data = json_decode ( file_get_contents ( $this->config_file_path ), true );
				foreach ( $data as $key => $value ) {
					switch ($key) {
						case 'db_host' :
							$this->db_host = $value;
							break;
						case 'db_name' :
							$this->db_name = $value;
							break;
						case 'db_user' :
							$this->db_user = $value;
							break;
						case 'db_password' :
							$this->db_password = $value;
							break;
						case 'appli_name' :
							$this->appli_name = $value;
							break;
						case 'appli_description' :
							$this->appli_description = $value;
							break;
						case 'appli_url' :
							$this->appli_url = $value;
							break;
						case 'googlemaps_api_key' :
							$this->googlemaps_api_key = $value;
							break;							
						case 'dir_path' :
							$this->dir_path = $value;
							break;
					}
				}
			} else {
				throw new Exception ( 'Le fichier de configuration doit être accessible en lecture.' );
			}
		} catch ( Exception $e ) {
			$this->reportException ( __METHOD__, $e );
			return false;
		}
	}
	/**
	 *
	 * @since 01/10/2016
	 * @return number|boolean
	 */
	public function saveConfigFile() {
		try {
			$a = array (
					'db_host' => $this->db_host,
					'db_name' => $this->db_name,
					'db_user' => $this->db_user,
					'db_password' => $this->db_password,
					'appli_name' => $this->appli_name,
					'appli_description' => $this->appli_description,
					'appli_url' => $this->appli_url,
					'googlemaps_api_key' => $this->googlemaps_api_key,
					'dir_path' => $this->dir_path 
			);
			return file_put_contents ( $this->config_file_path, json_encode ( $a ) );
		} catch ( Exception $e ) {
			$this->reportException ( __METHOD__, $e );
			return false;
		}
	}
	
	/**
	 * Retourne un PHP Data Object permettant de se connecter à la date de données.
	 *
	 * @since 04/08/2014
	 * @return PDO
	 */
	public function getPdo() {
		try {
			if (! isset ( $this->pdo )) {
				$this->pdo = new PDO ( 'mysql:host=' . $this->db_host . ';dbname=' . $this->db_name, $this->db_user, $this->db_password, array (
						PDO::ATTR_PERSISTENT => true 
				) );
				$this->pdo->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				$this->pdo->exec ( 'SET NAMES utf8' );
			}
			return $this->pdo;
		} catch ( PDOException $e ) {
			$this->reportException($e);
			return false;
		}
	}
	public function getUserIdFromCookies() {
		if (isset ( $_COOKIE ['user_name'] ) && isset ( $_COOKIE ['user_password'] )) {
			$user = new User ();
			if ($user->identification ( urldecode ( $_COOKIE ['user_name'] ), urldecode ( $_COOKIE ['user_password'] ) )) {
				return $this->user_id = $user->id;
			}
		}
		return false;
	}
	/**
	 *
	 * @version 04/08/2014
	 * @return string
	 */
	public function getHtmlLink() {
		return '<a href="' . $system->getAppliUrl () . '">' . ToolBox::toHtml( $this->getAppliName() ) . '</a>';
	}
	
	public function getHtmlHeadTagsForFavicon() {
		$output = array();
		$output[] = '<link rel="apple-touch-icon" sizes="180x180" href="'.$this->getSkinUrl().'images/apple-touch-icon.png">';
		$output[] = '<link rel="icon" type="image/png" sizes="32x32" href="'.$this->getSkinUrl().'images/favicon-32x32.png">';
		$output[] = '<link rel="icon" type="image/png" sizes="16x16" href="'.$this->getSkinUrl().'images/favicon-16x16.png">';
		$output[] = '<link rel="manifest" href="'.$this->getSkinUrl().'site.webmanifest">';
		$output[] = '<link rel="mask-icon" href="'.$this->getSkinUrl().'images/safari-pinned-tab.svg" color="#da8055">';
		$output[] = '<link rel="shortcut icon" href="'.$this->getSkinUrl().'images/favicon.ico">';
		$output[] = '<meta name="apple-mobile-web-app-title" content="'.ToolBox::toHtml( $this->getAppliName() ).'">';
		$output[] = '<meta name="application-name" content="'.ToolBox::toHtml( $this->getAppliName() ).'">';
		$output[] = '<meta name="msapplication-TileColor" content="#6a453b">';
		$output[] = '<meta name="msapplication-config" content="'.$this->getSkinUrl().'browserconfig.xml">';
		$output[] = '<meta name="theme-color" content="#6a453b">';
		return $output;
	}
	
	public function writeHtmlHeadTagsForFavicon() {
		foreach ($this->getHtmlHeadTagsForFavicon() as $tag) {
			echo $tag;
		}
	}
	/**
	 * Renvoie l'ensemble des utilisateurs accrédités.
	 *
	 * @return array
	 * @since 26/02/2006
	 * @version 04/08/2014
	 */
	public function getUsers() {
		global $system;
		try {
			$output = array ();
			$sql = 'SELECT * FROM user';
			foreach ( $system->getPdo ()->query ( $sql ) as $data ) {
				$u = new User ();
				$u->feed ( $data );
				array_push ( $output, $u );
			}
			return $output;
		} catch ( Exception $e ) {
			$this->reportException($e);
		}
	}
	/**
	 * @since 08/2014
	 * @version 07/2018
	 */
	public function getIndividualCollectionStatement($criteria = NULL, $sort = 'Name', $offset = 0, $count = NULL) {
		global $system;
		try {
			
			$sql = 'SELECT *, DATE_FORMAT(individual_creation_date, "%d/%m/%Y") AS individual_creation_date_fr';
			$sql .= ' FROM individual';
			
			// WHERE
			$where = array ();
			
			if (isset ( $criteria ['individual_id'] )) {
				$where [] = 'individual_id = :id';
			}
			
			if (isset ( $criteria ['individual_lastname_like_pattern'] )) {
				$where [] = 'individual_lastname LIKE :lastname_like';
			}
			
			if (isset ( $criteria ['individual_birth_date_distance_max'] )) {
				$where [] = '(MOD(TO_DAYS(CURDATE()) - TO_DAYS(individual_birth_date), 365.25)<:birthdate_distance OR MOD(TO_DAYS(CURDATE()) - TO_DAYS(individual_birth_date), 365.25)>(365.25-:birthdate_distance))';
			}
			
			if (isset ($criteria ['everPinned']) && $criteria ['everPinned']===true) {
				$where [] = 'individual_lastPin_date IS NOT NULL';
			}
			
			if (count ( $where ) > 0) {
				$sql .= ' WHERE ' . implode ( ' AND ', $where );
			}
			
			switch ($sort) {
				case 'Name' : 
					$sql .= ' ORDER BY individual_lastName ASC';
					break;
				case 'Last updated first' : 
					$sql .= ' ORDER BY individual_timestamp DESC';
					break;
				case 'Last created first' :
					$sql .= ' ORDER BY individual_creation_date DESC';
					break;
				case 'Last pinned first' :
					$sql .= ' ORDER BY individual_lastPin_date DESC';
					break;					
			}

			// LIMIT
			if (isset ( $count )) {
				$sql .= isset ( $offset ) ? ' LIMIT :offset,:count' : ' LIMIT :count';
			}
			
			$statement = $system->getPdo ()->prepare ( $sql );
			
			// echo $sql;
			
			/*
			 * Rattachement des variables
			 */
			if (isset ( $criteria ['individual_id'] )) {
				$statement->bindValue ( ':id', ( int ) $criteria ['individual_id'], PDO::PARAM_INT );
			}
			if (isset ( $criteria ['individual_lastname_like_pattern'] )) {
				$statement->bindValue ( ':lastname_like', '%' . $criteria ['individual_lastname_like_pattern'] . '%', PDO::PARAM_STR );
			}
			if (isset ( $criteria ['individual_birth_date_distance_max'] )) {
				$statement->bindValue ( ':birthdate_distance', ( int ) $criteria ['individual_birth_date_distance_max'], PDO::PARAM_INT );
			}
			
			if (isset ( $count )) {
				if (isset ( $offset )) {
					$statement->bindValue ( ':offset', ( int ) $offset, PDO::PARAM_INT );
				}
				$statement->bindValue ( ':count', ( int ) $count, PDO::PARAM_INT );
			}
			$statement->setFetchMode ( PDO::FETCH_ASSOC );
			return $statement;
		} catch ( Exception $e ) {
			$this->reportException($e);
		}
	}
	/**
	 * @since 05/2018
	 */
	public function getMemberships($criteria = NULL, $sort = 'Last updated first', $offset = 0, $count = NULL) {
		global $system;
		try {
			$sql = 'SELECT m.membership_id AS id, m.individual_id, m.society_id, m.title, m.department, m.description, m.init_year, m.end_year, m.timestamp';
			$sql.= ', i.individual_firstName, i.individual_lastName';
			$sql.= ', s.society_name, s.society_city';
			$sql.= ' FROM membership AS m';
			$sql.= ' INNER JOIN society AS s ON s.society_id = m.society_id';
			if (isset($criteria['industry_id'])) {
				$sql.= ' INNER JOIN society_industry AS si ON si.society_id = s.society_id';
			}
			$sql.= ' INNER JOIN individual AS i ON i.individual_id = m.individual_id';

			// WHERE
			$where = array ();
			
			if (isset ( $criteria ['individual_id'] )) {
				$where [] = 'm.individual_id = :individual_id';
			}
			
			if (isset ( $criteria ['society_id'] )) {
				$where [] = 'm.society_id = :society_id';
			}
			
			if ( isset($criteria['active']) && $criteria['active']===true ) {
				$where [] = 'm.end_year IS NULL';
			}			

			if (isset ( $criteria ['society_name_like_pattern'] )) {
				$where [] = 's.society_name LIKE :society_name_like_pattern';
			}			
			
			if (isset ( $criteria ['society_city'] )) {
				$where [] = 's.society_city = :society_city';
			}
			
			if (isset ($criteria ['everPinnedIndividual']) && $criteria ['everPinnedIndividual']===true) {
				$where [] = 'i.individual_lastPin_date IS NOT NULL';
			}
			
			if (isset ( $criteria ['industry_id'] )) {
				$where [] = 'si.industry_id = :industry_id';
			}

			if (count ( $where ) > 0) {
				$sql .= ' WHERE ' . implode ( ' AND ', $where );
			}
			
			// ORDER
			switch ($sort) {
				case 'Last updated first' : 
					$sql .= ' ORDER BY m.timestamp DESC';
					break;
				case 'Last pinned first' :
					$sql .= ' ORDER BY i.individual_lastPin_date DESC';
					break;					
			}

			// LIMIT
			if (isset ( $count )) {
				$sql .= isset ( $offset ) ? ' LIMIT :offset,:count' : ' LIMIT :count';
			}			
			
			$statement = $system->getPdo()->prepare($sql);

			if (isset ($criteria ['individual_id'])) {
				$statement->bindValue(':individual_id', $criteria['individual_id'], PDO::PARAM_INT);
			}
			if (isset ($criteria ['society_id'])) {
				$statement->bindValue(':society_id', $criteria['society_id'], PDO::PARAM_INT);
			}
			if (isset ($criteria ['society_name_like_pattern'])) {
				$statement->bindValue(':society_name_like_pattern', '%'.$criteria['society_name_like_pattern'].'%', PDO::PARAM_STR);
			}			
			if (isset ($criteria ['society_city'])) {
				$statement->bindValue(':society_city', $criteria['society_city'], PDO::PARAM_STR);
			}
			if (isset ($criteria ['industry_id'])) {
				$statement->bindValue(':industry_id', $criteria['industry_id'], PDO::PARAM_INT);
			}			

			if (isset ( $count )) {
				if (isset ( $offset )) {
					$statement->bindValue ( ':offset', ( int ) $offset, PDO::PARAM_INT );
				}
				$statement->bindValue ( ':count', ( int ) $count, PDO::PARAM_INT );
			}
			
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$statement->execute();

			$memberships = array ();
			foreach ( $statement->fetchAll() as $data ) {
				// individual
				$i = new Individual();
				$i->setId($data['individual_id']);
				$i->setFirstName($data['individual_firstName']);
				$i->setLastName($data['individual_lastName']);

				// society
				$s = new Society();
				$s->setId($data['society_id']);
				$s->setName($data['society_name']);
				$s->setCity($data['society_city']);

				// membership
				$m = new Membership();
				$m->setId($data['id']);
				$m->setSociety($s);
				$m->setIndividual($i);
				$m->setTitle($data['title']);
				$m->setDepartment($data['department']);
				$m->setDescription($data['description']);
				$m->setInitYear($data['init_year']);
				$m->setEndYear($data['end_year']);
				//$m->setTimestamp($data['timestamp']);
				$memberships[$data['id']] = $m;
			}
			return $memberships;			
		} catch (Exception $e) {
			$this->reportException($e, __METHOD__);
		}
	}	
	/**
	 * @since 08/2014
	 * @version 12/2016
	 */
	public static function getAloneIndividualCollectionStatement($criteria = NULL, $sort = 'Name', $offset = 0, $count = NULL) {
		global $system;
		try {
			$sql = 'SELECT i.*, DATE_FORMAT(individual_creation_date, "%d/%m/%Y") AS individual_creation_date_fr';
			$sql .= ' FROM individual AS i LEFT OUTER JOIN membership AS ms ON ms.individual_id = i.individual_id';
			
			// WHERE
			$where = array ();
			
			if (isset ( $criteria ['individual_lastname_like_pattern'] )) {
				$where [] = 'i.individual_lastname LIKE :lastname_like';
			}
			
			if (count ( $where ) > 0) {
				$sql .= ' WHERE ' . implode ( ' AND ', $where );
			}
			$sql .= ' GROUP BY i.individual_id';
			$sql .= ' HAVING COUNT(membership_id)=0';
			
			// ORDER BY
			switch ($sort) {
				case 'Name' :
					$sql .= ' ORDER BY individual_lastName ASC';
					break;
			}
			
			
			// LIMIT
			if (isset ( $count )) {
				$sql .= isset ( $offset ) ? ' LIMIT :offset,:count' : ' LIMIT :count';
			}
			
			$statement = $system->getPdo ()->prepare ( $sql );
			
			/*
			 * Rattachement des variables
			 */
			if (isset ( $criteria ['individual_lastname_like_pattern'] )) {
				$statement->bindValue ( ':lastname_like', '%' . $criteria ['individual_lastname_like_pattern'] . '%', PDO::PARAM_STR );
			}
			
			if (isset ( $count )) {
				if (isset ( $offset )) {
					$statement->bindValue ( ':offset', ( int ) $offset, PDO::PARAM_INT );
				}
				$statement->bindValue ( ':count', ( int ) $count, PDO::PARAM_INT );
			}
			$statement->setFetchMode ( PDO::FETCH_ASSOC );
			return $statement;
		} catch ( Exception $e ) {
			$this->reportException($e);
		}
	}
	/**
	 *
	 * @since 04/08/2014
	 */
	public function countIndividuals($criteria) {
		global $system;
		try {
			$sql = 'SELECT COUNT(*) FROM individual';
			
			$where = array ();
			
			if (isset ( $criteria ['individual_lastname_like_pattern'] )) {
				$where [] = 'individual_lastname LIKE :lastname_like';
			}
			
			if (count ( $where ) > 0) {
				$sql .= ' WHERE ' . implode ( ' AND ', $where );
			}
			
			$statement = $system->getPdo ()->prepare ( $sql );
			
			if (isset ( $criteria ['individual_lastname_like_pattern'] )) {
				$statement->bindValue ( ':lastname_like', '%' . $criteria ['individual_lastname_like_pattern'] . '%', PDO::PARAM_STR );
			}
			
			$statement->execute ();
			return $statement->fetchColumn ();
		} catch ( Exception $e ) {
			$system->reportException($e);
		}
	}
	/**
	 *
	 * @since 04/08/2014
	 */
	public function countAloneIndividuals($criteria) {
		global $system;
		try {
			$c = new IndividualCollection ( self::getAloneIndividualCollectionStatement ( $criteria ) );
			return $c->getSize ();
		} catch ( Exception $e ) {
			$system->reportException($e);
		}
	}
	/**
	 * Obtient les personnes dont la date d'anniversaire est proche de la date courante.
	 *
	 * @since 22/12/2006
	 * @version 04/08/2014
	 * @return IndividualCollection
	 */
	public function getCloseToBirthDateIndividuals($distance = '5') {
		try {
			$criteria = array (
					'individual_birth_date_distance_max' => $distance 
			);
			$sort_key = 'individual_birth_date';
			$sort_order = 'ASC';
			$statement = $this->getIndividualCollectionStatement ( $criteria, $sort_key, $sort_order );
			return new IndividualCollection ( $statement );
		} catch ( Exception $e ) {
			$system->reportException($e);
		}
	}
	/**
	 * Obtient les dernières personnes épinglées.
	 *
	 * @since 07/2018
	 * @return IndividualCollection
	 */	
	public function getLastPinnedIndividuals($nb = 12) {
		try {
			$criteria = array (
				'everPinned' => true
			);
			$sort_key = 'Last pinned first';
			$sort_order = 'DESC';
			$statement = $this->getIndividualCollectionStatement ($criteria, $sort_key, $sort_order, $nb);
			return new IndividualCollection ( $statement );
		} catch ( Exception $e ) {
			$system->reportException($e);
		}
	}	
	/**
	 * Renvoie les enregistrements des sociétés (avec critères éventuels).
	 *
	 * @version 13/12/2016
	 */
	private function getSocietiesData($criteria = NULL, $sort = 'Last created first', $offset = 0, $nb = NULL) {
		global $system;
		
		$sql = 'SELECT s.*, DATE_FORMAT(s.society_creation_date, "%d/%m/%Y") AS society_creation_date_fr, UNIX_TIMESTAMP(s.society_creation_date) AS society_creation_timestamp';
		$sql.= ' FROM society AS s LEFT OUTER JOIN society_industry AS si ON(si.society_id=s.society_id)';
		if (isset ($criteria) && count ( $criteria ) > 0) {
			$sql_criteria = array();
			if (isset ($criteria['name']) ) {
				$sql_criteria[] = 's.society_name LIKE :name';
			}
			if (isset ($criteria['city']) ) {
				$sql_criteria[] = 's.society_city = :city';
			}
			if (isset ($criteria['industry_id']) ) {
				$sql_criteria[] = 'si.industry_id = :industry_id';
			}
			$sql .= ' WHERE '.implode(' AND ', $sql_criteria);
		}

		switch ($sort) {
			case 'Last created first' :
				$sql .= ' GROUP BY society_creation_timestamp DESC, s.society_id';
				break;
			default :
				$sql .= ' GROUP BY s.society_name ASC, s.society_id';
		}

		if (isset ( $nb )) {
			$sql .= ' LIMIT :offset, :nb';
		}
		//echo $sql;
		$statement = $system->getPdo()->prepare($sql);
		
		if (isset ($criteria) && count ( $criteria ) > 0) {
			if (isset ($criteria['name']) ) {
				$statement->bindValue(':name', $criteria['name'].'%', PDO::PARAM_STR);
			}
			if (isset ($criteria['city']) ) {
				$statement->bindValue(':city', $criteria['city'], PDO::PARAM_STR);
			}
			if (isset ($criteria['industry_id']) ) {
				$statement->bindValue(':industry_id', $criteria['industry_id'], PDO::PARAM_INT);
			}
		}

		if (isset ( $nb )) {
			$statement->bindValue(':offset', $offset, PDO::PARAM_INT);
			$statement->bindValue(':nb', $nb, PDO::PARAM_INT);
		}
		
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	 * Renvoie le nombre de sociétés (avec critères éventuels)
	 *
	 * @return int
	 * @version 23/11/2016
	 */
	public function getSocietiesNb($criteria = NULL) {
		global $system;

		$sql = 'SELECT COUNT(DISTINCT s.society_id) FROM society AS s LEFT OUTER JOIN society_industry AS si ON(si.society_id=s.society_id)';

		if (isset ($criteria) && count ( $criteria ) > 0) {
			$sql_criteria = array();
			if (isset ($criteria['name']) ) {
				$sql_criteria[] = 's.society_name LIKE :name';
			}
			if (isset ($criteria['city']) ) {
				$sql_criteria[] = 's.society_city = :city';
			}
			if (isset ($criteria['industry_id']) ) {
				$sql_criteria[] = 'si.industry_id = :industry_id';
			}
			$sql .= ' WHERE '.implode(' AND ', $sql_criteria);
		}

		$statement = $system->getPdo()->prepare($sql);
		
		if (isset ($criteria) && count ( $criteria ) > 0) {
			if (isset ($criteria['name']) ) {
				$statement->bindValue(':name', $criteria['name'].'%', PDO::PARAM_STR);
			}
			if (isset ($criteria['city']) ) {
				$statement->bindValue(':city', $criteria['city'], PDO::PARAM_STR);
			}
			if (isset ($criteria['industry_id']) ) {
				$statement->bindValue(':industry_id', $criteria['industry_id'], PDO::PARAM_INT);
			}
		}		
		$statement->execute();
		return $statement->fetchColumn();
	}
	
	public function getSocieties($criteria = NULL, $sort = 'Last created first', $offset = 0, $nb = NULL) {
		
		$output = array();
		
		foreach ( $this->getSocietiesData($criteria, $sort, $offset, $nb) as $data ) {
			$s = new Society ();
			$s->feed ( $data );
			$output[] = $s;
		}

		return $output;
	}
	/**
	 * Retourne le nombre de sociétés groupées par ville.
	 *
	 * @version 03/06/2017
	 */
	public function getSocietyCountByCity($nb = NULL, $offset = 0) {
		$sql = 'SELECT society_city AS city, COUNT(*) AS count FROM society';
		$sql .= ' GROUP BY city';
		$sql .= ' ORDER BY COUNT(*) DESC, city ASC';
		if (isset($nb))	{
			$sql .= ' LIMIT :offset, :nb';
		}
		$statement = $this->getPdo()->prepare($sql);
		if (isset($nb))	{
			$statement->bindValue(':offset', $offset, PDO::PARAM_INT);
			$statement->bindValue(':nb', $nb, PDO::PARAM_INT);
		}
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}	
	/**
	 * Renvoie les pistes correspondant à certains critères (optionnels)
	 *
	 * @return resource
	 * @version 03/06/2017
	 */
	public function getLeadsRowset($criteria = NULL, $sort = 'Last created first', $nb = NULL, $offset = 0) {
		global $system;
		
		$sql = 'SELECT * FROM lead AS l';
		$sql .= ' LEFT JOIN individual AS c USING (individual_id)';
		$sql .= ' LEFT JOIN society AS a USING (society_id)';
		if (count ( $criteria ) > 0) {
			$where = array();
			if ($criteria['type']) {
				$where[] = 'l.lead_type = :type';
			}
			if ($criteria['source']) {
				$where[] = 'l.lead_source = :source';
			}
			if ($criteria['status']) {
				$where[] = 'l.lead_status = :status';
			}
			$sql .= ' WHERE ' . implode ( ' AND ', $where );
		}
		if (isset($sort)) {
			switch ($sort) {
				case 'Last created first' :
					$sql .= ' ORDER BY l.lead_creation_date DESC';
					break;
			}
		}
		if (isset ( $nb )) {
			$sql .= ' LIMIT :offset, :nb';
		}		
		
		$statement = $system->getPdo()->prepare($sql);
		if (count ( $criteria ) > 0) {
			if ($criteria['type']) {
				$statement->bindValue(':type', $criteria['type'], PDO::PARAM_STR);
			}
			if ($criteria['source']) {
				$statement->bindValue(':source', $criteria['source'], PDO::PARAM_STR);
			}
			if ($criteria['status']) {
				$statement->bindValue(':status', $criteria['status'], PDO::PARAM_STR);
			}
		}
		if (isset ( $nb )) {
			$statement->bindValue(':offset', $offset, PDO::PARAM_INT);
			$statement->bindValue(':nb', $nb, PDO::PARAM_INT);
		}
		
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	 * Renvoie le nombre de piste correspondant éventuellement à certains critères.
	 *
	 * @return int
	 * @version 03/06/2017
	 */
	public function getLeadsNb($criteria = NULL) {
		global $system;
		
		$sql = 'SELECT COUNT(*) AS nb FROM lead AS l';
		//$sql .= ' LEFT JOIN individual AS c USING (individual_id)';
		//$sql .= ' LEFT JOIN society AS a USING (society_id)';
		if (count ( $criteria ) > 0) {
			$where = array();
			if ($criteria['type']) {
				$where[] = 'l.lead_type = :type';
			}
			if ($criteria['source']) {
				$where[] = 'l.lead_source = :source';
			}
			if ($criteria['status']) {
				$where[] = 'l.lead_status = :status';
			}
			$sql .= ' WHERE ' . implode ( ' AND ', $where );
		}
		
		$statement = $system->getPdo()->prepare($sql);
		if (count ( $criteria ) > 0) {
			if ($criteria['type']) {
				$statement->bindValue(':type', $criteria['type'], PDO::PARAM_STR);
			}
			if ($criteria['source']) {
				$statement->bindValue(':source', $criteria['source'], PDO::PARAM_STR);
			}
			if ($criteria['status']) {
				$statement->bindValue(':status', $criteria['status'], PDO::PARAM_STR);
			}
		}		
		$statement->execute();
		return $statement->fetchColumn();
	}
	/**
	 * Concerne les types de pistes, fusionne 2 catégories.
	 *
	 * @param string $type1
	 *        	Le type de référence à conserver
	 * @param string $type2
	 *        	Le type à faire disparaître
	 * @since 16/01/2006
	 */
	public function mergeLeadTypes($type1, $type2) {
		global $system;
		$statement = $system->getPdo()->prepare('UPDATE lead SET lead_type = :t1 WHERE lead_type = :t2');
		$statement->bindValue(':t1', $type1, PDO::PARAM_INT);
		$statement->bindValue(':t2', $type2, PDO::PARAM_INT);
		return $statement->execute();
	}
	/**
	 * Obtient la liste des activités.
	 *
	 * @return array
	 * @since 07/2006
	 * @version 08/2018
	 */
	public function getIndustries($criteria = NULL, $sort = 'Most used first') {
		global $system;

		$output = array ();

		$sql = 'SELECT i.*, COUNT(IF(si.society_id IS NOT NULL, 1, NULL)) AS societies_nb FROM industry AS i';
		$sql.= ' LEFT OUTER JOIN society_industry AS si ON (si.industry_id=i.id)';
		
		if (! is_null ( $criteria )) {
			$conditions = array();

			// sélection d'activités identifiées
			if ( isset($criteria['ids']) ) {
				$ids = array();
				foreach (array_keys($criteria['ids']) as $i) {
					$ids[':id'.$i] = $criteria['ids'][$i];
				}
				$conditions[] = 'i.id IN ('.implode(',', array_keys($ids)).')';
			}
			
			$sql.= ' WHERE '.implode ( ' AND ', $conditions );
		}
		
		$sql.= ' GROUP BY i.id ASC';
		
		switch ($sort) {
			case 'Most used first':
				$sql.= ' ORDER BY societies_nb DESC';
				break;
			default :
				$sql.= ' ORDER BY name ASC';
		}
		
		$statement = $system->getPdo()->prepare($sql);
		
		if (! is_null ( $criteria )) {
			if ( isset($criteria['ids']) ) {
				foreach ($ids as $key=>$value) {
					$statement->bindValue($key, $value, PDO::PARAM_INT);
				}
			}
		}
		
		$statement->execute();

		foreach ( $statement->fetchAll(PDO::FETCH_ASSOC) as $item ) {
			$i = new Industry();
			$i->feed($item);
			$output[$i->getId()] = $i;
		}
		return $output;
	}
	/**
	 * @since 05/2018
	 */
	public function getIndustryFromId($id) {
		global $system;

		$sql = 'SELECT * FROM industry WHERE id=?';
		$statement = $system->getPdo()->prepare($sql);
		$statement->execute(array($id));
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		if ($data) {
			$output = new Industry();
			$output->feed($data);
			return $output;
		}
	}	
	/**
	 * @since 05/2018
	 */
	public function getIndustryFromName($name) {
		global $system;

		$sql = 'SELECT * FROM industry WHERE name=?';
		$statement = $system->getPdo()->prepare($sql);
		$statement->execute(array($name));
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		if ($data) {
			$output = new Industry();
			$output->feed($data);
			return $output;
		}
	}
	/**
	 * Obtient la liste pondérée des dernières activités utilisées pour qualifier une société.
	 * 
	 * @since 02/2017
	 */
	public function getLastUsedIndustries($scope = 100) {
		global $system;

		$output = array ();
		$sql = 'SELECT i.id, i.name, COUNT(*) AS weight FROM (SELECT * FROM society_industry ORDER BY timestamp DESC LIMIT :scope) AS si';
		$sql.= ' INNER JOIN industry AS i ON (i.id = si.industry_id)';
		$sql.= ' GROUP BY i.name ASC, i.id';

		$statement = $system->getPdo()->prepare($sql);
		$statement->bindParam(':scope', $scope, PDO::PARAM_INT);
		$statement->execute();

		foreach ( $statement->fetchAll(PDO::FETCH_ASSOC) as $item ) {
			$i = new Industry ();
			$i->feed ( $item );
			$output [] = array ('industry' => $i, 'weight' => $item['weight'] );
		}
		return $output;
	}	
	/**
	 * Obtient la liste d'activités à partir de la liste de leur identifiant.
	 *
	 * @return array
	 * @param ids array
	 * @since 08/2006
	 * @version 12/2016
	 */
	public function getIndustriesFromIds($ids) {
		return $this->getIndustries(array('ids'=>$ids));
	}
	/**
	 * Rassemble plusieurs activités en une seule.
	 *
	 * @return Industry
	 * @since 08/2006
	 * @version 05/2018
	 */
	public function mergeIndustries($a, $b) {
		try {
			if (! is_a ($a, 'Industry') || ! is_a($b, 'Industry')) throw new Exception ('Pour fusionner 2 activités, il faut désigner 2 activités');
			if (empty($a->getId()) || empty($b->getId())) throw new Exception ('Les 2 activités doivent être identifiées.');
			if ($a->getId() == $b->getId()) throw new Exception ('On fusionne 2 activités différentes !');

			if ($b->getSocietiesNb() > $a->getSocietiesNb()) {
				return $a->transferSocieties ($b) ? $a->delete() : false;
			} else {
				return $b->transferSocieties($a) ? $b->delete() : false;
			}			
		} catch (Exception $e) {
			$system->reportException($e);
			exit;
		}
	}
	/**
	 * Obtient la liste des activités enregistrées sous forme de tags HTML 'option'.
	 *
	 * @since 16/07/2006
	 * @version 03/06/2017
	 */
	public function getIndustryOptionsTags($toSelect = NULL) {
		global $system;

		$sql = 'SELECT i.id, i.name, COUNT(IF(si.society_id IS NOT NULL, 1, NULL)) AS societies_nb';
		$sql.= ' FROM industry AS i LEFT OUTER JOIN society_industry AS si';
		$sql.= ' ON ( si.industry_id = i.id )';
		$sql.= ' GROUP BY i.name ASC, i.id';
		
		$html = '';
		
		foreach  ($system->getPdo()->query($sql, PDO::FETCH_ASSOC) as $row) {
			$i = new Industry ();
			$i->feed ( $row );
			$html .= '<option value="' . $i->getId () . '"';
			if ( !empty($toSelect) ){
				if ( (is_array ( $toSelect ) && in_array ( $i->getId (), $toSelect )) || (is_string($toSelect) && strcmp($toSelect, $i->getId())==0) ) {
					$html .= ' selected="selected"';
				}
			}
			$html .= '>' . ToolBox::toHtml(ucfirst($i->getName())) . ' (' . $i->getSocietiesNb () . ')</option>';
		}
		
		return $html;
	}
	/**
	 * @since 08/2014
	 * @version 07/2018
	 */
	public function reportException(Exception $e, $comment=null) {
		$toDisplay = $e->getMessage();
		if (!empty($comment)) {
			$toDisplay.= ' ('.$comment.')';
		}
		// echo '<p>' . ToolBox::toHtml ( $toDisplay ) . '</p>';
		trigger_error($toDisplay);
		//error_log( $toDisplay );
	}
	/**
	 * Obtient la liste des derniers évènements enregistrés à l'historique.
	 * @version 06/2017
	 */
	public function getLastHistoryEvents($nb=3) {
		$criteria = array();
		$criteria['warehouse'] = 'history';
		return self::getEvents($criteria, 'Last created first', $nb);
	}
	/**
	 * @since 05/2018
	 */
	public function getLastUpdatedIndividualCollection($nb=3) {
		$statement = $this->getIndividualCollectionStatement(null, 'Last updated first',0 ,$nb);
		return new IndividualCollection($statement);
	}	
	/**
	 * Obtient la liste des prochains évènements enregistrés au planning.
	 * @version 01/06/2017 
	 */
	public function getNextPlanningEvents($nb=7) {
		$criteria = array();
		$criteria['warehouse'] = 'planning';
		return $this->getEvents($criteria, 'Last created first', $nb);		
	}
	/**
	 * @since 01/06/2017
	 */
	public function getEvents($criteria = NULL, $sort = 'Last created first', $nb = NULL, $offset = 0) {
		$output = array();
		foreach ( $this->getEventsData($criteria, $sort, $nb, $offset) as $data ) {
			$e = new Event ();
			$e->feed ( $data );
			$output[] = $e;
		}
		return $output;
	}
	/**
	 * @version 01/06/2017
	 */	
	private function getEventsData($criteria=null, $sort='Last created first', $nb = NULL, $offset = 0) {
		try {
			$fields = array();
			$fields[] = 't1.id';
			$fields[] = 't1.society_id';
			//$fields[] = 't1.user_id';
			$fields[] = 't1.user_position';
			$fields[] = 't1.media';
			$fields[] = 't1.type';
			$fields[] = 't1.datetime';
			$fields[] = 'DATE_FORMAT(t1.datetime, "%d/%m/%Y") as event_datetime_fr';
			$fields[] = 't1.comment';
			$fields[] = 't2.society_name';
			$sql = 'SELECT '.implode(',', $fields).' FROM event AS t1 LEFT JOIN society AS t2 USING (society_id)';
			
			if (isset ($criteria) && count ( $criteria ) > 0) {
				$sql_criteria = array();
				if (isset ($criteria['society_id']) ) {
					$sql_criteria[] = 't1.society_id = :society_id';
				}				
				if (isset ($criteria['warehouse']) ) {
					$sql_criteria[] = 't1.warehouse = :warehouse';
				}
				$sql .= ' WHERE '.implode(' AND ', $sql_criteria);
			}
			switch ($sort) {
				case 'Last created first' :
					$sql .= ' ORDER BY t1.datetime DESC';
					break;
				case 'First created first' :
					$sql .= ' ORDER BY t1.datetime ASC';
					break;
			}
	
			if (isset ( $nb )) {
				$sql .= ' LIMIT :offset, :nb';
			}

			$statement = $this->getPdo()->prepare($sql);
			
			if (isset ($criteria) && count ( $criteria ) > 0) {
				if (isset ($criteria['society_id']) ) {
					$statement->bindValue(':society_id', $criteria['society_id'], PDO::PARAM_INT);
				}
				if (isset ($criteria['warehouse']) ) {
					$statement->bindValue(':warehouse', $criteria['warehouse'], PDO::PARAM_STR);
				}
			}
			if (isset ( $nb )) {
				$statement->bindValue(':offset', $offset, PDO::PARAM_INT);
				$statement->bindValue(':nb', $nb, PDO::PARAM_INT);
			}
			
			$statement->execute();
			return $statement->fetchAll(PDO::FETCH_ASSOC);

		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}	
}
?>