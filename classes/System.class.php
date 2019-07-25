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
	 * @version 08/2014
	 * @return string
	 */
	public function getHtmlLink() {
		return '<a href="' . $this->getAppliUrl () . '">' . ToolBox::toHtml( $this->getAppliName() ) . '</a>';
	}
	
	public function getHtmlHeadTagsForFavicon() {
		$output = array();
		$output[] = '<link rel="icon" type="image/png" sizes="32x32" href="'.$this->getSkinUrl().'images/favicon-32x32.png">';
		$output[] = '<link rel="icon" type="image/png" sizes="16x16" href="'.$this->getSkinUrl().'images/favicon-16x16.png">';
		$output[] = '<link rel="manifest" href="'.$this->getSkinUrl().'manifest.json">';
		$output[] = '<meta name="application-name" content="'.ToolBox::toHtml( $this->getAppliName() ).'">';
		$output[] = '<meta name="theme-color" content="#da8055">';
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
	 * @since 02/2006
	 * @version 08/2014
	 */
	public function getUsers() {
		try {
			$output = array ();
			$sql = 'SELECT * FROM user';
			foreach ( $this->getPdo ()->query ( $sql ) as $data ) {
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
	 * @version 11/2018
	 */
	public function getIndividualCollectionStatement($criteria = NULL, $sort = 'Name', $offset = 0, $count = NULL) {
		try {
			$wholeNameSqlSelectPattern = 'IF((individual_lastname IS NOT NULL AND individual_firstname IS NOT NULL), CONCAT(individual_firstname, " ", individual_lastName), IF(individual_lastname IS NOT NULL, individual_lastname, individual_firstname))';
			$sql = 'SELECT *,DATE_FORMAT(individual_creation_date, "%d/%m/%Y") AS individual_creation_date_fr';
			$sql .= ' FROM individual';
			
			// WHERE
			$where = array ();
			
			if (isset ( $criteria ['individual_id'] )) {
				$where [] = 'individual_id = :id';
			}
			
			if (isset ( $criteria ['individual_lastname_like_pattern'] )) {
				$where [] = 'individual_lastname LIKE :lastname_like';
			}
			
			if (isset ( $criteria ['individual_wholename_like_pattern'] )) {
				$where [] = $wholeNameSqlSelectPattern.' LIKE :wholename_like';
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
			
			$statement = $this->getPdo ()->prepare ( $sql );
			
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
			if (isset ( $criteria ['individual_wholename_like_pattern'] )) {
				$statement->bindValue ( ':wholename_like', '%' . $criteria ['individual_wholename_like_pattern'] . '%', PDO::PARAM_STR );
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

			if (isset ( $criteria ['title'] )) {
				$where [] = 'm.title = :title';
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
				case 'Alphabetical' :
					$sql .= ' ORDER BY i.individual_lastName ASC, i.individual_firstName ASC';
					break;
			}

			// LIMIT
			if (isset ( $count )) {
				$sql .= isset ( $offset ) ? ' LIMIT :offset,:count' : ' LIMIT :count';
			}			
			
			$statement = $this->getPdo()->prepare($sql);

			if (isset ($criteria ['individual_id'])) {
				$statement->bindValue(':individual_id', $criteria['individual_id'], PDO::PARAM_INT);
			}
			if (isset ($criteria ['society_id'])) {
				$statement->bindValue(':society_id', $criteria['society_id'], PDO::PARAM_INT);
			}
			if (isset ($criteria ['title'])) {
				$statement->bindValue(':title', $criteria['title'], PDO::PARAM_STR);
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
	 * @since 10/2018
	 */	
	public function getMembershipHavingThatTitle($title) {
		$criteria = array('title'=>$title);
		return $this->getMemberships($criteria, 'Alphabetical');
	}
	/**
	 * @since 12/2018
	 */
	public function getMembershipTitles($sort = 'Alphabetical') {
		try {
			$sql = 'SELECT title, COUNT(*) AS nb FROM membership WHERE title IS NOT NULL GROUP BY title';

			// ORDER
			switch ($sort) {
				case 'Most used first' : 
					$sql .= ' ORDER BY nb DESC';
					break;
				case 'Alphabetical' :
					$sql .= ' ORDER BY title ASC';
					break;
			}

			$statement = $this->getPdo()->prepare($sql);

			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$statement->execute();

			$output = array();
			foreach ( $statement->fetchAll() as $data ) {
				$output[] = array('label' => $data['title'], 'count' => $data['nb']);
			}
			return $output;			
		} catch (Exception $e) {
			$this->reportException($e, __METHOD__);
		}
	}
	/**
	 * @since 02/2019
	 */
	public function getMembershipTitleAverageDuration($title) {
		try {
			$sql = 'SELECT ROUND(AVG(CAST(end_year AS UNSIGNED)-CAST(init_year AS UNSIGNED)+1)) AS avgDuration, COUNT(*) AS nb FROM membership WHERE init_year IS NOT NULL AND end_year IS NOT NULL AND title=:title GROUP BY title';

			$statement = $this->getPdo()->prepare($sql);

			$statement->bindValue(':title', $title, PDO::PARAM_STR);
			
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$statement->execute();

			$output = array();
			$data = $statement->fetch();
			return $data ? array('avg' => $data['avgDuration'], 'count' => $data['nb']) : null;
		} catch (Exception $e) {
			$this->reportException($e, __METHOD__);
		}		
	}
	/**
	 * @since 08/2014
	 * @version 12/2016
	 */
	public static function getAloneIndividualCollectionStatement($criteria = NULL, $sort = 'Name', $offset = 0, $count = NULL) {
		try {
			$wholeNameSqlSelectPattern = 'IF((i.individual_lastname IS NOT NULL AND i.individual_firstname IS NOT NULL), CONCAT(i.individual_firstname, " ", i.individual_lastName), IF(i.individual_lastname IS NOT NULL, i.individual_lastname, i.individual_firstname))';
			$sql = 'SELECT i.*, DATE_FORMAT(individual_creation_date, "%d/%m/%Y") AS individual_creation_date_fr';
			$sql .= ' FROM individual AS i LEFT OUTER JOIN membership AS ms ON ms.individual_id = i.individual_id';
			
			// WHERE
			$where = array ();
			
			if (isset ( $criteria ['individual_wholename_like_pattern'] )) {
				$where [] = $wholeNameSqlSelectPattern.' LIKE :wholename_like';
			}
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
			
			$statement = $this->getPdo ()->prepare ( $sql );
			
			/*
			 * Rattachement des variables
			 */
			if (isset ( $criteria ['individual_wholename_like_pattern'] )) {
				$statement->bindValue ( ':wholename_like', '%' . $criteria ['individual_wholename_like_pattern'] . '%', PDO::PARAM_STR );
			}
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
	 * @since 08/2014
	 * @version 11/2018
	 */
	public function countIndividuals($criteria) {
		try {
			$wholeNameSqlSelectPattern = 'IF((individual_lastname IS NOT NULL AND individual_firstname IS NOT NULL), CONCAT(individual_firstname, " ", individual_lastName), IF(individual_lastname IS NOT NULL, individual_lastname, individual_firstname))';
			$sql = 'SELECT COUNT(*) FROM individual';
			
			$where = array ();

			if (isset ( $criteria ['individual_wholename_like_pattern'] )) {
				$where [] = $wholeNameSqlSelectPattern.' LIKE :wholename_like';
			}

			if (isset ( $criteria ['individual_lastname_like_pattern'] )) {
				$where [] = 'individual_lastname LIKE :lastname_like';
			}
			
			if (count ( $where ) > 0) {
				$sql .= ' WHERE ' . implode ( ' AND ', $where );
			}
			
			$statement = $this->getPdo ()->prepare ( $sql );
			
			if (isset ( $criteria ['individual_wholename_like_pattern'] )) {
				$statement->bindValue ( ':wholename_like', '%' . $criteria ['individual_wholename_like_pattern'] . '%', PDO::PARAM_STR );
			}
			if (isset ( $criteria ['individual_lastname_like_pattern'] )) {
				$statement->bindValue ( ':lastname_like', '%' . $criteria ['individual_lastname_like_pattern'] . '%', PDO::PARAM_STR );
			}
			
			$statement->execute ();
			return $statement->fetchColumn ();
		} catch ( Exception $e ) {
			$this->reportException($e);
		}
	}
	/**
	 * @since 08/2014
	 */
	public function countAloneIndividuals($criteria) {
		try {
			$c = new IndividualCollection ( self::getAloneIndividualCollectionStatement ( $criteria ) );
			return $c->getSize ();
		} catch ( Exception $e ) {
			$this->reportException($e);
		}
	}
	/**
	 * Obtient les personnes dont la date d'anniversaire est proche de la date courante.
	 *
	 * @since 12/2006
	 * @version 08/2014
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
			$this->reportException($e);
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
			$this->reportException($e);
		}
	}
	/**
	 * Renvoie les enregistrements des sociétés (avec critères éventuels).
	 *
	 * @version 12/2016
	 */
	private function getSocietiesData($criteria = NULL, $sort = 'Last created first', $offset = 0, $nb = NULL) {
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
		$statement = $this->getPdo()->prepare($sql);
		
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
	 * @version 11/2016
	 */
	public function getSocietiesNb($criteria = NULL) {

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

		$statement = $this->getPdo()->prepare($sql);
		
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
	 * @version 06/2017
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
	 * @since 07/2019
	 */
	public function getSocietiesHavingThatRole($role) {
	    $sql = 'SELECT DISTINCT(t.id), t.name FROM ';
	    $sql.= '(';
	    $sql.= 'SELECT s.society_id AS id, s.society_name AS name FROM relationship AS r INNER JOIN society AS s ON(s.society_id = r.item0_id) WHERE r.item0_class=:item0_class AND r.item0_role=:item0_role';
	    $sql.= ' UNION ';
	    $sql.= 'SELECT s.society_id AS id, s.society_name AS name FROM relationship AS r INNER JOIN society AS s ON(s.society_id = r.item1_id) WHERE item1_class=:item1_class AND r.item1_role=:item1_role';
	    $sql.= ') AS t';
	    $sql.= ' ORDER BY t.name ASC';
	    $statement = $this->getPdo()->prepare($sql);
	    $statement->bindValue(':item0_role', $role, PDO::PARAM_STR);
	    $statement->bindValue(':item0_class', 'society', PDO::PARAM_STR);
	    $statement->bindValue(':item1_role', $role, PDO::PARAM_STR);
	    $statement->bindValue(':item1_class', 'society', PDO::PARAM_STR);
	    $statement->execute();
	    $output = array();
	    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) AS $i) {
	        $s = new Society();
	        $s->setId($i['id']);
	        $s->setName($i['name']);
	        $output[$i['id']] = $s;
	    }
	    return $output;
	}
	/**
	 * @since 02/2019
	 */
	public function getEntities($criteria = NULL, $sort = 'Alphabetical', $offset = 0, $nb = NULL) {
	
		if (isset ($criteria) && count ( $criteria ) > 0) {
			
			$society_criteria = array();
			$individual_criteria = array();
			
			if (isset ($criteria['name substring']) ) {
				$society_criteria[] = 'society_name LIKE :society_name_substring';
				$individual_criteria[] = '(individual_lastName LIKE :individual_lastName_firstLetters OR individual_firstName LIKE :individual_firstName_firstLetters)';
			}
		}

		$sql = '(SELECT society_id AS id, society_name AS name, \'society\' AS type FROM society WHERE '.implode(' AND ', $society_criteria).')';
		$individualWholeNameSqlSelectPattern = 'IF((individual_lastname IS NOT NULL AND individual_firstname IS NOT NULL), CONCAT(individual_firstname, " ", individual_lastName), IF(individual_lastname IS NOT NULL, individual_lastname, individual_firstname))';
		$sql.= ' UNION (SELECT individual_id AS id, '.$individualWholeNameSqlSelectPattern.' AS name, \'individual\' AS type FROM individual WHERE '.implode(' AND ', $individual_criteria).')';

		switch ($sort) {
			default :
				$sql .= ' ORDER BY name ASC';
		}

		if (isset ( $nb )) {
			$sql .= ' LIMIT :offset, :nb';
		}
		//echo $sql;
		$statement = $this->getPdo()->prepare($sql);
		
		if (isset ($criteria) && count ( $criteria ) > 0) {
			if (isset ($criteria['name substring']) ) {
				$statement->bindValue(':society_name_substring', '%'.$criteria['name substring'].'%', PDO::PARAM_STR);
				$statement->bindValue(':individual_lastName_firstLetters', $criteria['name substring'].'%', PDO::PARAM_STR);
				$statement->bindValue(':individual_firstName_firstLetters', $criteria['name substring'].'%', PDO::PARAM_STR);
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
	 * Renvoie les pistes correspondant à certains critères (optionnels)
	 *
	 * @version 06/2017
	 */
	public function getLeadsRowset($criteria = NULL, $sort = 'Last created first', $nb = NULL, $offset = 0) {
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
		
		$statement = $this->getPdo()->prepare($sql);
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
	 * @version 06/2017
	 */
	public function getLeadsNb($criteria = NULL) {

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
		
		$statement = $this->getPdo()->prepare($sql);
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
	 * @since 01/2006
	 */
	public function mergeLeadTypes($type1, $type2) {
		$statement = $this->getPdo()->prepare('UPDATE lead SET lead_type = :t1 WHERE lead_type = :t2');
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
			case 'Alphabetical':
			    $sql.= ' ORDER BY name ASC';
		}
		
		$statement = $this->getPdo()->prepare($sql);
		
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

		$sql = 'SELECT * FROM industry WHERE id=?';
		$statement = $this->getPdo()->prepare($sql);
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

		$sql = 'SELECT * FROM industry WHERE name=?';
		$statement = $this->getPdo()->prepare($sql);
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

		$output = array ();
		$sql = 'SELECT i.id, i.name, COUNT(*) AS weight FROM (SELECT * FROM society_industry ORDER BY timestamp DESC LIMIT :scope) AS si';
		$sql.= ' INNER JOIN industry AS i ON (i.id = si.industry_id)';
		$sql.= ' GROUP BY i.name ASC, i.id';

		$statement = $this->getPdo()->prepare($sql);
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
	 * @since 03/2019
	 */
	public function getMainIndustryMinWeight() {
	    $sql = 'SELECT ROUND(AVG(inventory.weight) + STD(inventory.weight)) AS minWeight FROM (SELECT industry_id, COUNT(*) AS weight FROM society_industry GROUP BY industry_id) AS inventory';
	    $statement = $this->getPdo()->prepare($sql);
	    $statement->execute();
	    return $statement->fetchColumn();
	}
	/**
	 * @since 03/2019
	 */
	public function getNotMarginalIndustryMinWeight() {
	    $sql = 'SELECT ROUND(AVG(inventory.weight) - STD(inventory.weight)) AS minWeight FROM (SELECT industry_id, COUNT(*) AS weight FROM society_industry GROUP BY industry_id) AS inventory';
	    $statement = $this->getPdo()->prepare($sql);
	    $statement->execute();
	    return $statement->fetchColumn();
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
			$this->reportException($e);
			exit;
		}
	}
	/**
	 * Obtient la liste des activités enregistrées sous forme de tags HTML 'option'.
	 *
	 * @since 07/2006
	 * @version 06/2017
	 */
	public function getIndustryOptionsTags($toSelect = NULL) {

		$sql = 'SELECT i.id, i.name, COUNT(IF(si.society_id IS NOT NULL, 1, NULL)) AS societies_nb';
		$sql.= ' FROM industry AS i LEFT OUTER JOIN society_industry AS si';
		$sql.= ' ON ( si.industry_id = i.id )';
		$sql.= ' GROUP BY i.name ASC, i.id';
		
		$html = '';
		
		foreach  ($this->getPdo()->query($sql, PDO::FETCH_ASSOC) as $row) {
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