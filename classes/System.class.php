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
	public function getDataDirPath() {
		return $this->dir_path . DIRECTORY_SEPARATOR . 'data';
	}
	public function getCvDirPath() {
		return $this->getDataDirPath () . DIRECTORY_SEPARATOR . 'cv';
	}
	public function getTrombiDirPath() {
		return $this->getDataDirPath () . DIRECTORY_SEPARATOR . 'trombinoscope';
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
			self::reportException ( $e );
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
		return '<a href="' . $system->getAppliUrl () . '">' . ToolBox::html5entities ( $system->getAppliName () ) . '</a>';
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
			self::reportException ( $e );
		}
	}
	/**
	 *
	 * @since 04/08/2014
	 */
	public static function getIndividualCollectionStatement($criteria = NULL, $sort = 'Name', $offset = 0, $count = NULL) {
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
			
			if (count ( $where ) > 0) {
				$sql .= ' WHERE ' . implode ( ' AND ', $where );
			}
			
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
			self::reportException ( $e );
		}
	}
	/**
	 *
	 * @since 04/08/2014
	 * @version 15/12/2016
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
			self::reportException ( $e );
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
			System::reportException ( $e );
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
			System::reportException ( $e );
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
			$statement = self::getIndividualCollectionStatement ( $criteria, $sort_key, $sort_order );
			return new IndividualCollection ( $statement );
		} catch ( Exception $e ) {
			self::reportException ( $e );
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
		$sql .= ' GROUP BY s.society_id';
		
		switch ($sort) {
			case 'Last created first' :
				$sql .= ' ORDER BY society_creation_timestamp DESC';
				break;
			default :
				$sql .= ' ORDER BY s.society_name ASC';
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
	 * @return resource
	 * @version 09/2005
	 */
	public function getSocietiesGroupByCityRowset($offset = 0, $row_count = NULL) {
		$sql = 'SELECT society_city AS city, COUNT(*) AS nb';
		$sql .= ' FROM society';
		// $sql.= ' WHERE society_city IS NOT NULL';
		$sql .= ' GROUP BY city';
		$sql .= ' ORDER BY nb DESC, city ASC';
		if (isset ( $count_row ))
			$sql .= ' LIMIT ' . $offset . ',' . $count_row;
		
		return mysql_query ( $sql );
	}
	/**
	 * Renvoie les pistes correspondant à certains critères (optionnels)
	 *
	 * @return resource
	 */
	public function getLeadsRowset($criterias, $sort_key = 'lead_creation_date', $sort_order = 'DESC', $offset = 0, $nb = NULL) {
		$sql = 'SELECT *';
		$sql .= ' FROM lead AS l';
		$sql .= ' LEFT JOIN individual AS c ON l.individual_id=c.individual_id';
		$sql .= ' LEFT JOIN society AS a ON l.society_id=a.society_id';
		if (count ( $criterias ) > 0) {
			$sql .= ' WHERE ' . implode ( ' AND ', $criterias );
		}
		$sql .= ' ORDER BY ' . $sort_key . ' ' . $sort_order;
		if (isset ( $nb ))
			$sql .= ' LIMIT ' . $offset . ',' . $nb;
		
		return mysql_query ( $sql );
	}
	/**
	 * Renvoie le nombre de piste correspondant éventuellement à certains critères.
	 *
	 * @return int
	 * @version 10/2005
	 */
	public function getLeadsNb($criterias = NULL) {
		$sql = 'SELECT COUNT(*) AS nb';
		$sql .= ' FROM lead AS l';
		$sql .= ' LEFT JOIN individual AS c ON l.individual_id=c.individual_id';
		$sql .= ' LEFT JOIN society AS a ON l.society_id=a.society_id';
		if (count ( $criterias ) > 0) {
			$sql .= ' WHERE ' . implode ( ' AND ', $criterias );
		}
		
		$rowset = mysql_query ( $sql );
		$row = mysql_fetch_assoc ( $rowset );
		mysql_free_result ( $rowset );
		return $row ? $row ['nb'] : 0;
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
	 * @since /6/07/2006
	 * @version 23/12/2016
	 */
	public function getIndustries($criteria = NULL) {
		global $system;

		$output = array ();

		$sql = 'SELECT i.*, COUNT(IF(si.society_id IS NOT NULL, 1, NULL)) AS industry_societies_nb';
		$sql .= ' FROM industry AS i LEFT OUTER JOIN society_industry AS si ON (si.industry_id=i.industry_id)';
		if (! is_null ( $criteria )) {
			$conditions = array();

			// sélection d'activités identifiées
			if ( isset($criteria['ids']) ) {
				$ids = array();
				foreach (array_keys($criteria['ids']) as $i) {
					$ids[':id'.$i] = $criteria['ids'][$i];
				}
				$conditions[] = 'i.industry_id IN ('.implode(',', array_keys($ids)).')';
			}
			
			$sql .= ' WHERE '.implode ( ' AND ', $conditions );
		}
		$sql .= ' GROUP BY i.industry_name ASC';
		
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
			$i = new Industry ();
			$i->feed ( $item );
			$output [] = $i;
		}
		return $output;
	}
	/**
	 * @since 09/02/2017
	 */
	public function getLastUsedIndustries() {
		global $system;

		$output = array ();
		$sql = 'SELECT i.*, COUNT(*) AS weight FROM (SELECT * FROM society_industry ORDER BY timestamp DESC LIMIT 100) AS si';
		$sql.= ' INNER JOIN industry AS i USING (industry_id)';
		$sql.= ' GROUP BY i.industry_name ASC';
		//echo $sql;
		
		$statement = $system->getPdo()->prepare($sql);
		$statement->execute();

		foreach ( $statement->fetchAll(PDO::FETCH_ASSOC) as $item ) {
			$i = new Industry ();
			$i->feed ( $item );
			$output [] = array($i, $item['weight']);
		}
		return $output;
	}	
	/**
	 * Obtient la liste d'activités à partir de la liste de leur identifiant.
	 *
	 * @return array
	 * @param ids array
	 * @since 19/08/2006
	 * @version 23/12/2016
	 */
	public function getIndustriesFromIds($ids) {
		return $this->getIndustries(array('ids'=>$ids));
	}
	/**
	 * Rassemble plusieurs activités en une seule.
	 *
	 * @return Industry
	 * @since 19/08/2006
	 * @version 23/12/2016
	 */
	public function mergeIndustries($a, $b) {
		try {
			if (! is_a ( $a, 'Industry' ) || ! is_a ( $b, 'Industry' )) throw new Exception ('Pour fusionner 2 activités, il faut désigner 2 activités');

			if ($b->getSocietiesNb () > $a->getSocietiesNb ()) {
				return $a->transferSocieties ( $b ) ? $a->delete() : false;
			} else {
				return $b->transferSocieties ( $a ) ? $b->delete() : false;
			}			
		} catch (Exception $e) {
			System::reportException($e);
			exit;
		}
	}
	/**
	 * Obtient la liste des activités enregistrées sous forme de tags HTML 'option'.
	 *
	 * @since 16/07/2006
	 * @version 22/12/2016
	 */
	public function getIndustryOptionsTags($toSelect = NULL) {
		global $system;

		$sql = 'SELECT i.*, COUNT(IF(si.society_id IS NOT NULL, 1, NULL)) AS industry_societies_nb';
		$sql .= ' FROM industry AS i LEFT OUTER JOIN society_industry AS si';
		$sql .= ' ON(si.industry_id=i.industry_id)';
		$sql .= ' GROUP BY i.industry_name ASC';
		$sql .= ' ORDER BY i.industry_name ASC';
		
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
	 * Informe d'une exception.
	 *
	 * @since 04/08/2014
	 */
	public static function reportException(Exception $e) {
		// echo '<p>' . ToolBox::toHtml ( $e->getMessage () ) . '</p>';
		// error_log ( $e->getMessage () );
		trigger_error ( $e->getMessage () );
	}
}
?>