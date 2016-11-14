<?php
/**
 * @package usocrate.exomemory.vostok
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
	public $init_date;	//	la date marquant le début de la participation
	public $end_date;	//	la date marquant la fin de la participation
	
	public function __construct($id=NULL) {
		if (isset($id)) $this->id = $id;
	}
	/**
	 * Fixe la valeur d'un attribut.
	 * @since 28/01/2006 
	 * @version 04/03/2006
	 */
	public function setAttribute($name, $value)	{
		$value = trim($value);
		$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		return $this->{$name} = $value;
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
	public function setId($input)	{
		return $this->setAttribute('id', $input);
	}
	/**
	 * Renvoie la fonction exercée dans le cadre de cette participation.
	 * @return string
	 */
	public function getTitle() {
		return $this->getAttribute('title');
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
		$sql.= ' GROUP BY title ORDER BY count DESC';
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
	public static function knownTitlesToJson($substring = NULL){
		$output = '{"titles":[';
		$items = self::getKnownTitles($substring);
		for ($i=0; $i<count($items); $i++) {
			$output.= '{"value":'.json_encode($items[$i]['value']).',"count":'.$items[$i]['count'].'}';
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
	public function getPhone(){
		return $this->getAttribute('phone');
	}	
	/**
	 * Renvoie le service dans lequel se situe cette participation.
	 * @return string
	 */
	public function getDepartment(){
		return $this->getAttribute('department');
	}
	/**
	 * @since 27/10/2012
	 */
	private static function getKnownDepartments($substring = NULL) {
		global $system;
		$sql = 'SELECT department AS value, COUNT(*) AS count FROM membership';
		$sql.= ' WHERE department IS NOT NULL';
		if (isset($substring)) {
			$sql.= ' AND department LIKE :pattern';
		}
		$sql.= ' GROUP BY department ORDER BY count DESC';
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
	public static function knownDepartmentsToJson($substring = NULL){
		$output = '{"departments":[';
		$items = self::getKnownDepartments($substring);
		for ($i=0; $i<count($items); $i++) {
			$output.= '{"value":'.json_encode($items[$i]['value']).',"count":'.$items[$i]['count'].'}';
			if ($i<count($items)-1) {
				$output.= ',';
			}
		}
		$output.= ']}';
		return $output;
	}
	/**
	 * Renvoie la personne impliquée.
	 * 
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
	 * Renvoie l'id de la personne impliquée.
	 */
	public function getIndividualId() {
		return isset($this->individual) ? $this->individual->getId() : NULL;
	}
	/**
	 * Fixe la personne impliquée.
	 * @param Individual $input
	 */
	public function setIndividual($input)	{
		if (is_a($input, 'Individual')) $this->individual = $input;
	}
	/**
	 * Renvoie la société concernée.
	 * @return Society|NULL
	 */
	public function getSociety()
	{
		if (isset($this->society)){
			return $this->society;
		} elseif ($this->getAttribute('society_id')) {
			$this->society = new Society($this->getAttribute('society_id'));
			return $this->society;
		}
		return NULL;
	}
	/**
	 * Fixe la société concernée.
	 * @param Society $input
	 * @version 09/04/2006	 
	 */	
	public function setSociety($input)
	{
		if (is_a($input, 'Society')) $this->society = $input;
	}	
	/**
	 * Obtient l'Url décrivant la participation de la personne.
	 * @since 07/01/2006
	 */
	public function getUrl()
	{
		return isset($this->url) ? $this->url : NULL;
	}
	/**
	 * Obtient un lien HTML vers un contenu web décrivant la participation. 
	 * @since 07/12/2006
	 */
	public function getWebHtmlLink()
	{
		return $this->getUrl() ? '<a href="'.$this->getUrl().'" title="'.$this->getUrl().'">[web]</a>' : NULL;	
	}	
	/**
	 * Enregistre en base de données les attributs de la participation.
	 */
	public function toDB()
	{
		//print_r($this);
		$new = empty ($this->id);
		// settings
		$settings = array ();
		if (isset($this->individual) && $this->individual->getId()) $settings[] = 'individual_id='.$this->individual->getId();
		if (isset($this->society) && $this->society->getId()) $settings[] = 'society_id='.$this->society->getId();
		if (isset($this->title)) $settings[] = 'title="'.mysql_real_escape_string($this->title).'"';
		if (isset($this->department)) $settings[] = 'department="'.mysql_real_escape_string($this->department).'"';
		if (isset($this->phone)) $settings[] = 'phone="'.mysql_real_escape_string($this->phone).'"';
		if (isset($this->email)) $settings[] = 'email="'.mysql_real_escape_string($this->email).'"';
		if (isset($this->url)) $settings[] = 'url="'.mysql_real_escape_string($this->url).'"';
		if (isset($this->description)) $settings[] = 'description="'.mysql_real_escape_string($this->description).'"';
		if (isset($this->init_date)) $settings[] = 'init_date="'.mysql_real_escape_string($this->init_date).'"';
		if (isset($this->end_date)) $settings[] = 'end_date="'.mysql_real_escape_string($this->end_date).'"';
		//	INSERT or UPDATE ?
		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql.= ' membership SET ';
		$sql.= implode(', ', $settings);
		if (!$new) $sql .= ' WHERE membership_id='.$this->id;
		
		$result = mysql_query($sql);
		if ($new) $this->id = mysql_insert_id();
		return $result;
	}
	/**
	 * Supprime la participation en base de données.
	 * 
	 * @return boolean
	 */
	public function delete() {
		if (empty($this->id)) return false;
		$sql = 'DELETE FROM membership';
		$sql.= ' WHERE membership_id='.$this->id;
		
		return mysql_query($sql);	
	}
	public function feed($array=NULL, $prefix=NULL)	{
		//print_r($array);
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
					case 'membership_id': $this->setId($value); break;
					case 'individual_id':
						$this->setIndividual(new Individual($value));
						break;
					case 'society_id':
						$this->setSociety(new Society($value));
						break;
					case 'title': $this->setAttribute('title', $value); break;
					case 'department': $this->setAttribute('department', $value); break;
					case 'phone': $this->setAttribute('phone', $value); break;
					case 'email': $this->setEmail($value); break;
					case 'url': $this->setAttribute('url', $value); break;
					case 'description': $this->setAttribute('description', $value); break;
					case 'init_date': $this->setAttribute('init_date', $value); break;
					case 'end_date': $this->setAttribute('end_date', $value); break;
				}
			}
			//print_r($this);
			return true;
		} elseif (isset($this->id)) {
			//	on ne transmet pas les données de l'initialisation
			//	mais on connaît l'identifiant de la participation
			$sql = 'SELECT * FROM membership WHERE membership_id='.$this->id;
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