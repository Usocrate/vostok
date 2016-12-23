<?php
/**
 * @package usocrate.exomemory.vostok
 * @author Florent Chanavat
 * @since 16/07/2006
 */
class Industry {
	protected $id;
	protected $name;
	protected $societies_nb;
	
	public function __construct($id=NULL) {
		$this->id = $id;
	}
	/**
	 * Tente d'indentifier l'activité par nom.
	 * @return boolean
	 * @version 23/12/2016
	 */
	public function identifyFromName() {
		global $system;
		if (empty($this->name)) return false;
		$statement = $system->getPdo()->prepare('SELECT industry_id FROM industry WHERE industry_name=:name');
		$statement->bindValue(':name', $this->name, PDO::PARAM_STR);
		$statement->execute();
		$this->id = $statement->fetch(PDO::FETCH_COLUMN);
		return !empty($this->id);
	}
	/**
	 * Obtient la valeur d'un attribut de l'activité.
	 */
	protected function getAttribute($name) {
		if (isset($this->$name)) return $this->$name;
	}	
	/**
	 * Fixe la valeur d'un attribut de l'activité.
	 */
	protected function setAttribute($name, $value) {
		$value = trim($value);
		$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		return $this->{$name} = $value;
	}
	/**
	 * Fixe l'identifiant de l'activité.
	 */
	public function setId($input) {
		return $this->id = $input;
	}
	/**
	 * Obtient l'identifiant de l'activité.
	 */
	public function getId()	{
		return $this->id;
	}
	/**
	 * Fixe le nom de l'activité.
	 */
	public function setName($input)	{
		if (!empty($input)) $this->name = $input;
	}
	/**
	 * Obtient le nom de l'activité.
	 */
	public function getName() {
		return isset($this->name) ? $this->name : NULL;
	}
	/**
	 * Obtient le nombre de sociétés en activité.
	 */
	public function getSocietiesNb() {
		return isset($this->societies_nb) ? $this->societies_nb : NULL;
	}
	/**
	 * Associe les sociétés exerçant l'activité courante à une autre activité.
	 * @since 19/08/2006
	 * @version 23/12/2013
	 */
	public function transferSocieties($targetIndustry) {
		global $system;
		try {
			if ( ! is_a($targetIndustry, 'Industry') || ! $targetIndustry->getId() || empty($this->id) ) return false;
			
			// seules les sociétés qui ne sont pas déjà associées à l'activité seront transférées
			
			$statement = $system->getPdo()->prepare('SELECT society_id FROM society_industry WHERE industry_id=:targetIndustry_id');
			$statement->bindValue(':targetIndustry_id', $targetIndustry->getId(), PDO::PARAM_INT);
			$statement->execute();
			$alreadyInIndustry = $statement->fetchAll(PDO::FETCH_COLUMN);
			
			$vars = array();
			for($i=0; $i<count($alreadyInIndustry); $i++) {
				$vars[':id'.$i] = $alreadyInIndustry[$i];
			}

			$sql = 'UPDATE society_industry SET industry_id=:targetIndustry_id WHERE industry_id=:id AND society_id NOT IN ('.implode(',', array_keys($vars)).')';
			$statement = $system->getPdo()->prepare($sql);
			$statement->bindValue(':targetIndustry_id', $targetIndustry->getId(), PDO::PARAM_INT);
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			foreach ($vars as $key=>$value) {
				$statement->bindValue($key, $value, PDO::PARAM_INT);	
			}
			return $statement->execute();
		} catch (Exception $e) {
			System::reportException($e);
			exit;
		}
	}
	/**
	 * Fournit un lien vers l'écran présentant la liste des sociétés exerçant l'activité.
	 * @since 19/08/2006
	 */
	public function getHtmlLink() {
		return '<a href="societies_list.php?society_newsearch=1&amp;industry_id='.$this->id.'">'.$this->name.'</a>';
	}
	/**
	 * Fixe les attributs de l'activité à partir d'un tableau aux clefs normalisées.
	 * @version 23/12/2016
	 */
	public function feed($array=NULL, $prefix='industry_') {
		global $system;
		if (is_null($array)) {
			// si aucune donnée transmise on puise dans la base de données
			$statement = $system->getPdo()->prepare('SELECT * FROM industry WHERE industry_id=:id');
			$statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			return is_array($row) ? $this->feed($row) : false;
		} else {
			foreach ($array as $clé=>$valeur) {
				if (is_null($valeur)) continue;
				if (isset($prefix)) {
					//	on ne traite que les clés avec le préfixe spécifié
					if (strcmp(iconv_substr($clé, 0, iconv_strlen($prefix)), $prefix)!=0) continue;
					//	on retire le préfixe
					$clé = iconv_substr($clé, iconv_strlen($prefix));
				}
				$this->setAttribute($clé, $valeur);
			}
			return true;
		}
	}
	/**
	 * Supprime définitivement l'activité.
	 * @return boolean
	 * @since 19/08/2006
	 * @version 23/12/2016
	 */
	public function delete() {
		global $system;
		if (empty($this->id)) return false;
		$statement = $system->getPdo()->prepare('DELETE FROM industry WHERE industry_id=:id');
		$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		return $statement->execute();
	}
	/**
	 * Enregistre les données de l'activité en base de données.
	 * @version 23/12/2016
	 */
	public function toDB()	{
		global $system;
		
		$new = empty($this->id);
		
		$settings = array();
		
		if (isset($this->name)) {
			$settings[] = 'industry_name=:name';
		}
		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql.= ' industry SET '.implode(', ',$settings);
		
		if (!$new) {
			$sql.= ' WHERE industry_id=:id';
		}
		
		$statement = $system->getPdo()->prepare($sql);

		if (isset($this->name)) {
			$statement->bindValue(':name', $this->name, PDO::PARAM_STR);
		}
		
		$result = $statement->execute();
		
		if ($new) {
			$this->id = $system->getPdo()->lastInsertId();
		}
		
		return $result;
	}
}
?>