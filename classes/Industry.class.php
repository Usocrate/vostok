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
	 */
	public function identifyFromName()
	{
		$sql = 'SELECT industry_id FROM industry WHERE industry_name="'.mysql_real_escape_string($this->name).'"';
		$rowset = mysql_query($sql);
		$row = mysql_fetch_assoc($rowset);
		if ($row) {
			$this->id = $row['industry_id'];
			return true;
		}
		return false;
	}
	/**
	 * Obtient la valeur d'un attribut de l'activité.
	 */
	protected function getAttribute($name)
	{
		if (isset($this->$name)) return $this->$name;
	}	
	/**
	 * Fixe la valeur d'un attribut de l'activité.
	 */
	protected function setAttribute($name, $value)
	{
		$value = trim($value);
		$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		return $this->{$name} = $value;
	}
	/**
	 * Fixe l'identifiant de l'activité.
	 */
	public function setId($input)
	{
		return $this->setAttribute('id', $input);
	}
	/**
	 * Obtient l'identifiant de l'activité.
	 */
	public function getId()
	{
		return $this->getAttribute('id');
	}
	/**
	 * Fixe le nom de l'activité.
	 */
	public function setName($input)
	{
		if (!empty($input)) $this->name = $input;
	}
	/**
	 * Obtient le nom de l'activité.
	 */
	public function getName()
	{
		return isset($this->name) ? $this->name : NULL;
	}
	/**
	 * Obtient le nombre de sociétés en activité.
	 */
	public function getSocietiesNb()
	{
		return isset($this->societies_nb) ? $this->societies_nb : NULL;
	}
	/**
	 * Associe les sociétés exerçant l'activité courante à une autre activité.
	 * @since 19/08/2006
	 * @todo vérifier que la société n'est n'exerce pas déjà l'activité
	 */
	public function transferSocieties($industry)
	{
		if (!is_a($industry, 'Industry') || !$industry->getId() || empty($this->id)) return false;
		$sql = 'UPDATE society_industry SET industry_id='.$industry->getId();
		$sql.= ' WHERE industry_id='.$this->id;
		return mysql_query($sql);
	}		
	/**
	 * Fournit un lien vers l'écran présentant la liste des sociétés exerçant l'activité.
	 * @since 19/08/2006
	 */
	public function getHtmlLink()
	{
		return '<a href="societies_list.php?society_newsearch=1&amp;industry_id='.$this->id.'">'.$this->name.'</a>';
	}
	/**
	 * Fixe les attributs de l'activité à partir d'un tableau aux clefs normalisées.
	 */
	public function feed($array=NULL, $prefix='industry_')
	{
		if (is_null($array)) {
			// si aucune donnée transmise on puise dans la base de données
			$row = $this->getRow();
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
				//echo $clé.': '.$valeur.'<br />';
				$this->setAttribute($clé, $valeur);
			}
			return true;
		}
	}
	/**
	 * Obtient les données enregistrées en base de données.
	 * @return resource
	 */
	public function getRow()
	{
		if (!empty($this->id)) {
			$sql = 'SELECT * FROM industry WHERE industry_id='.$this->id;
			$rowset = mysql_query($sql);
			$row = mysql_fetch_assoc($rowset);
			mysql_free_result($rowset);
			return $row;
		}
		return NULL;
	}
	/**
	 * Efface l'enregistrement de l'activité en base de données.
	 * @return boolean
	 * @since 19/08/2006
	 */
	protected function deleteRow()
	{
		if (empty($this->id)) return false;
		$sql = 'DELETE FROM industry';
		$sql.= ' WHERE industry_id='.$this->id;
		return mysql_query($sql);	
	}
	/**
	 * Supprime définitivement l'activité.
	 * @return boolean
	 * @since 19/08/2006
	 */
	public function delete()
	{
		return $this->deleteRow();
	}
	/**
	 * Enregistre les données de l'activité en base de données.
	 */
	public function toDB()
	{
		$new = empty($this->id);
		$settings = array();
		if (isset($this->name)) {
			$settings[] = 'industry_name="'.mysql_real_escape_string($this->name).'"';
		}
		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql.= ' industry SET ';
		$sql.= implode(', ',$settings);
		if (!$new) {
			$sql.= ' WHERE industry_id='.$this->id;
		}
		$result = mysql_query($sql);
		if ($new) {
			$this->id = mysql_insert_id();
		}
		return $result;
	}
}
?>