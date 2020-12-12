<?php
/**
 * @package usocrate.vostok
 * @author Florent Chanavat
 */
class Membership {
	public $id;
	public $society;
	public $individual;
	public $title;
	public $department;
	public $phone;
	public $email;
	public $url;
	public $description;
	public $init_year;
	public $end_year;
	
	public function __construct($id=NULL) {
		if (isset($id)) $this->id = $id;
	}
	/**
	 * Fixe la valeur d'un attribut.
	 * @since 01/2006 
	 * @version 03/2006
	 */
	public function setAttribute($name, $value)	{
		$value = trim($value);
		$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		return $this->{$name} = $value;
	}
	/**
	 * @since 05/2018
	 */
	public function setTitle($input)	{
		return $this->title = $input;
	}
	/**
	 * @since 05/2018
	 */
	public function setDepartment($input)	{
		return $this->department = $input;
	}
	/**
	 * @since 05/2018
	 */
	public function setDescription($input)	{
		return $this->description = $input;
	}	
	/**
	 * @version 06/2020
	 * @since 01/2017
	 */
	public function setInitYear($input) {
		if ( is_numeric($input) && strlen($input)==4 ) {
			$this->init_year = $input;
		} elseif (empty($input)) {
			$this->init_year = '';
		} else {
		    return false;
		}
	}
	/**
	 * @since 03/2019
	 * @return boolean
	 */
	public function getInitYear() {
	    return $this->init_year;
	}
	/**
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasInitYear() {
	    return !empty($this->init_year);
	}
	/**
	 * @version 06/2020
	 * @since 01/2017
	 **/	
	public function setEndYear($input) {
		if ( is_numeric($input) && strlen($input)==4 ) {
			$this->end_year = $input;
		} elseif (empty($input)) {
			$this->end_year = '';
		} else {
			return false;
		}
	}
	/**
	 * @since 03/2019
	 * @return boolean
	 */
	public function getEndYear() {
	    return $this->end_year;
	}
	/**
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasEndYear() {
	    return !empty($this->end_year);
	}
	public function getAttribute($name)	{
		return isset($this->$name) ? $this->$name : NULL;
	}
	/**
	 * Obtient l'identifiant de la participation.
	 * @return int
	 */	
	public function getId()	{
		return $this->getAttribute('id');
	}
	/**
	 * Fixe l'identifiant de la participation.
	 * @param int $input
	 * @since 19/11/2005
	 */
	public function setId($input) {
		return $this->setAttribute('id', $input);
	}
	/**
	 * @since 12/2018
	 */
	public function hasId() {
		return isset($this->id);
	}
	/**
	 * Renvoie la fonction exercée dans le cadre de cette participation.
	 * @return string
	 */
	public function getTitle() {
		return $this->getAttribute('title');
	}
	/**
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasTitle() {
	    return !empty($this->title);
	}
	/**
	 * @since 27/10/2012
	 */
	private static function getKnownTitles($substring = NULL) {
		global $system;
		$sql = 'SELECT title AS value, COUNT(*) AS count FROM membership';
		$sql.= ' WHERE title IS NOT NULL';
		if (isset($substring)) {
			$sql.= ' AND title LIKE :pattern';
		}
		$sql.= ' GROUP BY title ORDER BY COUNT(*) DESC';
		$statement = $system->getPdo()->prepare($sql);
		if (isset($substring)) {
			$statement->bindValue(':pattern', '%'.$substring.'%', PDO::PARAM_STR);
		}
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	 * @since 10/2012
	 */
	public static function knownTitlesToJson($substring = NULL) {
		$output = '{"titles":[';
		$items = self::getKnownTitles($substring);
		for ($i=0; $i<count($items); $i++) {
			$output.= '{"value":'.ucfirst(json_encode($items[$i]['value'])).',"count":'.$items[$i]['count'].'}';
			if ($i<count($items)-1) {
				$output.= ',';
			}
		}
		$output.= ']}';
		return $output;
	}
	/**
	 * Renvoie la description de cette participation.
	 * @return string
	 */
	public function getDescription() {
		return $this->getAttribute('description');
	}
	/**
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasDescription() {
	    return !empty($this->description);
	}
	/**
	 * @since 01/2017
	 */
	public function getPeriod() {
		$p = new Period($this->init_year, $this->end_year);
		return ucfirst($p->toString());
	}
	/**
	 * Renvoie l'email utilisé dans le cadre de cette participation.
	 * @return string
	 * @since 21/01/2006
	 */
	public function getEmail() {
		return $this->getAttribute('email');
	}
	public function setEmail($input) {
		return $this->setAttribute('email', strtolower($input));
	}	
	/**
	 * Renvoie le numéro de téléphone utilisé dans le cadre de cette participation.
	 * @return string
	 */
	public function getPhone() {
		return $this->getAttribute('phone');
	}	
	/**
	 * Renvoie le service dans lequel se situe cette participation.
	 * @return string
	 */
	public function getDepartment() {
		return $this->getAttribute('department');
	}
	/**
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasDepartment() {
	    return !empty($this->department);
	}
	/**
	 * @since 27/10/2012
	 */
	private static function getKnownDepartments($substring = NULL) {
		global $system;
		$sql = 'SELECT department AS value, COUNT(*) AS count FROM membership WHERE department IS NOT NULL';
		if (isset($substring)) {
			$sql.= ' AND department LIKE :pattern';
		}
		$sql.= ' GROUP BY department ORDER BY COUNT(*) DESC';
		$statement = $system->getPdo()->prepare($sql);
		if (isset($substring)) {
			$statement->bindValue(':pattern', '%'.$substring.'%', PDO::PARAM_STR);
		}
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	 * @since 27/10/2012
	 */
	public static function knownDepartmentsToJson($substring = NULL) {
		$output = '{"departments":[';
		$items = self::getKnownDepartments($substring);
		for ($i=0; $i<count($items); $i++) {
			$output.= '{"value":'.ucfirst(json_encode($items[$i]['value'])).',"count":'.$items[$i]['count'].'}';
			if ($i<count($items)-1) {
				$output.= ',';
			}
		}
		$output.= ']}';
		return $output;
	}
	/**
	 * Renvoie la personne impliquée.
	 * @version 03/2017
	 * @return Individual|NULL
	 */
	public function getIndividual()	{
		if (isset($this->individual)) {
			return $this->individual;
		} elseif ($this->getAttribute('individual_id')) {
			$this->individual = new Individual($this->getAttribute('individual_id'));
			return $this->individual;
		}
		return NULL;
	}
	/**
	 * @since 03/2020
	 */
	public function feedIndividual() {
	    if (isset($this->individual)) {
	        return $this->individual->feed();
	    }
	}
	/**
	 * Renvoie l'id de la personne impliquée.
	 */
	public function getIndividualId() {
		return isset($this->individual) ? $this->individual->getId() : NULL;
	}
	/**
	 * Fixe la personne impliquée.
	 * @param Individual $input
	 */
	public function setIndividual($input) {
		if (is_a($input, 'Individual')) $this->individual = $input;
	}
	/**
	 * Renvoie la société concernée.
	 */
	public function getSociety() {
		if (isset($this->society)) {
			return $this->society;
		} elseif ($this->getAttribute('society_id')) {
			$this->society = new Society($this->getAttribute('society_id'));
			return $this->society;
		}
		return NULL;
	}
	/**
	 * @since 03/2020
	 */
	public function feedSociety() {
	    if (isset($this->society)) {
	        return $this->society->feed();
	    }
	}
	/**
	 * Fixe la société concernée.
	 * @param Society $input
	 * @version 04/2006	 
	 */	
	public function setSociety($input) {
		if (is_a($input, 'Society')) $this->society = $input;
	}
	/**
	 * @since 12/2018
	 */
	public function isSocietyIdentified() {
		return isset($this->society) && !empty($this->society->getId());
	}
	/**
	 * @since 12/2018
	 */
	public function isIndividualIdentified() {
		return isset($this->individual) && !empty($this->individual->getId());
	}	
	/**
	 * Obtient l'Url décrivant la participation de la personne.
	 * @since 01/2006
	 */
	public function getUrl() {
		return isset($this->url) ? $this->url : NULL;
	}
	/**
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasUrl() {
	    return !empty($this->url);
	}
	/**
	 * @since 08/2018
	 */
	public function setUrl($input) {
		$this->url = $input;
	}	
	/**
	 * Obtient un lien HTML vers un contenu web décrivant la participation. 
	 * @since 12/2006
	 */
	public function getHtmlLinkToWeb() {
		return $this->getUrl() ? '<a href="'.$this->getUrl().'" title="'.$this->getUrl().'">[web]</a>' : NULL;	
	}
	/**
	 * @since 12/2018
	 */
	public function getHtmlLinkToIndividual($mode = 'normal') {
		if ($this->isIndividualIdentified()) {
			return $this->individual->getHtmlLinkToIndividual($mode);
		}
	}
	/**
	 * @since 12/2018
	 */
	public function getHtmlLinkToSociety() {
		if ($this->isSocietyIdentified()) {
			return $this->society->getHtmlLinkToSociety();
		}
	}	
	/**
	 * Enregistre en base de données les attributs de la participation.
	 * @version 13/01/2017
	 */
	public function toDB() {
		global $system;
		$new = empty ($this->id);

		$settings = array ();
		if ( isset($this->individual) && $this->individual->getId() ) 
			$settings[] = 'individual_id=:individual_id';
		if ( isset($this->society) && $this->society->getId() )
			$settings[] = 'society_id=:society_id';
		if ( isset($this->title) ) 
			$settings[] = 'title=:title';
		if ( isset($this->department) ) 
			$settings[] = 'department=:department';
		if ( isset($this->phone) ) 
			$settings[] = 'phone=:phone';
		if ( isset($this->email) ) 
			$settings[] = 'email=:email';
		if ( isset($this->url) ) 
			$settings[] = 'url=:url';
		if ( isset($this->description) ) 
			$settings[] = 'description=:description';
		if ( isset($this->init_year) ) 
			$settings[] = 'init_year=:init_year';
		if ( isset($this->end_year) ) 
			$settings[] = 'end_year=:end_year';
		
		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql.= ' membership SET ';
		$sql.= implode(', ', $settings);
		if (!$new)
			$sql .= ' WHERE membership_id=:membership_id';
		
		$statement = $system->getPdo()->prepare($sql);
		if (isset($this->individual) && $this->individual->getId() ) 
			$statement->bindValue(':individual_id', $this->individual->getId(), PDO::PARAM_INT);
		if (isset($this->society) && $this->society->getId()) 
			$statement->bindValue(':society_id', $this->society->getId(), PDO::PARAM_INT);
		if (isset($this->title))
			$statement->bindValue(':title', $this->title, PDO::PARAM_STR);
		if (isset($this->department))
			$statement->bindValue(':department', $this->department, PDO::PARAM_STR);
		if (isset($this->phone))
			$statement->bindValue(':phone', $this->phone, PDO::PARAM_STR);
		if (isset($this->email))
			$statement->bindValue(':email', $this->email, PDO::PARAM_STR);
		if (isset($this->url)) 
			$statement->bindValue(':url', $this->url, PDO::PARAM_STR);
		if (isset($this->description))
			$statement->bindValue(':description', $this->description, PDO::PARAM_STR);
		if (isset($this->init_year)) {
			empty($this->init_year) ? $statement->bindValue(':init_year', NULL, PDO::PARAM_NULL) : $statement->bindValue(':init_year', $this->init_year, PDO::PARAM_INT);
		}
		if (isset($this->end_year)) {
			empty($this->end_year) ? $statement->bindValue(':end_year', NULL, PDO::PARAM_NULL) : $statement->bindValue(':end_year', $this->end_year, PDO::PARAM_INT);
		}
		if (!$new) {
			$statement->bindValue(':membership_id', $this->id, PDO::PARAM_INT);
		}
		$result = $statement->execute();
		if ($new) $this->id = $system->getPdo()->lastInsertId();
		return $result;
	}
	/**
	 * Supprime la participation en base de données.
	 * 
	 * @return boolean
	 * @version 13/01/2017
	 */
	public function delete() {
		global $system;
		if (empty($this->id)) return false;
		$statement = $system->getPdo()->prepare('DELETE FROM membership WHERE membership_id=:id');
		$statement->bindValue(':id', $this->id, PDO::PARAM_STR);
		return $statement->execute();
	}
	public function feed($array=NULL, $prefix=NULL)	{
		if (is_array($array)) {
			//	les données de l'initialisation sont transmises
			foreach ($array as $key=>$value){
				if (is_null($value)) continue;
				if (isset($prefix)) {
					//	on ne traite que les clés avec le préfixe spécifié
					if (strcmp(iconv_substr($key, 0, iconv_strlen($prefix)), $prefix)!=0) continue;
					//	on retire le préfixe
					$key = iconv_substr($key, iconv_strlen($prefix));
				}				
				switch ($key) {
					case 'membership_id':
						$this->setId($value);
						break;
					case 'individual_id':
						$this->setIndividual(new Individual($value));
						break;
					case 'society_id':
						$this->setSociety(new Society($value));
						break;
					case 'title':
						$this->setAttribute('title', $value);
						break;
					case 'department':
						$this->setAttribute('department', $value);
						break;
					case 'phone':
						$this->setAttribute('phone', $value);
						break;
					case 'email':
						$this->setEmail($value);
						break;
					case 'url':
						$this->setAttribute('url', $value);
						break;
					case 'description':
						$this->setAttribute('description', $value);
						break;
					case 'init_year':
						$this->setInitYear($value);
						break;
					case 'end_year':
						$this->setEndYear($value);
						break;
				}
			}
			return true;
		} elseif (isset($this->id)) {
			//	on ne transmet pas les données de l'initialisation mais on connaît l'identifiant de la participation
			global $system;
			$statement = $system->getPdo()->prepare('SELECT * FROM membership WHERE membership_id=:id');
			$statement->bindValue(':id', $this->id, PDO::PARAM_STR);
			$statement->execute();
			$data = $statement->fetch(PDO::FETCH_ASSOC);
			return $data ? $this->feed($data) : false;
		}
		return false;
	}
}
?>