<?php
/**
 * @since 28/01/2006
 * @package usocrate.vostok
 * @author Florent Chanavat
 */
class Event {
	public $id;
	public $society;
	public $datetime;
	public $user;
	public $user_position;
	public $type;
	public $media;
	public $comment;
	public $involvements;

	public function __construct($id=NULL)	{
		$this->id = $id;
	}
	/**
	 * Obtient les enregistrements des implications.
	 * @return resource
	 */
	private function getInvolvementsRowset($criterias=NULL, $sort_key='timestamp', $sort_order='DESC', $offset=0, $row_count=NULL, $tablesToJoin=NULL)	{
		if (!isset($this->id)) {
			return false;
		}
		$sql = 'SELECT * FROM event_involvement WHERE event_id='.$this->id;
		return mysql_query($sql);
	}
	/**
	 * Indique si l'individu passé en paramètre est impliqué dans l'évènement.
	 * @param $individual
	 * @return EventInvolvement
	 */
	private function hasInvolvement(Individual $individual) {
		if (!isset($this->involvements)) {
			$this->getInvolvements();
		}
	}
	/**
	 * Obtient les implications individuelles.
	 * @return EventInvolvementCollection
	 */
	public function &getInvolvements() {
		if (!isset($this->involvements)) {
			$dataset = $this->getInvolvementsRowset();
			if ($dataset !== false) {
				$this->involvements = new EventInvolvementCollection($dataset);
			} else {
				$this->involvements = new EventInvolvementCollection();
			}
		}
		return $this->involvements;
	}
	public function getIndividualInvolvement() {

	}
	/**
	 * Obtient l'ensemble d'individus impliqués dans l'évènement.
	 *
	 * @return IndividualCollection
	 */
	public function getInvolvedIndividuals() {
		try {
			return $this->getInvolvements()->getIndividuals();
			//	$criterias = array();
			//	$criterias[] = 't1.event_id='.$this->id;
			//	$sql = 'SELECT t2.*';
			//	$sql.= ' FROM event_involvement AS t1';
			//	$sql.= ' LEFT JOIN individual AS t2 USING(individual_id)';
			//	$sql.= ' WHERE '.implode(' AND ', $criterias);
			//	$sql.= ' ORDER BY t2.individual_lastName, t2.individual_firstName';
			//	$result = mysql_query($sql);
			//	return new IndividualCollection($result);
		}
		catch (Exception $e) {
			trigger_error(__METHOD__.' : '.$e->getMessage());
		}
	}
	/**
	 * Fixe la valeur d'un attribut.
	 */
	public function setAttribute($name, $value)	{
		$value = trim($value);
		//$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		return $this->{$name} = $value;
	}
	/**
	 * @return int
	 */
	public function getId() {
		return isset($this->id) ? $this->id : NULL;
	}
	/**
	 * Obtient un label permettant d'indentifier l'évènement.
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->getType().' du '.date("d/m/Y", ToolBox::mktimeFromMySqlDatetime($this->getDatetime()));
	}
	public function getName() {
		return $this->getLabel();
	}
	/**
	 * Indique si l'évènement est identifié (comme étant enregistré en base de données).
	 *
	 * @return bool
	 */
	public function hasId() {
		return isset($this->id);
	}
	public function getAttribute($name)	{
		return isset($this->$name) ? $this->$name : NULL;
	}
	public function setDatetime($input)	{
		if ($input) {
			$this->datetime = $input;
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Obtient la date de l'évènement si elle est connue.
	 *
	 * @return string
	 */
	public function getDatetime()	{
		if (!isset($this->datetime) && $this->hasId()) {
			$dataset = $this->getDataFromBase(array('datetime'));
			if (isset($dataset['datetime'])) {
				$this->datetime = $dataset['datetime'];
			}
		}
		return isset($this->datetime) ? $this->datetime : null;
	}
	/**
	 * Indique si la date de l'évènement est connue.
	 *
	 * @return bool
	 */
	public function hasDatetime() {
		return $this->getDatetime() !== null;
	}
	public function setUser($input)	{
		if (is_a ($input, 'User')) {
			$this->user = $input;
		} else {
			return false;
		}
	}
	public function getUser()	{
		return isset($this->user) ? $this->user : NULL;
	}
	public function getUserAttribute($name)	{
		return isset($this->user) ? $this->user->getAttribute($name) : NULL;
	}
	/**
	 * Obtient les valeurs posibles pour l'attribut user_position.
	 *
	 * @return array
	 */
	public static function getUserPositionsOptions() {
		return array('active', 'passive');
	}
	/**
	 * Fixe la position de l'utilisateur.
	 *
	 * @param $input
	 * @return bool
	 */
	public function setUserPosition($input)	{
		if (in_array($input, self::getUserPositionsOptions())) {
			$this->user_position = $input;
			return true;
		} else {
			return false;
		}
	}
	public function getUserPosition(){
		if (!isset($this->user_position) && $this->hasId()) {
			$dataset = $this->getDataFromBase(array('user_position'));
			if (isset($dataset['user_position'])) {
				$this->datetime = $dataset['user_position'];
			}
		}
		return isset($this->user_position) ? $this->user_position : null;
	}
	public function hasUserPosition() {
		return $this->getUserPosition() !== null;
	}
	/**
	 * Obtient les valeurs autorisées pour l'attribut media.
	 *
	 * @return array
	 */
	public static function getMediaOptions() {
		return array('email','rendez-vous','appel téléphonique','plateforme','autre');
	}
	public static function getMediaOptionsTags($selected=NULL) {
		$values = self::getMediaOptions();
		sort($values);
		$html = '';
		foreach ($values as $value) {
			$html.= '<option ';
			$html.= 'value="'.ToolBox::toHtml($value).'"';
			if (strcasecmp($value, $selected)==0) {
				$html.= ' selected="selected"';
			}
			$html.= '>';
			$html.= ToolBox::toHtml($value);
			$html.= '</option>';
		}
		return $html;
	}
	public function setSociety($input) {
		if (is_a($input,'Society')) {
			$this->society = $input;
		}
	}
	public function getSociety() {
		return isset($this->society) ? $this->society : NULL;
	}
	public function getSocietyAttribute($name) {
		return isset($this->society) ? $this->society->getAttribute($name) : NULL;
	}
	public static function getTypeOptions() {
		return array('offre d\'emploi', 'recommandation', 'prise de contact', 'relance', 'contractualisation', 'autre');
	}
	/**
	 * Obtient les types d'évènements possibles sous forme de balises html <option>.
	 *
	 * @param $selected
	 * @return string
	 */
	public static function getTypeOptionsTags($selected=NULL) {
		$options = self::getTypeOptions();
		$html = '';
		foreach ($options as $o) {
			$html.= '<option ';
			$html.= 'value="'.ToolBox::toHtml($o).'"';
			if (strcasecmp($o, $selected)==0) {
				$html.= ' selected="selected"';
			}
			$html.= '>';
			$html.= ToolBox::toHtml($o);
			$html.= '</option>';
		}
		return $html;
	}
	public function setType($input)	{
		return $this->setAttribute('type', $input);
	}
	public function getType(){
		if (!isset($this->type) && $this->hasId()) {
			$dataset = $this->getDataFromBase(array('type'));
			if (isset($dataset['type'])) {
				$this->datetime = $dataset['type'];
			}
		}
		return isset($this->type) ? $this->type : null;
	}
	public function hasType() {
		return $this->getType() !== null;
	}
	public function setMedia($input){
		return $this->media = $input;
	}
	public function getMedia(){
		if (!isset($this->media) && $this->hasId()) {
			$dataset = $this->getDataFromBase(array('media'));
			if (isset($dataset['media'])) {
				$this->datetime = $dataset['media'];
			}
		}
		return isset($this->media) ? $this->media : null;
	}
	public function hasMedia() {
		return $this->getMedia() !== null;
	}
	/**
	 * Fixe les attributs de l'évènement
	 * à partir d'un tableau de valeur dont les clefs sont normalisées.
	 */
	public function feed($array=null, $prefix=null)	{
		if (is_null($array)) {
			$dataset = $this->getDataFromBase();
			return $this->feed($dataset);
		} else {
			foreach ($array as $key=>$value) {
				if (is_null($value)) {
					continue;
				}
				if (isset($prefix)) {
					//	on ne traite que les clés avec le préfixe spécifié
					if (strcmp(iconv_substr($key, 0, iconv_strlen($prefix)), $prefix)!=0) {
						continue;
					}
					//	on retire le préfixe
					$key = iconv_substr($key, iconv_strlen($prefix));
				}
				switch ($key) {
					case 'id': $this->id = $value; break;
					case 'society_id':
						$this->society = new Society($value);
						$this->society->feed($array,'society_');
						break;
					case 'user_id': $this->user = new User($value); break;
					case 'user_position': $this->setUserPosition($value); break;
					case 'type': $this->setType($value); break;
					case 'media': $this->setMedia($value); break;
					case 'datetime': $this->setDatetime($value); break;
					case 'comment': $this->setComment($value); break;
				}
			}
			return true;
		}
	}
	/**
	 * @param $fields
	 * @return array
	 */
	private function getDataFromBase($fields = NULL) {
		try {
			if (is_null($this->id)) {
				throw new Exception(__METHOD__.' : l\'instance doit être identifiée.');
			}
			if (is_null($fields)) {
				$fields = array('*');
			}
			$sql = 'SELECT '.implode(',', $fields).' FROM event WHERE id='.$this->id;
			$result = mysql_query($sql);
			return mysql_fetch_assoc($result);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	/**
	 * Enregistre les données de l'instance en base de données.
	 */
	public function toDB() {
		$new = !$this->hasId();
		$settings = array();
		if ($this->society instanceof Society && $this->society->hasId()) {
			$settings[] = 'society_id='.$this->society->getId();
		}
		if (isset($_SESSION['user_id'])) {
			$settings[] = 'user_id='.$_SESSION['user_id'];
		}
		if (isset($this->datetime)) {
			$settings[] = 'datetime="'.$this->datetime.'"';

			if ($new) {
				$settings[] = ToolBox::mktimeFromMySqlDatetime($this->datetime)>time() ? 'warehouse="planning"' : 'warehouse="history"';
			}
		}
		if ($this->user_position) {
			$settings[] = 'user_position="'.$this->user_position.'"';
		}
		if (isset($this->type)) {
			$settings[] = 'type="'.$this->type.'"';
		}
		if (isset($this->media)) {
			$settings[] = 'media="'.$this->media.'"';
		}
		if (isset($this->comment)) {
			$settings[] = 'comment="'.mysql_real_escape_string($this->comment).'"';
		}

		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql.= ' event SET ';
		$sql.= implode(', ',$settings);

		if (!$new) {
			$sql.= ' WHERE id='.$this->id;
		}
		
		$result = mysql_query($sql);
		if ($new) {
			$this->id = mysql_insert_id();
		}
		return $result;
	}
	/**
	 * Supprime l'enregistrement de l'instance.
	 *
	 * @return unknown_type
	 */
	public function delete() {
		$sql = 'DELETE FROM event ';
		$sql.= 'WHERE id='.$this->id;
		return mysql_query($sql);
	}
	/**
	 * Fixe le commentaire associé à l'évènement.
	 */
	public function setComment($input) {
		if ($input) {
			$this->comment = $input;
		}
		return true;
	}
	/**
	 * Obtient le commentaire associé à l'évènement.
	 *
	 * @return string
	 */
	public function getComment(){
		if (!isset($this->comment) && $this->hasId()) {
			$dataset = $this->getDataFromBase(array('comment'));
			$this->setComment($dataset['comment']);
		}
		return $this->comment;
	}
}
?>