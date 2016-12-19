<?php
/**
 * @package usocrate.exomemory.vostok
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
	/**
	 * la date marquant le début de la relation.
	 * @type date
	 */
	private $init_date;
	/**
	 * la date marquant la fin de la relation.
	 * @type date
	 */
	private $end_date;
	
	public function __construct($id=NULL)	{
		if (isset($id)) $this->id = $id;
	}
	/**
	 * Fixe la valeur d'un attribut.
	 */
	public function setAttribute($name, $value)	{
		$value = trim($value);
		$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		return $this->{$name} = $value;
	}
	/*
	 * Fixe la valeur d'un attribut.	
	 * @since 30/03/2006	
	 */
	public function getAttribute($name)
	{
		return isset($this->$name) ? $this->$name : NULL;
	}
	/**
	 * Obtient l'identifiant de la relation.
	 * @return int
	 * @since 30/03/2006	 
	 */	
	public function getId()
	{
		return $this->getAttribute('id');
	}
	/**
	 * Fixe l'identifiant de la relation.
	 * @param int $input
	 * @since 30/03/2006
	 */
	public function setId($input)
	{
		return $this->setAttribute('id', $input);
	}
	/**
	 * Fixe un item.
	 * @param object $input
	 * @since 30/03/2006	 
	 */	
	public function setItem($input, $rang)
	{
		if (is_object($input) && in_array(get_class($input), $this->item_class_options)) {
			$this->items[$rang] = $input;
		}
	}
	/**
	 * Fixe le rôle d'un item.
	 * @param string $input
	 * @since 09/04/2006	 
	 */	
	public function setItemRole($input, $rang)
	{
		$this->items_roles[$rang] = $input;
	}	
	/**
	 * Obtient le role d'un item.
	 * @since 09/04/2006
	 */	
	public function getItemRole($rang)
	{
		return isset($this->items_roles[$rang]) ? $this->items_roles[$rang] : NULL;
	}
	public function getItem($rang)
	{
		return isset($this->items[$rang]) ? $this->items[$rang] : NULL;
	}
	public static function getKnownRoles($substring = NULL)
	{
		global $system;
		if (isset ( $substring )) {
			$sql = 'SELECT DISTINCT(role) FROM (SELECT item0_role AS role FROM relationship WHERE item0_role LIKE :item0_role_pattern UNION SELECT item1_role AS role FROM relationship WHERE item1_role LIKE :item1_role_pattern) AS t ORDER BY role';
		} else {
			$sql = 'SELECT DISTINCT(role) FROM (SELECT item0_role AS role FROM relationship UNION SELECT item1_role AS role FROM relationship) AS t ORDER BY role';
		}
		$statement = $system->getPDO()->prepare($sql);
		if (isset ( $substring )) {
		    $statement->bindValue(':item0_role_pattern', '%'.$substring.'%', PDO::PARAM_STR);
		    $statement->bindValue(':item1_role_pattern', '%'.$substring.'%', PDO::PARAM_STR);
		}
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_COLUMN);
	}
	public static function knownRolesToJson($substring = NULL) {
		$output = '{"roles":' . json_encode ( self::getKnownRoles ( $substring ) ) . '}';
		return $output;
	}	
	/**
	 * Obtient l'Url décrivant la relation.
	 * @since 30/03/2006
	 */
	public function getUrl()
	{
		return isset($this->url) ? $this->url : NULL;
	}
	/**
	 * Enregistre en base de données les valeurs des attributs de la relation.
	 * @since 30/03/2006	 
	 */
	public function toDB()
	{
		//print_r($this);
		$new = empty ($this->id);
		
		// settings
		$settings = array ();
		
		for ($i=0; $i<count($this->items); $i++) {
			$settings[] = 'item'.$i.'_class="'.get_class($this->items[$i]).'"';
			if ($this->items[$i]->getId()) $settings[] = 'item'.$i.'_id='.$this->items[$i]->getId();
			if (isset($this->items_roles[$i])) $settings[] = 'item'.$i.'_role="'.$this->items_roles[$i].'"';
		}
		
		if (isset($this->description)) $settings[] = 'description="'.mysql_real_escape_string($this->description).'"';
		if (isset($this->url)) $settings[] = 'url="'.mysql_real_escape_string($this->url).'"';
		if (isset($this->init_date)) $settings[] = 'init_date="'.mysql_real_escape_string($this->init_date).'"';
		if (isset($this->end_date)) $settings[] = 'end_date="'.mysql_real_escape_string($this->end_date).'"';
		
		//	INSERT or UPDATE ?
		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql.= ' relationship SET ';
		$sql.= implode(', ', $settings);
		if (!$new) $sql .= ' WHERE relationship_id='.$this->id;
		
		$result = mysql_query($sql);
		if ($new) $this->id = mysql_insert_id();
		return $result;
	}
	/**
	 * Supprime la relation en base de données.
	 * @return boolean
	 * @since 30/03/2006
	 */
	public function delete()
	{
		global $system;
		if (empty($this->id)) return false;
		$statement = $system->getPDO()->prepare('DELETE FROM relationship WHERE relationship_id=:id');
		$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		return $statement->execute();
	}
	/**
	 * Fixe les valeurs des attributs de la relation à partir d'un tableau dont les clefs sont normalisées.
	 * @since 30/03/2006	 
	 */
	public function feed($array=NULL)
	{
		//print_r($array);
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
				/*
				if (isset($prefix)) {
					//	on ne traite que les clés avec le préfixe spécifié
					if (strcmp(iconv_substr($key, 0, iconv_strlen($prefix)), $prefix)!=0) continue;
					//	on retire le préfixe
					$key = iconv_substr($key, iconv_strlen($prefix));
				}
				*/				
				switch ($key) {
					case 'relationship_id': $this->setId($value); break;
					case 'description': $this->setAttribute('description', $value); break;
					case 'url': $this->setAttribute('url', $value); break;					
					case 'init_date': $this->setAttribute('init_date', $value); break;
					case 'end_date': $this->setAttribute('end_date', $value); break;
				}
			}
			//print_r($this);
			return true;
		} elseif (isset($this->id)) {
			//	on ne transmet pas les données de l'initialisation
			//	mais on connaît l'identifiant de la relation
			$sql = 'SELECT * FROM relationship WHERE relationship_id='.$this->id;
			//echo $sql.'<br/>';
			$rowset = mysql_query($sql);
			$row = mysql_fetch_array($rowset);
			mysql_free_result($rowset);
			if (!$row) return false;
			return $this->feed($row);
		}
		return false;
	}
}
?>