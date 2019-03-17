<?php
/**
 * @package usocrate.vostok
 * @author Florent Chanavat
 * @since 30/03/2006 
 */
class Relationship {
	private $id;
	/**
	 * Les items à mettre en relation.
	 * @type array
	 */
	private $items;
	/**
	 * Les classes autorisées pour un item.
	 */
	private $item_class_options = array('Individual', 'Society');	
	/**
	 * Les roles des items dans la relation.	
	 * @type array
	 */	
	private $items_roles;
	private $description;
	private $url;

	private $init_year;
	private $end_year;
	
	public function __construct($id=NULL)	{
		if (isset($id)) $this->id = $id;
	}
	/**
	 * Fixe la valeur d'un attribut.
	 */
	public function setAttribute($name, $value)	{
		$value = trim($value);
		$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		switch($name) {
			default:
				return $this->{$name} = $value;		
		}
	}
	/*
	 * Fixe la valeur d'un attribut.	
	 * @since 03/2006	
	 */
	public function getAttribute($name)	{
		return isset($this->$name) ? $this->$name : NULL;
	}
	/**
	 * Obtient l'identifiant de la relation.
	 * @return int
	 * @since 03/2006	 
	 */	
	public function getId()	{
		return $this->getAttribute('id');
	}
	/**
	 * Fixe l'identifiant de la relation.
	 * @param int $input
	 * @since 03/2006
	 */
	public function setId($input) {
		return $this->setAttribute('id', $input);
	}
	/**
	 * @since 03/2019
	 */
	public function getInitYear()	{
		return $this->getAttribute('init_year');
	}
	/**
	 * @since 03/2019
	 */	
	public function setInitYear($input) {
		return $this->setAttribute('init_year', $input);
	}	
	/**
	 * @since 03/2019
	 */
	public function getEndYear()	{
		return $this->getAttribute('end_year');
	}
	/**
	 * @since 03/2019
	 */	
	public function setEndYear($input) {
		return $this->setAttribute('end_year', $input);
	}	
	/**
	 * Obtient l'Url décrivant la relation.
	 * @since 03/2019
	 */
	public function getDescription() {
		return $this->getAttribute('description');
	}
	/**
	 * @since 03/2019
	 */	
	public function setDescription($input) {
		return $this->setAttribute('description', $input);
	}	
	/**
	 * Fixe un item.
	 * @param object $input
	 * @since 03/2006	 
	 */	
	public function setItem($input, $rang) {
		if (is_object($input) && in_array(get_class($input), $this->item_class_options)) {
			$this->items[$rang] = $input;
			return true;
		}
		return false;
	}
	/**
	 * @since 03/2019
	 */
	public function areItemsBothKnown() {
		return isset($this->items) && count($this->items)==2 && $this->items[0]->hasId() && $this->items[1]->hasId();
	}
	/**
	 * @since 03/2019
	 */	
	public function isItemKnown($rang) {
		return isset($this->items[$rang]) && $this->items[$rang]->hasId();
	}	
	/**
	 * Fixe le rôle d'un item.
	 * @param string $input
	 * @since 04/2006	 
	 */	
	public function setItemRole($input, $rang) {
		$this->items_roles[$rang] = $input;
	}	
	/**
	 * Obtient le role d'un item.
	 * @since 04/2006
	 */	
	public function getItemRole($rang) {
		return isset($this->items_roles[$rang]) ? $this->items_roles[$rang] : NULL;
	}
	public function getItem($rang) {
		return isset($this->items[$rang]) ? $this->items[$rang] : NULL;
	}
	/**
	 * @version 07/2018
	 */
	public static function getKnownRoles($substring = null, $type = null)	{
		global $system;

		if (isset($type)) {
			switch($type) {
				case 'societyRole':
					$classFilter = 'society';
					break;
				case 'individualRole':
					$classFilter = 'individual';
					break;					
			}
		}
		$sql = 'SELECT DISTINCT(role) FROM (SELECT item0_role AS role FROM relationship';
		$where = array();
		if (!empty($substring)) {
			$where[] = 'item0_role LIKE :item0_role_pattern';
		}
		if (isset($classFilter)) {
			$where[] = 'item0_class = :item0_class';
		}
		if (count($where)>0) {
			$sql.= ' WHERE '.implode(' AND ', $where);
		}
		$sql.= ' UNION SELECT item1_role AS role FROM relationship';
		$where = array();
		if (!empty($substring)) {
			$where[] = 'item1_role LIKE :item1_role_pattern';
		}
		if (isset($classFilter)) {
			$where[] = 'item1_class = :item1_class';
		}
		if (count($where)>0) {
			$sql.= ' WHERE '.implode(' AND ', $where);
		}		
		$sql.= ') AS t ORDER BY role';
		$statement = $system->getPDO()->prepare($sql);
		if (!empty($substring)) {
			$statement->bindValue(':item0_role_pattern', '%'.$substring.'%', PDO::PARAM_STR);
		    $statement->bindValue(':item1_role_pattern', '%'.$substring.'%', PDO::PARAM_STR);
		}
		if (isset($classFilter)) {
			$statement->bindValue(':item0_class', $classFilter, PDO::PARAM_STR);
			$statement->bindValue(':item1_class', $classFilter, PDO::PARAM_STR);
		}		    
		
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_COLUMN);
	}
	public static function knownRolesToJson($substring = null, $roleType = null) {
		$output = '{"roles":' . json_encode ( self::getKnownRoles ( $substring, $roleType ) ) . '}';
		return $output;
	}	
	/**
	 * Obtient l'Url décrivant la relation.
	 * @since 03/2006
	 */
	public function getUrl() {
		return $this->getAttribute('url');
	}
	/**
	 * Enregistre en base de données les valeurs des attributs de la relation.
	 * @since 03/2006
	 * @version 06/2017
	 */
	public function toDB() {
		global $system;
		
		try {
			$new = empty ($this->id);
			
			if (empty($this->items) && empty($this->items_roles)) {
				throw new Exception(__METHOD__.' : Les 2 termes de la relation doivent être connus.');
			}
			
			$settings = array ();
			
			$settings[] = 'item0_id=:item0_id';
			$settings[] = 'item0_class=:item0_class';
			$settings[] = 'item0_role=:item0_role';
			$settings[] = 'item1_id=:item1_id';
			$settings[] = 'item1_class=:item1_class';
			$settings[] = 'item1_role=:item1_role';

			if (isset($this->description)) {
				$settings[] = 'description=:description';
			}
			if (isset($this->url)) {
				$settings[] = 'url=:url';
			}
			if (isset($this->init_year)) {
				$settings[] = 'init_year=:init_year';
			}
			if (isset($this->end_year)) {
				$settings[] = 'end_year=:end_year';
			}
			
			$sql = $new ? 'INSERT INTO' : 'UPDATE';
			$sql.= ' relationship SET ';
			$sql.= implode(', ', $settings);
			if (!$new) {
				$sql .= ' WHERE relationship_id=:id';
			}
			$statement = $system->getPdo()->prepare($sql);			
			$statement->bindValue(':item0_id', $this->items[0]->getId(), PDO::PARAM_INT);
			$statement->bindValue(':item0_class', get_class($this->items[0]), PDO::PARAM_STR);
			$statement->bindValue(':item0_role', $this->items_roles[0], PDO::PARAM_STR);
			$statement->bindValue(':item1_id', $this->items[1]->getId(), PDO::PARAM_INT);
			$statement->bindValue(':item1_class', get_class($this->items[1]), PDO::PARAM_STR);
			$statement->bindValue(':item1_role', $this->items_roles[1], PDO::PARAM_STR);
			if (isset($this->description)) {
				$statement->bindValue(':description', $this->description, PDO::PARAM_STR);
			}
			if (isset($this->url)) {
				$statement->bindValue(':url', $this->url, PDO::PARAM_STR);
			}
			if (isset($this->init_year)) {
				empty($this->init_year) ? $statement->bindValue(':init_year', NULL, PDO::PARAM_NULL) : $statement->bindValue(':init_year', $this->init_year, PDO::PARAM_INT);
			}
			if (isset($this->end_year)) {
				empty($this->end_year) ? $statement->bindValue(':end_year', NULL, PDO::PARAM_NULL) : $statement->bindValue(':end_year', $this->end_year, PDO::PARAM_INT);
			}
			if (!$new) {
				$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			}
			$result = $statement->execute();
			if ($new) {
				$this->id = $system->getPdo()->lastInsertId();
			}
			return $result;			
		}
		catch (Exception $e) {
			$system->reportException($e);
		}
	}
	/**
	 * Supprime la relation en base de données.
	 * @return boolean
	 * @since 30/03/2006
	 */
	public function delete() {
		global $system;
		if (empty($this->id)) return false;
		$statement = $system->getPDO()->prepare('DELETE FROM relationship WHERE relationship_id=:id');
		$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		return $statement->execute();
	}
	/**
	 * Fixe les valeurs des attributs de la relation à partir d'un tableau dont les clefs sont normalisées.
	 * @since 03/2006
	 * @version 12/2016
	 */
	public function feed($array = NULL) {
		global $system;

		if (is_array($array)) {
			//	les données de l'initialisation sont transmises
			$keys = array_keys($array);
				
			// implémentation items
			for ($i=0; $i<2; $i++) {
				if (in_array("item{$i}_class", $keys) && isset($array["item{$i}_class"])) {
					switch ($array["item{$i}_class"]) {
						case 'Society' : 
							$this->setItem(new Society(), $i);
							break;
						case 'Individual' : 
							$this->setItem(new Individual(), $i);
							break;						
					}
					if (isset($array["item{$i}_id"])) $this->items{$i}->setId($array["item{$i}_id"]);
					if (isset($array["item{$i}_role"])) {
						if (is_null($this->items_roles)) {
							$this->items_roles = array();
						}
						$this->items_roles[$i] = $array["item{$i}_role"];
					}
				}
			}
			foreach ($array as $key=>$value) {
				if (is_null($value)) continue;
				switch ($key) {
					case 'relationship_id': $this->setId($value); break;
					case 'description': $this->setAttribute('description', $value); break;
					case 'url': $this->setAttribute('url', $value); break;					
					case 'init_year': $this->setAttribute('init_year', $value); break;
					case 'end_year': $this->setAttribute('end_year', $value); break;
				}
			}
			return true;
		} elseif (isset($this->id)) {
			//	on ne transmet pas les données de l'initialisation mais on connaît l'identifiant de la relation
			$statement = $system->getPdo()->prepare('SELECT * FROM relationship WHERE relationship_id=:id');
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			$statement->execute();
			$data = $statement->fetch(PDO::FETCH_ASSOC);
			if (!$data) return false;
			return $this->feed($data);
		}
		return false;
	}
}
?>