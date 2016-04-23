<?php
/**
 * @package usocrate.exomemory.vostok
 * @author Florent Chanavat
 */
class System {
	private $db_host;
	private $db_name;
	private $db_user;
	private $db_password;
	private $pdo;
	
	/**
	 *
	 * @version 04/08/2014
	 */
	public function __construct($db_host, $db_name, $db_user, $db_password) {
		$this->db_host = $db_host;
		$this->db_name = $db_name;
		$this->db_user = $db_user;
		$this->db_password = $db_password;
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
		return '<a href="' . APPLI_URL . '">' . ToolBox::html5entities ( APPLI_NAME ) . '</a>';
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
			foreach ($system->getPdo()->query($sql) as $data) {
				$u = new User ();
				$u->feed ( $data);
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
	public static function getIndividualCollectionStatement($criteria = NULL, $sort_key = 'individual_lastName', $sort_order = 'ASC', $offset = 0, $count = NULL) {
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
			
			$sql .= ' ORDER BY ' . $sort_key . ' ' . $sort_order;
			
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
	 */
	public static function getAloneIndividualCollectionStatement($criteria = NULL, $sort_key = 'individual_lastName', $sort_order = 'ASC', $offset = 0, $count = NULL) {
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
			$sql .= ' ORDER BY ' . $sort_key . ' ' . $sort_order;
			
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
	 * @return resource
	 */
	public function getSocietiesRowset($criterias = NULL, $sort_key = 'society_name', $sort_order = 'ASC', $offset = 0, $nb = NULL) {
		$sql = 'SELECT s.*';
		$sql .= ' ,DATE_FORMAT(s.society_creation_date, "%d/%m/%Y") AS society_creation_date_fr';
		$sql .= ' ,UNIX_TIMESTAMP(s.society_creation_date) AS society_creation_timestamp';
		$sql .= ' FROM society AS s LEFT OUTER JOIN society_industry AS si ON(si.society_id=s.society_id)';
		if (count ( $criterias ) > 0) {
			$sql .= ' WHERE ' . implode ( ' AND ', $criterias );
		}
		$sql .= ' GROUP BY s.society_id';
		$sql .= ' ORDER BY ' . $sort_key . ' ' . $sort_order;
		if (isset ( $nb ))
			$sql .= ' LIMIT ' . $offset . ',' . $nb;
		
		return mysql_query ( $sql );
	}
	/**
	 * Renvoie le nombre de sociétés (avec critères éventuels)
	 *
	 * @return int
	 * @version 10/2005
	 */
	public function getSocietiesNb($criterias = NULL) {
		$sql = 'SELECT COUNT(DISTINCT s.society_id)';
		$sql .= ' FROM society AS s LEFT OUTER JOIN society_industry AS si ON(si.society_id=s.society_id)';
		if (count ( $criterias ) > 0) {
			$sql .= ' WHERE ' . implode ( ' AND ', $criterias );
		}
		
		$rowset = mysql_query ( $sql );
		$row = mysql_fetch_row ( $rowset );
		return $row [0];
	}
	public function getSocieties() {
		if (! isset ( $this->societies )) {
			$this->societies = array ();
			$rowset = $this->getSocietiesRowset ();
			while ( $row = mysql_fetch_assoc ( $rowset ) ) {
				$society = new Society ();
				$society->feed ( $row );
				$this->societies [] = $society;
			}
		}
		return $this->societies;
	}
	public function getSocietiesOptionsTags($selectedValue = NULL) {
		$this->getSocieties ();
		$html = '';
		foreach ( $this->societies as $society ) {
			$html .= '<option value="' . $society->getId () . '"';
			if (strcmp ( $society->getId (), $selectedValue ) == 0)
				$html .= ' selected="selected"';
			$html .= '>' . $society->name . '</option>';
		}
		return $html;
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
	 * Retourne le nombre de sociétés groupées par activité.
	 * 
	 * @return resource
	 * @version 09/2005
	 */
	public function getSocietiesGroupByIndustryRowset($offset = 0, $row_count = NULL) {
		$sql = 'SELECT society_industry AS industry, COUNT(*) AS nb';
		$sql .= ' FROM society';
		// $sql.= ' WHERE society_industry IS NOT NULL';
		$sql .= ' GROUP BY industry';
		$sql .= ' ORDER BY nb DESC, industry ASC';
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
	 *  	Le type de référence à conserver
	 * @param string $type2
	 *  	Le type à faire disparaître
	 * @since 16/01/2006
	 */
	public function mergeLeadTypes($type1, $type2) {
		$sql = 'UPDATE lead SET lead_type="' . mysql_real_escape_string ( $type1 ) . '" WHERE lead_type="' . mysql_real_escape_string ( $type2 ) . '"';
		
		return mysql_query ( $sql );
	}
	/**
	 * Obtient les enregistrements des activités.
	 * 
	 * @return resource
	 * @since 16/07/2006
	 * @version 19/08/2006
	 */
	public function getIndustriesRowset($criterias = NULL) {
		$sql = 'SELECT i.*';
		$sql .= ', COUNT(IF(si.society_id IS NOT NULL, 1, NULL)) AS industry_societies_nb';
		$sql .= ' FROM industry AS i LEFT OUTER JOIN society_industry AS si';
		$sql .= ' ON(si.industry_id=i.industry_id)';
		if (! is_null ( $criterias )) {
			$sql .= ' WHERE ' . implode ( ' AND ', $criterias );
		}
		$sql .= ' GROUP BY i.industry_name ASC';
		$sql .= ' ORDER BY i.industry_name ASC';
		
		return mysql_query ( $sql );
	}
	/**
	 * Obtient la liste des activités.
	 * 
	 * @return array
	 * @since 16/07/2006
	 * @version 19/08/2006
	 */
	public function getIndustries($criterias = NULL) {
		$output = array ();
		$rowset = $this->getIndustriesRowset ( $criterias );
		while ( $row = mysql_fetch_array ( $rowset ) ) {
			$i = new Industry ();
			$i->feed ( $row );
			$output [] = $i;
		}
		return $output;
	}
	/**
	 * Obtient la liste d'activités à partir de la liste de leur identifiant.
	 * 
	 * @return array
	 * @param
	 *  	ids array
	 * @since 19/08/2006
	 */
	public function getIndustriesFromIds($ids) {
		if (! is_array ( $ids ) || count ( $ids ) < 1)
			return NULL;
		$criterias = array (
				'i.industry_id IN(' . implode ( ',', $ids ) . ')' 
		);
		return $this->getIndustries ( $criterias );
	}
	/**
	 * Rassemble plusieurs activités en une seule.
	 * 
	 * @return Industry
	 * @since 19/08/2006
	 */
	public function mergeIndustries($a, $b) {
		if (! is_a ( $a, 'Industry' ) || ! is_a ( $b, 'Industry' )) {
			trigger_error ( 'Tentative de fusion d\'activité en échec' );
			return false;
		}
		if ($b->getSocietiesNb () > $a->getSocietiesNb ()) {
			$a->transferSocieties ( $b );
			$a->delete ();
			return $b;
		} else {
			$b->transferSocieties ( $a );
			$b->delete ();
			return $a;
		}
	}
	/**
	 * Obtient la liste des activités enregistrées sous forme de tags HTML 'option'.
	 * 
	 * @since 16/07/2006
	 * @version 29/10/2013
	 */
	public function getIndustryOptionsTags($idsToSelect = NULL) {
		if (is_array ( $idsToSelect ) === false && empty ( $idsToSelect ) === false) {
			$idsToSelect = array (
					$idsToSelect 
			);
		}
		$rowset = $this->getIndustriesRowset ();
		$html = '';
		while ( $row = mysql_fetch_assoc ( $rowset ) ) {
			$i = new Industry ();
			$i->feed ( $row );
			$html .= '<option value="' . $i->getId () . '"';
			if (is_array ( $idsToSelect ) && in_array ( $i->getId (), $idsToSelect )) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . ToolBox::toHtml ( $i->getName () ) . ' (' . $i->getSocietiesNb () . ')</option>';
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